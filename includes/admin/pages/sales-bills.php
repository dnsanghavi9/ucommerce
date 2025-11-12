<?php
/**
 * Sales Bills admin page (Controller).
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_create_sales' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$bill_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$sales_bills_handler = new UC_Sales_Bills();

// Variable to store form data for preservation on errors
$form_data = null;
$form_items = null;

// Handle form submission
if ( isset( $_POST['uc_sales_bill_submit'] ) ) {
	check_admin_referer( 'uc_sales_bill_save', 'uc_sales_bill_nonce' );

	$data = array(
		'customer_id'    => isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0,
		'center_id'      => isset( $_POST['center_id'] ) ? absint( $_POST['center_id'] ) : 0,
		'bill_number'    => isset( $_POST['bill_number'] ) ? sanitize_text_field( $_POST['bill_number'] ) : '',
		'payment_status' => isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : 'paid',
		'payment_method' => isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : 'cash',
		'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '',
	);

	// Validate required fields
	if ( empty( $data['center_id'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Center is required.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
		$form_items = isset( $_POST['items'] ) ? $_POST['items'] : array();
	} elseif ( empty( $_POST['items'] ) || ! is_array( $_POST['items'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'At least one item is required.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
		$form_items = array();
	} else {
		// Prepare items
		$items = array();
		foreach ( $_POST['items'] as $item ) {
			if ( ! empty( $item['product_id'] ) && ! empty( $item['quantity'] ) && ! empty( $item['unit_price'] ) ) {
				$items[] = array(
					'product_id' => absint( $item['product_id'] ),
					'quantity'   => absint( $item['quantity'] ),
					'unit_price' => floatval( $item['unit_price'] ),
				);
			}
		}

		if ( empty( $items ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'At least one valid item is required.', 'u-commerce' ) . '</p></div>';
			$form_data = (object) $data; // Preserve form data
			$form_items = isset( $_POST['items'] ) ? $_POST['items'] : array();
		} else {
			if ( $bill_id ) {
				// For editing, we need to handle it differently
				// For now, only allow creation
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Editing sales bills is currently not supported.', 'u-commerce' ) . '</p></div>';
			} else {
				// Create
				$result = $sales_bills_handler->create( $data, $items );

				if ( $result ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Sales bill created successfully. Inventory has been updated.', 'u-commerce' ) . '</p></div>';
					// Redirect to list
					echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills' ) ) . '";</script>';
					exit;
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to create sales bill. Please check inventory availability.', 'u-commerce' ) . '</p></div>';
					$form_data = (object) $data; // Preserve form data
					$form_items = isset( $_POST['items'] ) ? $_POST['items'] : array();
				}
			}
		}
	}
}

// Handle delete
if ( $action === 'delete' && $bill_id ) {
	check_admin_referer( 'delete_sales_bill_' . $bill_id );

	$deleted = $sales_bills_handler->delete( $bill_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Sales bill deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete sales bill.', 'u-commerce' ) . '</p></div>';
	}

	$action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'view' && $bill_id ) {
	// Show view page
	$bill = $sales_bills_handler->get( $bill_id );
	if ( ! $bill ) {
		wp_die( esc_html__( 'Sales bill not found.', 'u-commerce' ) );
	}
	include UC_PLUGIN_DIR . 'includes/admin/pages/sales-bills-view.php';
} elseif ( $action === 'new' ) {
	// Show add form
	// Use form_data if validation failed
	$bill = $form_data;
	$bill_items = $form_items;
	include UC_PLUGIN_DIR . 'includes/admin/pages/sales-bills-form.php';
} else {
	// Show list
	include UC_PLUGIN_DIR . 'includes/admin/pages/sales-bills-list.php';
}
