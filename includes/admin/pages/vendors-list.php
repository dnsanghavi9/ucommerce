<?php
/**
 * Vendors list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all vendors
$vendors = $vendors_handler->get_all();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Vendors', 'u-commerce' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New Vendor', 'u-commerce' ); ?>
	</a>
	<hr class="wp-header-end">

	<div class="uc-card">
		<?php if ( $vendors ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Email', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'GST Number', 'u-commerce' ); ?></th>
						<th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
						<th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $vendors as $vendor ) : ?>
						<tr>
							<td><?php echo esc_html( $vendor->id ); ?></td>
							<td><strong><?php echo esc_html( $vendor->name ); ?></strong></td>
							<td><?php echo esc_html( $vendor->phone ); ?></td>
							<td><?php echo esc_html( $vendor->email ); ?></td>
							<td><code><?php echo esc_html( $vendor->gst_number ); ?></code></td>
							<td>
								<span class="uc-badge uc-badge-<?php echo esc_attr( $vendor->status === 'active' ? 'success' : 'warning' ); ?>">
									<?php echo esc_html( ucfirst( $vendor->status ) ); ?>
								</span>
							</td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-vendors&action=edit&id=' . $vendor->id ) ); ?>"
								   class="button button-small">
									<?php esc_html_e( 'Edit', 'u-commerce' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-vendors&action=delete&id=' . $vendor->id ), 'delete_vendor_' . $vendor->id ) ); ?>"
								   class="button button-small uc-delete-btn"
								   style="color: #b32d2e;"
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this vendor?', 'u-commerce' ); ?>');">
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
					<?php esc_html_e( 'No vendors found. Click "Add New Vendor" to create your first vendor.', 'u-commerce' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
