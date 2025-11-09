<?php
/**
 * Customers admin page.
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

$customers_handler = new UC_Customers();

// Handle delete
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$customer_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

if ( $action === 'delete' && $customer_id ) {
	check_admin_referer( 'delete_customer_' . $customer_id );

	$deleted = $customers_handler->delete( $customer_id );

	if ( $deleted ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Customer deleted successfully.', 'u-commerce' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete customer.', 'u-commerce' ) . '</p></div>';
	}
}

// Get all customers
$customers = $customers_handler->get_all();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'u-commerce' ); ?></h1>
	<button type="button" class="page-title-action" id="add-new-customer">
		<?php esc_html_e( 'Add New Customer', 'u-commerce' ); ?>
	</button>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $customers ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Email', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'GST Number', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $customers as $customer ) : ?>
						<tr>
							<td><?php echo esc_html( $customer->id ); ?></td>
							<td><strong><?php echo esc_html( $customer->name ); ?></strong></td>
							<td><?php echo esc_html( $customer->phone ); ?></td>
							<td><?php echo esc_html( $customer->email ); ?></td>
							<td><code><?php echo esc_html( $customer->gst_number ); ?></code></td>
							<td>
								<span class="uc-badge uc-badge-<?php echo esc_attr( $customer->status === 'active' ? 'success' : 'warning' ); ?>">
									<?php echo esc_html( ucfirst( $customer->status ) ); ?>
								</span>
							</td>
							<td>
								<button type="button"
										class="button button-small edit-customer-btn"
										data-id="<?php echo esc_attr( $customer->id ); ?>"
										data-name="<?php echo esc_attr( $customer->name ); ?>"
										data-phone="<?php echo esc_attr( $customer->phone ); ?>"
										data-email="<?php echo esc_attr( $customer->email ); ?>"
										data-address="<?php echo esc_attr( $customer->address ); ?>"
										data-gst="<?php echo esc_attr( $customer->gst_number ); ?>"
										data-status="<?php echo esc_attr( $customer->status ); ?>">
									<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
								</button>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-customers&action=delete&id=' . $customer->id ), 'delete_customer_' . $customer->id ) ); ?>"
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
					<?php esc_html_e( 'No customers found. Click "Add New Customer" to create your first customer.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- Customer Modal -->
<div id="customer-modal" class="uc-modal" style="display: none;">
	<div class="uc-modal-content">
		<div class="uc-modal-header">
			<h2 id="modal-title"><?php esc_html_e( 'Add New Customer', 'u-commerce' ); ?></h2>
			<button type="button" class="uc-modal-close">&times;</button>
		</div>
		<div class="uc-modal-body">
			<form method="post" action="" id="customer-form">
				<input type="hidden" name="customer_id" id="customer_id" value="">

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="customer_name">
								<?php esc_html_e( 'Customer Name', 'u-commerce' ); ?>
								<span style="color: red;">*</span>
							</label>
						</th>
						<td>
							<input type="text"
								   name="name"
								   id="customer_name"
								   class="regular-text"
								   required>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_phone">
								<?php esc_html_e( 'Phone', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="tel"
								   name="phone"
								   id="customer_phone"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_email">
								<?php esc_html_e( 'Email', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="email"
								   name="email"
								   id="customer_email"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_address">
								<?php esc_html_e( 'Address', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<textarea name="address"
									  id="customer_address"
									  class="large-text"
									  rows="3"></textarea>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_gst">
								<?php esc_html_e( 'GST Number', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								   name="gst_number"
								   id="customer_gst"
								   class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_status">
								<?php esc_html_e( 'Status', 'u-commerce' ); ?>
							</label>
						</th>
						<td>
							<select name="status" id="customer_status" class="regular-text">
								<option value="active"><?php esc_html_e( 'Active', 'u-commerce' ); ?></option>
								<option value="inactive"><?php esc_html_e( 'Inactive', 'u-commerce' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary button-large" id="save-customer-btn">
						<?php esc_html_e( 'Save Customer', 'u-commerce' ); ?>
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
	$('#add-new-customer').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Add New Customer', 'u-commerce' ); ?>');
		$('#customer-form')[0].reset();
		$('#customer_id').val('');
		$('#customer-modal').fadeIn();
	});

	// Open modal for edit
	$('.edit-customer-btn').on('click', function() {
		$('#modal-title').text('<?php esc_html_e( 'Edit Customer', 'u-commerce' ); ?>');
		$('#customer_id').val($(this).data('id'));
		$('#customer_name').val($(this).data('name'));
		$('#customer_phone').val($(this).data('phone'));
		$('#customer_email').val($(this).data('email'));
		$('#customer_address').val($(this).data('address'));
		$('#customer_gst').val($(this).data('gst'));
		$('#customer_status').val($(this).data('status'));
		$('#customer-modal').fadeIn();
	});

	// Close modal
	$('.uc-modal-close').on('click', function() {
		$('#customer-modal').fadeOut();
	});

	// Close modal on outside click
	$(window).on('click', function(e) {
		if ($(e.target).hasClass('uc-modal')) {
			$('#customer-modal').fadeOut();
		}
	});

	// Handle form submission via AJAX
	$('#customer-form').on('submit', function(e) {
		e.preventDefault();

		var formData = {
			action: 'uc_save_customer',
			nonce: '<?php echo wp_create_nonce( 'uc_save_customer' ); ?>',
			customer_id: $('#customer_id').val(),
			name: $('#customer_name').val(),
			phone: $('#customer_phone').val(),
			email: $('#customer_email').val(),
			address: $('#customer_address').val(),
			gst_number: $('#customer_gst').val(),
			status: $('#customer_status').val()
		};

		$('#save-customer-btn').prop('disabled', true).text('<?php esc_html_e( 'Saving...', 'u-commerce' ); ?>');

		$.post(ajaxurl, formData, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message || '<?php esc_html_e( 'Failed to save customer.', 'u-commerce' ); ?>');
				$('#save-customer-btn').prop('disabled', false).text('<?php esc_html_e( 'Save Customer', 'u-commerce' ); ?>');
			}
		});
	});

	// Confirm delete
	$('.uc-delete-btn').on('click', function(e) {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this customer?', 'u-commerce' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});
});
</script>
