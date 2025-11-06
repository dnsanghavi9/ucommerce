<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class - Singleton pattern.
 */
class UC_Plugin {

    /**
     * The single instance of the class.
     *
     * @var UC_Plugin
     */
    protected static $instance = null;

    /**
     * Database handler instance.
     *
     * @var UC_Database
     */
    public $database;

    /**
     * Admin handler instance.
     *
     * @var UC_Admin
     */
    public $admin;

    /**
     * Products handler instance.
     *
     * @var UC_Products
     */
    public $products;

    /**
     * Inventory handler instance.
     *
     * @var UC_Inventory
     */
    public $inventory;

    /**
     * Billing handler instance.
     *
     * @var UC_Billing
     */
    public $billing;

    /**
     * Centers handler instance.
     *
     * @var UC_Centers
     */
    public $centers;

    /**
     * Users handler instance.
     *
     * @var UC_User_Management
     */
    public $users;

    /**
     * Main UC_Plugin Instance.
     *
     * Ensures only one instance of UC_Plugin is loaded or can be loaded.
     *
     * @return UC_Plugin - Main instance.
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * UC_Plugin Constructor.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Core classes
        require_once UC_PLUGIN_DIR . 'includes/core/class-uc-database.php';
        require_once UC_PLUGIN_DIR . 'includes/core/class-uc-roles.php';
        require_once UC_PLUGIN_DIR . 'includes/core/class-uc-capabilities.php';

        // Helper classes
        require_once UC_PLUGIN_DIR . 'includes/helpers/class-uc-utilities.php';
        require_once UC_PLUGIN_DIR . 'includes/helpers/class-uc-barcode.php';

        // Module classes
        require_once UC_PLUGIN_DIR . 'includes/modules/products/class-uc-products.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/products/class-uc-categories.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/inventory/class-uc-inventory.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/inventory/class-uc-stock-manager.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/billing/class-uc-purchase-bills.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/billing/class-uc-sales-bills.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/centers/class-uc-centers.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/users/class-uc-user-management.php';
        require_once UC_PLUGIN_DIR . 'includes/modules/reports/class-uc-reports.php';

        // Admin classes
        if ( is_admin() ) {
            require_once UC_PLUGIN_DIR . 'includes/admin/class-uc-admin-menu.php';
            require_once UC_PLUGIN_DIR . 'includes/admin/class-uc-setup-wizard.php';
        }

        // API classes
        require_once UC_PLUGIN_DIR . 'includes/api/class-uc-rest-api.php';

        // Initialize components
        $this->database = new UC_Database();

        if ( is_admin() ) {
            $this->admin = new UC_Admin_Menu();
            new UC_Setup_Wizard(); // Initialize setup wizard
        }

        $this->products  = new UC_Products();
        $this->inventory = new UC_Inventory();
        $this->centers   = new UC_Centers();
        $this->users     = new UC_User_Management();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        if ( ! is_admin() ) {
            return;
        }

        // Load admin styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Add settings link on plugin page
        add_filter( 'plugin_action_links_' . UC_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );

        // AJAX handlers for categories
        add_action( 'wp_ajax_uc_get_category', array( $this, 'ajax_get_category' ) );
        add_action( 'wp_ajax_uc_save_category', array( $this, 'ajax_save_category' ) );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        // Load public styles and scripts if needed
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_admin_styles() {
        $screen = get_current_screen();

        // Only load on our plugin pages
        if ( strpos( $screen->id, 'u-commerce' ) !== false ) {
            wp_enqueue_style(
                'u-commerce-admin',
                UC_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                UC_VERSION,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_admin_scripts() {
        $screen = get_current_screen();

        // Only load on our plugin pages
        if ( strpos( $screen->id, 'u-commerce' ) !== false ) {
            wp_enqueue_script(
                'u-commerce-admin',
                UC_PLUGIN_URL . 'assets/js/admin.js',
                array( 'jquery' ),
                UC_VERSION,
                true
            );

            // Localize script for AJAX
            wp_localize_script(
                'u-commerce-admin',
                'ucData',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce'   => wp_create_nonce( 'uc_admin_nonce' ),
                )
            );
        }
    }

    /**
     * Register the stylesheets for the public-facing side.
     */
    public function enqueue_public_styles() {
        // Only enqueue if needed on frontend
    }

    /**
     * Register the JavaScript for the public-facing side.
     */
    public function enqueue_public_scripts() {
        // Only enqueue if needed on frontend
    }

    /**
     * Add plugin action links.
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=u-commerce-settings' ),
            esc_html__( 'Settings', 'u-commerce' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * AJAX handler: Get category data.
     */
    public function ajax_get_category() {
        check_ajax_referer( 'uc_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'u_commerce_manage_categories' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'u-commerce' ) ) );
        }

        $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;

        if ( ! $category_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid category ID.', 'u-commerce' ) ) );
        }

        $categories = new UC_Categories();
        $category = $categories->get( $category_id );

        if ( ! $category ) {
            wp_send_json_error( array( 'message' => __( 'Category not found.', 'u-commerce' ) ) );
        }

        wp_send_json_success( $category );
    }

    /**
     * AJAX handler: Save category.
     */
    public function ajax_save_category() {
        check_ajax_referer( 'uc_category_save', 'uc_category_nonce' );

        if ( ! current_user_can( 'u_commerce_manage_categories' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'u-commerce' ) ) );
        }

        $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
        $data = array(
            'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
            'slug'        => isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '',
            'parent_id'   => isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0,
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
        );

        if ( empty( $data['name'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Category name is required.', 'u-commerce' ) ) );
        }

        $categories = new UC_Categories();

        if ( $category_id ) {
            // Update existing category
            $result = $categories->update( $category_id, $data );
            $message = __( 'Category updated successfully.', 'u-commerce' );
        } else {
            // Create new category
            $result = $categories->create( $data );
            $message = __( 'Category created successfully.', 'u-commerce' );
        }

        if ( $result ) {
            wp_send_json_success( array( 'message' => $message ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to save category.', 'u-commerce' ) ) );
        }
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Plugin is now running
        do_action( 'u_commerce_loaded' );
    }
}
