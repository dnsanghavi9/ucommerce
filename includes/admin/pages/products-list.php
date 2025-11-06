<?php
/**
 * Products list view.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get products with pagination
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;
$offset = ( $paged - 1 ) * $per_page;

$products = $products_handler->get_all( array(
    'limit'  => $per_page,
    'offset' => $offset,
) );

$total_products = $products_handler->get_count();
$total_pages = ceil( $total_products / $per_page );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Products', 'u-commerce' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Product', 'u-commerce' ); ?>
    </a>
    <hr class="wp-header-end">

    <div class="uc-card">
        <?php if ( $products ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e( 'ID', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'SKU', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Base Cost', 'u-commerce' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'u-commerce' ); ?></th>
                        <th style="width: 180px;"><?php esc_html_e( 'Actions', 'u-commerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $products as $product ) : ?>
                        <tr>
                            <td><?php echo esc_html( $product->id ); ?></td>
                            <td><strong><?php echo esc_html( $product->name ); ?></strong></td>
                            <td><code><?php echo esc_html( $product->sku ); ?></code></td>
                            <td>
                                <?php
                                if ( $product->category_id ) {
                                    $categories = new UC_Categories();
                                    $category = $categories->get( $product->category_id );
                                    echo esc_html( $category ? $category->name : '—' );
                                } else {
                                    echo '—';
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
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Edit', 'u-commerce' ); ?>
                                </a>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=u-commerce-products&action=delete&id=' . $product->id ), 'delete_product_' . $product->id ) ); ?>"
                                   class="button button-small uc-delete-btn"
                                   style="color: #b32d2e;">
                                    <?php esc_html_e( 'Delete', 'u-commerce' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php
                            printf(
                                /* translators: %d: number of items */
                                _n( '%d item', '%d items', $total_products, 'u-commerce' ),
                                number_format_i18n( $total_products )
                            );
                            ?>
                        </span>
                        <?php
                        $page_links = paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total'     => $total_pages,
                            'current'   => $paged,
                        ) );

                        if ( $page_links ) {
                            echo '<span class="pagination-links">' . $page_links . '</span>';
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div style="padding: 40px; text-align: center;">
                <p style="font-size: 16px; color: #666;">
                    <?php esc_html_e( 'No products found. Click "Add New Product" to create your first product.', 'u-commerce' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
