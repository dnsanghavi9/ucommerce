<?php
/**
 * Product Inventory Tab.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get inventory for this product across all centers
global $wpdb;
$database = new UC_Database();

$inventory_table = $database->get_table( 'inventory' );
$centers_table = $database->get_table( 'centers' );

$query = $wpdb->prepare(
	"SELECT
		i.*,
		c.name as center_name,
		c.type as center_type,
		c.status as center_status
	FROM {$inventory_table} i
	LEFT JOIN {$centers_table} c ON i.center_id = c.id
	WHERE i.product_id = %d
	ORDER BY c.type ASC, c.name ASC",
	$product->id
);

$inventory_records = $wpdb->get_results( $query );

// Calculate totals
$total_quantity = 0;
$total_available = 0;
$total_reserved = 0;

if ( $inventory_records ) {
	foreach ( $inventory_records as $inv ) {
		$total_quantity += $inv->quantity;
		$total_reserved += $inv->reserved_quantity;
		$total_available += ( $inv->quantity - $inv->reserved_quantity );
	}
}
?>

<div class="uc-card">
	<h2><?php esc_html_e( 'Inventory Levels', 'u-commerce' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Stock levels for this product across all centers:', 'u-commerce' ); ?></p>

	<?php if ( $inventory_records ) : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Type', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Total Quantity', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Reserved', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Available', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Reorder Level', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Last Updated', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $inventory_records as $inv ) : ?>
					<?php
					$available = $inv->quantity - $inv->reserved_quantity;
					$is_low_stock = ( $inv->reorder_level > 0 && $available <= $inv->reorder_level );
					$is_out_of_stock = ( $available <= 0 );
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $inv->center_name ? $inv->center_name : __( 'Unknown Center', 'u-commerce' ) ); ?></strong>
						</td>
						<td>
							<span class="uc-badge <?php echo $inv->center_type === 'main' ? 'uc-badge-info' : ''; ?>" style="<?php echo $inv->center_type !== 'main' ? 'background: #ddd; color: #333;' : ''; ?>">
								<?php echo esc_html( ucfirst( $inv->center_type ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( number_format( $inv->quantity ) ); ?></td>
						<td>
							<?php if ( $inv->reserved_quantity > 0 ) : ?>
								<span style="color: #d63638;">
									<?php echo esc_html( number_format( $inv->reserved_quantity ) ); ?>
								</span>
							<?php else : ?>
								<?php echo esc_html( number_format( $inv->reserved_quantity ) ); ?>
							<?php endif; ?>
						</td>
						<td>
							<strong style="color: <?php echo $is_out_of_stock ? '#d63638' : ( $is_low_stock ? '#dba617' : '#00a32a' ); ?>;">
								<?php echo esc_html( number_format( $available ) ); ?>
							</strong>
						</td>
						<td><?php echo esc_html( number_format( $inv->reorder_level ) ); ?></td>
						<td><?php echo esc_html( UC_Utilities::format_datetime( $inv->last_updated ) ); ?></td>
						<td>
							<?php if ( $is_out_of_stock ) : ?>
								<span class="uc-badge" style="background: #d63638; color: #fff;">
									<?php esc_html_e( 'Out of Stock', 'u-commerce' ); ?>
								</span>
							<?php elseif ( $is_low_stock ) : ?>
								<span class="uc-badge uc-badge-warning">
									<?php esc_html_e( 'Low Stock', 'u-commerce' ); ?>
								</span>
							<?php else : ?>
								<span class="uc-badge uc-badge-success">
									<?php esc_html_e( 'In Stock', 'u-commerce' ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory&center_id=' . $inv->center_id . '&product_id=' . $product->id ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Adjust Stock', 'u-commerce' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="2" style="text-align: right;"><strong><?php esc_html_e( 'Totals:', 'u-commerce' ); ?></strong></th>
					<th><strong><?php echo esc_html( number_format( $total_quantity ) ); ?></strong></th>
					<th><strong><?php echo esc_html( number_format( $total_reserved ) ); ?></strong></th>
					<th>
						<strong style="color: <?php echo $total_available <= 0 ? '#d63638' : '#00a32a'; ?>;">
							<?php echo esc_html( number_format( $total_available ) ); ?>
						</strong>
					</th>
					<th colspan="4"></th>
				</tr>
			</tfoot>
		</table>

		<div style="margin-top: 20px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory&product_id=' . $product->id ) ); ?>" class="button">
				<?php esc_html_e( 'View Full Inventory History', 'u-commerce' ); ?>
			</a>
		</div>
	<?php else : ?>
		<div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px;">
			<p style="font-size: 16px; color: #666;">
				<?php esc_html_e( 'No inventory records found for this product.', 'u-commerce' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory&action=add&product_id=' . $product->id ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add Initial Stock', 'u-commerce' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>
