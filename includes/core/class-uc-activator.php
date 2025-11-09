<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin activator class.
 */
class UC_Activator {

    /**
     * Activate the plugin.
     *
     * Creates database tables, sets up roles and capabilities,
     * and initializes default settings.
     */
    public static function activate() {
        // Create database tables
        self::create_tables();

        // Set up roles and capabilities
        self::setup_roles();

        // Initialize default settings
        self::initialize_settings();

        // Set database version
        update_option( 'u_commerce_db_version', UC_DB_VERSION );

        // Set plugin version
        update_option( 'u_commerce_version', UC_VERSION );

        // Mark as newly installed
        update_option( 'u_commerce_newly_installed', true );

        // Flush rewrite rules
        flush_rewrite_rules();

        // Fire activation hook
        do_action( 'u_commerce_plugin_activated' );
    }

    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Fire deactivation hook
        do_action( 'u_commerce_plugin_deactivated' );
    }

    /**
     * Create all database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix    = $wpdb->prefix;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Product Categories Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_product_categories (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            description text,
            parent_id bigint(20) UNSIGNED DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY parent_id (parent_id)
        ) $charset_collate;";

        // Product Variables Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_product_variables (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            type varchar(50) NOT NULL,
            `values` text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type)
        ) $charset_collate;";

        // Centers Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_centers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'sub',
            parent_id bigint(20) UNSIGNED DEFAULT 0,
            address text,
            contact_info text,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY parent_id (parent_id),
            KEY status (status)
        ) $charset_collate;";

        // Products Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_products (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            sku varchar(100) NOT NULL,
            category_id bigint(20) UNSIGNED,
            variables text,
            base_cost decimal(10,2) DEFAULT 0.00,
            description text,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY sku (sku),
            KEY category_id (category_id),
            KEY status (status)
        ) $charset_collate;";

        // Inventory Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_inventory (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            center_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL DEFAULT 0,
            reserved_quantity int(11) NOT NULL DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY product_center (product_id, center_id),
            KEY product_id (product_id),
            KEY center_id (center_id)
        ) $charset_collate;";

        // Pricing Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_pricing (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            center_id bigint(20) UNSIGNED NOT NULL,
            selling_price decimal(10,2) NOT NULL DEFAULT 0.00,
            margin_percentage decimal(5,2) DEFAULT 0.00,
            updated_by bigint(20) UNSIGNED,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY product_center_pricing (product_id, center_id),
            KEY product_id (product_id),
            KEY center_id (center_id)
        ) $charset_collate;";

        // Purchase Bills Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_purchase_bills (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            bill_number varchar(50) NOT NULL,
            vendor_id bigint(20) UNSIGNED,
            center_id bigint(20) UNSIGNED NOT NULL,
            bill_date date NOT NULL,
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            status varchar(20) NOT NULL DEFAULT 'pending',
            notes text,
            created_by bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY bill_number (bill_number),
            KEY vendor_id (vendor_id),
            KEY center_id (center_id),
            KEY bill_date (bill_date),
            KEY status (status)
        ) $charset_collate;";

        // Purchase Items Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_purchase_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            purchase_bill_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL,
            unit_cost decimal(10,2) NOT NULL,
            total_cost decimal(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY purchase_bill_id (purchase_bill_id),
            KEY product_id (product_id)
        ) $charset_collate;";

        // Sales Bills Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_sales_bills (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            bill_number varchar(50) NOT NULL,
            customer_id bigint(20) UNSIGNED,
            center_id bigint(20) UNSIGNED NOT NULL,
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            payment_status varchar(20) NOT NULL DEFAULT 'pending',
            payment_method varchar(50),
            notes text,
            created_by bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY bill_number (bill_number),
            KEY customer_id (customer_id),
            KEY center_id (center_id),
            KEY created_at (created_at),
            KEY payment_status (payment_status)
        ) $charset_collate;";

        // Sales Items Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_sales_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            sales_bill_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL,
            unit_price decimal(10,2) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            barcode varchar(100),
            PRIMARY KEY (id),
            KEY sales_bill_id (sales_bill_id),
            KEY product_id (product_id),
            KEY barcode (barcode)
        ) $charset_collate;";

        // Vendors Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_vendors (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            phone varchar(20) NOT NULL,
            email varchar(100),
            address text,
            gst_number varchar(50),
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY phone (phone),
            KEY status (status)
        ) $charset_collate;";

        // Vendor Contact Persons Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_vendor_contacts (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) UNSIGNED NOT NULL,
            contact_name varchar(200) NOT NULL,
            contact_mobile varchar(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id)
        ) $charset_collate;";

        // Customers Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_customers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            phone varchar(20) NOT NULL,
            email varchar(100),
            address text,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY phone (phone),
            KEY status (status),
            KEY email (email)
        ) $charset_collate;";

        // Barcodes Table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_barcodes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            center_id bigint(20) UNSIGNED NOT NULL,
            barcode varchar(100) NOT NULL,
            generated_by bigint(20) UNSIGNED,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY barcode (barcode),
            KEY product_id (product_id),
            KEY center_id (center_id)
        ) $charset_collate;";

        // Execute all SQL statements
        foreach ( $sql as $query ) {
            dbDelta( $query );
        }
    }

    /**
     * Set up custom roles and capabilities.
     */
    private static function setup_roles() {
        require_once UC_PLUGIN_DIR . 'includes/core/class-uc-roles.php';
        UC_Roles::create_roles();
    }

    /**
     * Initialize default settings.
     */
    private static function initialize_settings() {
        $default_settings = array(
            'general'       => array(
                'company_name'    => get_bloginfo( 'name' ),
                'company_address' => '',
                'currency'        => 'INR',
                'decimal_places'  => 2,
            ),
            'inventory'     => array(
                'low_stock_threshold'     => 10,
                'auto_barcode_generation' => true,
                'stock_management_method' => 'fifo',
            ),
            'billing'       => array(
                'bill_number_format'   => 'UC-{TYPE}-{YEAR}-{NUMBER}',
                'auto_bill_numbering'  => true,
                'default_payment_terms' => 'immediate',
            ),
            'notifications' => array(
                'email_notifications'   => true,
                'low_stock_alerts'      => true,
                'new_sale_notifications' => false,
            ),
        );

        add_option( 'u_commerce_settings', $default_settings );
    }
}
