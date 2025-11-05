<?php
/**
 * Settings admin page.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin/pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'u_commerce_manage_settings' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'u-commerce' ) );
}

// Handle form submission
if ( isset( $_POST['uc_settings_submit'] ) ) {
    check_admin_referer( 'uc_settings_save', 'uc_settings_nonce' );

    $settings = array(
        'general'       => array(
            'company_name'    => isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '',
            'company_address' => isset( $_POST['company_address'] ) ? sanitize_textarea_field( $_POST['company_address'] ) : '',
            'currency'        => isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : 'INR',
            'decimal_places'  => isset( $_POST['decimal_places'] ) ? absint( $_POST['decimal_places'] ) : 2,
        ),
        'inventory'     => array(
            'low_stock_threshold'     => isset( $_POST['low_stock_threshold'] ) ? absint( $_POST['low_stock_threshold'] ) : 10,
            'auto_barcode_generation' => isset( $_POST['auto_barcode_generation'] ),
            'stock_management_method' => isset( $_POST['stock_management_method'] ) ? sanitize_text_field( $_POST['stock_management_method'] ) : 'fifo',
        ),
        'billing'       => array(
            'bill_number_format'   => isset( $_POST['bill_number_format'] ) ? sanitize_text_field( $_POST['bill_number_format'] ) : 'UC-{TYPE}-{YEAR}-{NUMBER}',
            'auto_bill_numbering'  => isset( $_POST['auto_bill_numbering'] ),
            'default_payment_terms' => isset( $_POST['default_payment_terms'] ) ? sanitize_text_field( $_POST['default_payment_terms'] ) : 'immediate',
        ),
        'notifications' => array(
            'email_notifications'   => isset( $_POST['email_notifications'] ),
            'low_stock_alerts'      => isset( $_POST['low_stock_alerts'] ),
            'new_sale_notifications' => isset( $_POST['new_sale_notifications'] ),
        ),
    );

    update_option( 'u_commerce_settings', $settings );

    echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'u-commerce' ) . '</p></div>';
}

// Get current settings
$settings = get_option( 'u_commerce_settings', array() );
?>

<div class="wrap">
    <h1><?php esc_html_e( 'U-Commerce Settings', 'u-commerce' ); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field( 'uc_settings_save', 'uc_settings_nonce' ); ?>

        <div class="uc-tabs">
            <ul class="uc-tabs-nav">
                <li><a href="#general" class="active"><?php esc_html_e( 'General', 'u-commerce' ); ?></a></li>
                <li><a href="#inventory"><?php esc_html_e( 'Inventory', 'u-commerce' ); ?></a></li>
                <li><a href="#billing"><?php esc_html_e( 'Billing', 'u-commerce' ); ?></a></li>
                <li><a href="#notifications"><?php esc_html_e( 'Notifications', 'u-commerce' ); ?></a></li>
            </ul>
        </div>

        <div class="uc-tabs-container">
            <!-- General Settings -->
            <div id="general" class="uc-tab-content active">
                <div class="uc-card">
                    <h2><?php esc_html_e( 'General Settings', 'u-commerce' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="company_name"><?php esc_html_e( 'Company Name', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="company_name" id="company_name" class="regular-text"
                                       value="<?php echo esc_attr( $settings['general']['company_name'] ?? '' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="company_address"><?php esc_html_e( 'Company Address', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <textarea name="company_address" id="company_address" rows="3" class="large-text"><?php
                                    echo esc_textarea( $settings['general']['company_address'] ?? '' );
                                ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="currency"><?php esc_html_e( 'Currency', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="currency" id="currency" class="regular-text"
                                       value="<?php echo esc_attr( $settings['general']['currency'] ?? 'INR' ); ?>">
                                <p class="description"><?php esc_html_e( 'Currency code (e.g., INR, USD, EUR)', 'u-commerce' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="decimal_places"><?php esc_html_e( 'Decimal Places', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="decimal_places" id="decimal_places" class="small-text"
                                       value="<?php echo esc_attr( $settings['general']['decimal_places'] ?? 2 ); ?>" min="0" max="4">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Inventory Settings -->
            <div id="inventory" class="uc-tab-content">
                <div class="uc-card">
                    <h2><?php esc_html_e( 'Inventory Settings', 'u-commerce' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="low_stock_threshold"><?php esc_html_e( 'Low Stock Threshold', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="small-text"
                                       value="<?php echo esc_attr( $settings['inventory']['low_stock_threshold'] ?? 10 ); ?>" min="0">
                                <p class="description"><?php esc_html_e( 'Alert when stock falls below this number', 'u-commerce' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="auto_barcode_generation"><?php esc_html_e( 'Auto Barcode Generation', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="auto_barcode_generation" id="auto_barcode_generation" value="1"
                                    <?php checked( $settings['inventory']['auto_barcode_generation'] ?? false ); ?>>
                                <label for="auto_barcode_generation"><?php esc_html_e( 'Automatically generate barcodes for new products', 'u-commerce' ); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="stock_management_method"><?php esc_html_e( 'Stock Management Method', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <select name="stock_management_method" id="stock_management_method">
                                    <option value="fifo" <?php selected( $settings['inventory']['stock_management_method'] ?? 'fifo', 'fifo' ); ?>>
                                        <?php esc_html_e( 'FIFO (First In, First Out)', 'u-commerce' ); ?>
                                    </option>
                                    <option value="lifo" <?php selected( $settings['inventory']['stock_management_method'] ?? 'fifo', 'lifo' ); ?>>
                                        <?php esc_html_e( 'LIFO (Last In, First Out)', 'u-commerce' ); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Billing Settings -->
            <div id="billing" class="uc-tab-content">
                <div class="uc-card">
                    <h2><?php esc_html_e( 'Billing Settings', 'u-commerce' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="bill_number_format"><?php esc_html_e( 'Bill Number Format', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="bill_number_format" id="bill_number_format" class="regular-text"
                                       value="<?php echo esc_attr( $settings['billing']['bill_number_format'] ?? 'UC-{TYPE}-{YEAR}-{NUMBER}' ); ?>">
                                <p class="description"><?php esc_html_e( 'Available placeholders: {TYPE}, {YEAR}, {NUMBER}', 'u-commerce' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="auto_bill_numbering"><?php esc_html_e( 'Auto Bill Numbering', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="auto_bill_numbering" id="auto_bill_numbering" value="1"
                                    <?php checked( $settings['billing']['auto_bill_numbering'] ?? true ); ?>>
                                <label for="auto_bill_numbering"><?php esc_html_e( 'Automatically generate bill numbers', 'u-commerce' ); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="default_payment_terms"><?php esc_html_e( 'Default Payment Terms', 'u-commerce' ); ?></label>
                            </th>
                            <td>
                                <select name="default_payment_terms" id="default_payment_terms">
                                    <option value="immediate" <?php selected( $settings['billing']['default_payment_terms'] ?? 'immediate', 'immediate' ); ?>>
                                        <?php esc_html_e( 'Immediate', 'u-commerce' ); ?>
                                    </option>
                                    <option value="net30" <?php selected( $settings['billing']['default_payment_terms'] ?? 'immediate', 'net30' ); ?>>
                                        <?php esc_html_e( 'Net 30', 'u-commerce' ); ?>
                                    </option>
                                    <option value="net60" <?php selected( $settings['billing']['default_payment_terms'] ?? 'immediate', 'net60' ); ?>>
                                        <?php esc_html_e( 'Net 60', 'u-commerce' ); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Notifications Settings -->
            <div id="notifications" class="uc-tab-content">
                <div class="uc-card">
                    <h2><?php esc_html_e( 'Notification Settings', 'u-commerce' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Email Notifications', 'u-commerce' ); ?></th>
                            <td>
                                <input type="checkbox" name="email_notifications" id="email_notifications" value="1"
                                    <?php checked( $settings['notifications']['email_notifications'] ?? true ); ?>>
                                <label for="email_notifications"><?php esc_html_e( 'Enable email notifications', 'u-commerce' ); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Low Stock Alerts', 'u-commerce' ); ?></th>
                            <td>
                                <input type="checkbox" name="low_stock_alerts" id="low_stock_alerts" value="1"
                                    <?php checked( $settings['notifications']['low_stock_alerts'] ?? true ); ?>>
                                <label for="low_stock_alerts"><?php esc_html_e( 'Send alerts for low stock items', 'u-commerce' ); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'New Sale Notifications', 'u-commerce' ); ?></th>
                            <td>
                                <input type="checkbox" name="new_sale_notifications" id="new_sale_notifications" value="1"
                                    <?php checked( $settings['notifications']['new_sale_notifications'] ?? false ); ?>>
                                <label for="new_sale_notifications"><?php esc_html_e( 'Notify on new sales', 'u-commerce' ); ?></label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="uc_settings_submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'u-commerce' ); ?>">
        </p>
    </form>
</div>
