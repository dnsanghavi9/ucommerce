<?php
/**
 * Purchase bills management.
 *
 * Handles purchase bill operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/billing
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Purchase bills class.
 */
class UC_Purchase_Bills {

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
     * Create a new purchase bill.
     *
     * @param array $data Purchase bill data.
     * @param array $items Purchase items.
     * @return int|false Bill ID or false on failure.
     */
    public function create( $data, $items ) {
        if ( empty( $items ) || empty( $data['center_id'] ) ) {
            return false;
        }

        // Generate bill number
        $bill_number = isset( $data['bill_number'] ) ?
                      sanitize_text_field( $data['bill_number'] ) :
                      UC_Utilities::generate_bill_number( 'purchase' );

        // Calculate total
        $total_amount = $this->calculate_total( $items );

        // Prepare bill data
        $bill_data = array(
            'bill_number'  => $bill_number,
            'vendor_id'    => isset( $data['vendor_id'] ) ? absint( $data['vendor_id'] ) : 0,
            'center_id'    => absint( $data['center_id'] ),
            'bill_date'    => isset( $data['bill_date'] ) ? sanitize_text_field( $data['bill_date'] ) : current_time( 'mysql' ),
            'total_amount' => $total_amount,
            'status'       => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'completed',
            'notes'        => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
            'created_by'   => get_current_user_id(),
        );

        // Insert bill
        $bill_id = $this->database->insert( 'purchase_bills', $bill_data );

        if ( ! $bill_id ) {
            return false;
        }

        // Insert items
        $this->insert_items( $bill_id, $items );

        // Update inventory
        $inventory = new UC_Inventory();
        foreach ( $items as $item ) {
            $inventory->update_quantity(
                $item['product_id'],
                $data['center_id'],
                $item['quantity']
            );

            // Update base cost if provided
            if ( isset( $item['unit_cost'] ) && $item['unit_cost'] > 0 ) {
                $products = new UC_Products();
                $products->update(
                    $item['product_id'],
                    array( 'base_cost' => $item['unit_cost'] )
                );
            }
        }

        do_action( 'u_commerce_after_purchase_bill_created', $bill_id, $bill_data, $items );

        return $bill_id;
    }

    /**
     * Update a purchase bill.
     *
     * @param int   $bill_id Bill ID.
     * @param array $data    Updated data.
     * @return bool True on success.
     */
    public function update( $bill_id, $data ) {
        $bill_data = array();

        if ( isset( $data['vendor_id'] ) ) {
            $bill_data['vendor_id'] = absint( $data['vendor_id'] );
        }

        if ( isset( $data['bill_date'] ) ) {
            $bill_data['bill_date'] = sanitize_text_field( $data['bill_date'] );
        }

        if ( isset( $data['status'] ) ) {
            $bill_data['status'] = sanitize_text_field( $data['status'] );
        }

        if ( isset( $data['notes'] ) ) {
            $bill_data['notes'] = sanitize_textarea_field( $data['notes'] );
        }

        return $this->database->update(
            'purchase_bills',
            $bill_data,
            array( 'id' => $bill_id )
        ) !== false;
    }

    /**
     * Delete a purchase bill.
     *
     * @param int $bill_id Bill ID.
     * @return bool True on success.
     */
    public function delete( $bill_id ) {
        // Delete items
        $this->database->delete(
            'purchase_items',
            array( 'purchase_bill_id' => $bill_id )
        );

        // Delete bill
        return $this->database->delete(
            'purchase_bills',
            array( 'id' => $bill_id )
        ) !== false;
    }

    /**
     * Get a purchase bill by ID.
     *
     * @param int $bill_id Bill ID.
     * @return object|null Bill object or null.
     */
    public function get( $bill_id ) {
        $bill = $this->database->get_row(
            'purchase_bills',
            array( 'id' => $bill_id )
        );

        if ( $bill ) {
            $bill->items = $this->get_items( $bill_id );
        }

        return $bill;
    }

    /**
     * Get all purchase bills.
     *
     * @param array $args Query arguments.
     * @return array Array of bills.
     */
    public function get_all( $args = array() ) {
        $defaults = array(
            'where'    => array(),
            'orderby'  => 'bill_date',
            'order'    => 'DESC',
            'limit'    => 20,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        return $this->database->get_results( 'purchase_bills', $args );
    }

    /**
     * Get purchase bill items.
     *
     * @param int $bill_id Bill ID.
     * @return array Array of items.
     */
    public function get_items( $bill_id ) {
        global $wpdb;

        $items_table = $this->database->get_table( 'purchase_items' );
        $products_table = $this->database->get_table( 'products' );

        $query = $wpdb->prepare(
            "SELECT i.*, p.name as product_name, p.sku
            FROM {$items_table} i
            INNER JOIN {$products_table} p ON i.product_id = p.id
            WHERE i.purchase_bill_id = %d",
            $bill_id
        );

        return $wpdb->get_results( $query );
    }

    /**
     * Insert purchase items.
     *
     * @param int   $bill_id Bill ID.
     * @param array $items   Items array.
     * @return bool True on success.
     */
    private function insert_items( $bill_id, $items ) {
        foreach ( $items as $item ) {
            $item_data = array(
                'purchase_bill_id' => $bill_id,
                'product_id'       => absint( $item['product_id'] ),
                'quantity'         => absint( $item['quantity'] ),
                'unit_cost'        => floatval( $item['unit_cost'] ),
                'total_cost'       => floatval( $item['quantity'] * $item['unit_cost'] ),
            );

            $this->database->insert( 'purchase_items', $item_data );
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
            $total += floatval( $item['quantity'] * $item['unit_cost'] );
        }

        return $total;
    }

    /**
     * Get purchase summary.
     *
     * @param array $args Filter arguments.
     * @return array Summary data.
     */
    public function get_summary( $args = array() ) {
        global $wpdb;

        $table = $this->database->get_table( 'purchase_bills' );

        $where_clauses = array( '1=1' );

        if ( isset( $args['start_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'bill_date >= %s', $args['start_date'] );
        }

        if ( isset( $args['end_date'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'bill_date <= %s', $args['end_date'] );
        }

        if ( isset( $args['center_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( 'center_id = %d', $args['center_id'] );
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
}
