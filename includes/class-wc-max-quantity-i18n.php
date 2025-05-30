
<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://volume11.agency
 * @since      1.0.0
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 * @author     Volume11 <info@volume11.agency>
 */
class WC_Max_Quantity_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wc-max-quantity',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
