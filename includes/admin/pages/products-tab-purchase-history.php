<?php
/**
 * Product Purchase History Tab.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get purchase history for this product
global $wpdb;
$database = new UC_Database();

$purchase_items_table = $database->get_table( 'purchase_items' );
$purchase_bills_table = $database->get_table( 'purchase_bills' );
$centers_table = $database->get_table( 'centers' );
$vendors_table = $database->get_table( 'vendors' );

$query = $wpdb->prepare(
    "SELECT
        pi.*,
        pb.bill_number,
        pb.bill_date,
        pb.total_amount as bill_total,
        pb.status,
        c.name as center_name,
        v.name as vendor_name
    FROM {$purchase_items_table} pi
    INNER JOIN {$purchase_bills_table} pb ON pi.purchase_bill_id = pb.id
    LEFT JOIN {$centers_table} c ON pb.center_id = c.id
    LEFT JOIN {$vendors_table} v ON pb.vendor_id = v.id
    WHERE pi.product_id = %d
    ORDER BY pb.bill_date DESC, pb.id DESC",
    $product->id
);

$purchase_history = $wpdb->get_results( $query );
?>

<div class="uc-card">
    <h2><?php esc_html_e( 'Purchase History', 'u-commerce' ); ?></h2>
    <p class="description"><?php esc_html_e( 'This product was included in the following purchase bills:', 'u-commerce' ); ?></p>

    <?php if ( $purchase_history ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Bill #', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Vendor', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Quantity', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Unit Cost', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Total Cost', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'u-commerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $purchase_history as $item ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $item->bill_number ); ?></strong></td>
                        <td><?php echo esc_html( UC_Utilities::format_date( $item->bill_date ) ); ?></td>
                        <td><?php echo esc_html( $item->vendor_name ? $item->vendor_name : '—' ); ?></td>
                        <td><?php echo esc_html( $item->center_name ? $item->center_name : '—' ); ?></td>
                        <td><?php echo esc_html( number_format( $item->quantity ) ); ?></td>
                        <td><?php echo esc_html( UC_Utilities::format_price( $item->unit_cost ) ); ?></td>
                        <td><?php echo esc_html( UC_Utilities::format_price( $item->total_cost ) ); ?></td>
                        <td>
                            <span class="uc-badge uc-badge-<?php echo esc_attr( $item->status === 'completed' ? 'success' : 'warning' ); ?>">
                                <?php echo esc_html( ucfirst( $item->status ) ); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-purchase-bills&action=view&id=' . $item->purchase_bill_id ) ); ?>" class="button button-small">
                                <?php esc_html_e( 'View Bill', 'u-commerce' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;"><strong><?php esc_html_e( 'Totals:', 'u-commerce' ); ?></strong></th>
                    <th><strong><?php echo esc_html( number_format( array_sum( wp_list_pluck( $purchase_history, 'quantity' ) ) ) ); ?></strong></th>
                    <th colspan="1"></th>
                    <th><strong><?php echo esc_html( UC_Utilities::format_price( array_sum( wp_list_pluck( $purchase_history, 'total_cost' ) ) ) ); ?></strong></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    <?php else : ?>
        <div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px;">
            <p style="font-size: 16px; color: #666;">
                <?php esc_html_e( 'This product has not been purchased yet.', 'u-commerce' ); ?>
            </p>
        </div>
    <?php endif; ?>
</div>
