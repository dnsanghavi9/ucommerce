<?php
/**
 * Product Variables Handler
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/modules/products
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables handler class.
 */
class UC_Variables {

	/**
	 * Database handler.
	 *
	 * @var UC_Database
	 */
	private $database;

	/**
	 * Variables table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->database   = new UC_Database();
		$this->table_name = $this->database->get_table( 'variables' );
	}

	/**
	 * Get all variables.
	 *
	 * @param array $args Query arguments.
	 * @return array List of variables.
	 */
	public function get_all( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'orderby' => 'created_at',
			'order'   => 'DESC',
			'limit'   => null,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT * FROM {$this->table_name}";
		$query .= " ORDER BY {$args['orderby']} {$args['order']}";

		if ( $args['limit'] ) {
			$query .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Get a single variable by ID.
	 *
	 * @param int $id Variable ID.
	 * @return object|null Variable object or null if not found.
	 */
	public function get( $id ) {
		return $this->database->get_row( 'product_variables', $id );
	}

	/**
	 * Create a new variable.
	 *
	 * @param array $data Variable data.
	 * @return int|false Variable ID on success, false on failure.
	 */
	public function create( $data ) {
		$prepared_data = array(
			'name'   => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'type'   => isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'text',
			'values' => isset( $data['values'] ) ? sanitize_textarea_field( $data['values'] ) : '',
		);

		return $this->database->insert( 'variables', $prepared_data );
	}

	/**
	 * Update a variable.
	 *
	 * @param int   $id   Variable ID.
	 * @param array $data Variable data.
	 * @return bool True on success, false on failure.
	 */
	public function update( $id, $data ) {
		$prepared_data = array();

		if ( isset( $data['name'] ) ) {
			$prepared_data['name'] = sanitize_text_field( $data['name'] );
		}

		if ( isset( $data['type'] ) ) {
			$prepared_data['type'] = sanitize_text_field( $data['type'] );
		}

		if ( isset( $data['values'] ) ) {
			$prepared_data['values'] = sanitize_textarea_field( $data['values'] );
		}

		return $this->database->update( 'variables', $prepared_data, array( 'id' => $id ) ) !== false;
	}

	/**
	 * Delete a variable.
	 *
	 * @param int $id Variable ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		// Also delete relationships
		global $wpdb;
		$category_variables_table = $this->database->get_table( 'category_variables' );
		$product_variables_table  = $this->database->get_table( 'product_variable_values' );

		$wpdb->delete( $category_variables_table, array( 'variable_id' => $id ) );
		$wpdb->delete( $product_variables_table, array( 'variable_id' => $id ) );

		return $this->database->delete( 'variables', array( 'id' => $id ) ) !== false;
	}

	/**
	 * Get total count of variables.
	 *
	 * @return int Total count.
	 */
	public function get_count() {
		return $this->database->get_count( 'variables' );
	}

	/**
	 * Link a variable to a category.
	 *
	 * @param int $category_id Category ID.
	 * @param int $variable_id Variable ID.
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function link_to_category( $category_id, $variable_id ) {
		global $wpdb;
		$table = $this->database->get_table( 'category_variables' );

		// Check if already linked
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE category_id = %d AND variable_id = %d",
				$category_id,
				$variable_id
			)
		);

		if ( $existing ) {
			return $existing;
		}

		$result = $wpdb->insert(
			$table,
			array(
				'category_id' => $category_id,
				'variable_id' => $variable_id,
			),
			array( '%d', '%d' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Unlink a variable from a category.
	 *
	 * @param int $category_id Category ID.
	 * @param int $variable_id Variable ID.
	 * @return bool True on success, false on failure.
	 */
	public function unlink_from_category( $category_id, $variable_id ) {
		global $wpdb;
		$table = $this->database->get_table( 'category_variables' );

		return $wpdb->delete(
			$table,
			array(
				'category_id' => $category_id,
				'variable_id' => $variable_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Get variables linked to a category.
	 *
	 * @param int $category_id Category ID.
	 * @return array List of variable objects.
	 */
	public function get_by_category( $category_id ) {
		global $wpdb;
		$category_variables_table = $this->database->get_table( 'category_variables' );

		$query = $wpdb->prepare(
			"SELECT v.*
			FROM {$this->table_name} v
			INNER JOIN {$category_variables_table} cv ON v.id = cv.variable_id
			WHERE cv.category_id = %d
			ORDER BY v.name ASC",
			$category_id
		);

		return $wpdb->get_results( $query );
	}

	/**
	 * Get categories linked to a variable.
	 *
	 * @param int $variable_id Variable ID.
	 * @return array List of category IDs.
	 */
	public function get_linked_categories( $variable_id ) {
		global $wpdb;
		$table = $this->database->get_table( 'category_variables' );

		$query = $wpdb->prepare(
			"SELECT category_id FROM {$table} WHERE variable_id = %d",
			$variable_id
		);

		return $wpdb->get_col( $query );
	}

	/**
	 * Link a variable to a product with selected values.
	 *
	 * @param int    $product_id      Product ID.
	 * @param int    $variable_id     Variable ID.
	 * @param string $selected_values Comma-separated selected values.
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function link_to_product( $product_id, $variable_id, $selected_values ) {
		global $wpdb;
		$table = $this->database->get_table( 'product_variable_values' );

		// Check if already linked
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND variable_id = %d",
				$product_id,
				$variable_id
			)
		);

		if ( $existing ) {
			// Update existing
			$wpdb->update(
				$table,
				array( 'selected_values' => $selected_values ),
				array( 'id' => $existing ),
				array( '%s' ),
				array( '%d' )
			);
			return $existing;
		}

		// Insert new
		$result = $wpdb->insert(
			$table,
			array(
				'product_id'      => $product_id,
				'variable_id'     => $variable_id,
				'selected_values' => $selected_values,
			),
			array( '%d', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Unlink a variable from a product.
	 *
	 * @param int $product_id  Product ID.
	 * @param int $variable_id Variable ID.
	 * @return bool True on success, false on failure.
	 */
	public function unlink_from_product( $product_id, $variable_id ) {
		global $wpdb;
		$table = $this->database->get_table( 'product_variable_values' );

		return $wpdb->delete(
			$table,
			array(
				'product_id'  => $product_id,
				'variable_id' => $variable_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Get variables linked to a product.
	 *
	 * @param int $product_id Product ID.
	 * @return array List of variable objects with selected_values property.
	 */
	public function get_by_product( $product_id ) {
		global $wpdb;
		$product_variables_table = $this->database->get_table( 'product_variable_values' );

		$query = $wpdb->prepare(
			"SELECT v.*, pv.selected_values
			FROM {$this->table_name} v
			INNER JOIN {$product_variables_table} pv ON v.id = pv.variable_id
			WHERE pv.product_id = %d
			ORDER BY v.name ASC",
			$product_id
		);

		return $wpdb->get_results( $query );
	}
}
