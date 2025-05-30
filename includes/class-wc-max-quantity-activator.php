
<?php

/**
 * Fired during plugin activation
 *
 * @link       https://volume11.agency
 * @since      1.0.0
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 */

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
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce Max Quantity Limiter requires PHP 7.4 or higher.', 'wc-max-quantity'),
                esc_html__('Plugin Activation Error', 'wc-max-quantity'),
                array('back_link' => true)
            );
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce Max Quantity Limiter requires WordPress 5.0 or higher.', 'wc-max-quantity'),
                esc_html__('Plugin Activation Error', 'wc-max-quantity'),
                array('back_link' => true)
            );
        }
        
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce Max Quantity Limiter requires WooCommerce to be installed and active.', 'wc-max-quantity'),
                esc_html__('Plugin Activation Error', 'wc-max-quantity'),
                array('back_link' => true)
            );
        }
    }
}
