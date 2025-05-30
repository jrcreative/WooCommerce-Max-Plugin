<?php

/**
 * Fired during plugin activation
 *
 * @link       https://volume11.agency
 * @since      1.0.3
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include WordPress core files
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 * @author     Volume11 <info@volume11.agency>
 */
class WC_Max_Quantity_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Check if WordPress functions are available
        if (!function_exists('add_action') || !function_exists('esc_html__')) {
            return;
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('WooCommerce Max Quantity Limiter requires PHP 7.4 or higher.', 'wc-max-quantity');
                echo '</p></div>';
            });
            return;
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('WooCommerce Max Quantity Limiter requires WordPress 5.0 or higher.', 'wc-max-quantity');
                echo '</p></div>';
            });
            return;
        }
        
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('WooCommerce Max Quantity Limiter requires WooCommerce to be installed and active.', 'wc-max-quantity');
                echo '</p></div>';
            });
            return;
        }
    }
}
