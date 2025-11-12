<?php
/**
 * Customers list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all customers
$customers = $customers_handler->get_all();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New Customer', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $customers ) : ?>
			<!-- Search and Filter Controls -->
			<div class="uc-table-controls" style="padding: 15px 15px 0; border-bottom: 1px solid #ddd;">
				<div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
					<input type="text" class="uc-table-search" placeholder="<?php esc_attr_e( 'Search customers by name, phone, or email...', 'u-commerce' ); ?>" style="flex: 1; min-width: 250px; padding: 6px 10px;">
					<select class="uc-table-filter" data-filter="status" style="padding: 6px 10px;">
						<option value=""><?php esc_html_e( 'All Status', 'u-commerce' ); ?></option>
						<option value="active"><?php esc_html_e( 'Active', 'u-commerce' ); ?></option>
						<option value="inactive"><?php esc_html_e( 'Inactive', 'u-commerce' ); ?></option>
					</select>
					<button type="button" class="button uc-clear-search"><?php esc_html_e( 'Clear', 'u-commerce' ); ?></button>
				</div>
				<div class="uc-results-count" style="color: #666; font-size: 13px; margin-bottom: 10px;"></div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Email', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $customers as $customer ) : ?>
						<tr>
							<td><?php echo esc_html( $customer->id ); ?></td>
							<td><strong><?php echo esc_html( $customer->name ); ?></strong></td>
							<td><?php echo esc_html( $customer->phone ); ?></td>
							<td><?php echo esc_html( $customer->email ); ?></td>
							<td data-filter-status="<?php echo esc_attr( $customer->status ); ?>">
								<span class="uc-badge uc-badge-<?php echo esc_attr( $customer->status === 'active' ? 'success' : 'warning' ); ?>">
									<?php echo esc_html( ucfirst( $customer->status ) ); ?>
								</span>
							</td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-customers&action=edit&id=' . $customer->id ) ); ?>"
								   class="button button-small">
									<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-customers&action=delete&id=' . $customer->id ), 'delete_customer_' . $customer->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;"
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this customer?', 'u-commerce' ); ?>');">
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
					<?php esc_html_e( 'No customers found. Click "Add New Customer" to create your first customer.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
