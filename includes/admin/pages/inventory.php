<?php
/**
 * Inventory admin page - Dashboard with stock levels.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_inventory' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

// Get filter parameters
$selected_center = isset( $_GET['center_filter'] ) ? absint( $_GET['center_filter'] ) : 0;
$stock_filter = isset( $_GET['stock_filter'] ) ? sanitize_text_field( $_GET['stock_filter'] ) : 'all';
$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

// Get all centers
$centers_handler = new UC_Centers();
$all_centers = $centers_handler->get_all();

// Get inventory data
$inventory_handler = new UC_Inventory();
global $wpdb;
$database = new UC_Database();
$inventory_table = $database->get_table( 'inventory' );
$products_table = $database->get_table( 'products' );
$centers_table = $database->get_table( 'centers' );

// Build query
$where_clauses = array( '1=1' );

if ( $selected_center ) {
	$where_clauses[] = $wpdb->prepare( 'i.center_id = %d', $selected_center );
}

if ( $search ) {
	$where_clauses[] = $wpdb->prepare( '(p.name LIKE %s OR p.sku LIKE %s)', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
}

$where = implode( ' AND ', $where_clauses );

$query = "
	SELECT
		i.id,
		i.product_id,
		i.center_id,
		i.quantity,
		i.reserved_quantity,
		(i.quantity - i.reserved_quantity) as available_quantity,
		p.name as product_name,
		p.sku,
		p.base_cost,
		c.name as center_name
	FROM {$inventory_table} i
	INNER JOIN {$products_table} p ON i.product_id = p.id
	INNER JOIN {$centers_table} c ON i.center_id = c.id
	WHERE {$where}
	ORDER BY p.name ASC, c.name ASC
";

$inventory_items = $wpdb->get_results( $query );

// Apply stock filter
if ( $stock_filter !== 'all' && $inventory_items ) {
	$filtered_items = array();
	foreach ( $inventory_items as $item ) {
		$matches = false;

		switch ( $stock_filter ) {
			case 'out_of_stock':
				$matches = ( $item->quantity <= 0 );
				break;
			case 'low_stock':
				$matches = ( $item->quantity > 0 && $item->quantity <= 10 );
				break;
			case 'in_stock':
				$matches = ( $item->quantity > 10 );
				break;
		}

		if ( $matches ) {
			$filtered_items[] = $item;
		}
	}
	$inventory_items = $filtered_items;
}

// Calculate summary statistics
$total_products = 0;
$out_of_stock = 0;
$low_stock = 0;
$total_value = 0;

if ( $inventory_items ) {
	$product_ids = array();
	foreach ( $inventory_items as $item ) {
		if ( ! in_array( $item->product_id, $product_ids ) ) {
			$product_ids[] = $item->product_id;
			$total_products++;
		}

		if ( $item->quantity <= 0 ) {
			$out_of_stock++;
		} elseif ( $item->quantity <= 10 ) {
			$low_stock++;
		}

		$total_value += ( $item->quantity * $item->base_cost );
	}
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Inventory Dashboard', 'u-commerce' ); ?></h1>
	<hr class="wp-header-end">

	<!-- Summary Cards -->
	<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0;">
		<div class="uc-card" style="padding: 20px; text-align: center;">
			<div style="font-size: 14px; color: #666; margin-bottom: 10px;"><?php esc_html_e( 'Total Products', 'u-commerce' ); ?></div>
			<div style="font-size: 32px; font-weight: bold; color: #0073aa;"><?php echo number_format( $total_products ); ?></div>
		</div>
		<div class="uc-card" style="padding: 20px; text-align: center;">
			<div style="font-size: 14px; color: #666; margin-bottom: 10px;"><?php esc_html_e( 'Out of Stock', 'u-commerce' ); ?></div>
			<div style="font-size: 32px; font-weight: bold; color: #dc3232;"><?php echo number_format( $out_of_stock ); ?></div>
		</div>
		<div class="uc-card" style="padding: 20px; text-align: center;">
			<div style="font-size: 14px; color: #666; margin-bottom: 10px;"><?php esc_html_e( 'Low Stock', 'u-commerce' ); ?></div>
			<div style="font-size: 32px; font-weight: bold; color: #f56e28;"><?php echo number_format( $low_stock ); ?></div>
		</div>
		<div class="uc-card" style="padding: 20px; text-align: center;">
			<div style="font-size: 14px; color: #666; margin-bottom: 10px;"><?php esc_html_e( 'Inventory Value', 'u-commerce' ); ?></div>
			<div style="font-size: 32px; font-weight: bold; color: #46b450;">₹<?php echo number_format( $total_value, 2 ); ?></div>
		</div>
	</div>

	<!-- Filters -->
	<div class="uc-card" style="padding: 15px; margin-bottom: 20px;">
		<form method="get" action="">
			<input type="hidden" name="page" value="u-commerce-inventory">
			<div style="display: flex; gap: 15px; align-items: center;">
				<div>
					<label for="center_filter" style="margin-right: 5px;"><?php esc_html_e( 'Center:', 'u-commerce' ); ?></label>
					<select name="center_filter" id="center_filter" class="regular-text">
						<option value="0"><?php esc_html_e( 'All Centers', 'u-commerce' ); ?></option>
						<?php foreach ( $all_centers as $center ) : ?>
							<option value="<?php echo esc_attr( $center->id ); ?>" <?php selected( $selected_center, $center->id ); ?>>
								<?php echo esc_html( $center->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label for="stock_filter" style="margin-right: 5px;"><?php esc_html_e( 'Stock Level:', 'u-commerce' ); ?></label>
					<select name="stock_filter" id="stock_filter" class="regular-text">
						<option value="all" <?php selected( $stock_filter, 'all' ); ?>><?php esc_html_e( 'All', 'u-commerce' ); ?></option>
						<option value="out_of_stock" <?php selected( $stock_filter, 'out_of_stock' ); ?>><?php esc_html_e( 'Out of Stock', 'u-commerce' ); ?></option>
						<option value="low_stock" <?php selected( $stock_filter, 'low_stock' ); ?>><?php esc_html_e( 'Low Stock (≤10)', 'u-commerce' ); ?></option>
						<option value="in_stock" <?php selected( $stock_filter, 'in_stock' ); ?>><?php esc_html_e( 'In Stock (>10)', 'u-commerce' ); ?></option>
					</select>
				</div>

				<div>
					<label for="search" style="margin-right: 5px;"><?php esc_html_e( 'Search:', 'u-commerce' ); ?></label>
					<input type="text" name="search" id="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Product name or SKU', 'u-commerce' ); ?>" class="regular-text">
				</div>

				<button type="submit" class="button button-primary"><?php esc_html_e( 'Filter', 'u-commerce' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'u-commerce' ); ?></a>
			</div>
		</form>
	</div>

	<!-- Inventory Table -->
	<div class="uc-card">
		<?php if ( $inventory_items ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 30%;"><?php esc_html_e( 'Product', 'u-commerce' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'SKU', 'u-commerce' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
						<th style="width: 10%; text-align: center;"><?php esc_html_e( 'In Stock', 'u-commerce' ); ?></th>
						<th style="width: 10%; text-align: center;"><?php esc_html_e( 'Reserved', 'u-commerce' ); ?></th>
						<th style="width: 10%; text-align: center;"><?php esc_html_e( 'Available', 'u-commerce' ); ?></th>
						<th style="width: 10%; text-align: center;"><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $inventory_items as $item ) : ?>
						<?php
						// Determine status
						$status_class = 'success';
						$status_text = __( 'In Stock', 'u-commerce' );

						if ( $item->quantity <= 0 ) {
							$status_class = 'error';
							$status_text = __( 'Out of Stock', 'u-commerce' );
						} elseif ( $item->quantity <= 10 ) {
							$status_class = 'warning';
							$status_text = __( 'Low Stock', 'u-commerce' );
						}
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $item->product_name ); ?></strong>
							</td>
							<td><code><?php echo esc_html( $item->sku ); ?></code></td>
							<td><?php echo esc_html( $item->center_name ); ?></td>
							<td style="text-align: center;"><strong><?php echo number_format( $item->quantity ); ?></strong></td>
							<td style="text-align: center;"><?php echo number_format( $item->reserved_quantity ); ?></td>
							<td style="text-align: center;"><strong><?php echo number_format( $item->available_quantity ); ?></strong></td>
							<td style="text-align: center;">
								<span class="uc-badge uc-badge-<?php echo esc_attr( $status_class ); ?>">
									<?php echo esc_html( $status_text ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div style="padding: 40px; text-align: center;">
				<p style="font-size: 16px; color: #666;">
					<?php esc_html_e( 'No inventory found. Create purchase bills to add stock.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
