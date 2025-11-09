<?php
/**
 * Sales Bills view page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get related data
$customers_handler = new UC_Customers();
$centers_handler = new UC_Centers();

$customer = $bill->customer_id ? $customers_handler->get( $bill->customer_id ) : null;
$center = $centers_handler->get( $bill->center_id );
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Sales Bill', 'u-commerce' ); ?>: <?php echo esc_html( $bill->bill_number ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills' ) ); ?>" class="page-title-action">
		<?php esc_html_e( '← Back to Sales Bills', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<div class="uc-card">
		<h2><?php esc_html_e( 'Bill Information', 'u-commerce' ); ?></h2>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></th>
				<td><strong><?php echo esc_html( $bill->bill_number ); ?></strong></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Customer', 'u-commerce' ); ?></th>
				<td>
					<?php
					if ( $customer ) {
						echo esc_html( $customer->name );
						echo '<br><span style="color: #666;">' . esc_html( $customer->phone ) . '</span>';
					} else {
						echo '<span style="color: #999;">' . esc_html__( 'Walk-in customer', 'u-commerce' ) . '</span>';
					}
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
				<td><?php echo esc_html( $center ? $center->name : '-' ); ?></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
				<td><?php echo esc_html( date( 'F j, Y g:i A', strtotime( $bill->created_at ) ) ); ?></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Payment Status', 'u-commerce' ); ?></th>
				<td>
					<span class="uc-badge uc-badge-<?php echo esc_attr( $bill->payment_status === 'paid' ? 'success' : ( $bill->payment_status === 'pending' ? 'warning' : 'info' ) ); ?>">
						<?php echo esc_html( ucfirst( $bill->payment_status ) ); ?>
					</span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Payment Method', 'u-commerce' ); ?></th>
				<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $bill->payment_method ) ) ); ?></td>
			</tr>

			<?php if ( $bill->notes ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Notes', 'u-commerce' ); ?></th>
					<td><?php echo esc_html( $bill->notes ); ?></td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Created By', 'u-commerce' ); ?></th>
				<td>
					<?php
					$creator = get_userdata( $bill->created_by );
					echo esc_html( $creator ? $creator->display_name : '-' );
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Created At', 'u-commerce' ); ?></th>
				<td><?php echo esc_html( date( 'F j, Y g:i A', strtotime( $bill->created_at ) ) ); ?></td>
			</tr>
		</table>
	</div>

	<div class="uc-card" style="margin-top: 20px;">
		<h2><?php esc_html_e( 'Sales Items', 'u-commerce' ); ?></h2>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 40%;"><?php esc_html_e( 'Product', 'u-commerce' ); ?></th>
					<th style="width: 15%;"><?php esc_html_e( 'Quantity', 'u-commerce' ); ?></th>
					<th style="width: 20%;"><?php esc_html_e( 'Unit Price (₹)', 'u-commerce' ); ?></th>
					<th style="width: 25%;"><?php esc_html_e( 'Total (₹)', 'u-commerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $bill->items ) : ?>
					<?php foreach ( $bill->items as $item ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $item->product_name ); ?></strong><br>
								<span style="color: #666; font-size: 12px;">SKU: <?php echo esc_html( $item->sku ); ?></span>
							</td>
							<td><?php echo esc_html( $item->quantity ); ?></td>
							<td>₹<?php echo number_format( $item->unit_price, 2 ); ?></td>
							<td><strong>₹<?php echo number_format( $item->total_price, 2 ); ?></strong></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="4" style="text-align: center; padding: 20px; color: #999;">
							<?php esc_html_e( 'No items found.', 'u-commerce' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" style="text-align: right; font-weight: bold; padding: 15px;">
						<?php esc_html_e( 'Grand Total:', 'u-commerce' ); ?>
					</td>
					<td style="padding: 15px;">
						<strong style="font-size: 18px;">₹<?php echo number_format( $bill->total_amount, 2 ); ?></strong>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<p class="submit">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills' ) ); ?>" class="button button-large">
			<?php esc_html_e( '← Back to List', 'u-commerce' ); ?>
		</a>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=delete&id=' . $bill->id ), 'delete_sales_bill_' . $bill->id ) ); ?>"
		   class="button button-large"
		   style="color: #b32d2e; margin-left: 10px;"
		   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this sales bill? This will not restore inventory.', 'u-commerce' ); ?>');">
			<?php esc_html_e( 'Delete Bill', 'u-commerce' ); ?>
		</a>
	</p>
</div>
