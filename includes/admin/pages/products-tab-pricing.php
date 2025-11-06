<?php
/**
 * Product Pricing Tab.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get pricing for this product across all centers
global $wpdb;
$database = new UC_Database();

$pricing_table = $database->get_table( 'pricing' );
$centers_table = $database->get_table( 'centers' );

$query = $wpdb->prepare(
	"SELECT
		p.*,
		c.name as center_name,
		c.type as center_type,
		c.status as center_status
	FROM {$pricing_table} p
	LEFT JOIN {$centers_table} c ON p.center_id = c.id
	WHERE p.product_id = %d
	ORDER BY c.type ASC, c.name ASC, p.effective_from DESC",
	$product->id
);

$pricing_records = $wpdb->get_results( $query );

// Get all active centers for the add pricing form
$all_centers = $wpdb->get_results(
	"SELECT id, name, type, status FROM {$centers_table} WHERE status = 'active' ORDER BY type ASC, name ASC"
);

// Calculate margin percentage helper
function calculate_margin( $selling_price, $base_cost ) {
	if ( $base_cost <= 0 ) {
		return 0;
	}
	return ( ( $selling_price - $base_cost ) / $base_cost ) * 100;
}

// Get current date for effective_from default
$current_date = current_time( 'Y-m-d' );
?>

<div class="uc-card">
	<h2><?php esc_html_e( 'Pricing Information', 'u-commerce' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Manage selling prices for this product across different centers.', 'u-commerce' ); ?>
		<br>
		<?php
		printf(
			/* translators: %s: base cost */
			esc_html__( 'Base Cost: %s', 'u-commerce' ),
			'<strong>' . esc_html( UC_Utilities::format_price( $product->base_cost ) ) . '</strong>'
		);
		?>
	</p>

	<?php if ( $pricing_records ) : ?>
		<table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Selling Price', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Margin', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Effective From', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Effective To', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pricing_records as $price ) : ?>
					<?php
					$margin = calculate_margin( $price->selling_price, $product->base_cost );
					$is_active = true;
					$current_datetime = current_time( 'mysql' );

					// Check if price is currently active based on effective dates
					if ( $price->effective_from && $price->effective_from > $current_datetime ) {
						$is_active = false; // Future price
					}
					if ( $price->effective_to && $price->effective_to < $current_datetime ) {
						$is_active = false; // Expired price
					}
					?>
					<tr style="<?php echo ! $is_active ? 'opacity: 0.6;' : ''; ?>">
						<td>
							<strong><?php echo esc_html( $price->center_name ? $price->center_name : __( 'All Centers', 'u-commerce' ) ); ?></strong>
							<?php if ( $price->center_type ) : ?>
								<br>
								<span class="uc-badge <?php echo $price->center_type === 'main' ? 'uc-badge-info' : ''; ?>" style="<?php echo $price->center_type !== 'main' ? 'background: #ddd; color: #333; font-size: 11px;' : 'font-size: 11px;'; ?>">
									<?php echo esc_html( ucfirst( $price->center_type ) ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td>
							<strong><?php echo esc_html( UC_Utilities::format_price( $price->selling_price ) ); ?></strong>
						</td>
						<td>
							<span style="color: <?php echo $margin >= 0 ? '#00a32a' : '#d63638'; ?>;">
								<?php echo esc_html( number_format( $margin, 2 ) ); ?>%
							</span>
							<?php if ( $margin < 0 ) : ?>
								<br><small style="color: #d63638;"><?php esc_html_e( '(Loss)', 'u-commerce' ); ?></small>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $price->effective_from ? UC_Utilities::format_date( $price->effective_from ) : '—' ); ?></td>
						<td><?php echo esc_html( $price->effective_to ? UC_Utilities::format_date( $price->effective_to ) : '—' ); ?></td>
						<td>
							<?php if ( $is_active ) : ?>
								<span class="uc-badge uc-badge-success">
									<?php esc_html_e( 'Active', 'u-commerce' ); ?>
								</span>
							<?php elseif ( $price->effective_from > $current_datetime ) : ?>
								<span class="uc-badge uc-badge-info">
									<?php esc_html_e( 'Scheduled', 'u-commerce' ); ?>
								</span>
							<?php else : ?>
								<span class="uc-badge uc-badge-warning">
									<?php esc_html_e( 'Expired', 'u-commerce' ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td>
							<button type="button" class="button button-small uc-edit-price-btn"
									data-id="<?php echo esc_attr( $price->id ); ?>"
									data-center-id="<?php echo esc_attr( $price->center_id ); ?>"
									data-price="<?php echo esc_attr( $price->selling_price ); ?>"
									data-from="<?php echo esc_attr( $price->effective_from ); ?>"
									data-to="<?php echo esc_attr( $price->effective_to ); ?>">
								<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
							</button>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-products&action=delete-price&price_id=' . $price->id . '&product_id=' . $product->id ), 'delete_price_' . $price->id ) ); ?>"
							   class="button button-small uc-delete-btn"
							   style="color: #b32d2e;"
							   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this price?', 'u-commerce' ); ?>');">
								<?php esc_html_e( 'Delete', 'u-commerce' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<!-- Add/Edit Pricing Form -->
	<div class="uc-card" style="background: #f9f9f9; padding: 20px;">
		<h3 id="pricing-form-title"><?php esc_html_e( 'Add New Price', 'u-commerce' ); ?></h3>
		<form method="post" action="" id="uc-pricing-form">
			<input type="hidden" name="pricing_id" id="pricing_id" value="">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->id ); ?>">
			<?php wp_nonce_field( 'uc_pricing_save', 'uc_pricing_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="pricing_center">
							<?php esc_html_e( 'Center', 'u-commerce' ); ?>
							<span style="color: red;">*</span>
						</label>
					</th>
					<td>
						<select name="center_id" id="pricing_center" class="regular-text" required>
							<option value="0"><?php esc_html_e( 'All Centers (Default)', 'u-commerce' ); ?></option>
							<?php foreach ( $all_centers as $center ) : ?>
								<option value="<?php echo esc_attr( $center->id ); ?>">
									<?php echo esc_html( $center->name . ' (' . ucfirst( $center->type ) . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Select a specific center or leave as "All Centers" for default pricing', 'u-commerce' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="pricing_selling_price">
							<?php esc_html_e( 'Selling Price', 'u-commerce' ); ?>
							<span style="color: red;">*</span>
						</label>
					</th>
					<td>
						<input type="number"
							   name="selling_price"
							   id="pricing_selling_price"
							   class="regular-text"
							   step="0.01"
							   min="0"
							   required>
						<p class="description">
							<?php
							printf(
								/* translators: %s: base cost */
								esc_html__( 'Base cost is %s. Current margin will be calculated automatically.', 'u-commerce' ),
								'<strong>' . esc_html( UC_Utilities::format_price( $product->base_cost ) ) . '</strong>'
							);
							?>
						</p>
						<p id="margin-display" style="margin-top: 10px; font-weight: bold;"></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="pricing_effective_from">
							<?php esc_html_e( 'Effective From', 'u-commerce' ); ?>
						</label>
					</th>
					<td>
						<input type="date"
							   name="effective_from"
							   id="pricing_effective_from"
							   class="regular-text"
							   value="<?php echo esc_attr( $current_date ); ?>">
						<p class="description"><?php esc_html_e( 'When this price becomes active (leave empty for immediate)', 'u-commerce' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="pricing_effective_to">
							<?php esc_html_e( 'Effective To', 'u-commerce' ); ?>
						</label>
					</th>
					<td>
						<input type="date"
							   name="effective_to"
							   id="pricing_effective_to"
							   class="regular-text">
						<p class="description"><?php esc_html_e( 'When this price expires (leave empty for no expiration)', 'u-commerce' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit"
					   name="uc_pricing_submit"
					   class="button button-primary"
					   value="<?php esc_attr_e( 'Save Price', 'u-commerce' ); ?>">
				<button type="button" id="cancel-pricing-edit" class="button" style="display: none;">
					<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
				</button>
			</p>
		</form>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	var baseCost = <?php echo (float) $product->base_cost; ?>;

	// Calculate and display margin when price changes
	$('#pricing_selling_price').on('input', function() {
		var sellingPrice = parseFloat($(this).val()) || 0;
		if (baseCost > 0) {
			var margin = ((sellingPrice - baseCost) / baseCost) * 100;
			var marginColor = margin >= 0 ? '#00a32a' : '#d63638';
			var marginText = margin >= 0 ? 'Margin' : 'Loss';

			$('#margin-display').html(
				'<span style="color: ' + marginColor + ';">' +
				marginText + ': ' + margin.toFixed(2) + '%</span>'
			);
		}
	});

	// Edit price button
	$('.uc-edit-price-btn').on('click', function() {
		var priceId = $(this).data('id');
		var centerId = $(this).data('center-id');
		var price = $(this).data('price');
		var effectiveFrom = $(this).data('from');
		var effectiveTo = $(this).data('to');

		$('#pricing-form-title').text('<?php esc_html_e( 'Edit Price', 'u-commerce' ); ?>');
		$('#pricing_id').val(priceId);
		$('#pricing_center').val(centerId);
		$('#pricing_selling_price').val(price).trigger('input');
		$('#pricing_effective_from').val(effectiveFrom ? effectiveFrom.split(' ')[0] : '');
		$('#pricing_effective_to').val(effectiveTo ? effectiveTo.split(' ')[0] : '');
		$('#cancel-pricing-edit').show();

		// Scroll to form
		$('html, body').animate({
			scrollTop: $('#uc-pricing-form').offset().top - 50
		}, 500);
	});

	// Cancel edit
	$('#cancel-pricing-edit').on('click', function() {
		$('#pricing-form-title').text('<?php esc_html_e( 'Add New Price', 'u-commerce' ); ?>');
		$('#uc-pricing-form')[0].reset();
		$('#pricing_id').val('');
		$('#pricing_effective_from').val('<?php echo esc_attr( $current_date ); ?>');
		$('#margin-display').html('');
		$(this).hide();
	});

	// Confirm delete with proper styling
	$('.uc-delete-btn').on('click', function(e) {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this pricing record? This action cannot be undone.', 'u-commerce' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});
});
</script>
