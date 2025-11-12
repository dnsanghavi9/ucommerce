<?php
/**
 * Sales Bills add/edit form.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all customers, centers, and products
$customers_handler = new UC_Customers();
$centers_handler = new UC_Centers();
$products_handler = new UC_Products();

$all_customers = $customers_handler->get_all();
$all_centers = $centers_handler->get_all();
$all_products = $products_handler->get_all( array( 'status' => 'active' ) );

// Generate default bill number
$bill_number = UC_Utilities::generate_bill_number( 'sales' );
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Add New Sales Bill', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills' ) ); ?>" class="page-title-action">
		<?php esc_html_e( '← Back to Sales Bills', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<form method="post" action="" id="sales-bill-form">
		<?php wp_nonce_field( 'uc_sales_bill_save', 'uc_sales_bill_nonce' ); ?>

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
						<label for="customer_id"><?php esc_html_e( 'Customer', 'u-commerce' ); ?></label>
					</th>
					<td>
						<select name="customer_id" id="customer_id" class="regular-text">
							<option value=""><?php esc_html_e( '-- Walk-in Customer (Optional) --', 'u-commerce' ); ?></option>
							<?php foreach ( $all_customers as $customer ) : ?>
								<option value="<?php echo esc_attr( $customer->id ); ?>">
									<?php echo esc_html( $customer->name . ' - ' . $customer->phone ); ?>
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
						<label for="payment_status"><?php esc_html_e( 'Payment Status', 'u-commerce' ); ?></label>
					</th>
					<td>
						<select name="payment_status" id="payment_status" class="regular-text">
							<option value="paid"><?php esc_html_e( 'Paid', 'u-commerce' ); ?></option>
							<option value="pending"><?php esc_html_e( 'Pending', 'u-commerce' ); ?></option>
							<option value="partial"><?php esc_html_e( 'Partial', 'u-commerce' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="payment_method"><?php esc_html_e( 'Payment Method', 'u-commerce' ); ?></label>
					</th>
					<td>
						<select name="payment_method" id="payment_method" class="regular-text">
							<option value="cash"><?php esc_html_e( 'Cash', 'u-commerce' ); ?></option>
							<option value="card"><?php esc_html_e( 'Card', 'u-commerce' ); ?></option>
							<option value="upi"><?php esc_html_e( 'UPI', 'u-commerce' ); ?></option>
							<option value="bank_transfer"><?php esc_html_e( 'Bank Transfer', 'u-commerce' ); ?></option>
							<option value="cheque"><?php esc_html_e( 'Cheque', 'u-commerce' ); ?></option>
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
			<h2><?php esc_html_e( 'Sales Items', 'u-commerce' ); ?> <span style="color: red;">*</span></h2>
			<p class="description" style="margin-top: -10px; margin-bottom: 15px;">
				<?php esc_html_e( 'Note: Inventory will be checked before creating the bill. Insufficient stock will prevent bill creation.', 'u-commerce' ); ?>
			</p>

			<table class="wp-list-table widefat fixed" id="items-table">
				<thead>
					<tr>
						<th style="width: 35%;"><?php esc_html_e( 'Product', 'u-commerce' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Quantity', 'u-commerce' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Unit Price (₹)', 'u-commerce' ); ?></th>
						<th style="width: 20%;"><?php esc_html_e( 'Total (₹)', 'u-commerce' ); ?></th>
						<th style="width: 10%;"><?php esc_html_e( 'Stock', 'u-commerce' ); ?></th>
						<th style="width: 5%;"></th>
					</tr>
				</thead>
				<tbody id="items-container">
					<!-- Items will be added here dynamically -->
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6">
							<button type="button" id="add-item-btn" class="button button-secondary">
								<?php esc_html_e( '+ Add Item', 'u-commerce' ); ?>
							</button>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="text-align: right; font-weight: bold; padding: 15px;">
							<?php esc_html_e( 'Grand Total:', 'u-commerce' ); ?>
						</td>
						<td colspan="3" style="padding: 15px;">
							<strong style="font-size: 16px;">₹<span id="grand-total">0.00</span></strong>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<p class="submit">
			<input type="submit" name="uc_sales_bill_submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Create Sales Bill', 'u-commerce' ); ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills' ) ); ?>" class="button button-large" style="margin-left: 10px;">
				<?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
			</a>
		</p>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	var itemIndex = 0;
	var products = <?php echo json_encode( $all_products ); ?>;
	var centerId = null;

	// Monitor center selection
	$('#center_id').on('change', function() {
		centerId = $(this).val();
		// Clear all items when center changes
		$('#items-container').empty();
		itemIndex = 0;
		addItem();
		calculateGrandTotal();
	});

	// Add first item automatically
	addItem();

	// Add item button
	$('#add-item-btn').on('click', function() {
		if (!centerId) {
			alert('<?php esc_html_e( 'Please select a center first.', 'u-commerce' ); ?>');
			return;
		}
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
			'<td><input type="number" name="items[' + itemIndex + '][unit_price]" class="item-unit-price regular-text" min="0" step="0.01" value="0" required></td>' +
			'<td><strong class="item-total">₹0.00</strong></td>' +
			'<td><span class="item-stock" style="color: #666;">-</span></td>' +
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

	// Fetch stock when product is selected (unit price not auto-filled for sales)
	$(document).on('change', '.item-product', function() {
		var baseCost = $(this).find('option:selected').data('cost');
		var productId = $(this).val();
		var row = $(this).closest('.item-row');

		// Don't auto-fill unit price for sales bills - let user enter selling price
		// row.find('.item-unit-price').val(baseCost);

		// Fetch stock for this product at selected center
		if (productId && centerId) {
			fetchStock(productId, centerId, row);
		} else {
			row.find('.item-stock').text('-');
		}

		calculateRowTotal(row);
	});

	// Fetch stock via AJAX
	function fetchStock(productId, centerId, row) {
		$.post(ajaxurl, {
			action: 'uc_get_stock',
			product_id: productId,
			center_id: centerId
		}, function(response) {
			if (response.success) {
				var stock = response.data.quantity || 0;
				var stockDisplay = row.find('.item-stock');
				stockDisplay.text(stock);

				// Color code based on stock
				if (stock > 10) {
					stockDisplay.css('color', '#46b450');
				} else if (stock > 0) {
					stockDisplay.css('color', '#f56e28');
				} else {
					stockDisplay.css('color', '#dc3232');
				}
			}
		});
	}

	// Calculate row total when quantity or unit price changes
	$(document).on('input', '.item-quantity, .item-unit-price', function() {
		var row = $(this).closest('.item-row');
		calculateRowTotal(row);
	});

	// Calculate row total
	function calculateRowTotal(row) {
		var quantity = parseFloat(row.find('.item-quantity').val()) || 0;
		var unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
		var total = quantity * unitPrice;
		row.find('.item-total').text('₹' + total.toFixed(2));
		calculateGrandTotal();
	}

	// Calculate grand total
	function calculateGrandTotal() {
		var grandTotal = 0;
		$('.item-row').each(function() {
			var quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
			var unitPrice = parseFloat($(this).find('.item-unit-price').val()) || 0;
			grandTotal += quantity * unitPrice;
		});
		$('#grand-total').text(grandTotal.toFixed(2));
	}

	// Form validation before submit
	$('#sales-bill-form').on('submit', function(e) {
		var hasInsufficientStock = false;

		$('.item-row').each(function() {
			var quantity = parseInt($(this).find('.item-quantity').val()) || 0;
			var stock = parseInt($(this).find('.item-stock').text()) || 0;

			if (quantity > stock) {
				hasInsufficientStock = true;
				$(this).css('background-color', '#ffebee');
			}
		});

		if (hasInsufficientStock) {
			e.preventDefault();
			alert('<?php esc_html_e( 'Some items have insufficient stock. Please adjust quantities.', 'u-commerce' ); ?>');
			return false;
		}
	});
});
</script>
