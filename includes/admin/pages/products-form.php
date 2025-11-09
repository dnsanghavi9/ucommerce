<?php
/**
 * Products add/edit form with tabs.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_edit = isset( $product ) && $product;
$page_title = $is_edit ? __( 'Edit Product', 'u-commerce' ) : __( 'Add New Product', 'u-commerce' );

// Get all categories
$categories_handler = new UC_Categories();
$all_categories = $categories_handler->get_all();

// Get active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'basic';

// Decode variables if editing
$variables = array();
if ( $is_edit && ! empty( $product->variables ) ) {
    if ( is_string( $product->variables ) ) {
        $variables = json_decode( $product->variables, true );
    } elseif ( is_array( $product->variables ) ) {
        $variables = $product->variables;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products' ) ); ?>" class="page-title-action">
        <?php esc_html_e( '← Back to Products', 'u-commerce' ); ?>
    </a>
    <hr class="wp-header-end">

    <?php if ( $is_edit ) : ?>
        <!-- Tabs Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=basic' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'basic' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Basic Info', 'u-commerce' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=variables' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'variables' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Variables', 'u-commerce' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=purchase-history' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'purchase-history' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Purchase History', 'u-commerce' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=sales-history' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'sales-history' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Sales History', 'u-commerce' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=inventory' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'inventory' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Inventory', 'u-commerce' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products&action=edit&id=' . $product->id . '&tab=pricing' ) ); ?>"
               class="nav-tab <?php echo $active_tab === 'pricing' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Pricing', 'u-commerce' ); ?>
            </a>
        </h2>
    <?php endif; ?>

    <?php
    // Show appropriate tab content
    if ( $active_tab === 'purchase-history' && $is_edit ) {
        include UC_PLUGIN_DIR . 'includes/admin/pages/products-tab-purchase-history.php';
    } elseif ( $active_tab === 'sales-history' && $is_edit ) {
        include UC_PLUGIN_DIR . 'includes/admin/pages/products-tab-sales-history.php';
    } elseif ( $active_tab === 'inventory' && $is_edit ) {
        include UC_PLUGIN_DIR . 'includes/admin/pages/products-tab-inventory.php';
    } elseif ( $active_tab === 'pricing' && $is_edit ) {
        include UC_PLUGIN_DIR . 'includes/admin/pages/products-tab-pricing.php';
    } else {
        // Show form for basic and variables tabs
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'uc_product_save', 'uc_product_nonce' ); ?>
            <input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

            <div class="uc-card">
                <?php if ( $active_tab === 'variables' && $is_edit ) : ?>
                    <!-- Variables Tab -->
                    <h2><?php esc_html_e( 'Product Variables', 'u-commerce' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Select variables and their values for this product. Variables are inherited from the category and can be customized.', 'u-commerce' ); ?>
                    </p>

                    <?php
                    // Get all variables
                    $variables_handler = new UC_Variables();
                    $all_variables = $variables_handler->get_all();

                    // Get variables from category
                    $category_variables = array();
                    if ( $product->category_id ) {
                        $category_variables = $variables_handler->get_by_category( $product->category_id );
                    }

                    // Get currently selected variables for this product
                    $product_variables = $variables_handler->get_by_product( $product->id );
                    $selected_variable_ids = array();
                    $selected_values_map = array();
                    foreach ( $product_variables as $pv ) {
                        $selected_variable_ids[] = $pv->id;
                        $selected_values_map[ $pv->id ] = $pv->selected_values;
                    }
                    ?>

                    <?php if ( $all_variables ) : ?>
                        <div id="variables-container">
                            <?php if ( $category_variables ) : ?>
                                <div style="background: #f0f6fc; padding: 15px; border-left: 4px solid #0073aa; margin-bottom: 20px;">
                                    <strong><?php esc_html_e( 'Category Variables', 'u-commerce' ); ?></strong>
                                    <p class="description" style="margin: 5px 0 0 0;">
                                        <?php esc_html_e( 'The following variables are automatically available from the selected category:', 'u-commerce' ); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php foreach ( $all_variables as $variable ) : ?>
                                <?php
                                $is_from_category = false;
                                foreach ( $category_variables as $cv ) {
                                    if ( $cv->id == $variable->id ) {
                                        $is_from_category = true;
                                        break;
                                    }
                                }

                                // Auto-select category variables by default (unless explicitly deselected)
                                $is_selected = in_array( $variable->id, $selected_variable_ids );
                                if ( ! $is_selected && $is_from_category ) {
                                    // Auto-select category variables for new products or if not manually set
                                    $is_selected = true;
                                }

                                $selected_values = isset( $selected_values_map[ $variable->id ] ) ? $selected_values_map[ $variable->id ] : '';

                                // If from category and no values selected yet, select all values by default
                                if ( $is_from_category && empty( $selected_values ) ) {
                                    $selected_values = $variable->values;
                                }

                                $all_values = array_map( 'trim', explode( ',', $variable->values ) );
                                $selected_values_array = array_map( 'trim', explode( ',', $selected_values ) );
                                ?>

                                <div class="variable-selection-row" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; <?php echo $is_from_category ? 'background: #f9f9f9;' : ''; ?>">
                                    <div style="margin-bottom: 10px;">
                                        <label style="display: flex; align-items: center;">
                                            <input type="checkbox"
                                                   name="selected_variables[]"
                                                   value="<?php echo esc_attr( $variable->id ); ?>"
                                                   class="variable-checkbox"
                                                   data-variable-id="<?php echo esc_attr( $variable->id ); ?>"
                                                   <?php checked( $is_selected ); ?>>
                                            <strong style="margin-left: 10px; font-size: 14px;">
                                                <?php echo esc_html( $variable->name ); ?>
                                            </strong>
                                            <?php if ( $is_from_category ) : ?>
                                                <span class="uc-badge uc-badge-info" style="margin-left: 10px; font-size: 11px;">
                                                    <?php esc_html_e( 'From Category', 'u-commerce' ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </label>
                                    </div>

                                    <div class="variable-values-container"
                                         id="values-container-<?php echo esc_attr( $variable->id ); ?>"
                                         style="margin-left: 30px; <?php echo ! $is_selected ? 'display: none;' : ''; ?>">
                                        <p class="description" style="margin-bottom: 8px;">
                                            <?php esc_html_e( 'Select values for this variable:', 'u-commerce' ); ?>
                                        </p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                            <?php foreach ( $all_values as $value ) : ?>
                                                <label style="display: inline-flex; align-items: center; padding: 5px 10px; background: #fff; border: 1px solid #ddd; border-radius: 3px;">
                                                    <input type="checkbox"
                                                           name="variable_values_<?php echo esc_attr( $variable->id ); ?>[]"
                                                           value="<?php echo esc_attr( $value ); ?>"
                                                           <?php checked( in_array( $value, $selected_values_array ) ); ?>>
                                                    <span style="margin-left: 5px;"><?php echo esc_html( $value ); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="description" style="margin-top: 15px;">
                            <strong><?php esc_html_e( 'Note:', 'u-commerce' ); ?></strong>
                            <?php esc_html_e( 'To add new variables, go to U-Commerce → Variables and create them there.', 'u-commerce' ); ?>
                        </p>
                    <?php else : ?>
                        <div style="padding: 40px; text-align: center; background: #f9f9f9; border: 1px dashed #ddd; border-radius: 4px;">
                            <p style="font-size: 16px; color: #666; margin-bottom: 15px;">
                                <?php esc_html_e( 'No variables available.', 'u-commerce' ); ?>
                            </p>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-variables' ) ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Create Variables', 'u-commerce' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <!-- Basic Info Tab -->
                    <h2><?php esc_html_e( 'Product Information', 'u-commerce' ); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="product_name">
                                    <?php esc_html_e( 'Product Name', 'u-commerce' ); ?>
                                    <span style="color: red;">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                       name="name"
                                       id="product_name"
                                       class="regular-text"
                                       value="<?php echo $is_edit ? esc_attr( $product->name ) : ''; ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="product_sku">
                                    <?php esc_html_e( 'SKU', 'u-commerce' ); ?>
                                    <span style="color: red;">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                       name="sku"
                                       id="product_sku"
                                       class="regular-text"
                                       value="<?php echo $is_edit ? esc_attr( $product->sku ) : ''; ?>"
                                       required>
                                <?php if ( ! $is_edit ) : ?>
                                    <button type="button" id="generate-sku" class="button" style="margin-left: 10px;">
                                        <?php esc_html_e( 'Generate SKU', 'u-commerce' ); ?>
                                    </button>
                                <?php endif; ?>
                                <p class="description"><?php esc_html_e( 'Stock Keeping Unit - unique identifier', 'u-commerce' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="product_category">
                                    <?php esc_html_e( 'Category', 'u-commerce' ); ?>
                                </label>
                            </th>
                            <td>
                                <select name="category_id" id="product_category" class="regular-text">
                                    <option value="0"><?php esc_html_e( 'Select Category', 'u-commerce' ); ?></option>
                                    <?php
                                    // Build hierarchical category map
                                    $category_map = array();
                                    foreach ( $all_categories as $cat ) {
                                        if ( $cat->parent_id == 0 ) {
                                            if ( ! isset( $category_map[0] ) ) {
                                                $category_map[0] = array();
                                            }
                                            $category_map[0][] = $cat;
                                        } else {
                                            if ( ! isset( $category_map[ $cat->parent_id ] ) ) {
                                                $category_map[ $cat->parent_id ] = array();
                                            }
                                            $category_map[ $cat->parent_id ][] = $cat;
                                        }
                                    }

                                    // Recursive function to display categories
                                    function display_category_option( $category, $level, $category_map, $selected_id ) {
                                        $indent = str_repeat( '&nbsp;&nbsp;&nbsp;', $level );
                                        $prefix = $level > 0 ? '—' : '';
                                        ?>
                                        <option value="<?php echo esc_attr( $category->id ); ?>" <?php selected( $selected_id, $category->id ); ?>>
                                            <?php echo $indent . $prefix . ' ' . esc_html( $category->name ); ?>
                                        </option>
                                        <?php
                                        // Display children
                                        if ( isset( $category_map[ $category->id ] ) ) {
                                            foreach ( $category_map[ $category->id ] as $child ) {
                                                display_category_option( $child, $level + 1, $category_map, $selected_id );
                                            }
                                        }
                                    }

                                    // Display root categories and their children
                                    if ( isset( $category_map[0] ) ) {
                                        foreach ( $category_map[0] as $root_cat ) {
                                            display_category_option( $root_cat, 0, $category_map, $is_edit ? $product->category_id : 0 );
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="product_base_cost">
                                    <?php esc_html_e( 'Base Cost', 'u-commerce' ); ?>
                                    <span style="color: red;">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="number"
                                       name="base_cost"
                                       id="product_base_cost"
                                       class="regular-text"
                                       step="0.01"
                                       min="0"
                                       value="<?php echo $is_edit ? esc_attr( $product->base_cost ) : ''; ?>"
                                       required>
                                <p class="description"><?php esc_html_e( 'Purchase cost per unit', 'u-commerce' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="product_description">
                                    <?php esc_html_e( 'Description', 'u-commerce' ); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                $content = $is_edit ? $product->description : '';
                                wp_editor(
                                    $content,
                                    'product_description',
                                    array(
                                        'textarea_name' => 'description',
                                        'textarea_rows' => 10,
                                        'media_buttons' => false,
                                        'teeny'         => true,
                                    )
                                );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="product_status">
                                    <?php esc_html_e( 'Status', 'u-commerce' ); ?>
                                </label>
                            </th>
                            <td>
                                <select name="status" id="product_status" class="regular-text">
                                    <option value="active" <?php echo ( ! $is_edit || $product->status === 'active' ) ? 'selected' : ''; ?>>
                                        <?php esc_html_e( 'Active', 'u-commerce' ); ?>
                                    </option>
                                    <option value="inactive" <?php echo ( $is_edit && $product->status === 'inactive' ) ? 'selected' : ''; ?>>
                                        <?php esc_html_e( 'Inactive', 'u-commerce' ); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>

            <p class="submit">
                <input type="submit"
                       name="uc_product_submit"
                       class="button button-primary button-large"
                       value="<?php echo $is_edit ? esc_attr__( 'Update Product', 'u-commerce' ) : esc_attr__( 'Add Product', 'u-commerce' ); ?>">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=u-commerce-products' ) ); ?>"
                   class="button button-large"
                   style="margin-left: 10px;">
                    <?php esc_html_e( 'Cancel', 'u-commerce' ); ?>
                </a>
            </p>
        </form>
    <?php } ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Generate SKU
    $('#generate-sku').on('click', function() {
        var timestamp = Date.now();
        var random = Math.floor(Math.random() * 9999);
        var sku = 'UC-' + timestamp + '-' + random;
        $('#product_sku').val(sku);
    });

    // Toggle variable values container when checkbox is clicked
    $('.variable-checkbox').on('change', function() {
        var variableId = $(this).data('variable-id');
        var valuesContainer = $('#values-container-' + variableId);

        if ($(this).is(':checked')) {
            valuesContainer.slideDown();
        } else {
            valuesContainer.slideUp();
            // Uncheck all value checkboxes when variable is unchecked
            valuesContainer.find('input[type="checkbox"]').prop('checked', false);
        }
    });
});
</script>
