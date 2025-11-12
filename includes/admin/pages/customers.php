<?php
/**
 * Customers admin page (Controller).
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_customers' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$customer_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$customers_handler = new UC_Customers();

// Variable to store form data for preservation on errors
$form_data = null;

// Handle form submission
if ( isset( $_POST['uc_customer_submit'] ) ) {
	check_admin_referer( 'uc_customer_save', 'uc_customer_nonce' );

	$data = array(
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
		'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '',
		'address' => isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '',
		'status'  => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'active',
	);

	// Validate required fields
	if ( empty( $data['name'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Customer name is required.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
	} elseif ( empty( $data['phone'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Phone number is required.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
	} elseif ( ! preg_match( '/^[6-9][0-9]{9}$/', $data['phone'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid phone number. Must be 10 digits starting with 6, 7, 8, or 9.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
	} elseif ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid email address.', 'u-commerce' ) . '</p></div>';
		$form_data = (object) $data; // Preserve form data
	} else {
		// Check phone uniqueness
		global $wpdb;
		$database = new UC_Database();
		$customers_table = $database->get_table( 'customers' );

		$existing_customer = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $customers_table WHERE phone = %s AND id != %d",
			$data['phone'],
			$customer_id
		) );

		if ( $existing_customer ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Phone number already exists for another customer.', 'u-commerce' ) . '</p></div>';
			$form_data = (object) $data; // Preserve form data
		} else {
			if ( $customer_id ) {
				// Update
				$result = $customers_handler->update( $customer_id, $data );
				$message = __( 'Customer updated successfully.', 'u-commerce' );
				$saved_id = $customer_id;
			} else {
				// Create
				$result = $customers_handler->create( $data );
				$message = __( 'Customer created successfully.', 'u-commerce' );
				$saved_id = $result;
			}

			if ( $result ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';

				// Redirect to edit page after creation
				if ( ! $customer_id && $saved_id ) {
					echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-customers&action=edit&id=' . $saved_id ) ) . '";</script>';
					exit;
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save customer.', 'u-commerce' ) . '</p></div>';
			}
		}
	}
}

// Handle delete
if ( $action === 'delete' && $customer_id ) {
	check_admin_referer( 'delete_customer_' . $customer_id );

	$deleted = $customers_handler->delete( $customer_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Customer deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete customer.', 'u-commerce' ) . '</p></div>';
	}

	$action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'edit' && $customer_id ) {
	// Show edit form
	$customer = $customers_handler->get( $customer_id );
	if ( ! $customer ) {
		wp_die( esc_html__( 'Customer not found.', 'u-commerce' ) );
	}
	// Use form_data if validation failed, otherwise use database data
	if ( $form_data ) {
		$customer = $form_data;
		$customer->id = $customer_id;
	}
	include UC_PLUGIN_DIR . 'includes/admin/pages/customers-form.php';
} elseif ( $action === 'new' ) {
	// Show add form
	// Use form_data if validation failed
	$customer = $form_data;
	include UC_PLUGIN_DIR . 'includes/admin/pages/customers-form.php';
} else {
	// Show list
	include UC_PLUGIN_DIR . 'includes/admin/pages/customers-list.php';
}
