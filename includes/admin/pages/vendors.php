<?php
/**
 * Vendors admin page (Controller).
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_vendors' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$vendor_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$vendors_handler = new UC_Vendors();

// Variable to store form data for preservation on errors
$form_data = null;

// Handle form submission
if ( isset( $_POST['uc_vendor_submit'] ) ) {
	check_admin_referer( 'uc_vendor_save', 'uc_vendor_nonce' );

	$active_tab = isset( $_POST['active_tab'] ) ? sanitize_text_field( $_POST['active_tab'] ) : 'basic';

	if ( $active_tab === 'contacts' && $vendor_id ) {
		// Handle contact persons
		$database = new UC_Database();
		global $wpdb;
		$contacts_table = $database->get_table( 'vendor_contacts' );

		// First, remove all existing contacts for this vendor
		$wpdb->delete( $contacts_table, array( 'vendor_id' => $vendor_id ), array( '%d' ) );

		// Then add new contacts
		if ( isset( $_POST['contact_names'] ) && is_array( $_POST['contact_names'] ) ) {
			$contact_names = array_map( 'sanitize_text_field', $_POST['contact_names'] );
			$contact_mobiles = isset( $_POST['contact_mobiles'] ) ? array_map( 'sanitize_text_field', $_POST['contact_mobiles'] ) : array();

			foreach ( $contact_names as $index => $name ) {
				if ( ! empty( $name ) && ! empty( $contact_mobiles[ $index ] ) ) {
					$wpdb->insert(
						$contacts_table,
						array(
							'vendor_id'      => $vendor_id,
							'contact_name'   => $name,
							'contact_mobile' => $contact_mobiles[ $index ],
						),
						array( '%d', '%s', '%s' )
					);
				}
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Contact persons updated successfully.', 'u-commerce' ) . '</p></div>';
		$result = true;
	} else {
		// Basic info tab
		$data = array(
			'name'       => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
			'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '',
			'address'    => isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '',
			'gst_number' => isset( $_POST['gst_number'] ) ? sanitize_text_field( $_POST['gst_number'] ) : '',
			'status'     => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'active',
		);

		// Validate required fields
		if ( empty( $data['name'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Vendor name is required.', 'u-commerce' ) . '</p></div>';
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
		} elseif ( ! empty( $data['gst_number'] ) && ! preg_match( '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $data['gst_number'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid GST number format.', 'u-commerce' ) . '</p></div>';
			$form_data = (object) $data; // Preserve form data
		} else {
			// Check phone uniqueness
			global $wpdb;
			$database = new UC_Database();
			$vendors_table = $database->get_table( 'vendors' );

			$existing_vendor = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM $vendors_table WHERE phone = %s AND id != %d",
				$data['phone'],
				$vendor_id
			) );

			if ( $existing_vendor ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Phone number already exists for another vendor.', 'u-commerce' ) . '</p></div>';
				$form_data = (object) $data; // Preserve form data
			} else {
				if ( $vendor_id ) {
					// Update
					$result = $vendors_handler->update( $vendor_id, $data );
					$message = __( 'Vendor updated successfully.', 'u-commerce' );
					$saved_id = $vendor_id;
				} else {
					// Create
					$result = $vendors_handler->create( $data );
					$message = __( 'Vendor created successfully.', 'u-commerce' );
					$saved_id = $result;
				}

				if ( $result ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';

					// Redirect to edit page after creation
					if ( ! $vendor_id && $saved_id ) {
						echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=edit&id=' . $saved_id ) ) . '";</script>';
						exit;
					}
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save vendor.', 'u-commerce' ) . '</p></div>';
				}
			}
		}
	}
}

// Handle delete
if ( $action === 'delete' && $vendor_id ) {
	check_admin_referer( 'delete_vendor_' . $vendor_id );

	$deleted = $vendors_handler->delete( $vendor_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Vendor deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete vendor.', 'u-commerce' ) . '</p></div>';
	}

	$action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'edit' && $vendor_id ) {
	// Show edit form
	$vendor = $vendors_handler->get( $vendor_id );
	if ( ! $vendor ) {
		wp_die( esc_html__( 'Vendor not found.', 'u-commerce' ) );
	}
	// Use form_data if validation failed, otherwise use database data
	if ( $form_data ) {
		$vendor = $form_data;
		$vendor->id = $vendor_id;
	}
	include UC_PLUGIN_DIR . 'includes/admin/pages/vendors-form.php';
} elseif ( $action === 'new' ) {
	// Show add form
	// Use form_data if validation failed
	$vendor = $form_data;
	include UC_PLUGIN_DIR . 'includes/admin/pages/vendors-form.php';
} else {
	// Show list
	include UC_PLUGIN_DIR . 'includes/admin/pages/vendors-list.php';
}
