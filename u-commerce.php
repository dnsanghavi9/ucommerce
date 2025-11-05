<?php
/**
 * Plugin Name: U-Commerce
 * Plugin URI: https://github.com/yourusername/u-commerce
 * Description: A comprehensive multi-center retail business management system with inventory, billing, and reporting capabilities for WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: u-commerce
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package UCommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'UC_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'UC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'UC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'UC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Database version.
 */
define( 'UC_DB_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function uc_activate_plugin() {
    require_once UC_PLUGIN_DIR . 'includes/core/class-uc-activator.php';
    UC_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function uc_deactivate_plugin() {
    require_once UC_PLUGIN_DIR . 'includes/core/class-uc-activator.php';
    UC_Activator::deactivate();
}

register_activation_hook( __FILE__, 'uc_activate_plugin' );
register_deactivation_hook( __FILE__, 'uc_deactivate_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require UC_PLUGIN_DIR . 'includes/class-uc-plugin.php';

/**
 * Begins execution of the plugin.
 */
function uc_run_plugin() {
    $plugin = UC_Plugin::get_instance();
    $plugin->run();
}

// Initialize the plugin
uc_run_plugin();
