<?php
/**
 * Purchase Bills add/edit form.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all vendors, centers, and products
$vendors_handler = new UC_Vendors();
$centers_handler = new UC_Centers();
$products_handler = new UC_Products();

$all_vendors = $vendors_handler->get_all();
$all_centers = $centers_handler->get_all();
$all_products = $products_handler->get_all( array( 'status' => 'active' ) );

// Generate default bill number
$bill_number = UC_Utilities::generate_bill_number( 'purchase' );
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Add New Purchase Bill', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills' ) ); ?>" class="page-title-action">
		<?php esc_html_e( '← Back to Purchase Bills', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<form method="post" action="" id="purchase-bill-form">
		<?php wp_nonce_field( 'uc_purchase_bill_save', 'uc_purchase_bill_nonce' ); ?>

		<div class="uc-card">
			<h2><?php esc_html_e( 'Bill Information', 'u-commerce' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="bill_number"><?php esc_html_e( 'Bill Number', 'u-commerce' ); ?></label>
					</th>
					<td>
						<input type="text"
							   name="bill_number"
							   id="bill_number"
							   value="<?php echo esc_attr( $bill_number ); ?>"
							   class="regular-text"
							   readonly>
						<p class="description"><?php esc_html_e( 'Auto-generated bill number.', 'u-commerce' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="vendor_id"><?php esc_html_e( 'Vendor', 'u-commerce' ); ?></label>
					</th>
					<td>
						<select name="vendor_id" id="vendor_id" class="regular-text">
							<option value=""><?php esc_html_e( '-- Select Vendor (Optional) --', 'u-commerce' ); ?></option>
							<?php foreach ( $all_vendors as $vendor ) : ?>
								<option value="<?php echo esc_attr( $vendor->id ); ?>">
									<?php echo esc_html( $vendor->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="center_id">
							<?php esc_html_e( 'Center', 'u-commerce' ); ?>
							<span style="color: red;">*</span>
						</label>
					</th>
					<td>
						<select name="center_id" id="center_id" class="regular-text" required>
							<option value=""><?php esc_html_e( '-- Select Center --', 'u-commerce' ); ?></option>
							<?php foreach ( $all_centers as $center ) : ?>
								<option value="<?php echo esc_attr( $center->id ); ?>">
									<?php echo esc_html( $center->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="bill_date"><?php esc_html_e( 'Bill Date', 'u-commerce' ); ?></label>
					</th>
					<td>
						<input type="date"
							   name="bill_date"
							   id="bill_date"
							   value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"
							   class="regular-text">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'u-commerce' ); ?></label>
					</th>
					<td>
						<select name="status" id="status" class="regular-text">
							<option value="completed"><?php esc_html_e( 'Completed', 'u-commerce' ); ?></option>
							<option value="pending"><?php esc_html_e( 'Pending', 'u-commerce' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="notes"><?php esc_html_e( 'Notes', 'u-commerce' ); ?></label>
					</th>
					<td>
						<textarea name="notes" id="notes" class="large-text" rows="3"></textarea>
					</td>
				</tr>
			</table>
		</div>

		<div class="uc-card" style="margin-top: 20px;">
			<h2><?php esc_html_e( 'Purchase Items', 'u-commerce' ); ?> <span style="color: red;">*</span></h2>

			<table class="wp-list-table widefat fixed" id="items-table">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Product', 'u-commerce' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Quantity', 'u-commerce' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Unit Cost (₹)', 'u-commerce' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Total (₹)', 'u-commerce' ); ?></th>
						<th style="width: 5%;"></th>
					</tr>
				</thead>
				<tbody id="items-container">
					<!-- Items will be added here dynamically -->
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5">
							<button type="button" id="add-item-btn" class="button button-secondary">
								<?php esc_html_e( '+ Add Item', 'u-commerce' ); ?>
							</button>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="text-align: right; font-weight: bold; padding: 15px;">
							<?php esc_html_e( 'Grand Total:', 'u-commerce' ); ?>
						</td>
						<td style="padding: 15px;">
							<strong style="font-size: 16px;">₹<span id="grand-total">0.00</span></strong>
						</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<p class="submit">
			<input type="submit" name="uc_purchase_bill_submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Create Purchase Bill', 'u-commerce' ); ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills' ) ); ?>" class="button button-large" style="margin-left: 10px;">
				<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
			</a>
		</p>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	var itemIndex = 0;
	var products = <?php echo json_encode( $all_products ); ?>;

	// Add first item automatically
	addItem();

	// Add item button
	$('#add-item-btn').on('click', function() {
		addItem();
	});

	// Add item function
	function addItem() {
		var row = $('<tr class="item-row">' +
			'<td>' +
			'<select name="items[' + itemIndex + '][product_id]" class="item-product regular-text" required>' +
			'<option value="">-- Select Product --</option>' +
			'</select>' +
			'</td>' +
			'<td><input type="number" name="items[' + itemIndex + '][quantity]" class="item-quantity regular-text" min="1" value="1" required></td>' +
			'<td><input type="number" name="items[' + itemIndex + '][unit_cost]" class="item-unit-cost regular-text" min="0" step="0.01" value="0" required></td>' +
			'<td><strong class="item-total">₹0.00</strong></td>' +
			'<td><button type="button" class="button remove-item-btn" style="color: #b32d2e;">×</button></td>' +
			'</tr>');

		// Populate products dropdown
		var productSelect = row.find('.item-product');
		$.each(products, function(index, product) {
			productSelect.append('<option value="' + product.id + '" data-cost="' + product.base_cost + '">' +
								product.name + ' (' + product.sku + ')' + '</option>');
		});

		$('#items-container').append(row);
		itemIndex++;
		calculateGrandTotal();
	}

	// Remove item
	$(document).on('click', '.remove-item-btn', function() {
		$(this).closest('.item-row').remove();
		calculateGrandTotal();
	});

	// Auto-fill unit cost when product is selected
	$(document).on('change', '.item-product', function() {
		var baseCost = $(this).find('option:selected').data('cost');
		var row = $(this).closest('.item-row');
		row.find('.item-unit-cost').val(baseCost);
		calculateRowTotal(row);
	});

	// Calculate row total when quantity or unit cost changes
	$(document).on('input', '.item-quantity, .item-unit-cost', function() {
		var row = $(this).closest('.item-row');
		calculateRowTotal(row);
	});

	// Calculate row total
	function calculateRowTotal(row) {
		var quantity = parseFloat(row.find('.item-quantity').val()) || 0;
		var unitCost = parseFloat(row.find('.item-unit-cost').val()) || 0;
		var total = quantity * unitCost;
		row.find('.item-total').text('₹' + total.toFixed(2));
		calculateGrandTotal();
	}

	// Calculate grand total
	function calculateGrandTotal() {
		var grandTotal = 0;
		$('.item-row').each(function() {
			var quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
			var unitCost = parseFloat($(this).find('.item-unit-cost').val()) || 0;
			grandTotal += quantity * unitCost;
		});
		$('#grand-total').text(grandTotal.toFixed(2));
	}
});
</script>
