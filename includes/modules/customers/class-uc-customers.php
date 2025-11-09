<?php
/**
 * Customers Handler
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/customers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customers handler class.
 */
class UC_Customers {

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
	 * Get all customers.
	 *
	 * @param array $args Query arguments.
	 * @return array List of customers.
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'orderby' => 'name',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->database->get_results( 'customers', $args );
	}

	/**
	 * Get a single customer by ID.
	 *
	 * @param int $customer_id Customer ID.
	 * @return object|null Customer object or null if not found.
	 */
	public function get( $customer_id ) {
		return $this->database->get_row( 'customers', array( 'id' => $customer_id ) );
	}

	/**
	 * Create a new customer.
	 *
	 * @param array $data Customer data.
	 * @return int|false Customer ID on success, false on failure.
	 */
	public function create( $data ) {
		$customer_data = array(
			'name'       => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'phone'      => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
			'email'      => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'address'    => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
			'gst_number' => isset( $data['gst_number'] ) ? sanitize_text_field( $data['gst_number'] ) : '',
			'status'     => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
		);

		return $this->database->insert( 'customers', $customer_data );
	}

	/**
	 * Update a customer.
	 *
	 * @param int   $customer_id Customer ID.
	 * @param array $data        Customer data.
	 * @return bool True on success, false on failure.
	 */
	public function update( $customer_id, $data ) {
		$customer_data = array();

		if ( isset( $data['name'] ) ) {
			$customer_data['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['phone'] ) ) {
			$customer_data['phone'] = sanitize_text_field( $data['phone'] );
		}

		if ( isset( $data['email'] ) ) {
			$customer_data['email'] = sanitize_email( $data['email'] );
		}

		if ( isset( $data['address'] ) ) {
			$customer_data['address'] = sanitize_textarea_field( $data['address'] );
		}

		if ( isset( $data['gst_number'] ) ) {
			$customer_data['gst_number'] = sanitize_text_field( $data['gst_number'] );
		}

		if ( isset( $data['status'] ) ) {
			$customer_data['status'] = sanitize_text_field( $data['status'] );
		}

		return $this->database->update( 'customers', $customer_data, array( 'id' => $customer_id ) ) !== false;
	}

	/**
	 * Delete a customer.
	 *
	 * @param int $customer_id Customer ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $customer_id ) {
		return $this->database->delete( 'customers', array( 'id' => $customer_id ) ) !== false;
	}

	/**
	 * Get total count of customers.
	 *
	 * @return int Total count.
	 */
	public function get_count() {
		return $this->database->get_count( 'customers' );
	}
}
