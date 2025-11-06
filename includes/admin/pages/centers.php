<?php
/**
 * Centers admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_centers' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$center_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$centers_handler = new UC_Centers();

// Handle form submission
if ( isset( $_POST['uc_center_submit'] ) ) {
    check_admin_referer( 'uc_center_save', 'uc_center_nonce' );

    $data = array(
        'name'         => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
        'type'         => isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'sub',
        'parent_id'    => isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0,
        'address'      => isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '',
        'contact_info' => isset( $_POST['contact_info'] ) ? sanitize_textarea_field( $_POST['contact_info'] ) : '',
        'status'       => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'active',
    );

    if ( $center_id ) {
        // Update
        $result = $centers_handler->update( $center_id, $data );
        $message = __( 'Center updated successfully.', 'u-commerce' );
    } else {
        // Create
        $result = $centers_handler->create( $data );
        $message = __( 'Center created successfully.', 'u-commerce' );
    }

    if ( $result ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save center.', 'u-commerce' ) . '</p></div>';
    }

    // Redirect to list after save
    if ( $result ) {
        echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-centers' ) ) . '";</script>';
    }
}

// Handle delete
if ( $action === 'delete' && $center_id ) {
    check_admin_referer( 'delete_center_' . $center_id );

    $deleted = $centers_handler->delete( $center_id );

    if ( $deleted ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Center deleted successfully.', 'u-commerce' ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete center.', 'u-commerce' ) . '</p></div>';
    }

    $action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'edit' && $center_id ) {
    // Show edit form
    $center = $centers_handler->get( $center_id );
    if ( ! $center ) {
        wp_die( esc_html__( 'Center not found.', 'u-commerce' ) );
    }
    include UC_PLUGIN_DIR . 'includes/admin/pages/centers-form.php';
} elseif ( $action === 'new' ) {
    // Show add form
    $center = null;
    include UC_PLUGIN_DIR . 'includes/admin/pages/centers-form.php';
} else {
    // Show list
    include UC_PLUGIN_DIR . 'includes/admin/pages/centers-list.php';
}
