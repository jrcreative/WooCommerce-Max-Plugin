
<?php
/**
 * Plugin Name: WooCommerce Max Quantity Limiter
 * Plugin URI: https://volume11.agency
 * Description: Allows setting maximum quantity limits for WooCommerce products with customizable error messages.
 * Version: 1.0.0
 * Author: Volume11
 * Requires at least: 5.0
 * Tested up to: 6.3
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * Text Domain: wc-max-quantity
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . __('WooCommerce Max Quantity Limiter requires WooCommerce to be installed and active.', 'wc-max-quantity') . '</p></div>';
    });
    return;
}

class WC_Max_Quantity_Limiter {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
    }
    
    public function init() {
        // Add product tab
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_product_data_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_data_fields'));
        
        // Cart validation
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
        add_action('woocommerce_check_cart_items', array($this, 'check_cart_items'));
        
        // Admin settings
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_max_quantity', array($this, 'settings_tab'));
        add_action('woocommerce_update_options_max_quantity', array($this, 'update_settings'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('wc-max-quantity', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
    
    public function add_product_data_tab($tabs) {
        $tabs['max_quantity'] = array(
            'label'    => __('Max Quantity', 'wc-max-quantity'),
            'target'   => 'max_quantity_product_data',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 80,
        );
        return $tabs;
    }
    
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
    
    public function validate_add_to_cart($passed, $product_id, $quantity) {
        $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
        
        // If no product-specific limit, check for global default
        if (empty($max_quantity)) {
            $max_quantity = get_option('wc_max_quantity_global_default', '');
            if (empty($max_quantity)) {
                return $passed;
            }
        }
        
        // Check current cart quantity
        $cart_quantity = 0;
        if (!empty(WC()->cart->get_cart())) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $cart_quantity += $cart_item['quantity'];
                }
            }
        }
        
        $total_quantity = $cart_quantity + $quantity;
        
        if ($total_quantity > $max_quantity) {
            $product = wc_get_product($product_id);
            $message = $this->get_error_message($product, $max_quantity);
            wc_add_notice($message, 'error');
            return false;
        }
        
        return $passed;
    }
    
    public function check_cart_items() {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
            
            // If no product-specific limit, check for global default
            if (empty($max_quantity)) {
                $max_quantity = get_option('wc_max_quantity_global_default', '');
            }
            
            if (!empty($max_quantity) && $cart_item['quantity'] > $max_quantity) {
                $product = wc_get_product($product_id);
                $message = $this->get_error_message($product, $max_quantity);
                wc_add_notice($message, 'error');
                
                // Update cart item to max allowed quantity
                WC()->cart->set_quantity($cart_item_key, $max_quantity);
            }
        }
    }
    
    private function get_error_message($product, $max_quantity) {
        // Get custom message for this product
        $custom_message = get_post_meta($product->get_id(), '_max_quantity_message', true);
        
        if (!empty($custom_message)) {
            $message = $custom_message;
        } else {
            // Get global default message
            $message = get_option('wc_max_quantity_default_message', __('You cannot add more than {max_qty} of "{product_name}" to your cart.', 'wc-max-quantity'));
        }
        
        // Replace placeholders
        $message = str_replace('{max_qty}', $max_quantity, $message);
        $message = str_replace('{product_name}', $product->get_name(), $message);
        
        return $message;
    }
    
    public function add_settings_tab($settings_tabs) {
        $settings_tabs['max_quantity'] = __('Max Quantity', 'wc-max-quantity');
        return $settings_tabs;
    }
    
    public function settings_tab() {
        woocommerce_admin_fields($this->get_settings());
    }
    
    public function update_settings() {
        woocommerce_update_options($this->get_settings());
    }
    
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

// Initialize the plugin
new WC_Max_Quantity_Limiter();
