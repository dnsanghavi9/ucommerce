<?php
/**
 * Purchase Bills list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all bills
$bills = $purchase_bills_handler->get_all( array( 'limit' => 100 ) );

// Get vendors and centers for display
$vendors_handler = new UC_Vendors();
$centers_handler = new UC_Centers();
$all_vendors = $vendors_handler->get_all();
$all_centers = $centers_handler->get_all();

// Create lookup arrays
$vendors_map = array();
foreach ( $all_vendors as $vendor ) {
	$vendors_map[ $vendor->id ] = $vendor->name;
}

$centers_map = array();
foreach ( $all_centers as $center ) {
	$centers_map[ $center->id ] = $center->name;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Purchase Bills', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New Purchase Bill', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $bills ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 120px;"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Vendor', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
						<th style="width: 120px; text-align: right;"><?php esc_html_e( 'Total Amount', 'u-commerce' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $bills as $bill ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $bill->bill_number ); ?></strong></td>
							<td>
								<?php
								if ( $bill->vendor_id && isset( $vendors_map[ $bill->vendor_id ] ) ) {
									echo esc_html( $vendors_map[ $bill->vendor_id ] );
								} else {
									echo '<span style="color: #999;">' . esc_html__( 'No vendor', 'u-commerce' ) . '</span>';
								}
								?>
							</td>
							<td><?php echo esc_html( isset( $centers_map[ $bill->center_id ] ) ? $centers_map[ $bill->center_id ] : '-' ); ?></td>
							<td><?php echo esc_html( date( 'Y-m-d', strtotime( $bill->bill_date ) ) ); ?></td>
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
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-purchase-bills&action=delete&id=' . $bill->id ), 'delete_purchase_bill_' . $bill->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;"
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this purchase bill?', 'u-commerce' ); ?>');">
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
					<?php esc_html_e( 'No purchase bills found. Click "Add New Purchase Bill" to create your first bill.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
