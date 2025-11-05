<?php
/**
 * Barcode generation helper.
 *
 * Provides barcode generation functionality.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/helpers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Barcode helper class.
 */
class UC_Barcode {

    /**
     * Generate barcode for product.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return string|false Barcode string or false on failure.
     */
    public static function generate( $product_id, $center_id ) {
        global $wpdb;

        $database = new UC_Database();

        // Check if barcode already exists
        $existing = $database->get_row(
            'barcodes',
            array(
                'product_id' => $product_id,
                'center_id'  => $center_id,
            )
        );

        if ( $existing ) {
            return $existing->barcode;
        }

        // Generate unique barcode
        $barcode = self::generate_unique_barcode( $product_id, $center_id );

        // Insert into database
        $inserted = $database->insert(
            'barcodes',
            array(
                'product_id'   => $product_id,
                'center_id'    => $center_id,
                'barcode'      => $barcode,
                'generated_by' => get_current_user_id(),
            ),
            array( '%d', '%d', '%s', '%d' )
        );

        if ( $inserted ) {
            do_action( 'u_commerce_barcode_generated', $barcode, $product_id, $center_id );
            return $barcode;
        }

        return false;
    }

    /**
     * Generate unique barcode.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return string Unique barcode.
     */
    private static function generate_unique_barcode( $product_id, $center_id ) {
        $database = new UC_Database();

        do {
            // Generate EAN-13 style barcode
            $barcode = self::generate_ean13( $product_id, $center_id );

            // Check if barcode exists
            $exists = $database->get_row(
                'barcodes',
                array( 'barcode' => $barcode )
            );
        } while ( $exists );

        return $barcode;
    }

    /**
     * Generate EAN-13 barcode.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return string EAN-13 barcode.
     */
    private static function generate_ean13( $product_id, $center_id ) {
        // Country code (2 digits) - Using 99 for internal use
        $country_code = '99';

        // Center code (3 digits)
        $center_code = str_pad( $center_id, 3, '0', STR_PAD_LEFT );

        // Product code (6 digits)
        $product_code = str_pad( $product_id, 6, '0', STR_PAD_LEFT );

        // First 12 digits
        $barcode = $country_code . $center_code . $product_code;

        // Calculate check digit
        $check_digit = self::calculate_ean13_check_digit( $barcode );

        return $barcode . $check_digit;
    }

    /**
     * Calculate EAN-13 check digit.
     *
     * @param string $barcode First 12 digits of barcode.
     * @return int Check digit.
     */
    private static function calculate_ean13_check_digit( $barcode ) {
        $sum = 0;

        for ( $i = 0; $i < 12; $i++ ) {
            $digit = (int) $barcode[ $i ];
            $sum += ( $i % 2 === 0 ) ? $digit : $digit * 3;
        }

        $check_digit = ( 10 - ( $sum % 10 ) ) % 10;

        return $check_digit;
    }

    /**
     * Validate EAN-13 barcode.
     *
     * @param string $barcode Barcode to validate.
     * @return bool True if valid.
     */
    public static function validate_ean13( $barcode ) {
        if ( strlen( $barcode ) !== 13 || ! ctype_digit( $barcode ) ) {
            return false;
        }

        $check_digit = (int) substr( $barcode, -1 );
        $calculated_check_digit = self::calculate_ean13_check_digit( substr( $barcode, 0, 12 ) );

        return $check_digit === $calculated_check_digit;
    }

    /**
     * Get product by barcode.
     *
     * @param string $barcode Barcode.
     * @return object|null Product data or null.
     */
    public static function get_product_by_barcode( $barcode ) {
        $database = new UC_Database();

        $barcode_data = $database->get_row(
            'barcodes',
            array( 'barcode' => $barcode )
        );

        if ( ! $barcode_data ) {
            return null;
        }

        return $database->get_row(
            'products',
            array( 'id' => $barcode_data->product_id )
        );
    }

    /**
     * Generate barcode SVG.
     *
     * @param string $barcode Barcode string.
     * @param int    $width   Image width.
     * @param int    $height  Image height.
     * @return string SVG markup.
     */
    public static function generate_svg( $barcode, $width = 200, $height = 100 ) {
        // Simple barcode SVG representation
        $svg = '<svg width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" xmlns="http://www.w3.org/2000/svg">';

        // Draw bars
        $bar_width = $width / 95; // EAN-13 has 95 bars
        $x = 0;

        // Start guard
        $svg .= self::draw_bar( $x, $height * 0.8, $bar_width );
        $x += $bar_width * 3;

        // Data bars (simplified representation)
        $barcode_array = str_split( $barcode );
        foreach ( $barcode_array as $digit ) {
            if ( (int) $digit % 2 === 0 ) {
                $svg .= self::draw_bar( $x, $height * 0.7, $bar_width );
            }
            $x += $bar_width * 7;
        }

        // Text
        $svg .= '<text x="' . ( $width / 2 ) . '" y="' . ( $height - 5 ) . '" font-size="12" text-anchor="middle" font-family="monospace">' . esc_html( $barcode ) . '</text>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Draw SVG bar.
     *
     * @param float $x      X position.
     * @param float $height Bar height.
     * @param float $width  Bar width.
     * @return string SVG rect element.
     */
    private static function draw_bar( $x, $height, $width ) {
        return '<rect x="' . esc_attr( $x ) . '" y="10" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" fill="black"/>';
    }

    /**
     * Delete barcode.
     *
     * @param int $product_id Product ID.
     * @param int $center_id  Center ID.
     * @return bool True on success.
     */
    public static function delete( $product_id, $center_id ) {
        $database = new UC_Database();

        $result = $database->delete(
            'barcodes',
            array(
                'product_id' => $product_id,
                'center_id'  => $center_id,
            ),
            array( '%d', '%d' )
        );

        return $result !== false;
    }
}
