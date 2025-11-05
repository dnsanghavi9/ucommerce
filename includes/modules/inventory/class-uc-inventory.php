<?php
/**
 * Inventory management.
 *
 * Handles inventory operations and tracking.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/inventory
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Inventory class.
 */
class UC_Inventory {

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
     * Update inventory quantity.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @param int $quantity   Quantity to add (can be negative).
     * @return bool True on success.
     */
    public function update_quantity( $product_id, $center_id, $quantity ) {
        $existing = $this->get( $product_id, $center_id );

        if ( $existing ) {
            // Update existing record
            $new_quantity = max( 0, $existing->quantity + $quantity );

            $result = $this->database->update(
                'inventory',
                array( 'quantity' => $new_quantity ),
                array(
                    'product_id' => $product_id,
                    'center_id'  => $center_id,
                ),
                array( '%d' ),
                array( '%d', '%d' )
            );
        } else {
            // Create new record
            $result = $this->database->insert(
                'inventory',
                array(
                    'product_id' => $product_id,
                    'center_id'  => $center_id,
                    'quantity'   => max( 0, $quantity ),
                ),
                array( '%d', '%d', '%d' )
            );
        }

        if ( $result !== false ) {
            do_action( 'u_commerce_inventory_updated', $product_id, $center_id, $quantity );

            // Check for low stock
            $this->check_low_stock( $product_id, $center_id );
        }

        return $result !== false;
    }

    /**
     * Set inventory quantity (absolute).
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @param int $quantity   New quantity.
     * @return bool True on success.
     */
    public function set_quantity( $product_id, $center_id, $quantity ) {
        $existing = $this->get( $product_id, $center_id );
        $quantity = max( 0, $quantity );

        if ( $existing ) {
            $result = $this->database->update(
                'inventory',
                array( 'quantity' => $quantity ),
                array(
                    'product_id' => $product_id,
                    'center_id'  => $center_id,
                ),
                array( '%d' ),
                array( '%d', '%d' )
            );
        } else {
            $result = $this->database->insert(
                'inventory',
                array(
                    'product_id' => $product_id,
                    'center_id'  => $center_id,
                    'quantity'   => $quantity,
                ),
                array( '%d', '%d', '%d' )
            );
        }

        if ( $result !== false ) {
            do_action( 'u_commerce_inventory_set', $product_id, $center_id, $quantity );
            $this->check_low_stock( $product_id, $center_id );
        }

        return $result !== false;
    }

    /**
     * Reserve inventory quantity.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @param int $quantity   Quantity to reserve.
     * @return bool True on success.
     */
    public function reserve_quantity( $product_id, $center_id, $quantity ) {
        $existing = $this->get( $product_id, $center_id );

        if ( ! $existing ) {
            return false;
        }

        $available = $existing->quantity - $existing->reserved_quantity;

        if ( $available < $quantity ) {
            return false; // Not enough available
        }

        $new_reserved = $existing->reserved_quantity + $quantity;

        return $this->database->update(
            'inventory',
            array( 'reserved_quantity' => $new_reserved ),
            array(
                'product_id' => $product_id,
                'center_id'  => $center_id,
            ),
            array( '%d' ),
            array( '%d', '%d' )
        ) !== false;
    }

    /**
     * Release reserved inventory.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @param int $quantity   Quantity to release.
     * @return bool True on success.
     */
    public function release_reserved( $product_id, $center_id, $quantity ) {
        $existing = $this->get( $product_id, $center_id );

        if ( ! $existing ) {
            return false;
        }

        $new_reserved = max( 0, $existing->reserved_quantity - $quantity );

        return $this->database->update(
            'inventory',
            array( 'reserved_quantity' => $new_reserved ),
            array(
                'product_id' => $product_id,
                'center_id'  => $center_id,
            ),
            array( '%d' ),
            array( '%d', '%d' )
        ) !== false;
    }

    /**
     * Get inventory record.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return object|null Inventory object or null.
     */
    public function get( $product_id, $center_id ) {
        return $this->database->get_row(
            'inventory',
            array(
                'product_id' => $product_id,
                'center_id'  => $center_id,
            )
        );
    }

    /**
     * Get inventory for product (all centers).
     *
     * @param int $product_id Product ID.
     * @return array Array of inventory records.
     */
    public function get_by_product( $product_id ) {
        return $this->database->get_results(
            'inventory',
            array(
                'where' => array( 'product_id' => $product_id ),
            )
        );
    }

    /**
     * Get inventory for center.
     *
     * @param int   $center_id Center ID.
     * @param array $args      Query arguments.
     * @return array Array of inventory records.
     */
    public function get_by_center( $center_id, $args = array() ) {
        $args['where'] = array( 'center_id' => $center_id );

        return $this->database->get_results( 'inventory', $args );
    }

    /**
     * Get available quantity.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return int Available quantity.
     */
    public function get_available_quantity( $product_id, $center_id ) {
        $inventory = $this->get( $product_id, $center_id );

        if ( ! $inventory ) {
            return 0;
        }

        return max( 0, $inventory->quantity - $inventory->reserved_quantity );
    }

    /**
     * Check if product is in stock.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @param int $quantity   Required quantity.
     * @return bool True if in stock.
     */
    public function is_in_stock( $product_id, $center_id, $quantity = 1 ) {
        $available = $this->get_available_quantity( $product_id, $center_id );

        return $available >= $quantity;
    }

    /**
     * Check for low stock and trigger alert.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     */
    private function check_low_stock( $product_id, $center_id ) {
        $threshold = UC_Utilities::get_setting( 'inventory.low_stock_threshold', 10 );
        $available = $this->get_available_quantity( $product_id, $center_id );

        if ( $available <= $threshold ) {
            do_action( 'u_commerce_low_stock_alert', $product_id, $center_id, $available );
        }
    }

    /**
     * Get low stock products.
     *
     * @param int $center_id Center ID (0 for all centers).
     * @return array Array of low stock products.
     */
    public function get_low_stock_products( $center_id = 0 ) {
        global $wpdb;

        $threshold = UC_Utilities::get_setting( 'inventory.low_stock_threshold', 10 );
        $inventory_table = $this->database->get_table( 'inventory' );
        $products_table = $this->database->get_table( 'products' );

        $where_center = $center_id ? $wpdb->prepare( 'AND i.center_id = %d', $center_id ) : '';

        $query = "
            SELECT i.*, p.name, p.sku
            FROM {$inventory_table} i
            INNER JOIN {$products_table} p ON i.product_id = p.id
            WHERE (i.quantity - i.reserved_quantity) <= %d
            {$where_center}
            ORDER BY (i.quantity - i.reserved_quantity) ASC
        ";

        return $wpdb->get_results( $wpdb->prepare( $query, $threshold ) );
    }
}
