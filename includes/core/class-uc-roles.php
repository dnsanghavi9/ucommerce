<?php
/**
 * User roles handler.
 *
 * Creates and manages custom user roles for the plugin.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Roles handler class.
 */
class UC_Roles {

    /**
     * Create custom roles.
     */
    public static function create_roles() {
        require_once UC_PLUGIN_DIR . 'includes/core/class-uc-capabilities.php';

        // Remove roles if they exist (for clean install)
        self::remove_roles();

        // Get capabilities for each role
        $capabilities = UC_Capabilities::get_all_capabilities();

        // Super Admin Role
        add_role(
            'u_commerce_super_admin',
            __( 'U-Commerce Super Admin', 'u-commerce' ),
            $capabilities['super_admin']
        );

        // Center Manager Role
        add_role(
            'u_commerce_center_manager',
            __( 'U-Commerce Center Manager', 'u-commerce' ),
            $capabilities['center_manager']
        );

        // Sales Person Role
        add_role(
            'u_commerce_sales_person',
            __( 'U-Commerce Sales Person', 'u-commerce' ),
            $capabilities['sales_person']
        );

        // Inventory Manager Role
        add_role(
            'u_commerce_inventory_manager',
            __( 'U-Commerce Inventory Manager', 'u-commerce' ),
            $capabilities['inventory_manager']
        );

        // Add capabilities to Administrator role
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            foreach ( $capabilities['super_admin'] as $cap => $grant ) {
                $admin_role->add_cap( $cap, $grant );
            }
        }
    }

    /**
     * Remove custom roles.
     */
    public static function remove_roles() {
        remove_role( 'u_commerce_super_admin' );
        remove_role( 'u_commerce_center_manager' );
        remove_role( 'u_commerce_sales_person' );
        remove_role( 'u_commerce_inventory_manager' );

        // Remove capabilities from Administrator role
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $all_caps = UC_Capabilities::get_all_capability_names();
            foreach ( $all_caps as $cap ) {
                $admin_role->remove_cap( $cap );
            }
        }
    }

    /**
     * Get all custom role names.
     *
     * @return array Role names.
     */
    public static function get_custom_roles() {
        return array(
            'u_commerce_super_admin',
            'u_commerce_center_manager',
            'u_commerce_sales_person',
            'u_commerce_inventory_manager',
        );
    }

    /**
     * Check if user has U-Commerce role.
     *
     * @param int $user_id User ID.
     * @return bool True if user has U-Commerce role.
     */
    public static function user_has_uc_role( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return false;
        }

        $custom_roles = self::get_custom_roles();

        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $custom_roles, true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user's U-Commerce role.
     *
     * @param int $user_id User ID.
     * @return string|false Role name or false.
     */
    public static function get_user_uc_role( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return false;
        }

        $custom_roles = self::get_custom_roles();

        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $custom_roles, true ) ) {
                return $role;
            }
        }

        return false;
    }
}
