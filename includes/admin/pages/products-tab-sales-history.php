<?php
/**
 * Product Sales History Tab.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get sales history for this product
global $wpdb;
$database = new UC_Database();

$sales_items_table = $database->get_table( 'sales_items' );
$sales_bills_table = $database->get_table( 'sales_bills' );
$centers_table = $database->get_table( 'centers' );
$customers_table = $database->get_table( 'customers' );

$query = $wpdb->prepare(
    "SELECT
        si.*,
        sb.bill_number,
        sb.created_at as sale_date,
        sb.total_amount as bill_total,
        sb.payment_status,
        c.name as center_name,
        cu.name as customer_name
    FROM {$sales_items_table} si
    INNER JOIN {$sales_bills_table} sb ON si.sales_bill_id = sb.id
    LEFT JOIN {$centers_table} c ON sb.center_id = c.id
    LEFT JOIN {$customers_table} cu ON sb.customer_id = cu.id
    WHERE si.product_id = %d
    ORDER BY sb.created_at DESC, sb.id DESC",
    $product->id
);

$sales_history = $wpdb->get_results( $query );

// Calculate profit if we have base cost
$total_profit = 0;
if ( $sales_history && $product->base_cost > 0 ) {
    foreach ( $sales_history as $item ) {
        $item_profit = ( $item->unit_price - $product->base_cost ) * $item->quantity;
        $item->profit = $item_profit;
        $total_profit += $item_profit;
    }
}
?>

<div class="uc-card">
    <h2><?php esc_html_e( 'Sales History', 'u-commerce' ); ?></h2>
    <p class="description"><?php esc_html_e( 'This product was sold in the following sales bills:', 'u-commerce' ); ?></p>

    <?php if ( $sales_history ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Bill #', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Customer', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Center', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Quantity', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Unit Price', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Total Price', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Profit', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Payment', 'u-commerce' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'u-commerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $sales_history as $item ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $item->bill_number ); ?></strong></td>
                        <td><?php echo esc_html( UC_Utilities::format_date( $item->sale_date ) ); ?></td>
                        <td><?php echo esc_html( $item->customer_name ? $item->customer_name : '—' ); ?></td>
                        <td><?php echo esc_html( $item->center_name ? $item->center_name : '—' ); ?></td>
                        <td><?php echo esc_html( number_format( $item->quantity ) ); ?></td>
                        <td><?php echo esc_html( UC_Utilities::format_price( $item->unit_price ) ); ?></td>
                        <td><?php echo esc_html( UC_Utilities::format_price( $item->total_price ) ); ?></td>
                        <td>
                            <span style="color: <?php echo $item->profit > 0 ? '#00a32a' : '#d63638'; ?>;">
                                <?php echo esc_html( UC_Utilities::format_price( $item->profit ) ); ?>
                            </span>
                        </td>
                        <td>
                            <span class="uc-badge uc-badge-<?php echo esc_attr( $item->payment_status === 'paid' ? 'success' : 'warning' ); ?>">
                                <?php echo esc_html( ucfirst( $item->payment_status ) ); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-sales-bills&action=view&id=' . $item->sales_bill_id ) ); ?>" class="button button-small">
                                <?php esc_html_e( 'View Bill', 'u-commerce' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;"><strong><?php esc_html_e( 'Totals:', 'u-commerce' ); ?></strong></th>
                    <th><strong><?php echo esc_html( number_format( array_sum( wp_list_pluck( $sales_history, 'quantity' ) ) ) ); ?></strong></th>
                    <th colspan="1"></th>
                    <th><strong><?php echo esc_html( UC_Utilities::format_price( array_sum( wp_list_pluck( $sales_history, 'total_price' ) ) ) ); ?></strong></th>
                    <th>
                        <strong style="color: <?php echo $total_profit > 0 ? '#00a32a' : '#d63638'; ?>;">
                            <?php echo esc_html( UC_Utilities::format_price( $total_profit ) ); ?>
                        </strong>
                    </th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    <?php else : ?>
        <div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px;">
            <p style="font-size: 16px; color: #666;">
                <?php esc_html_e( 'This product has not been sold yet.', 'u-commerce' ); ?>
            </p>
        </div>
    <?php endif; ?>
</div>
