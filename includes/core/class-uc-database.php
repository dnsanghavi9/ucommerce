<?php
/**
 * Database operations handler.
 *
 * Provides a centralized database interface for all plugin operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database handler class.
 */
class UC_Database {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Table names.
     *
     * @var array
     */
    private $tables;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;

        $this->wpdb = $wpdb;

        // Initialize table names
        $this->tables = array(
            'categories'              => $wpdb->prefix . 'ucommerce_product_categories',
            'variables'               => $wpdb->prefix . 'ucommerce_product_variables',
            'category_variables'      => $wpdb->prefix . 'ucommerce_category_variables',
            'product_variable_values' => $wpdb->prefix . 'ucommerce_product_variable_values',
            'centers'                 => $wpdb->prefix . 'ucommerce_centers',
            'products'                => $wpdb->prefix . 'ucommerce_products',
            'inventory'               => $wpdb->prefix . 'ucommerce_inventory',
            'pricing'                 => $wpdb->prefix . 'ucommerce_pricing',
            'purchase_bills'          => $wpdb->prefix . 'ucommerce_purchase_bills',
            'purchase_items'          => $wpdb->prefix . 'ucommerce_purchase_items',
            'sales_bills'             => $wpdb->prefix . 'ucommerce_sales_bills',
            'sales_items'             => $wpdb->prefix . 'ucommerce_sales_items',
            'vendors'                 => $wpdb->prefix . 'ucommerce_vendors',
            'vendor_contacts'         => $wpdb->prefix . 'ucommerce_vendor_contacts',
            'customers'               => $wpdb->prefix . 'ucommerce_customers',
            'barcodes'                => $wpdb->prefix . 'ucommerce_barcodes',
        );
    }

    /**
     * Get table name.
     *
     * @param string $table Table key.
     * @return string Table name with prefix.
     */
    public function get_table( $table ) {
        return isset( $this->tables[ $table ] ) ? $this->tables[ $table ] : '';
    }

    /**
     * Insert a record.
     *
     * @param string $table Table key.
     * @param array  $data  Data to insert.
     * @param array  $format Data format.
     * @return int|false The number of rows inserted, or false on error.
     */
    public function insert( $table, $data, $format = null ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return false;
        }

        $result = $this->wpdb->insert( $table_name, $data, $format );

        return $result !== false ? $this->wpdb->insert_id : false;
    }

    /**
     * Update records.
     *
     * @param string $table  Table key.
     * @param array  $data   Data to update.
     * @param array  $where  WHERE clause.
     * @param array  $format Data format.
     * @param array  $where_format WHERE format.
     * @return int|false The number of rows updated, or false on error.
     */
    public function update( $table, $data, $where, $format = null, $where_format = null ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return false;
        }

        return $this->wpdb->update( $table_name, $data, $where, $format, $where_format );
    }

    /**
     * Delete records.
     *
     * @param string $table  Table key.
     * @param array  $where  WHERE clause.
     * @param array  $where_format WHERE format.
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete( $table, $where, $where_format = null ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return false;
        }

        return $this->wpdb->delete( $table_name, $where, $where_format );
    }

    /**
     * Get a single row.
     *
     * @param string $table Table key.
     * @param array  $where WHERE clause.
     * @param string $output Output type (OBJECT, ARRAY_A, ARRAY_N).
     * @return object|array|null Database row or null on failure.
     */
    public function get_row( $table, $where = array(), $output = OBJECT ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return null;
        }

        $where_clause = $this->build_where_clause( $where );
        $query        = "SELECT * FROM {$table_name} {$where_clause}";

        return $this->wpdb->get_row( $query, $output );
    }

    /**
     * Get multiple rows.
     *
     * @param string $table Table key.
     * @param array  $args  Query arguments.
     * @param string $output Output type (OBJECT, ARRAY_A, ARRAY_N).
     * @return array|null Database rows or null on failure.
     */
    public function get_results( $table, $args = array(), $output = OBJECT ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return null;
        }

        $defaults = array(
            'where'    => array(),
            'orderby'  => 'id',
            'order'    => 'DESC',
            'limit'    => null,
            'offset'   => null,
        );

        $args = wp_parse_args( $args, $defaults );

        $where_clause = $this->build_where_clause( $args['where'] );
        $order_clause = sprintf( 'ORDER BY %s %s', esc_sql( $args['orderby'] ), esc_sql( $args['order'] ) );
        $limit_clause = $args['limit'] ? sprintf( 'LIMIT %d', absint( $args['limit'] ) ) : '';
        $offset_clause = $args['offset'] ? sprintf( 'OFFSET %d', absint( $args['offset'] ) ) : '';

        $query = "SELECT * FROM {$table_name} {$where_clause} {$order_clause} {$limit_clause} {$offset_clause}";

        return $this->wpdb->get_results( $query, $output );
    }

    /**
     * Get count of rows.
     *
     * @param string $table Table key.
     * @param array  $where WHERE clause.
     * @return int Count of rows.
     */
    public function get_count( $table, $where = array() ) {
        $table_name = $this->get_table( $table );

        if ( ! $table_name ) {
            return 0;
        }

        $where_clause = $this->build_where_clause( $where );
        $query        = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";

        return (int) $this->wpdb->get_var( $query );
    }

    /**
     * Build WHERE clause from array.
     *
     * @param array $where WHERE conditions.
     * @return string WHERE clause.
     */
    private function build_where_clause( $where ) {
        if ( empty( $where ) ) {
            return '';
        }

        $conditions = array();

        foreach ( $where as $key => $value ) {
            if ( is_array( $value ) ) {
                // Handle IN clause
                $placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
                $conditions[] = $this->wpdb->prepare( "{$key} IN ({$placeholders})", $value );
            } elseif ( is_null( $value ) ) {
                $conditions[] = "{$key} IS NULL";
            } else {
                $conditions[] = $this->wpdb->prepare( "{$key} = %s", $value );
            }
        }

        return 'WHERE ' . implode( ' AND ', $conditions );
    }

    /**
     * Execute a custom query.
     *
     * @param string $query SQL query.
     * @return mixed Query result.
     */
    public function query( $query ) {
        return $this->wpdb->query( $query );
    }

    /**
     * Get last error.
     *
     * @return string Last error message.
     */
    public function get_last_error() {
        return $this->wpdb->last_error;
    }

    /**
     * Get last insert ID.
     *
     * @return int Last insert ID.
     */
    public function get_insert_id() {
        return $this->wpdb->insert_id;
    }
}
