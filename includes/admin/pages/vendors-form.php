<?php
/**
 * Vendors add/edit form with tabs.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = isset( $vendor ) && $vendor;
$page_title = $is_edit ? __( 'Edit Vendor', 'u-commerce' ) : __( 'Add New Vendor', 'u-commerce' );

// Get active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'basic';

// Get vendor contacts if editing
$vendor_contacts = array();
if ( $is_edit ) {
	global $wpdb;
	$database = new UC_Database();
	$contacts_table = $database->get_table( 'vendor_contacts' );
	$vendor_contacts = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $contacts_table WHERE vendor_id = %d ORDER BY id ASC",
		$vendor->id
	) );
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors' ) ); ?>" class="page-title-action">
		<?php esc_html_e( '← Back to Vendors', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php if ( $is_edit ) : ?>
		<!-- Tabs Navigation -->
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=edit&id=' . $vendor->id . '&tab=basic' ) ); ?>"
			   class="nav-tab <?php echo $active_tab === 'basic' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Basic Info', 'u-commerce' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=edit&id=' . $vendor->id . '&tab=contacts' ) ); ?>"
			   class="nav-tab <?php echo $active_tab === 'contacts' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Contact Persons', 'u-commerce' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=edit&id=' . $vendor->id . '&tab=history' ) ); ?>"
			   class="nav-tab <?php echo $active_tab === 'history' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'History', 'u-commerce' ); ?>
			</a>
		</h2>
	<?php endif; ?>

	<?php if ( $active_tab === 'history' && $is_edit ) : ?>
		<!-- History Tab - Purchase Bills -->
		<?php
		// Get purchase bills for this vendor
		global $wpdb;
		$database = new UC_Database();

		$purchase_bills_table = $database->get_table( 'purchase_bills' );
		$centers_table = $database->get_table( 'centers' );

		$query = $wpdb->prepare(
			"SELECT
				pb.*,
				c.name as center_name,
				(SELECT COUNT(*) FROM {$database->get_table( 'purchase_items' )} WHERE purchase_bill_id = pb.id) as items_count
			FROM {$purchase_bills_table} pb
			LEFT JOIN {$centers_table} c ON pb.center_id = c.id
			WHERE pb.vendor_id = %d
			ORDER BY pb.bill_date DESC, pb.id DESC",
			$vendor->id
		);

		$purchase_bills = $wpdb->get_results( $query );

		// Calculate totals
		$total_bills = count( $purchase_bills );
		$total_amount = 0;
		if ( $purchase_bills ) {
			$total_amount = array_sum( wp_list_pluck( $purchase_bills, 'total_amount' ) );
		}
		?>

		<div class="uc-card">
			<h2><?php esc_html_e( 'Purchase History', 'u-commerce' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'All purchase bills from this vendor:', 'u-commerce' ); ?>
			</p>

			<!-- Summary -->
			<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0;">
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Total Bills', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #0073aa;"><?php echo number_format( $total_bills ); ?></div>
				</div>
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #46b450;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #46b450;">₹<?php echo number_format( $total_amount, 2 ); ?></div>
				</div>
			</div>

			<?php if ( $purchase_bills ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 120px;"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
							<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
							<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Items', 'u-commerce' ); ?></th>
							<th style="width: 120px; text-align: right;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Action', 'u-commerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $purchase_bills as $bill ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $bill->bill_number ); ?></strong></td>
								<td><?php echo esc_html( date( 'Y-m-d', strtotime( $bill->bill_date ) ) ); ?></td>
								<td><?php echo esc_html( $bill->center_name ? $bill->center_name : '—' ); ?></td>
								<td style="text-align: center;"><?php echo number_format( $bill->items_count ); ?></td>
								<td style="text-align: right;"><strong>₹<?php echo number_format( $bill->total_amount, 2 ); ?></strong></td>
								<td>
									<span class="uc-badge uc-badge-<?php echo esc_attr( $bill->status === 'completed' ? 'success' : 'warning' ); ?>">
										<?php echo esc_html( ucfirst( $bill->status ) ); ?>
									</span>
								</td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills&action=view&id=' . $bill->id ) ); ?>"
									   class="button button-small">
										<?php esc_html_e( 'View', 'u-commerce' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="3" style="text-align: right;"><strong><?php esc_html_e( 'Totals:', 'u-commerce' ); ?></strong></th>
							<th style="text-align: center;"><strong><?php echo number_format( array_sum( wp_list_pluck( $purchase_bills, 'items_count' ) ) ); ?></strong></th>
							<th style="text-align: right;"><strong>₹<?php echo number_format( $total_amount, 2 ); ?></strong></th>
							<th colspan="2"></th>
						</tr>
					</tfoot>
				</table>
			<?php else : ?>
				<div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px; margin-top: 20px;">
					<p style="font-size: 16px; color: #666;">
						<?php esc_html_e( 'No purchase bills from this vendor yet.', 'u-commerce' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<!-- Form for Basic and Contacts tabs -->
		<form method="post" action="">
			<?php wp_nonce_field( 'uc_vendor_save', 'uc_vendor_nonce' ); ?>
			<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<div class="uc-card">
				<?php if ( $active_tab === 'contacts' && $is_edit ) : ?>
					<!-- Contact Persons Tab -->
					<h2><?php esc_html_e( 'Contact Persons', 'u-commerce' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Add multiple contact persons for this vendor with their names and mobile numbers.', 'u-commerce' ); ?>
					</p>

					<div id="contacts-container" style="margin-top: 20px;">
						<?php if ( $vendor_contacts ) : ?>
							<?php foreach ( $vendor_contacts as $index => $contact ) : ?>
								<div class="contact-row" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-left: 3px solid #0073aa;">
									<table class="form-table" style="margin: 0;">
										<tr>
											<th scope="row" style="width: 150px;">
												<label><?php esc_html_e( 'Contact Name', 'u-commerce' ); ?> <span style="color: red;">*</span></label>
											</th>
											<td style="padding-right: 20px;">
												<input type="text"
													   name="contact_names[]"
													   value="<?php echo esc_attr( $contact->contact_name ); ?>"
													   class="regular-text"
													   required>
											</td>
											<th scope="row" style="width: 150px;">
												<label><?php esc_html_e( 'Mobile Number', 'u-commerce' ); ?> <span style="color: red;">*</span></label>
											</th>
											<td>
												<input type="tel"
													   name="contact_mobiles[]"
													   value="<?php echo esc_attr( $contact->contact_mobile ); ?>"
													   class="regular-text"
													   pattern="[6-9][0-9]{9}"
													   title="10 digit mobile number starting with 6, 7, 8, or 9"
													   required>
											</td>
											<td style="width: 100px;">
												<button type="button" class="button remove-contact-btn" style="color: #b32d2e;">
													<?php esc_html_e( 'Remove', 'u-commerce' ); ?>
												</button>
											</td>
										</tr>
									</table>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<button type="button" id="add-contact-btn" class="button button-secondary" style="margin-top: 15px;">
						<?php esc_html_e( '+ Add Contact Person', 'u-commerce' ); ?>
					</button>

					<p class="submit">
						<input type="submit" name="uc_vendor_submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save Contact Persons', 'u-commerce' ); ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors' ) ); ?>" class="button button-large" style="margin-left: 10px;">
							<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
						</a>
					</p>

					<script>
					jQuery(document).ready(function($) {
						// Add new contact row
						$('#add-contact-btn').on('click', function() {
							var newRow = $('<div class="contact-row" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-left: 3px solid #0073aa;">' +
								'<table class="form-table" style="margin: 0;">' +
								'<tr>' +
								'<th scope="row" style="width: 150px;"><label><?php esc_html_e( 'Contact Name', 'u-commerce' ); ?> <span style="color: red;">*</span></label></th>' +
								'<td style="padding-right: 20px;"><input type="text" name="contact_names[]" class="regular-text" required></td>' +
								'<th scope="row" style="width: 150px;"><label><?php esc_html_e( 'Mobile Number', 'u-commerce' ); ?> <span style="color: red;">*</span></label></th>' +
								'<td><input type="tel" name="contact_mobiles[]" class="regular-text" pattern="[6-9][0-9]{9}" title="10 digit mobile number starting with 6, 7, 8, or 9" required></td>' +
								'<td style="width: 100px;"><button type="button" class="button remove-contact-btn" style="color: #b32d2e;"><?php esc_html_e( 'Remove', 'u-commerce' ); ?></button></td>' +
								'</tr>' +
								'</table>' +
								'</div>');
							$('#contacts-container').append(newRow);
						});

						// Remove contact row
						$(document).on('click', '.remove-contact-btn', function() {
							$(this).closest('.contact-row').remove();
						});
					});
					</script>

				<?php else : ?>
					<!-- Basic Info Tab -->
					<h2><?php esc_html_e( 'Vendor Information', 'u-commerce' ); ?></h2>

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
									   value="<?php echo $is_edit ? esc_attr( $vendor->name ) : ''; ?>"
									   class="regular-text"
									   required>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="vendor_phone">
									<?php esc_html_e( 'Phone Number', 'u-commerce' ); ?>
									<span style="color: red;">*</span>
								</label>
							</th>
							<td>
								<input type="tel"
									   name="phone"
									   id="vendor_phone"
									   value="<?php echo $is_edit ? esc_attr( $vendor->phone ) : ''; ?>"
									   class="regular-text"
									   pattern="[6-9][0-9]{9}"
									   title="10 digit mobile number starting with 6, 7, 8, or 9"
									   required>
								<p class="description"><?php esc_html_e( 'Enter 10 digit Indian mobile number (starting with 6, 7, 8, or 9).', 'u-commerce' ); ?></p>
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
									   value="<?php echo $is_edit ? esc_attr( $vendor->email ) : ''; ?>"
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
										  rows="3"><?php echo $is_edit ? esc_textarea( $vendor->address ) : ''; ?></textarea>
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
									   value="<?php echo $is_edit ? esc_attr( $vendor->gst_number ) : ''; ?>"
									   class="regular-text"
									   pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}"
									   title="GST format: 22AAAAA0000A1Z5"
									   style="text-transform: uppercase;">
								<p class="description"><?php esc_html_e( 'Format: 22AAAAA0000A1Z5 (15 characters)', 'u-commerce' ); ?></p>
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
									<option value="active" <?php echo ( $is_edit && $vendor->status === 'active' ) ? 'selected' : ''; ?>>
										<?php esc_html_e( 'Active', 'u-commerce' ); ?>
									</option>
									<option value="inactive" <?php echo ( $is_edit && $vendor->status === 'inactive' ) ? 'selected' : ''; ?>>
										<?php esc_html_e( 'Inactive', 'u-commerce' ); ?>
									</option>
								</select>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="uc_vendor_submit" class="button button-primary button-large" value="<?php echo $is_edit ? esc_attr__( 'Update Vendor', 'u-commerce' ) : esc_attr__( 'Create Vendor', 'u-commerce' ); ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors' ) ); ?>" class="button button-large" style="margin-left: 10px;">
							<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		</form>
	<?php endif; ?>
</div>
