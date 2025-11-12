<?php
/**
 * Products management.
 *
 * Handles product CRUD operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/products
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Products class.
 */
class UC_Products {

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
     * Create a new product.
     *
     * @param array $data Product data.
     * @return int|false Product ID or false on failure.
     */
    public function create( $data ) {
        // Validate required fields
        if ( empty( $data['name'] ) ) {
            return false;
        }

        // Generate SKU if not provided
        if ( empty( $data['sku'] ) ) {
            $data['sku'] = UC_Utilities::generate_sku();
        }

        // Prepare data
        $product_data = array(
            'name'        => sanitize_text_field( $data['name'] ),
            'sku'         => sanitize_text_field( $data['sku'] ),
            'category_id' => isset( $data['category_id'] ) ? absint( $data['category_id'] ) : 0,
            'variables'   => isset( $data['variables'] ) ? wp_json_encode( $data['variables'] ) : '',
            'base_cost'   => isset( $data['base_cost'] ) ? floatval( $data['base_cost'] ) : 0.00,
            'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
            'status'      => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
        );

        $product_id = $this->database->insert( 'products', $product_data );

        if ( $product_id ) {
            do_action( 'u_commerce_product_created', $product_id, $product_data );
        }

        return $product_id;
    }

    /**
     * Update a product.
     *
     * @param int   $product_id Product ID.
     * @param array $data       Updated data.
     * @return bool True on success.
     */
    public function update( $product_id, $data ) {
        $product_data = array();

        if ( isset( $data['name'] ) ) {
            $product_data['name'] = sanitize_text_field( $data['name'] );
        }

        if ( isset( $data['sku'] ) ) {
            $product_data['sku'] = sanitize_text_field( $data['sku'] );
        }

        if ( isset( $data['category_id'] ) ) {
            $product_data['category_id'] = absint( $data['category_id'] );
        }

        if ( isset( $data['variables'] ) ) {
            $product_data['variables'] = wp_json_encode( $data['variables'] );
        }

        if ( isset( $data['base_cost'] ) ) {
            $product_data['base_cost'] = floatval( $data['base_cost'] );
        }

        if ( isset( $data['description'] ) ) {
            $product_data['description'] = sanitize_textarea_field( $data['description'] );
        }

        if ( isset( $data['status'] ) ) {
            $product_data['status'] = sanitize_text_field( $data['status'] );
        }

        $result = $this->database->update(
            'products',
            $product_data,
            array( 'id' => $product_id )
        );

        if ( $result !== false ) {
            do_action( 'u_commerce_product_updated', $product_id, $product_data );
        }

        return $result !== false;
    }

    /**
     * Delete a product.
     *
     * @param int $product_id Product ID.
     * @return bool True on success.
     */
    public function delete( $product_id ) {
        $result = $this->database->delete(
            'products',
            array( 'id' => $product_id )
        );

        if ( $result !== false ) {
            do_action( 'u_commerce_product_deleted', $product_id );
        }

        return $result !== false;
    }

    /**
     * Get a product by ID.
     *
     * @param int $product_id Product ID.
     * @return object|null Product object or null.
     */
    public function get( $product_id ) {
        $product = $this->database->get_row(
            'products',
            array( 'id' => $product_id )
        );

        if ( $product && ! empty( $product->variables ) ) {
            $product->variables = json_decode( $product->variables, true );
        }

        return apply_filters( 'u_commerce_product_data', $product, $product_id );
    }

    /**
     * Get product by SKU.
     *
     * @param string $sku Product SKU.
     * @return object|null Product object or null.
     */
    public function get_by_sku( $sku ) {
        return $this->database->get_row(
            'products',
            array( 'sku' => $sku )
        );
    }

    /**
     * Get all products.
     *
     * @param array $args Query arguments.
     * @return array Array of products.
     */
    public function get_all( $args = array() ) {
        $defaults = array(
            'where'    => array( 'status' => 'active' ),
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'limit'    => null,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        $products = $this->database->get_results( 'products', $args );

        // Decode variables JSON
        if ( $products ) {
            foreach ( $products as $product ) {
                if ( ! empty( $product->variables ) ) {
                    $product->variables = json_decode( $product->variables, true );
                }
            }
        }

        return $products;
    }

    /**
     * Search products.
     *
     * @param string $search_term Search term.
     * @param array  $args        Additional arguments.
     * @return array Array of products.
     */
    public function search( $search_term, $args = array() ) {
        global $wpdb;

        $table_name = $this->database->get_table( 'products' );
        $search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

        $where_clause = $wpdb->prepare(
            'WHERE (name LIKE %s OR sku LIKE %s) AND status = %s',
            $search_term,
            $search_term,
            'active'
        );

        $limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 10;
        $limit_clause = "LIMIT {$limit}";

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY name ASC {$limit_clause}";

        return $wpdb->get_results( $query );
    }

    /**
     * Get products by category.
     *
     * @param int   $category_id Category ID.
     * @param array $args        Query arguments.
     * @return array Array of products.
     */
    public function get_by_category( $category_id, $args = array() ) {
        $args['where'] = array( 'category_id' => $category_id, 'status' => 'active' );

        return $this->get_all( $args );
    }

    /**
     * Get product count.
     *
     * @param array $where WHERE conditions.
     * @return int Product count.
     */
    public function get_count( $where = array() ) {
        if ( empty( $where ) ) {
            $where = array( 'status' => 'active' );
        }

        return $this->database->get_count( 'products', $where );
    }

    /**
     * Check if SKU exists.
     *
     * @param string $sku        SKU to check.
     * @param int    $exclude_id Product ID to exclude.
     * @return bool True if SKU exists.
     */
    public function sku_exists( $sku, $exclude_id = 0 ) {
        global $wpdb;

        $table_name = $this->database->get_table( 'products' );

        if ( $exclude_id ) {
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE sku = %s AND id != %d",
                $sku,
                $exclude_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE sku = %s",
                $sku
            );
        }

        return $wpdb->get_var( $query ) > 0;
    }
}
