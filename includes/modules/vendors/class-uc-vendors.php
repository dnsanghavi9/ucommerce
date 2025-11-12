<?php
/**
 * Vendors Handler
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/vendors
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendors handler class.
 */
class UC_Vendors {

	/**
	 * Database handler.
	 *
	 * @var UC_Database
	 */
	private $database;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->database = new UC_Database();
	}

	/**
	 * Get all vendors.
	 *
	 * @param array $args Query arguments.
	 * @return array List of vendors.
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->database->get_results( 'vendors', $args );
	}

	/**
	 * Get a single vendor by ID.
	 *
	 * @param int $vendor_id Vendor ID.
	 * @return object|null Vendor object or null if not found.
	 */
	public function get( $vendor_id ) {
		return $this->database->get_row( 'vendors', array( 'id' => $vendor_id ) );
	}

	/**
	 * Create a new vendor.
	 *
	 * @param array $data Vendor data.
	 * @return int|false Vendor ID on success, false on failure.
	 */
	public function create( $data ) {
		$vendor_data = array(
			'name'       => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'phone'      => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
			'email'      => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'address'    => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
			'gst_number' => isset( $data['gst_number'] ) ? sanitize_text_field( $data['gst_number'] ) : '',
			'status'     => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
		);

		return $this->database->insert( 'vendors', $vendor_data );
	}

	/**
	 * Update a vendor.
	 *
	 * @param int   $vendor_id Vendor ID.
	 * @param array $data      Vendor data.
	 * @return bool True on success, false on failure.
	 */
	public function update( $vendor_id, $data ) {
		$vendor_data = array();

		if ( isset( $data['name'] ) ) {
			$vendor_data['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['phone'] ) ) {
			$vendor_data['phone'] = sanitize_text_field( $data['phone'] );
		}

		if ( isset( $data['email'] ) ) {
			$vendor_data['email'] = sanitize_email( $data['email'] );
		}

		if ( isset( $data['address'] ) ) {
			$vendor_data['address'] = sanitize_textarea_field( $data['address'] );
		}

		if ( isset( $data['gst_number'] ) ) {
			$vendor_data['gst_number'] = sanitize_text_field( $data['gst_number'] );
		}

		if ( isset( $data['status'] ) ) {
			$vendor_data['status'] = sanitize_text_field( $data['status'] );
		}

		return $this->database->update( 'vendors', $vendor_data, array( 'id' => $vendor_id ) ) !== false;
	}

	/**
	 * Delete a vendor.
	 *
	 * @param int $vendor_id Vendor ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $vendor_id ) {
		return $this->database->delete( 'vendors', array( 'id' => $vendor_id ) ) !== false;
	}

	/**
	 * Get total count of vendors.
	 *
	 * @return int Total count.
	 */
	public function get_count() {
		return $this->database->get_count( 'vendors' );
	}
}
