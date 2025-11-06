<?php
/**
 * Database Updater for schema changes.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database updater class.
 */
class UC_Database_Updater {

	/**
	 * Run database updates.
	 */
	public static function update() {
		self::update_schema_v1_1();
	}

	/**
	 * Update schema to version 1.1
	 * - Add category_variables pivot table
	 * - Add product_variables pivot table
	 * - Add reorder_level to inventory table
	 * - Add effective dates to pricing table
	 */
	private static function update_schema_v1_1() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_prefix    = $wpdb->prefix;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Category-Variables Relationship Table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_category_variables (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			category_id bigint(20) UNSIGNED NOT NULL,
			variable_id bigint(20) UNSIGNED NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY category_variable (category_id, variable_id),
			KEY category_id (category_id),
			KEY variable_id (variable_id)
		) $charset_collate;";

		// Product-Variables Relationship Table
		$sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}ucommerce_product_variable_values (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id bigint(20) UNSIGNED NOT NULL,
			variable_id bigint(20) UNSIGNED NOT NULL,
			selected_values text NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY product_variable (product_id, variable_id),
			KEY product_id (product_id),
			KEY variable_id (variable_id)
		) $charset_collate;";

		// Execute table creation
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}

		// Add reorder_level to inventory table if not exists
		$inventory_table = $table_prefix . 'ucommerce_inventory';
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `{$inventory_table}` LIKE %s",
				'reorder_level'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query(
				"ALTER TABLE `{$inventory_table}`
				ADD COLUMN `reorder_level` int(11) NOT NULL DEFAULT 0 AFTER `reserved_quantity`"
			);
		}

		// Add effective_from to pricing table if not exists
		$pricing_table = $table_prefix . 'ucommerce_pricing';
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `{$pricing_table}` LIKE %s",
				'effective_from'
			)
		);

		if ( empty( $column_exists ) ) {
			$wpdb->query(
				"ALTER TABLE `{$pricing_table}`
				ADD COLUMN `effective_from` datetime NULL AFTER `margin_percentage`,
				ADD COLUMN `effective_to` datetime NULL AFTER `effective_from`"
			);
		}

		// Mark as updated
		update_option( 'u_commerce_db_version', '1.1' );
	}
}
