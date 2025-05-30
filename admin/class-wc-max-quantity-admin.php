
<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://volume11.agency
 * @since      1.0.0
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/admin
 * @author     Volume11 <info@volume11.agency>
 */
class WC_Max_Quantity_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, WC_MAX_QUANTITY_PLUGIN_URL . 'admin/css/wc-max-quantity-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, WC_MAX_QUANTITY_PLUGIN_URL . 'admin/js/wc-max-quantity-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Declare HPOS compatibility
     *
     * @since    1.0.0
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', WC_MAX_QUANTITY_PLUGIN_FILE, true);
        }
    }

    /**
     * Add product data tab
     *
     * @since    1.0.0
     */
    public function add_product_data_tab($tabs) {
        $tabs['max_quantity'] = array(
            'label'    => __('Max Quantity', 'wc-max-quantity'),
            'target'   => 'max_quantity_product_data',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 80,
        );
        return $tabs;
    }

    /**
     * Add product data fields
     *
     * @since    1.0.0
     */
    public function add_product_data_fields() {
        global $post;
        ?>
        <div id="max_quantity_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(array(
                    'id'          => '_max_quantity_limit',
                    'label'       => __('Maximum Quantity', 'wc-max-quantity'),
                    'placeholder' => __('No limit', 'wc-max-quantity'),
                    'desc_tip'    => true,
                    'description' => __('Set the maximum quantity that can be purchased for this product. Leave empty for no limit.', 'wc-max-quantity'),
                    'type'        => 'number',
                    'custom_attributes' => array(
                        'step' => '1',
                        'min'  => '1'
                    )
                ));
                
                woocommerce_wp_textarea_input(array(
                    'id'          => '_max_quantity_message',
                    'label'       => __('Custom Error Message', 'wc-max-quantity'),
                    'placeholder' => __('Leave empty to use default message', 'wc-max-quantity'),
                    'desc_tip'    => true,
                    'description' => __('Custom message to show when quantity limit is exceeded. Use {max_qty} to display the maximum quantity and {product_name} for the product name.', 'wc-max-quantity'),
                    'rows'        => 3
                ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save product data fields
     *
     * @since    1.0.0
     */
    public function save_product_data_fields($post_id) {
        $max_quantity = isset($_POST['_max_quantity_limit']) ? sanitize_text_field($_POST['_max_quantity_limit']) : '';
        $custom_message = isset($_POST['_max_quantity_message']) ? sanitize_textarea_field($_POST['_max_quantity_message']) : '';
        
        if (!empty($max_quantity) && is_numeric($max_quantity) && $max_quantity > 0) {
            update_post_meta($post_id, '_max_quantity_limit', intval($max_quantity));
        } else {
            delete_post_meta($post_id, '_max_quantity_limit');
        }
        
        if (!empty($custom_message)) {
            update_post_meta($post_id, '_max_quantity_message', $custom_message);
        } else {
            delete_post_meta($post_id, '_max_quantity_message');
        }
    }

    /**
     * Add settings tab
     *
     * @since    1.0.0
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs['max_quantity'] = __('Max Quantity', 'wc-max-quantity');
        return $settings_tabs;
    }

    /**
     * Settings tab content
     *
     * @since    1.0.0
     */
    public function settings_tab() {
        woocommerce_admin_fields($this->get_settings());
    }

    /**
     * Update settings
     *
     * @since    1.0.0
     */
    public function update_settings() {
        woocommerce_update_options($this->get_settings());
    }

    /**
     * Get settings array
     *
     * @since    1.0.0
     */
    public function get_settings() {
        $settings = array(
            'section_title' => array(
                'name'     => __('Max Quantity Settings', 'wc-max-quantity'),
                'type'     => 'title',
                'desc'     => __('Configure default settings for maximum quantity limits.', 'wc-max-quantity'),
                'id'       => 'wc_max_quantity_section_title'
            ),
            'global_default_maximum' => array(
                'name'     => __('Global Default Maximum Quantity', 'wc-max-quantity'),
                'type'     => 'number',
                'desc'     => __('Default maximum quantity for all products. This will be used for products that don\'t have their own specific limit set. Leave empty for no global limit.', 'wc-max-quantity'),
                'id'       => 'wc_max_quantity_global_default',
                'default'  => '',
                'css'      => 'min-width:100px;',
                'custom_attributes' => array(
                    'step' => '1',
                    'min'  => '1'
                )
            ),
            'default_message' => array(
                'name'     => __('Default Error Message', 'wc-max-quantity'),
                'type'     => 'textarea',
                'desc'     => __('Default message to show when quantity limit is exceeded. Use {max_qty} to display the maximum quantity and {product_name} for the product name.', 'wc-max-quantity'),
                'id'       => 'wc_max_quantity_default_message',
                'default'  => __('You cannot add more than {max_qty} of "{product_name}" to your cart.', 'wc-max-quantity'),
                'css'      => 'min-width:300px;',
                'rows'     => 3
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'wc_max_quantity_section_end'
            )
        );
        
        return apply_filters('wc_max_quantity_settings', $settings);
    }
}
