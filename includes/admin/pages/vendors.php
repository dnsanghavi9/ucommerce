<?php
/**
 * Vendors admin page.
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

$vendors_handler = new UC_Vendors();

// Handle delete
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$vendor_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

if ( $action === 'delete' && $vendor_id ) {
	check_admin_referer( 'delete_vendor_' . $vendor_id );

	$deleted = $vendors_handler->delete( $vendor_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Vendor deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete vendor.', 'u-commerce' ) . '</p></div>';
	}
}

// Get all vendors
$vendors = $vendors_handler->get_all();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Vendors', 'u-commerce' ); ?></h1>
	<button type="button" class="page-title-action" id="add-new-vendor">
		<?php esc_html_e( 'Add New Vendor', 'u-commerce' ); ?>
	</button>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $vendors ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Contact Person', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Email', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'GST Number', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $vendors as $vendor ) : ?>
						<tr>
							<td><?php echo esc_html( $vendor->id ); ?></td>
							<td><strong><?php echo esc_html( $vendor->name ); ?></strong></td>
							<td><?php echo esc_html( $vendor->contact_person ); ?></td>
							<td><?php echo esc_html( $vendor->phone ); ?></td>
							<td><?php echo esc_html( $vendor->email ); ?></td>
							<td><code><?php echo esc_html( $vendor->gst_number ); ?></code></td>
							<td>
								<span class="uc-badge uc-badge-<?php echo esc_attr( $vendor->status === 'active' ? 'success' : 'warning' ); ?>">
									<?php echo esc_html( ucfirst( $vendor->status ) ); ?>
								</span>
							</td>
							<td>
								<button type="button"
										class="button button-small edit-vendor-btn"
										data-id="<?php echo esc_attr( $vendor->id ); ?>"
										data-name="<?php echo esc_attr( $vendor->name ); ?>"
										data-contact="<?php echo esc_attr( $vendor->contact_person ); ?>"
										data-phone="<?php echo esc_attr( $vendor->phone ); ?>"
										data-email="<?php echo esc_attr( $vendor->email ); ?>"
										data-address="<?php echo esc_attr( $vendor->address ); ?>"
										data-gst="<?php echo esc_attr( $vendor->gst_number ); ?>"
										data-status="<?php echo esc_attr( $vendor->status ); ?>">
									<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
								</button>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-vendors&action=delete&id=' . $vendor->id ), 'delete_vendor_' . $vendor->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;">
									<?php esc_html_e( 'Delete', 'u-commerce' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div style="padding: 40px; text-align: center;">
				<p style="font-size: 16px; color: #666;">
					<?php esc_html_e( 'No vendors found. Click "Add New Vendor" to create your first vendor.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- Vendor Modal -->
<div id="vendor-modal" class="uc-modal" style="display: none;">
	<div class="uc-modal-content">
		<div class="uc-modal-header">
			<h2 id="modal-title"><?php esc_html_e( 'Add New Vendor', 'u-commerce' ); ?></h2>
			<button type="button" class="uc-modal-close">&times;</button>
		</div>
		<div class="uc-modal-body">
			<form method="post" action="" id="vendor-form">
				<input type="hidden" name="vendor_id" id="vendor_id" value="">

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="vendor_name">
								<?php esc_html_e( 'Vendor Name', 'u-commerce' ); ?>
								<span style="color: red;">*</span>
							</label>
						</th>
						<td>
							<input type="text"
								   name="name"
								   id="vendor_name"
								   class="regular-text"
								   required>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_contact">
								<?php esc_html_e( 'Contact Person', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								   name="contact_person"
								   id="vendor_contact"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_phone">
								<?php esc_html_e( 'Phone', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="tel"
								   name="phone"
								   id="vendor_phone"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_email">
								<?php esc_html_e( 'Email', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="email"
								   name="email"
								   id="vendor_email"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_address">
								<?php esc_html_e( 'Address', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<textarea name="address"
									  id="vendor_address"
									  class="large-text"
									  rows="3"></textarea>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_gst">
								<?php esc_html_e( 'GST Number', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								   name="gst_number"
								   id="vendor_gst"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="vendor_status">
								<?php esc_html_e( 'Status', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<select name="status" id="vendor_status" class="regular-text">
								<option value="active"><?php esc_html_e( 'Active', 'u-commerce' ); ?></option>
								<option value="inactive"><?php esc_html_e( 'Inactive', 'u-commerce' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary button-large" id="save-vendor-btn">
						<?php esc_html_e( 'Save Vendor', 'u-commerce' ); ?>
					</button>
					<button type="button" class="button button-large uc-modal-close" style="margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Open modal for add
	$('#add-new-vendor').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Add New Vendor', 'u-commerce' ); ?>');
		$('#vendor-form')[0].reset();
		$('#vendor_id').val('');
		$('#vendor-modal').fadeIn();
	});

	// Open modal for edit
	$('.edit-vendor-btn').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Edit Vendor', 'u-commerce' ); ?>');
		$('#vendor_id').val($(this).data('id'));
		$('#vendor_name').val($(this).data('name'));
		$('#vendor_contact').val($(this).data('contact'));
		$('#vendor_phone').val($(this).data('phone'));
		$('#vendor_email').val($(this).data('email'));
		$('#vendor_address').val($(this).data('address'));
		$('#vendor_gst').val($(this).data('gst'));
		$('#vendor_status').val($(this).data('status'));
		$('#vendor-modal').fadeIn();
	});

	// Close modal
	$('.uc-modal-close').on('click', function() {
		$('#vendor-modal').fadeOut();
	});

	// Close modal on outside click
	$(window).on('click', function(e) {
		if ($(e.target).hasClass('uc-modal')) {
			$('#vendor-modal').fadeOut();
		}
	});

	// Handle form submission via AJAX
	$('#vendor-form').on('submit', function(e) {
		e.preventDefault();

		var formData = {
			action: 'uc_save_vendor',
			nonce: '<?php echo wp_create_nonce( 'uc_save_vendor' ); ?>',
			vendor_id: $('#vendor_id').val(),
			name: $('#vendor_name').val(),
			contact_person: $('#vendor_contact').val(),
			phone: $('#vendor_phone').val(),
			email: $('#vendor_email').val(),
			address: $('#vendor_address').val(),
			gst_number: $('#vendor_gst').val(),
			status: $('#vendor_status').val()
		};

		$('#save-vendor-btn').prop('disabled', true).text('<?php esc_html_e( 'Saving...', 'u-commerce' ); ?>');

		$.post(ajaxurl, formData, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message || '<?php esc_html_e( 'Failed to save vendor.', 'u-commerce' ); ?>');
				$('#save-vendor-btn').prop('disabled', false).text('<?php esc_html_e( 'Save Vendor', 'u-commerce' ); ?>');
			}
		});
	});

	// Confirm delete
	$('.uc-delete-btn').on('click', function(e) {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this vendor?', 'u-commerce' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});
});
</script>
