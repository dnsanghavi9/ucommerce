<?php
/**
 * Utility functions.
 *
 * Provides common utility functions used throughout the plugin.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/helpers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Utilities class.
 */
class UC_Utilities {

    /**
     * Sanitize array recursively.
     *
     * @param array $array Array to sanitize.
     * @return array Sanitized array.
     */
    public static function sanitize_array( $array ) {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = self::sanitize_array( $value );
            } else {
                $value = sanitize_text_field( $value );
            }
        }

        return $array;
    }

    /**
     * Format price.
     *
     * @param float  $price    Price value.
     * @param string $currency Currency symbol.
     * @return string Formatted price.
     */
    public static function format_price( $price, $currency = '' ) {
        $settings = get_option( 'u_commerce_settings', array() );
        $decimals = isset( $settings['general']['decimal_places'] ) ? $settings['general']['decimal_places'] : 2;

        if ( empty( $currency ) ) {
            $currency = isset( $settings['general']['currency'] ) ? $settings['general']['currency'] : 'INR';
        }

        $formatted_price = number_format( $price, $decimals );

        return $currency . ' ' . $formatted_price;
    }

    /**
     * Generate unique bill number.
     *
     * @param string $type Bill type (purchase/sales).
     * @return string Bill number.
     */
    public static function generate_bill_number( $type = 'sales' ) {
        $settings = get_option( 'u_commerce_settings', array() );
        $format   = isset( $settings['billing']['bill_number_format'] ) ?
                    $settings['billing']['bill_number_format'] : 'UC-{TYPE}-{YEAR}-{NUMBER}';

        $year   = date( 'Y' );
        $type_code = strtoupper( $type );

        // Get last bill number for this type and year
        $option_key = "u_commerce_last_bill_number_{$type}_{$year}";
        $last_number = get_option( $option_key, 0 );
        $new_number = $last_number + 1;

        // Update option
        update_option( $option_key, $new_number );

        // Replace placeholders
        $bill_number = str_replace(
            array( '{TYPE}', '{YEAR}', '{NUMBER}' ),
            array( $type_code, $year, str_pad( $new_number, 5, '0', STR_PAD_LEFT ) ),
            $format
        );

        return $bill_number;
    }

    /**
     * Generate unique SKU.
     *
     * @param string $prefix SKU prefix.
     * @return string SKU.
     */
    public static function generate_sku( $prefix = 'UC' ) {
        $timestamp = time();
        $random = wp_rand( 1000, 9999 );

        return strtoupper( $prefix ) . '-' . $timestamp . '-' . $random;
    }

    /**
     * Log message to debug file.
     *
     * @param string $message Message to log.
     * @param string $level   Log level (info, warning, error).
     */
    public static function log( $message, $level = 'info' ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $log_file = UC_PLUGIN_DIR . 'debug.log';
        $timestamp = date( 'Y-m-d H:i:s' );
        $log_entry = "[{$timestamp}] [{$level}] {$message}\n";

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $log_file, $log_entry, FILE_APPEND );
    }

    /**
     * Get date format.
     *
     * @return string Date format.
     */
    public static function get_date_format() {
        return get_option( 'date_format', 'Y-m-d' );
    }

    /**
     * Get time format.
     *
     * @return string Time format.
     */
    public static function get_time_format() {
        return get_option( 'time_format', 'H:i:s' );
    }

    /**
     * Get datetime format.
     *
     * @return string Datetime format.
     */
    public static function get_datetime_format() {
        return self::get_date_format() . ' ' . self::get_time_format();
    }

    /**
     * Format date.
     *
     * @param string $date   Date string.
     * @param string $format Date format.
     * @return string Formatted date.
     */
    public static function format_date( $date, $format = '' ) {
        if ( empty( $format ) ) {
            $format = self::get_date_format();
        }

        return date_i18n( $format, strtotime( $date ) );
    }

    /**
     * Format datetime.
     *
     * @param string $datetime Datetime string.
     * @param string $format   Datetime format.
     * @return string Formatted datetime.
     */
    public static function format_datetime( $datetime, $format = '' ) {
        if ( empty( $format ) ) {
            $format = self::get_datetime_format();
        }

        return date_i18n( $format, strtotime( $datetime ) );
    }

    /**
     * Calculate margin percentage.
     *
     * @param float $cost         Cost price.
     * @param float $selling_price Selling price.
     * @return float Margin percentage.
     */
    public static function calculate_margin( $cost, $selling_price ) {
        if ( $selling_price <= 0 ) {
            return 0;
        }

        $margin = ( ( $selling_price - $cost ) / $selling_price ) * 100;

        return round( $margin, 2 );
    }

    /**
     * Calculate selling price from margin.
     *
     * @param float $cost   Cost price.
     * @param float $margin Margin percentage.
     * @return float Selling price.
     */
    public static function calculate_selling_price( $cost, $margin ) {
        $selling_price = $cost / ( 1 - ( $margin / 100 ) );

        return round( $selling_price, 2 );
    }

    /**
     * Get plugin setting.
     *
     * @param string $key     Setting key (dot notation supported).
     * @param mixed  $default Default value.
     * @return mixed Setting value.
     */
    public static function get_setting( $key, $default = '' ) {
        $settings = get_option( 'u_commerce_settings', array() );

        // Support dot notation
        $keys = explode( '.', $key );
        $value = $settings;

        foreach ( $keys as $k ) {
            if ( isset( $value[ $k ] ) ) {
                $value = $value[ $k ];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Update plugin setting.
     *
     * @param string $key   Setting key (dot notation supported).
     * @param mixed  $value Setting value.
     * @return bool True on success.
     */
    public static function update_setting( $key, $value ) {
        $settings = get_option( 'u_commerce_settings', array() );

        // Support dot notation
        $keys = explode( '.', $key );
        $current = &$settings;

        foreach ( $keys as $k ) {
            if ( ! isset( $current[ $k ] ) ) {
                $current[ $k ] = array();
            }
            $current = &$current[ $k ];
        }

        $current = $value;

        return update_option( 'u_commerce_settings', $settings );
    }

    /**
     * Check if user can access center.
     *
     * @param int $center_id Center ID.
     * @param int $user_id   User ID (default: current user).
     * @return bool True if user can access.
     */
    public static function user_can_access_center( $center_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Admins and super admins can access all centers
        if ( current_user_can( 'manage_options' ) ||
             current_user_can( 'u_commerce_manage_centers' ) ) {
            return true;
        }

        // Check if user's assigned center matches
        $user_center_id = UC_Capabilities::get_user_center_id( $user_id );

        return $user_center_id === $center_id;
    }
}
