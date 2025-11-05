<?php
/**
 * Purchase Bills admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_add_purchase_bills' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Purchase Bills', 'u-commerce' ); ?></h1>
    <p><?php esc_html_e( 'Manage your purchase bills and track vendor purchases.', 'u-commerce' ); ?></p>
</div>
