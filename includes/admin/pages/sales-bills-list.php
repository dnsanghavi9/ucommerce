<?php
/**
 * Sales Bills list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all bills
$bills = $sales_bills_handler->get_all( array( 'limit' => 100 ) );

// Get customers and centers for display
$customers_handler = new UC_Customers();
$centers_handler = new UC_Centers();
$all_customers = $customers_handler->get_all();
$all_centers = $centers_handler->get_all();

// Create lookup arrays
$customers_map = array();
foreach ( $all_customers as $customer ) {
	$customers_map[ $customer->id ] = $customer->name;
}

$centers_map = array();
foreach ( $all_centers as $center ) {
	$centers_map[ $center->id ] = $center->name;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Sales Bills', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New Sales Bill', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $bills ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 120px;"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
						<th style="width: 120px; text-align: right;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Payment', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $bills as $bill ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $bill->bill_number ); ?></strong></td>
							<td>
								<?php
								if ( $bill->customer_id && isset( $customers_map[ $bill->customer_id ] ) ) {
									echo esc_html( $customers_map[ $bill->customer_id ] );
								} else {
									echo '<span style="color: #999;">' . esc_html__( 'Walk-in customer', 'u-commerce' ) . '</span>';
								}
								?>
							</td>
							<td><?php echo esc_html( isset( $centers_map[ $bill->center_id ] ) ? $centers_map[ $bill->center_id ] : '-' ); ?></td>
							<td><?php echo esc_html( date( 'Y-m-d', strtotime( $bill->created_at ) ) ); ?></td>
							<td style="text-align: right;"><strong>₹<?php echo number_format( $bill->total_amount, 2 ); ?></strong></td>
							<td>
								<span class="uc-badge uc-badge-<?php echo esc_attr( $bill->payment_status === 'paid' ? 'success' : 'warning' ); ?>">
									<?php echo esc_html( ucfirst( $bill->payment_status ) ); ?>
								</span>
							</td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=view&id=' . $bill->id ) ); ?>"
								   class="button button-small">
									<?php esc_html_e( 'View', 'u-commerce' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=delete&id=' . $bill->id ), 'delete_sales_bill_' . $bill->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;"
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this sales bill?', 'u-commerce' ); ?>');">
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
					<?php esc_html_e( 'No sales bills found. Click "Add New Sales Bill" to create your first bill.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
