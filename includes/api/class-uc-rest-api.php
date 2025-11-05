<?php
/**
 * REST API endpoints.
 *
 * Provides REST API endpoints for external integrations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/api
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST API class.
 */
class UC_REST_API {

    /**
     * API namespace.
     *
     * @var string
     */
    private $namespace = 'u-commerce/v1';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST API routes.
     */
    public function register_routes() {
        // Products endpoints
        register_rest_route(
            $this->namespace,
            '/products',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_products' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/products/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_product' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        // Inventory endpoints
        register_rest_route(
            $this->namespace,
            '/inventory/(?P<product_id>\d+)/(?P<center_id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_inventory' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        // Sales endpoints
        register_rest_route(
            $this->namespace,
            '/sales',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_sale' ),
                'permission_callback' => array( $this, 'check_sales_permission' ),
            )
        );

        // Barcode lookup
        register_rest_route(
            $this->namespace,
            '/barcode/(?P<barcode>[a-zA-Z0-9\-]+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'lookup_barcode' ),
                'permission_callback' => array( $this, 'check_permission' ),
            )
        );

        // Reports endpoint
        register_rest_route(
            $this->namespace,
            '/reports/dashboard',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dashboard_stats' ),
                'permission_callback' => array( $this, 'check_reports_permission' ),
            )
        );
    }

    /**
     * Check permission callback.
     *
     * @return bool True if user has permission.
     */
    public function check_permission() {
        return current_user_can( 'u_commerce_view_dashboard' );
    }

    /**
     * Check sales permission callback.
     *
     * @return bool True if user has permission.
     */
    public function check_sales_permission() {
        return current_user_can( 'u_commerce_create_sales' );
    }

    /**
     * Check reports permission callback.
     *
     * @return bool True if user has permission.
     */
    public function check_reports_permission() {
        return current_user_can( 'u_commerce_view_reports' );
    }

    /**
     * Get products.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_products( $request ) {
        $products = new UC_Products();

        $args = array(
            'limit' => $request->get_param( 'limit' ) ?? 10,
            'offset' => $request->get_param( 'offset' ) ?? 0,
        );

        $items = $products->get_all( $args );

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $items,
            ),
            200
        );
    }

    /**
     * Get single product.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_product( $request ) {
        $products = new UC_Products();
        $product = $products->get( $request['id'] );

        if ( ! $product ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Product not found.', 'u-commerce' ),
                ),
                404
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $product,
            ),
            200
        );
    }

    /**
     * Get inventory.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_inventory( $request ) {
        $inventory = new UC_Inventory();
        $data = $inventory->get( $request['product_id'], $request['center_id'] );

        if ( ! $data ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Inventory record not found.', 'u-commerce' ),
                ),
                404
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $data,
            ),
            200
        );
    }

    /**
     * Create sale.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function create_sale( $request ) {
        $data = $request->get_json_params();

        if ( empty( $data['items'] ) || empty( $data['center_id'] ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Missing required data.', 'u-commerce' ),
                ),
                400
            );
        }

        $sales = new UC_Sales_Bills();
        $bill_id = $sales->create( $data, $data['items'] );

        if ( ! $bill_id ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Failed to create sale.', 'u-commerce' ),
                ),
                500
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'bill_id' => $bill_id,
                'message' => __( 'Sale created successfully.', 'u-commerce' ),
            ),
            201
        );
    }

    /**
     * Lookup barcode.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function lookup_barcode( $request ) {
        $product = UC_Barcode::get_product_by_barcode( $request['barcode'] );

        if ( ! $product ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Product not found for this barcode.', 'u-commerce' ),
                ),
                404
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $product,
            ),
            200
        );
    }

    /**
     * Get dashboard stats.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_dashboard_stats( $request ) {
        $center_id = $request->get_param( 'center_id' ) ?? 0;

        $reports = new UC_Reports();
        $stats = $reports->get_dashboard_stats( $center_id );

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $stats,
            ),
            200
        );
    }
}
