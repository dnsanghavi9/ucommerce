<?php
/**
 * Customers add/edit form with tabs.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = isset( $customer ) && $customer;
$page_title = $is_edit ? __( 'Edit Customer', 'u-commerce' ) : __( 'Add New Customer', 'u-commerce' );

// Get active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'basic';
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers' ) ); ?>" class="page-title-action">
		<?php esc_html_e( '← Back to Customers', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php if ( $is_edit ) : ?>
		<!-- Tabs Navigation -->
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers&action=edit&id=' . $customer->id . '&tab=basic' ) ); ?>"
			   class="nav-tab <?php echo $active_tab === 'basic' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Basic Info', 'u-commerce' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers&action=edit&id=' . $customer->id . '&tab=history' ) ); ?>"
			   class="nav-tab <?php echo $active_tab === 'history' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'History', 'u-commerce' ); ?>
			</a>
		</h2>
	<?php endif; ?>

	<?php if ( $active_tab === 'history' && $is_edit ) : ?>
		<!-- History Tab (Placeholder for future) -->
		<div class="uc-card">
			<h2><?php esc_html_e( 'Customer History', 'u-commerce' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This section will display sales history, payment details, and transaction records.', 'u-commerce' ); ?>
			</p>
			<div style="padding: 40px; text-align: center; background: #f9f9f9; border: 2px dashed #ddd; margin-top: 20px;">
				<p style="color: #999; font-size: 16px;">
					<?php esc_html_e( 'History features will be available after implementing the Bills module.', 'u-commerce' ); ?>
				</p>
			</div>
		</div>
	<?php else : ?>
		<!-- Basic Info Form -->
		<form method="post" action="">
			<?php wp_nonce_field( 'uc_customer_save', 'uc_customer_nonce' ); ?>

			<div class="uc-card">
				<h2><?php esc_html_e( 'Customer Information', 'u-commerce' ); ?></h2>

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
								   value="<?php echo $is_edit ? esc_attr( $customer->name ) : ''; ?>"
								   class="regular-text"
								   required>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="customer_phone">
								<?php esc_html_e( 'Phone Number', 'u-commerce' ); ?>
								<span style="color: red;">*</span>
							</label>
						</th>
						<td>
							<input type="tel"
								   name="phone"
								   id="customer_phone"
								   value="<?php echo $is_edit ? esc_attr( $customer->phone ) : ''; ?>"
								   class="regular-text"
								   pattern="[6-9][0-9]{9}"
								   title="10 digit mobile number starting with 6, 7, 8, or 9"
								   required>
							<p class="description"><?php esc_html_e( 'Enter 10 digit Indian mobile number (starting with 6, 7, 8, or 9). Must be unique.', 'u-commerce' ); ?></p>
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
								   value="<?php echo $is_edit ? esc_attr( $customer->email ) : ''; ?>"
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
									  rows="3"><?php echo $is_edit ? esc_textarea( $customer->address ) : ''; ?></textarea>
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
								<option value="active" <?php echo ( $is_edit && $customer->status === 'active' ) ? 'selected' : ''; ?>>
									<?php esc_html_e( 'Active', 'u-commerce' ); ?>
								</option>
								<option value="inactive" <?php echo ( $is_edit && $customer->status === 'inactive' ) ? 'selected' : ''; ?>>
									<?php esc_html_e( 'Inactive', 'u-commerce' ); ?>
								</option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="uc_customer_submit" class="button button-primary button-large" value="<?php echo $is_edit ? esc_attr__( 'Update Customer', 'u-commerce' ) : esc_attr__( 'Create Customer', 'u-commerce' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers' ) ); ?>" class="button button-large" style="margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
					</a>
				</p>
			</div>
		</form>
	<?php endif; ?>
</div>
