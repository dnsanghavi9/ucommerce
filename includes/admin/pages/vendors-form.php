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
		<!-- History Tab (Placeholder for future) -->
		<div class="uc-card">
			<h2><?php esc_html_e( 'Vendor History', 'u-commerce' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This section will display purchase history, payment details, and transaction records.', 'u-commerce' ); ?>
			</p>
			<div style="padding: 40px; text-align: center; background: #f9f9f9; border: 2px dashed #ddd; margin-top: 20px;">
				<p style="color: #999; font-size: 16px;">
					<?php esc_html_e( 'History features will be available after implementing the Bills module.', 'u-commerce' ); ?>
				</p>
			</div>
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
													   pattern="[0-9]{10,15}"
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
								'<td><input type="tel" name="contact_mobiles[]" class="regular-text" pattern="[0-9]{10,15}" required></td>' +
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
									   pattern="[0-9]{10,15}"
									   title="Phone number must be 10-15 digits"
									   required>
								<p class="description"><?php esc_html_e( 'Enter 10-15 digit phone number (numbers only).', 'u-commerce' ); ?></p>
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
