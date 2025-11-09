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
							<td>
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
