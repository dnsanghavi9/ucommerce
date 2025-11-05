<?php
/**
 * User management.
 *
 * Handles U-Commerce user operations.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/users
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * User management class.
 */
class UC_User_Management {

    /**
     * Get U-Commerce users.
     *
     * @param array $args Query arguments.
     * @return array Array of users.
     */
    public function get_users( $args = array() ) {
        $defaults = array(
            'role__in' => UC_Roles::get_custom_roles(),
            'orderby'  => 'display_name',
            'order'    => 'ASC',
        );

        $args = wp_parse_args( $args, $defaults );

        return get_users( $args );
    }

    /**
     * Get users by role.
     *
     * @param string $role Role name.
     * @return array Array of users.
     */
    public function get_users_by_role( $role ) {
        return get_users( array( 'role' => $role ) );
    }

    /**
     * Get users by center.
     *
     * @param int $center_id Center ID.
     * @return array Array of users.
     */
    public function get_users_by_center( $center_id ) {
        $args = array(
            'role__in'   => UC_Roles::get_custom_roles(),
            'meta_key'   => 'u_commerce_center_id',
            'meta_value' => $center_id,
        );

        return get_users( $args );
    }

    /**
     * Assign user to center.
     *
     * @param int $user_id   User ID.
     * @param int $center_id Center ID.
     * @return bool True on success.
     */
    public function assign_to_center( $user_id, $center_id ) {
        return UC_Capabilities::set_user_center_id( $user_id, $center_id );
    }

    /**
     * Get user's center.
     *
     * @param int $user_id User ID.
     * @return object|null Center object or null.
     */
    public function get_user_center( $user_id ) {
        $center_id = UC_Capabilities::get_user_center_id( $user_id );

        if ( ! $center_id ) {
            return null;
        }

        $centers = new UC_Centers();
        return $centers->get( $center_id );
    }

    /**
     * Create U-Commerce user.
     *
     * @param array $data User data.
     * @return int|WP_Error User ID on success, WP_Error on failure.
     */
    public function create_user( $data ) {
        // Validate required fields
        if ( empty( $data['user_login'] ) || empty( $data['user_email'] ) || empty( $data['role'] ) ) {
            return new WP_Error( 'missing_data', __( 'Missing required user data.', 'u-commerce' ) );
        }

        // Create user
        $user_id = wp_create_user(
            sanitize_user( $data['user_login'] ),
            isset( $data['user_pass'] ) ? $data['user_pass'] : wp_generate_password(),
            sanitize_email( $data['user_email'] )
        );

        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        // Set role
        $user = new WP_User( $user_id );
        $user->set_role( $data['role'] );

        // Set additional data
        if ( isset( $data['first_name'] ) ) {
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $data['first_name'] ) );
        }

        if ( isset( $data['last_name'] ) ) {
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $data['last_name'] ) );
        }

        if ( isset( $data['center_id'] ) ) {
            $this->assign_to_center( $user_id, absint( $data['center_id'] ) );
        }

        return $user_id;
    }

    /**
     * Update U-Commerce user.
     *
     * @param int   $user_id User ID.
     * @param array $data    Updated data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function update_user( $user_id, $data ) {
        $user_data = array( 'ID' => $user_id );

        if ( isset( $data['user_email'] ) ) {
            $user_data['user_email'] = sanitize_email( $data['user_email'] );
        }

        if ( isset( $data['first_name'] ) ) {
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $data['first_name'] ) );
        }

        if ( isset( $data['last_name'] ) ) {
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $data['last_name'] ) );
        }

        if ( isset( $data['role'] ) ) {
            $user = new WP_User( $user_id );
            $user->set_role( $data['role'] );
        }

        if ( isset( $data['center_id'] ) ) {
            $this->assign_to_center( $user_id, absint( $data['center_id'] ) );
        }

        if ( ! empty( $user_data ) && count( $user_data ) > 1 ) {
            $result = wp_update_user( $user_data );
            return ! is_wp_error( $result );
        }

        return true;
    }

    /**
     * Delete U-Commerce user.
     *
     * @param int $user_id User ID.
     * @return bool True on success.
     */
    public function delete_user( $user_id ) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
        return wp_delete_user( $user_id );
    }

    /**
     * Get user stats.
     *
     * @param int $user_id User ID.
     * @return array User statistics.
     */
    public function get_user_stats( $user_id ) {
        global $wpdb;

        $database = new UC_Database();

        // Get sales created by user
        $sales_table = $database->get_table( 'sales_bills' );
        $total_sales = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$sales_table} WHERE created_by = %d",
                $user_id
            )
        );

        $sales_amount = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total_amount) FROM {$sales_table} WHERE created_by = %d",
                $user_id
            )
        );

        // Get purchases created by user
        $purchase_table = $database->get_table( 'purchase_bills' );
        $total_purchases = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$purchase_table} WHERE created_by = %d",
                $user_id
            )
        );

        return array(
            'total_sales'     => (int) $total_sales,
            'sales_amount'    => (float) $sales_amount,
            'total_purchases' => (int) $total_purchases,
        );
    }
}
