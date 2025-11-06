<?php
/**
 * Products admin page (Controller).
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

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$product_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$products_handler = new UC_Products();

// Handle form submission
if ( isset( $_POST['uc_product_submit'] ) ) {
    check_admin_referer( 'uc_product_save', 'uc_product_nonce' );

    // Collect basic data
    $data = array(
        'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
        'sku'         => isset( $_POST['sku'] ) ? sanitize_text_field( $_POST['sku'] ) : '',
        'category_id' => isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0,
        'base_cost'   => isset( $_POST['base_cost'] ) ? floatval( $_POST['base_cost'] ) : 0,
        'description' => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
        'status'      => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'active',
    );

    // Handle variables (JSON)
    $variables = array();
    if ( isset( $_POST['variable_name'] ) && is_array( $_POST['variable_name'] ) ) {
        foreach ( $_POST['variable_name'] as $index => $var_name ) {
            if ( ! empty( $var_name ) && ! empty( $_POST['variable_values'][ $index ] ) ) {
                $variables[] = array(
                    'name'   => sanitize_text_field( $var_name ),
                    'values' => sanitize_text_field( $_POST['variable_values'][ $index ] ),
                );
            }
        }
    }
    $data['variables'] = $variables;

    if ( $product_id ) {
        // Update
        $result = $products_handler->update( $product_id, $data );
        $message = __( 'Product updated successfully.', 'u-commerce' );
        $saved_id = $product_id;
    } else {
        // Create
        $result = $products_handler->create( $data );
        $message = __( 'Product created successfully.', 'u-commerce' );
        $saved_id = $result;
    }

    if ( $result ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';

        // Redirect to edit page after creation
        if ( ! $product_id && $saved_id ) {
            echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $saved_id ) ) . '";</script>';
            exit;
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save product.', 'u-commerce' ) . '</p></div>';
    }
}

// Handle pricing form submission
if ( isset( $_POST['uc_pricing_submit'] ) ) {
    check_admin_referer( 'uc_pricing_save', 'uc_pricing_nonce' );

    global $wpdb;
    $database = new UC_Database();
    $pricing_table = $database->get_table( 'pricing' );

    $pricing_id = isset( $_POST['pricing_id'] ) ? absint( $_POST['pricing_id'] ) : 0;
    $pricing_product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    $pricing_data = array(
        'product_id'    => $pricing_product_id,
        'center_id'     => isset( $_POST['center_id'] ) ? absint( $_POST['center_id'] ) : 0,
        'selling_price' => isset( $_POST['selling_price'] ) ? floatval( $_POST['selling_price'] ) : 0,
        'effective_from' => isset( $_POST['effective_from'] ) && ! empty( $_POST['effective_from'] ) ? sanitize_text_field( $_POST['effective_from'] ) : null,
        'effective_to'   => isset( $_POST['effective_to'] ) && ! empty( $_POST['effective_to'] ) ? sanitize_text_field( $_POST['effective_to'] ) : null,
    );

    // Calculate margin percentage
    if ( $pricing_product_id ) {
        $product_obj = $products_handler->get( $pricing_product_id );
        if ( $product_obj && $product_obj->base_cost > 0 ) {
            $pricing_data['margin_percentage'] = ( ( $pricing_data['selling_price'] - $product_obj->base_cost ) / $product_obj->base_cost ) * 100;
        }
    }

    if ( $pricing_id ) {
        // Update existing pricing
        $pricing_result = $wpdb->update(
            $pricing_table,
            $pricing_data,
            array( 'id' => $pricing_id ),
            array( '%d', '%d', '%f', '%s', '%s', '%f' ),
            array( '%d' )
        );
        $pricing_message = __( 'Price updated successfully.', 'u-commerce' );
    } else {
        // Create new pricing
        $pricing_result = $wpdb->insert(
            $pricing_table,
            $pricing_data,
            array( '%d', '%d', '%f', '%s', '%s', '%f' )
        );
        $pricing_message = __( 'Price added successfully.', 'u-commerce' );
    }

    if ( $pricing_result !== false ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $pricing_message ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save price.', 'u-commerce' ) . '</p></div>';
    }
}

// Handle delete price
if ( $action === 'delete-price' ) {
    $price_id = isset( $_GET['price_id'] ) ? absint( $_GET['price_id'] ) : 0;

    if ( $price_id ) {
        check_admin_referer( 'delete_price_' . $price_id );

        global $wpdb;
        $database = new UC_Database();
        $pricing_table = $database->get_table( 'pricing' );

        $deleted = $wpdb->delete( $pricing_table, array( 'id' => $price_id ), array( '%d' ) );

        if ( $deleted ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Price deleted successfully.', 'u-commerce' ) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete price.', 'u-commerce' ) . '</p></div>';
        }
    }

    // Redirect back to pricing tab
    if ( $product_id ) {
        echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product_id . '&tab=pricing' ) ) . '";</script>';
        exit;
    }
}

// Handle delete
if ( $action === 'delete' && $product_id ) {
    check_admin_referer( 'delete_product_' . $product_id );

    $deleted = $products_handler->delete( $product_id );

    if ( $deleted ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Product deleted successfully.', 'u-commerce' ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete product.', 'u-commerce' ) . '</p></div>';
    }

    $action = ''; // Reset to show list
}

// Determine which view to show
if ( $action === 'edit' && $product_id ) {
    // Show edit form
    $product = $products_handler->get( $product_id );
    if ( ! $product ) {
        wp_die( esc_html__( 'Product not found.', 'u-commerce' ) );
    }
    include UC_PLUGIN_DIR . 'includes/admin/pages/products-form.php';
} elseif ( $action === 'new' ) {
    // Show add form
    $product = null;
    include UC_PLUGIN_DIR . 'includes/admin/pages/products-form.php';
} else {
    // Show list
    include UC_PLUGIN_DIR . 'includes/admin/pages/products-list.php';
}
