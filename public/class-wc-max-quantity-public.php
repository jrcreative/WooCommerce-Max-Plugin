
<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://volume11.agency
 * @since      1.0.0
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/public
 * @author     Volume11 <info@volume11.agency>
 */
class WC_Max_Quantity_Public {

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
     * Shop data for JavaScript output.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $shop_data    Shop validation data.
     */
    private $shop_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        if (is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_woocommerce()) {
            wp_enqueue_style($this->plugin_name, WC_MAX_QUANTITY_PLUGIN_URL . 'public/css/wc-max-quantity-public.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        if (is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_woocommerce()) {
            wp_enqueue_script($this->plugin_name, WC_MAX_QUANTITY_PLUGIN_URL . 'public/js/wc-max-quantity-public.js', array('jquery'), $this->version, false);
        }
    }

    /**
     * Validate add to cart
     *
     * @since    1.0.0
     */
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

    /**
     * Check cart items
     *
     * @since    1.0.0
     */
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
                
                // Don't automatically reduce quantity - let user choose
            }
        }
    }

    /**
     * Get error message
     *
     * @since    1.0.0
     */
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

    /**
     * Output product data for single product page
     *
     * @since    1.0.0
     */
    public function output_product_data() {
        global $product;
        
        if (!$product || !is_object($product)) {
            return;
        }
        
        $product_id = $product->get_id();
        if (!$product_id) {
            return;
        }
        
        $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
        
        // If no product-specific limit, check for global default
        if (empty($max_quantity)) {
            $max_quantity = get_option('wc_max_quantity_global_default', '');
        }
        
        if (!empty($max_quantity) && is_numeric($max_quantity)) {
            // Get current cart quantity
            $cart_quantity = 0;
            if (WC()->cart && !empty(WC()->cart->get_cart())) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if (isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
                        $cart_quantity += intval($cart_item['quantity']);
                    }
                }
            }
            
            $error_message = $this->get_error_message($product, $max_quantity);
            
            wp_add_inline_script($this->plugin_name, 
                'var wcMaxQuantityData = {
                    maxQuantity: ' . intval($max_quantity) . ',
                    currentCartQuantity: ' . intval($cart_quantity) . ',
                    errorMessage: ' . wp_json_encode($error_message) . ',
                    productId: ' . intval($product_id) . '
                };'
            );
        }
    }

    /**
     * Output product data for shop pages
     *
     * @since    1.0.0
     */
    public function output_shop_product_data() {
        global $product;
        
        if (!$product || !is_object($product)) {
            return;
        }
        
        $product_id = $product->get_id();
        if (!$product_id) {
            return;
        }
        
        $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
        
        // If no product-specific limit, check for global default
        if (empty($max_quantity)) {
            $max_quantity = get_option('wc_max_quantity_global_default', '');
        }
        
        if (!empty($max_quantity) && is_numeric($max_quantity)) {
            // Get current cart quantity
            $cart_quantity = 0;
            if (WC()->cart && !empty(WC()->cart->get_cart())) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if (isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
                        $cart_quantity += intval($cart_item['quantity']);
                    }
                }
            }
            
            $error_message = $this->get_error_message($product, $max_quantity);
            
            // Use class property to store shop data
            if (!isset($this->shop_data)) {
                $this->shop_data = array();
            }
            
            $this->shop_data[$product_id] = array(
                'maxQuantity' => intval($max_quantity),
                'currentCartQuantity' => intval($cart_quantity),
                'errorMessage' => $error_message,
                'productId' => intval($product_id)
            );
            
            // Add action to output all shop data at once in footer
            if (!has_action('wp_footer', array($this, 'output_all_shop_data'))) {
                add_action('wp_footer', array($this, 'output_all_shop_data'));
            }
        }
    }

    /**
     * Output all shop data in footer
     *
     * @since    1.0.0
     */
    public function output_all_shop_data() {
        if (!empty($this->shop_data)) {
            echo '<script type="text/javascript">
                var wcMaxQuantityShopData = ' . wp_json_encode($this->shop_data) . ';
            </script>';
        }
    }

    /**
     * Output cart validation data
     *
     * @since    1.0.0
     */
    public function output_cart_validation_data() {
        if (!is_cart()) {
            return;
        }
        
        $cart_validation_data = array();
        $has_errors = false;
        
        if (WC()->cart && !empty(WC()->cart->get_cart())) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
                $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
                
                // If no product-specific limit, check for global default
                if (empty($max_quantity)) {
                    $max_quantity = get_option('wc_max_quantity_global_default', '');
                }
                
                if (!empty($max_quantity) && is_numeric($max_quantity)) {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        $cart_validation_data[$cart_item_key] = array(
                            'productId' => $product_id,
                            'maxQuantity' => intval($max_quantity),
                            'currentQuantity' => intval($quantity),
                            'isExceeded' => $quantity > $max_quantity,
                            'errorMessage' => $this->get_error_message($product, $max_quantity)
                        );
                        
                        if ($quantity > $max_quantity) {
                            $has_errors = true;
                        }
                    }
                }
            }
        }
        
        if (!empty($cart_validation_data)) {
            wp_add_inline_script($this->plugin_name, 
                'var wcMaxQuantityCartData = {
                    cartItems: ' . wp_json_encode($cart_validation_data) . ',
                    hasErrors: ' . ($has_errors ? 'true' : 'false') . '
                };'
            );
        }
    }

    /**
     * Prevent checkout if exceeded
     *
     * @since    1.0.0
     */
    public function prevent_checkout_if_exceeded() {
        // Check if WooCommerce cart is available
        if (!WC() || !WC()->cart) {
            return;
        }
        
        $has_exceeded = false;
        $error_messages = array();
        
        $cart_contents = WC()->cart->get_cart();
        if (!empty($cart_contents) && is_array($cart_contents)) {
            foreach ($cart_contents as $cart_item_key => $cart_item) {
                if (!isset($cart_item['product_id']) || !isset($cart_item['quantity'])) {
                    continue;
                }
                
                $product_id = intval($cart_item['product_id']);
                $quantity = intval($cart_item['quantity']);
                $max_quantity = get_post_meta($product_id, '_max_quantity_limit', true);
                
                // If no product-specific limit, check for global default
                if (empty($max_quantity)) {
                    $max_quantity = get_option('wc_max_quantity_global_default', '');
                }
                
                if (!empty($max_quantity) && is_numeric($max_quantity) && $quantity > intval($max_quantity)) {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        $message = $this->get_error_message($product, $max_quantity);
                        $error_messages[] = $message;
                        $has_exceeded = true;
                    }
                }
            }
        }
        
        if ($has_exceeded && !empty($error_messages)) {
            foreach ($error_messages as $message) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice(esc_html($message), 'error');
                }
            }
            
            // If we're on checkout page, prevent proceeding
            if (function_exists('is_checkout') && is_checkout() && function_exists('is_wc_endpoint_url') && !is_wc_endpoint_url()) {
                wp_die(
                    esc_html__('Please return to your cart and adjust quantities before proceeding to checkout.', 'wc-max-quantity'),
                    esc_html__('Checkout Error', 'wc-max-quantity'),
                    array('back_link' => true)
                );
            }
        }
    }
}
