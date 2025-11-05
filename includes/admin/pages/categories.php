<?php
/**
 * Categories admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_categories' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Product Categories', 'u-commerce' ); ?></h1>
    <p><?php esc_html_e( 'Manage your product categories here.', 'u-commerce' ); ?></p>
</div>
