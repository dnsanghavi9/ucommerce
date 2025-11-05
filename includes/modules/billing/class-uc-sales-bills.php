<?php
/**
 * Sales bills management.
 *
 * Handles sales bill operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/billing
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sales bills class.
 */
class UC_Sales_Bills {

    /**
     * Database handler.
     *
     * @var UC_Database
     */
    private $database;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->database = new UC_Database();
    }

    /**
     * Create a new sales bill.
     *
     * @param array $data  Sales bill data.
     * @param array $items Sales items.
     * @return int|false Bill ID or false on failure.
     */
    public function create( $data, $items ) {
        if ( empty( $items ) || empty( $data['center_id'] ) ) {
            return false;
        }

        // Check inventory availability
        $inventory = new UC_Inventory();
        foreach ( $items as $item ) {
            if ( ! $inventory->is_in_stock( $item['product_id'], $data['center_id'], $item['quantity'] ) ) {
                return false; // Insufficient stock
            }
        }

        // Generate bill number
        $bill_number = isset( $data['bill_number'] ) ?
                      sanitize_text_field( $data['bill_number'] ) :
                      UC_Utilities::generate_bill_number( 'sales' );

        // Calculate total
        $total_amount = $this->calculate_total( $items );

        // Prepare bill data
        $bill_data = array(
            'bill_number'    => $bill_number,
            'customer_id'    => isset( $data['customer_id'] ) ? absint( $data['customer_id'] ) : 0,
            'center_id'      => absint( $data['center_id'] ),
            'total_amount'   => $total_amount,
            'payment_status' => isset( $data['payment_status'] ) ? sanitize_text_field( $data['payment_status'] ) : 'paid',
            'payment_method' => isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : 'cash',
            'notes'          => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
            'created_by'     => get_current_user_id(),
        );

        // Insert bill
        $bill_id = $this->database->insert( 'sales_bills', $bill_data );

        if ( ! $bill_id ) {
            return false;
        }

        // Insert items
        $this->insert_items( $bill_id, $items );

        // Deduct inventory
        foreach ( $items as $item ) {
            $inventory->update_quantity(
                $item['product_id'],
                $data['center_id'],
                -$item['quantity']
            );
        }

        do_action( 'u_commerce_after_sales_bill_created', $bill_id, $bill_data, $items );

        return $bill_id;
    }

    /**
     * Update a sales bill.
     *
     * @param int   $bill_id Bill ID.
     * @param array $data    Updated data.
     * @return bool True on success.
     */
    public function update( $bill_id, $data ) {
        $bill_data = array();

        if ( isset( $data['customer_id'] ) ) {
            $bill_data['customer_id'] = absint( $data['customer_id'] );
        }

        if ( isset( $data['payment_status'] ) ) {
            $bill_data['payment_status'] = sanitize_text_field( $data['payment_status'] );
        }

        if ( isset( $data['payment_method'] ) ) {
            $bill_data['payment_method'] = sanitize_text_field( $data['payment_method'] );
        }

        if ( isset( $data['notes'] ) ) {
            $bill_data['notes'] = sanitize_textarea_field( $data['notes'] );
        }

        return $this->database->update(
            'sales_bills',
            $bill_data,
            array( 'id' => $bill_id )
        ) !== false;
    }

    /**
     * Delete a sales bill.
     *
     * @param int $bill_id Bill ID.
     * @return bool True on success.
     */
    public function delete( $bill_id ) {
        // Get bill and items first to restore inventory
        $bill = $this->get( $bill_id );

        if ( $bill && $bill->items ) {
            $inventory = new UC_Inventory();
            foreach ( $bill->items as $item ) {
                // Restore inventory
                $inventory->update_quantity(
                    $item->product_id,
                    $bill->center_id,
                    $item->quantity
                );
            }
        }

        // Delete items
        $this->database->delete(
            'sales_items',
            array( 'sales_bill_id' => $bill_id )
        );

        // Delete bill
        return $this->database->delete(
            'sales_bills',
            array( 'id' => $bill_id )
        ) !== false;
    }

    /**
     * Get a sales bill by ID.
     *
     * @param int $bill_id Bill ID.
     * @return object|null Bill object or null.
     */
    public function get( $bill_id ) {
        $bill = $this->database->get_row(
            'sales_bills',
            array( 'id' => $bill_id )
        );

        if ( $bill ) {
            $bill->items = $this->get_items( $bill_id );
        }

        return $bill;
    }

    /**
     * Get all sales bills.
     *
     * @param array $args Query arguments.
     * @return array Array of bills.
     */
    public function get_all( $args = array() ) {
        $defaults = array(
            'where'    => array(),
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'limit'    => 20,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        return $this->database->get_results( 'sales_bills', $args );
    }

    /**
     * Get sales bill items.
     *
     * @param int $bill_id Bill ID.
     * @return array Array of items.
     */
    public function get_items( $bill_id ) {
        global $wpdb;

        $items_table = $this->database->get_table( 'sales_items' );
        $products_table = $this->database->get_table( 'products' );

        $query = $wpdb->prepare(
            "SELECT i.*, p.name as product_name, p.sku
            FROM {$items_table} i
            INNER JOIN {$products_table} p ON i.product_id = p.id
            WHERE i.sales_bill_id = %d",
            $bill_id
        );

        return $wpdb->get_results( $query );
    }

    /**
     * Insert sales items.
     *
     * @param int   $bill_id Bill ID.
     * @param array $items   Items array.
     * @return bool True on success.
     */
    private function insert_items( $bill_id, $items ) {
        foreach ( $items as $item ) {
            $item_data = array(
                'sales_bill_id' => $bill_id,
                'product_id'    => absint( $item['product_id'] ),
                'quantity'      => absint( $item['quantity'] ),
                'unit_price'    => floatval( $item['unit_price'] ),
                'total_price'   => floatval( $item['quantity'] * $item['unit_price'] ),
                'barcode'       => isset( $item['barcode'] ) ? sanitize_text_field( $item['barcode'] ) : '',
            );

            $this->database->insert( 'sales_items', $item_data );
        }

        return true;
    }

    /**
     * Calculate total amount.
     *
     * @param array $items Items array.
     * @return float Total amount.
     */
    private function calculate_total( $items ) {
        $total = 0;

        foreach ( $items as $item ) {
            $total += floatval( $item['quantity'] * $item['unit_price'] );
        }

        return apply_filters( 'u_commerce_bill_total', $total, $items );
    }

    /**
     * Get sales summary.
     *
     * @param array $args Filter arguments.
     * @return array Summary data.
     */
    public function get_summary( $args = array() ) {
        global $wpdb;

        $table = $this->database->get_table( 'sales_bills' );

        $where_clauses = array( '1=1' );

        if ( isset( $args['start_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'created_at >= %s', $args['start_date'] );
        }

        if ( isset( $args['end_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'created_at <= %s', $args['end_date'] );
        }

        if ( isset( $args['center_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'center_id = %d', $args['center_id'] );
        }

        if ( isset( $args['payment_status'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'payment_status = %s', $args['payment_status'] );
        }

        $where = implode( ' AND ', $where_clauses );

        $query = "
            SELECT
                COUNT(*) as total_bills,
                SUM(total_amount) as total_amount,
                AVG(total_amount) as average_amount
            FROM {$table}
            WHERE {$where}
        ";

        return $wpdb->get_row( $query, ARRAY_A );
    }

    /**
     * Get top selling products.
     *
     * @param array $args Filter arguments.
     * @return array Top selling products.
     */
    public function get_top_selling_products( $args = array() ) {
        global $wpdb;

        $sales_items_table = $this->database->get_table( 'sales_items' );
        $products_table = $this->database->get_table( 'products' );
        $sales_bills_table = $this->database->get_table( 'sales_bills' );

        $limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 10;

        $where_clauses = array( '1=1' );

        if ( isset( $args['start_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'sb.created_at >= %s', $args['start_date'] );
        }

        if ( isset( $args['end_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'sb.created_at <= %s', $args['end_date'] );
        }

        $where = implode( ' AND ', $where_clauses );

        $query = "
            SELECT
                si.product_id,
                p.name,
                p.sku,
                SUM(si.quantity) as total_quantity,
                SUM(si.total_price) as total_revenue
            FROM {$sales_items_table} si
            INNER JOIN {$products_table} p ON si.product_id = p.id
            INNER JOIN {$sales_bills_table} sb ON si.sales_bill_id = sb.id
            WHERE {$where}
            GROUP BY si.product_id
            ORDER BY total_quantity DESC
            LIMIT {$limit}
        ";

        return $wpdb->get_results( $query );
    }
}
