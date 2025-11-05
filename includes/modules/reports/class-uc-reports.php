<?php
/**
 * Reports generation.
 *
 * Handles report generation and data aggregation.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/reports
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reports class.
 */
class UC_Reports {

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
     * Get sales report.
     *
     * @param array $args Filter arguments.
     * @return array Sales report data.
     */
    public function get_sales_report( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'start_date' => date( 'Y-m-01' ),
            'end_date'   => date( 'Y-m-d' ),
            'center_id'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $sales_bills = new UC_Sales_Bills();
        $summary = $sales_bills->get_summary( $args );

        // Get daily breakdown
        $sales_table = $this->database->get_table( 'sales_bills' );

        $where_clauses = array(
            $wpdb->prepare( 'created_at >= %s', $args['start_date'] ),
            $wpdb->prepare( 'created_at <= %s', $args['end_date'] . ' 23:59:59' ),
        );

        if ( $args['center_id'] ) {
            $where_clauses[] = $wpdb->prepare( 'center_id = %d', $args['center_id'] );
        }

        $where = implode( ' AND ', $where_clauses );

        $daily_sales = $wpdb->get_results(
            "SELECT
                DATE(created_at) as date,
                COUNT(*) as total_bills,
                SUM(total_amount) as total_amount
            FROM {$sales_table}
            WHERE {$where}
            GROUP BY DATE(created_at)
            ORDER BY date ASC"
        );

        return array(
            'summary'     => $summary,
            'daily_sales' => $daily_sales,
            'top_products' => $sales_bills->get_top_selling_products( $args ),
        );
    }

    /**
     * Get inventory report.
     *
     * @param int $center_id Center ID (0 for all centers).
     * @return array Inventory report data.
     */
    public function get_inventory_report( $center_id = 0 ) {
        global $wpdb;

        $inventory_table = $this->database->get_table( 'inventory' );
        $products_table = $this->database->get_table( 'products' );

        $where_center = $center_id ? $wpdb->prepare( 'AND i.center_id = %d', $center_id ) : '';

        $query = "
            SELECT
                i.*,
                p.name,
                p.sku,
                p.base_cost,
                (i.quantity - i.reserved_quantity) as available_quantity,
                (i.quantity * p.base_cost) as stock_value
            FROM {$inventory_table} i
            INNER JOIN {$products_table} p ON i.product_id = p.id
            WHERE 1=1 {$where_center}
            ORDER BY p.name ASC
        ";

        $inventory_data = $wpdb->get_results( $query );

        // Calculate totals
        $total_quantity = 0;
        $total_value = 0;

        foreach ( $inventory_data as $item ) {
            $total_quantity += $item->quantity;
            $total_value += $item->stock_value;
        }

        return array(
            'items'          => $inventory_data,
            'total_quantity' => $total_quantity,
            'total_value'    => $total_value,
        );
    }

    /**
     * Get profit report.
     *
     * @param array $args Filter arguments.
     * @return array Profit report data.
     */
    public function get_profit_report( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'start_date' => date( 'Y-m-01' ),
            'end_date'   => date( 'Y-m-d' ),
            'center_id'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $sales_items_table = $this->database->get_table( 'sales_items' );
        $sales_bills_table = $this->database->get_table( 'sales_bills' );
        $products_table = $this->database->get_table( 'products' );

        $where_clauses = array(
            $wpdb->prepare( 'sb.created_at >= %s', $args['start_date'] ),
            $wpdb->prepare( 'sb.created_at <= %s', $args['end_date'] . ' 23:59:59' ),
        );

        if ( $args['center_id'] ) {
            $where_clauses[] = $wpdb->prepare( 'sb.center_id = %d', $args['center_id'] );
        }

        $where = implode( ' AND ', $where_clauses );

        $query = "
            SELECT
                SUM(si.total_price) as total_revenue,
                SUM(si.quantity * p.base_cost) as total_cost,
                SUM(si.total_price - (si.quantity * p.base_cost)) as total_profit
            FROM {$sales_items_table} si
            INNER JOIN {$sales_bills_table} sb ON si.sales_bill_id = sb.id
            INNER JOIN {$products_table} p ON si.product_id = p.id
            WHERE {$where}
        ";

        $profit_data = $wpdb->get_row( $query, ARRAY_A );

        // Calculate profit margin
        $profit_margin = 0;
        if ( $profit_data['total_revenue'] > 0 ) {
            $profit_margin = ( $profit_data['total_profit'] / $profit_data['total_revenue'] ) * 100;
        }

        $profit_data['profit_margin'] = round( $profit_margin, 2 );

        return apply_filters( 'u_commerce_report_data', $profit_data, 'profit', $args );
    }

    /**
     * Get dashboard statistics.
     *
     * @param int $center_id Center ID (0 for all centers).
     * @return array Dashboard statistics.
     */
    public function get_dashboard_stats( $center_id = 0 ) {
        // Today's sales
        $today_sales = $this->get_sales_report(
            array(
                'start_date' => date( 'Y-m-d' ),
                'end_date'   => date( 'Y-m-d' ),
                'center_id'  => $center_id,
            )
        );

        // This month's sales
        $month_sales = $this->get_sales_report(
            array(
                'start_date' => date( 'Y-m-01' ),
                'end_date'   => date( 'Y-m-d' ),
                'center_id'  => $center_id,
            )
        );

        // Low stock items
        $inventory = new UC_Inventory();
        $low_stock = $inventory->get_low_stock_products( $center_id );

        // Product count
        $products = new UC_Products();
        $product_count = $products->get_count();

        return array(
            'today_sales'    => $today_sales['summary'],
            'month_sales'    => $month_sales['summary'],
            'low_stock_count' => count( $low_stock ),
            'product_count'  => $product_count,
        );
    }

    /**
     * Get center comparison report.
     *
     * @param array $args Filter arguments.
     * @return array Center comparison data.
     */
    public function get_center_comparison( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'start_date' => date( 'Y-m-01' ),
            'end_date'   => date( 'Y-m-d' ),
        );

        $args = wp_parse_args( $args, $defaults );

        $sales_table = $this->database->get_table( 'sales_bills' );
        $centers_table = $this->database->get_table( 'centers' );

        $query = $wpdb->prepare(
            "SELECT
                c.id,
                c.name,
                COUNT(s.id) as total_sales,
                SUM(s.total_amount) as total_revenue
            FROM {$centers_table} c
            LEFT JOIN {$sales_table} s ON c.id = s.center_id
                AND s.created_at >= %s
                AND s.created_at <= %s
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY total_revenue DESC",
            $args['start_date'],
            $args['end_date'] . ' 23:59:59'
        );

        return $wpdb->get_results( $query );
    }
}
