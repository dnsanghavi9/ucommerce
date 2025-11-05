<?php
/**
 * Admin menu handler.
 *
 * Creates and manages the admin menu structure.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu class.
 */
class UC_Admin_Menu {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
    }

    /**
     * Add menu pages.
     */
    public function add_menu_pages() {
        // Main menu
        add_menu_page(
            __( 'U-Commerce', 'u-commerce' ),
            __( 'U-Commerce', 'u-commerce' ),
            'u_commerce_view_dashboard',
            'u-commerce',
            array( $this, 'render_dashboard_page' ),
            'dashicons-cart',
            30
        );

        // Dashboard (duplicate of main menu)
        add_submenu_page(
            'u-commerce',
            __( 'Dashboard', 'u-commerce' ),
            __( 'Dashboard', 'u-commerce' ),
            'u_commerce_view_dashboard',
            'u-commerce',
            array( $this, 'render_dashboard_page' )
        );

        // Products
        add_submenu_page(
            'u-commerce',
            __( 'Products', 'u-commerce' ),
            __( 'Products', 'u-commerce' ),
            'u_commerce_manage_products',
            'u-commerce-products',
            array( $this, 'render_products_page' )
        );

        // Categories
        add_submenu_page(
            'u-commerce',
            __( 'Categories', 'u-commerce' ),
            __( 'Categories', 'u-commerce' ),
            'u_commerce_manage_categories',
            'u-commerce-categories',
            array( $this, 'render_categories_page' )
        );

        // Inventory
        add_submenu_page(
            'u-commerce',
            __( 'Inventory', 'u-commerce' ),
            __( 'Inventory', 'u-commerce' ),
            'u_commerce_manage_inventory',
            'u-commerce-inventory',
            array( $this, 'render_inventory_page' )
        );

        // Purchase Bills
        add_submenu_page(
            'u-commerce',
            __( 'Purchase Bills', 'u-commerce' ),
            __( 'Purchase Bills', 'u-commerce' ),
            'u_commerce_add_purchase_bills',
            'u-commerce-purchase-bills',
            array( $this, 'render_purchase_bills_page' )
        );

        // Sales Bills
        add_submenu_page(
            'u-commerce',
            __( 'Sales Bills', 'u-commerce' ),
            __( 'Sales Bills', 'u-commerce' ),
            'u_commerce_create_sales',
            'u-commerce-sales-bills',
            array( $this, 'render_sales_bills_page' )
        );

        // Centers
        add_submenu_page(
            'u-commerce',
            __( 'Centers', 'u-commerce' ),
            __( 'Centers', 'u-commerce' ),
            'u_commerce_manage_centers',
            'u-commerce-centers',
            array( $this, 'render_centers_page' )
        );

        // Vendors
        add_submenu_page(
            'u-commerce',
            __( 'Vendors', 'u-commerce' ),
            __( 'Vendors', 'u-commerce' ),
            'u_commerce_manage_vendors',
            'u-commerce-vendors',
            array( $this, 'render_vendors_page' )
        );

        // Customers
        add_submenu_page(
            'u-commerce',
            __( 'Customers', 'u-commerce' ),
            __( 'Customers', 'u-commerce' ),
            'u_commerce_manage_customers',
            'u-commerce-customers',
            array( $this, 'render_customers_page' )
        );

        // Reports
        add_submenu_page(
            'u-commerce',
            __( 'Reports', 'u-commerce' ),
            __( 'Reports', 'u-commerce' ),
            'u_commerce_view_reports',
            'u-commerce-reports',
            array( $this, 'render_reports_page' )
        );

        // Settings
        add_submenu_page(
            'u-commerce',
            __( 'Settings', 'u-commerce' ),
            __( 'Settings', 'u-commerce' ),
            'u_commerce_manage_settings',
            'u-commerce-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
        $this->render_page( 'dashboard' );
    }

    /**
     * Render products page.
     */
    public function render_products_page() {
        $this->render_page( 'products' );
    }

    /**
     * Render categories page.
     */
    public function render_categories_page() {
        $this->render_page( 'categories' );
    }

    /**
     * Render inventory page.
     */
    public function render_inventory_page() {
        $this->render_page( 'inventory' );
    }

    /**
     * Render purchase bills page.
     */
    public function render_purchase_bills_page() {
        $this->render_page( 'purchase-bills' );
    }

    /**
     * Render sales bills page.
     */
    public function render_sales_bills_page() {
        $this->render_page( 'sales-bills' );
    }

    /**
     * Render centers page.
     */
    public function render_centers_page() {
        $this->render_page( 'centers' );
    }

    /**
     * Render vendors page.
     */
    public function render_vendors_page() {
        $this->render_page( 'vendors' );
    }

    /**
     * Render customers page.
     */
    public function render_customers_page() {
        $this->render_page( 'customers' );
    }

    /**
     * Render reports page.
     */
    public function render_reports_page() {
        $this->render_page( 'reports' );
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        $this->render_page( 'settings' );
    }

    /**
     * Render a page template.
     *
     * @param string $page_name Page template name.
     */
    private function render_page( $page_name ) {
        $page_file = UC_PLUGIN_DIR . "includes/admin/pages/{$page_name}.php";

        if ( file_exists( $page_file ) ) {
            include $page_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__( 'U-Commerce', 'u-commerce' ) . '</h1>';
            echo '<p>' . esc_html__( 'Page under construction.', 'u-commerce' ) . '</p>';
            echo '</div>';
        }
    }
}
