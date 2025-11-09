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
		<!-- History Tab - Sales Bills -->
		<?php
		// Get sales bills for this customer
		global $wpdb;
		$database = new UC_Database();

		$sales_bills_table = $database->get_table( 'sales_bills' );
		$centers_table = $database->get_table( 'centers' );

		$query = $wpdb->prepare(
			"SELECT
				sb.*,
				c.name as center_name,
				(SELECT COUNT(*) FROM {$database->get_table( 'sales_items' )} WHERE sales_bill_id = sb.id) as items_count
			FROM {$sales_bills_table} sb
			LEFT JOIN {$centers_table} c ON sb.center_id = c.id
			WHERE sb.customer_id = %d
			ORDER BY sb.created_at DESC, sb.id DESC",
			$customer->id
		);

		$sales_bills = $wpdb->get_results( $query );

		// Calculate totals
		$total_bills = count( $sales_bills );
		$total_amount = 0;
		$total_paid = 0;
		$total_unpaid = 0;
		if ( $sales_bills ) {
			foreach ( $sales_bills as $bill ) {
				$total_amount += $bill->total_amount;
				if ( $bill->payment_status === 'paid' ) {
					$total_paid += $bill->total_amount;
				} else {
					$total_unpaid += $bill->total_amount;
				}
			}
		}
		?>

		<div class="uc-card">
			<h2><?php esc_html_e( 'Sales History', 'u-commerce' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'All sales bills for this customer:', 'u-commerce' ); ?>
			</p>

			<!-- Summary -->
			<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Total Bills', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #0073aa;"><?php echo number_format( $total_bills ); ?></div>
				</div>
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #46b450;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #46b450;">₹<?php echo number_format( $total_amount, 2 ); ?></div>
				</div>
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #00a32a;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Paid', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #00a32a;">₹<?php echo number_format( $total_paid, 2 ); ?></div>
				</div>
				<div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #dc3232;">
					<div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php esc_html_e( 'Unpaid', 'u-commerce' ); ?></div>
					<div style="font-size: 24px; font-weight: bold; color: #dc3232;">₹<?php echo number_format( $total_unpaid, 2 ); ?></div>
				</div>
			</div>

			<?php if ( $sales_bills ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 120px;"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
							<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
							<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Items', 'u-commerce' ); ?></th>
							<th style="width: 120px; text-align: right;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></th>
							<th style="width: 120px;"><?php esc_html_e( 'Payment Status', 'u-commerce' ); ?></th>
							<th style="width: 120px;"><?php esc_html_e( 'Payment Method', 'u-commerce' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Action', 'u-commerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sales_bills as $bill ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $bill->bill_number ); ?></strong></td>
								<td><?php echo esc_html( date( 'Y-m-d', strtotime( $bill->created_at ) ) ); ?></td>
								<td><?php echo esc_html( $bill->center_name ? $bill->center_name : '—' ); ?></td>
								<td style="text-align: center;"><?php echo number_format( $bill->items_count ); ?></td>
								<td style="text-align: right;"><strong>₹<?php echo number_format( $bill->total_amount, 2 ); ?></strong></td>
								<td>
									<span class="uc-badge uc-badge-<?php echo esc_attr( $bill->payment_status === 'paid' ? 'success' : 'warning' ); ?>">
										<?php echo esc_html( ucfirst( str_replace( '_', ' ', $bill->payment_status ) ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $bill->payment_method ) ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=view&id=' . $bill->id ) ); ?>"
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
							<th style="text-align: center;"><strong><?php echo number_format( array_sum( wp_list_pluck( $sales_bills, 'items_count' ) ) ); ?></strong></th>
							<th style="text-align: right;"><strong>₹<?php echo number_format( $total_amount, 2 ); ?></strong></th>
							<th colspan="3"></th>
						</tr>
					</tfoot>
				</table>
			<?php else : ?>
				<div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px; margin-top: 20px;">
					<p style="font-size: 16px; color: #666;">
						<?php esc_html_e( 'No sales bills for this customer yet.', 'u-commerce' ); ?>
					</p>
				</div>
			<?php endif; ?>
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
