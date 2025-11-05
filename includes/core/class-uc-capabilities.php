<?php
/**
 * User capabilities handler.
 *
 * Defines and manages custom capabilities for the plugin.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Capabilities handler class.
 */
class UC_Capabilities {

    /**
     * Get all custom capabilities.
     *
     * @return array All capabilities organized by role.
     */
    public static function get_all_capabilities() {
        return array(
            'super_admin'       => self::get_super_admin_capabilities(),
            'center_manager'    => self::get_center_manager_capabilities(),
            'sales_person'      => self::get_sales_person_capabilities(),
            'inventory_manager' => self::get_inventory_manager_capabilities(),
        );
    }

    /**
     * Get all capability names.
     *
     * @return array All capability names.
     */
    public static function get_all_capability_names() {
        return array(
            'u_commerce_manage_categories',
            'u_commerce_manage_centers',
            'u_commerce_manage_users',
            'u_commerce_add_purchase_bills',
            'u_commerce_manage_inventory',
            'u_commerce_set_pricing',
            'u_commerce_generate_barcodes',
            'u_commerce_create_sales',
            'u_commerce_view_reports',
            'u_commerce_manage_vendors',
            'u_commerce_manage_customers',
            'u_commerce_manage_products',
            'u_commerce_view_dashboard',
            'u_commerce_manage_settings',
        );
    }

    /**
     * Get Super Admin capabilities.
     *
     * @return array Capabilities with true/false values.
     */
    private static function get_super_admin_capabilities() {
        return array(
            // WordPress core capabilities
            'read'                           => true,

            // U-Commerce capabilities
            'u_commerce_manage_categories'   => true,
            'u_commerce_manage_centers'      => true,
            'u_commerce_manage_users'        => true,
            'u_commerce_add_purchase_bills'  => true,
            'u_commerce_manage_inventory'    => true,
            'u_commerce_set_pricing'         => true,
            'u_commerce_generate_barcodes'   => true,
            'u_commerce_create_sales'        => true,
            'u_commerce_view_reports'        => true,
            'u_commerce_manage_vendors'      => true,
            'u_commerce_manage_customers'    => true,
            'u_commerce_manage_products'     => true,
            'u_commerce_view_dashboard'      => true,
            'u_commerce_manage_settings'     => true,
        );
    }

    /**
     * Get Center Manager capabilities.
     *
     * @return array Capabilities with true/false values.
     */
    private static function get_center_manager_capabilities() {
        return array(
            // WordPress core capabilities
            'read'                           => true,

            // U-Commerce capabilities (center-specific)
            'u_commerce_manage_categories'   => false,
            'u_commerce_manage_centers'      => false,
            'u_commerce_manage_users'        => false,
            'u_commerce_add_purchase_bills'  => false,
            'u_commerce_manage_inventory'    => true, // Center-specific
            'u_commerce_set_pricing'         => true, // Center-specific
            'u_commerce_generate_barcodes'   => true, // Center-specific
            'u_commerce_create_sales'        => true, // Center-specific
            'u_commerce_view_reports'        => true, // Center-specific
            'u_commerce_manage_vendors'      => false,
            'u_commerce_manage_customers'    => true,
            'u_commerce_manage_products'     => false,
            'u_commerce_view_dashboard'      => true,
            'u_commerce_manage_settings'     => false,
        );
    }

    /**
     * Get Sales Person capabilities.
     *
     * @return array Capabilities with true/false values.
     */
    private static function get_sales_person_capabilities() {
        return array(
            // WordPress core capabilities
            'read'                           => true,

            // U-Commerce capabilities (limited)
            'u_commerce_manage_categories'   => false,
            'u_commerce_manage_centers'      => false,
            'u_commerce_manage_users'        => false,
            'u_commerce_add_purchase_bills'  => false,
            'u_commerce_manage_inventory'    => false,
            'u_commerce_set_pricing'         => false,
            'u_commerce_generate_barcodes'   => false,
            'u_commerce_create_sales'        => true, // Center-specific
            'u_commerce_view_reports'        => true, // Limited
            'u_commerce_manage_vendors'      => false,
            'u_commerce_manage_customers'    => true,
            'u_commerce_manage_products'     => false,
            'u_commerce_view_dashboard'      => true,
            'u_commerce_manage_settings'     => false,
        );
    }

    /**
     * Get Inventory Manager capabilities.
     *
     * @return array Capabilities with true/false values.
     */
    private static function get_inventory_manager_capabilities() {
        return array(
            // WordPress core capabilities
            'read'                           => true,

            // U-Commerce capabilities (inventory focused)
            'u_commerce_manage_categories'   => false,
            'u_commerce_manage_centers'      => false,
            'u_commerce_manage_users'        => false,
            'u_commerce_add_purchase_bills'  => true,
            'u_commerce_manage_inventory'    => true,
            'u_commerce_set_pricing'         => false,
            'u_commerce_generate_barcodes'   => false,
            'u_commerce_create_sales'        => false,
            'u_commerce_view_reports'        => true, // Limited
            'u_commerce_manage_vendors'      => true,
            'u_commerce_manage_customers'    => false,
            'u_commerce_manage_products'     => true,
            'u_commerce_view_dashboard'      => true,
            'u_commerce_manage_settings'     => false,
        );
    }

    /**
     * Check if current user has capability.
     *
     * @param string $capability Capability name.
     * @return bool True if user has capability.
     */
    public static function current_user_can( $capability ) {
        return current_user_can( $capability );
    }

    /**
     * Check if user has capability.
     *
     * @param int    $user_id    User ID.
     * @param string $capability Capability name.
     * @return bool True if user has capability.
     */
    public static function user_can( $user_id, $capability ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return false;
        }

        return $user->has_cap( $capability );
    }

    /**
     * Get user's center ID (for center-specific operations).
     *
     * @param int $user_id User ID.
     * @return int|false Center ID or false.
     */
    public static function get_user_center_id( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $center_id = get_user_meta( $user_id, 'u_commerce_center_id', true );

        return $center_id ? absint( $center_id ) : false;
    }

    /**
     * Set user's center ID.
     *
     * @param int $user_id   User ID.
     * @param int $center_id Center ID.
     * @return bool True on success.
     */
    public static function set_user_center_id( $user_id, $center_id ) {
        return update_user_meta( $user_id, 'u_commerce_center_id', absint( $center_id ) );
    }
}
