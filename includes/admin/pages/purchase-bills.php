<?php
/**
 * Purchase Bills admin page (Controller).
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_add_purchase_bills' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$bill_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$purchase_bills_handler = new UC_Purchase_Bills();

// Handle form submission
if ( isset( $_POST['uc_purchase_bill_submit'] ) ) {
	check_admin_referer( 'uc_purchase_bill_save', 'uc_purchase_bill_nonce' );

	$data = array(
		'vendor_id'   => isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0,
		'center_id'   => isset( $_POST['center_id'] ) ? absint( $_POST['center_id'] ) : 0,
		'bill_number' => isset( $_POST['bill_number'] ) ? sanitize_text_field( $_POST['bill_number'] ) : '',
		'bill_date'   => isset( $_POST['bill_date'] ) ? sanitize_text_field( $_POST['bill_date'] ) : current_time( 'Y-m-d' ),
		'status'      => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'completed',
		'notes'       => isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '',
	);

	// Validate required fields
	if ( empty( $data['center_id'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Center is required.', 'u-commerce' ) . '</p></div>';
	} elseif ( empty( $_POST['items'] ) || ! is_array( $_POST['items'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'At least one item is required.', 'u-commerce' ) . '</p></div>';
	} else {
		// Prepare items
		$items = array();
		foreach ( $_POST['items'] as $item ) {
			if ( ! empty( $item['product_id'] ) && ! empty( $item['quantity'] ) && ! empty( $item['unit_cost'] ) ) {
				$items[] = array(
					'product_id' => absint( $item['product_id'] ),
					'quantity'   => absint( $item['quantity'] ),
					'unit_cost'  => floatval( $item['unit_cost'] ),
				);
			}
		}

		if ( empty( $items ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'At least one valid item is required.', 'u-commerce' ) . '</p></div>';
		} else {
			if ( $bill_id ) {
				// For editing, we need to handle it differently
				// For now, only allow creation
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Editing purchase bills is currently not supported.', 'u-commerce' ) . '</p></div>';
			} else {
				// Create
				$result = $purchase_bills_handler->create( $data, $items );

				if ( $result ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Purchase bill created successfully. Inventory has been updated.', 'u-commerce' ) . '</p></div>';
					// Redirect to list
					echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills' ) ) . '";</script>';
					exit;
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to create purchase bill.', 'u-commerce' ) . '</p></div>';
				}
			}
		}
	}
}

// Handle delete
if ( $action === 'delete' && $bill_id ) {
	check_admin_referer( 'delete_purchase_bill_' . $bill_id );

	$deleted = $purchase_bills_handler->delete( $bill_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Purchase bill deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete purchase bill.', 'u-commerce' ) . '</p></div>';
	}

	$action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'view' && $bill_id ) {
	// Show view page
	$bill = $purchase_bills_handler->get( $bill_id );
	if ( ! $bill ) {
		wp_die( esc_html__( 'Purchase bill not found.', 'u-commerce' ) );
	}
	include UC_PLUGIN_DIR . 'includes/admin/pages/purchase-bills-view.php';
} elseif ( $action === 'new' ) {
	// Show add form
	$bill = null;
	include UC_PLUGIN_DIR . 'includes/admin/pages/purchase-bills-form.php';
} else {
	// Show list
	include UC_PLUGIN_DIR . 'includes/admin/pages/purchase-bills-list.php';
}
