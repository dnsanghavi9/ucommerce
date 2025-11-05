<?php
/**
 * Stock manager.
 *
 * Handles complex stock operations and transfers.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/inventory
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Stock manager class.
 */
class UC_Stock_Manager {

    /**
     * Inventory handler.
     *
     * @var UC_Inventory
     */
    private $inventory;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->inventory = new UC_Inventory();
    }

    /**
     * Transfer stock between centers.
     *
     * @param int $product_id      Product ID.
     * @param int $from_center_id  Source center ID.
     * @param int $to_center_id    Destination center ID.
     * @param int $quantity        Quantity to transfer.
     * @return bool True on success.
     */
    public function transfer_stock( $product_id, $from_center_id, $to_center_id, $quantity ) {
        // Check if enough stock available
        if ( ! $this->inventory->is_in_stock( $product_id, $from_center_id, $quantity ) ) {
            return false;
        }

        // Deduct from source center
        $deducted = $this->inventory->update_quantity( $product_id, $from_center_id, -$quantity );

        if ( ! $deducted ) {
            return false;
        }

        // Add to destination center
        $added = $this->inventory->update_quantity( $product_id, $to_center_id, $quantity );

        if ( $added ) {
            do_action( 'u_commerce_stock_transferred', $product_id, $from_center_id, $to_center_id, $quantity );
        }

        return $added;
    }

    /**
     * Bulk update inventory from purchase.
     *
     * @param array $items     Purchase items.
     * @param int   $center_id Center ID.
     * @return bool True on success.
     */
    public function bulk_update_from_purchase( $items, $center_id ) {
        foreach ( $items as $item ) {
            $this->inventory->update_quantity(
                $item['product_id'],
                $center_id,
                $item['quantity']
            );
        }

        return true;
    }

    /**
     * Bulk deduct inventory from sale.
     *
     * @param array $items     Sales items.
     * @param int   $center_id Center ID.
     * @return bool True on success.
     */
    public function bulk_deduct_from_sale( $items, $center_id ) {
        foreach ( $items as $item ) {
            $this->inventory->update_quantity(
                $item['product_id'],
                $center_id,
                -$item['quantity']
            );
        }

        return true;
    }

    /**
     * Get stock summary.
     *
     * @param int $center_id Center ID (0 for all centers).
     * @return array Stock summary data.
     */
    public function get_stock_summary( $center_id = 0 ) {
        global $wpdb;

        $database = new UC_Database();
        $inventory_table = $database->get_table( 'inventory' );

        $where_center = $center_id ? $wpdb->prepare( 'WHERE center_id = %d', $center_id ) : '';

        $query = "
            SELECT
                SUM(quantity) as total_quantity,
                SUM(reserved_quantity) as total_reserved,
                SUM(quantity - reserved_quantity) as total_available,
                COUNT(DISTINCT product_id) as total_products
            FROM {$inventory_table}
            {$where_center}
        ";

        return $wpdb->get_row( $query, ARRAY_A );
    }

    /**
     * Get stock valuation.
     *
     * @param int $center_id Center ID (0 for all centers).
     * @return float Total stock value.
     */
    public function get_stock_valuation( $center_id = 0 ) {
        global $wpdb;

        $database = new UC_Database();
        $inventory_table = $database->get_table( 'inventory' );
        $products_table = $database->get_table( 'products' );

        $where_center = $center_id ? $wpdb->prepare( 'AND i.center_id = %d', $center_id ) : '';

        $query = "
            SELECT SUM(i.quantity * p.base_cost) as total_value
            FROM {$inventory_table} i
            INNER JOIN {$products_table} p ON i.product_id = p.id
            WHERE 1=1 {$where_center}
        ";

        return (float) $wpdb->get_var( $query );
    }
}
