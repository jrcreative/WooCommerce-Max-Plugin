<?php
/**
 * Plugin Name: WooCommerce Max Quantity Limiter
 * Plugin URI: https://volume11.agency
 * Description: Allows setting maximum quantity limits for WooCommerce products with customizable error messages.
 * Version: 1.0.3
 * Author: Volume11
 * Requires at least: 5.0
 * Tested up to: 6.3
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * Text Domain: wc-max-quantity
 * Domain Path: /languages
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_MAX_QUANTITY_VERSION', '1.0.3');
define('WC_MAX_QUANTITY_PLUGIN_FILE', __FILE__);
define('WC_MAX_QUANTITY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_MAX_QUANTITY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_MAX_QUANTITY_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WC_MAX_QUANTITY_TEXT_DOMAIN', 'wc-max-quantity');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-max-quantity-activator.php
 */
function activate_wc_max_quantity() {
    require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'includes/class-wc-max-quantity-activator.php';
    WC_Max_Quantity_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-max-quantity-deactivator.php
 */
function deactivate_wc_max_quantity() {
    require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'includes/class-wc-max-quantity-deactivator.php';
    WC_Max_Quantity_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wc_max_quantity');
register_deactivation_hook(__FILE__, 'deactivate_wc_max_quantity');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WC_MAX_QUANTITY_PLUGIN_DIR . 'includes/class-wc-max-quantity.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_max_quantity() {
    $plugin = new WC_Max_Quantity();
    $plugin->run();
}

// Check if WooCommerce is active before running plugin
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        run_wc_max_quantity();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('WooCommerce Max Quantity Limiter requires WooCommerce to be installed and active.', 'wc-max-quantity');
            echo '</p></div>';
        });
    }
});