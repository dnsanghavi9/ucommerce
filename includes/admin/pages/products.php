<?php
/**
 * Products admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_products' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

// Get products
$products_handler = new UC_Products();
$products = $products_handler->get_all( array( 'limit' => 20 ) );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Products', 'u-commerce' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'u-commerce' ); ?>
    </a>
    <hr class="wp-header-end">

    <div class="uc-card">
        <?php if ( $products ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'SKU', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Base Cost', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $products as $product ) : ?>
                        <tr>
                            <td><?php echo esc_html( $product->id ); ?></td>
                            <td><strong><?php echo esc_html( $product->name ); ?></strong></td>
                            <td><?php echo esc_html( $product->sku ); ?></td>
                            <td>
                                <?php
                                if ( $product->category_id ) {
                                    $categories = new UC_Categories();
                                    $category = $categories->get( $product->category_id );
                                    echo esc_html( $category ? $category->name : '-' );
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( UC_Utilities::format_price( $product->base_cost ) ); ?></td>
                            <td>
                                <span class="uc-badge uc-badge-<?php echo esc_attr( $product->status === 'active' ? 'success' : 'warning' ); ?>">
                                    <?php echo esc_html( ucfirst( $product->status ) ); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id ) ); ?>">
                                    <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                </a> |
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-inventory&product_id=' . $product->id ) ); ?>">
                                    <?php esc_html_e( 'Inventory', 'u-commerce' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'No products found. Click "Add New" to create your first product.', 'u-commerce' ); ?></p>
        <?php endif; ?>
    </div>
</div>
