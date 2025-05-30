
<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://volume11.agency
 * @since      1.0.0
 *
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Max_Quantity
 * @subpackage WC_Max_Quantity/includes
 * @author     Volume11 <info@volume11.agency>
 */
class WC_Max_Quantity {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WC_Max_Quantity_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WC_MAX_QUANTITY_VERSION')) {
            $this->version = WC_MAX_QUANTITY_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wc-max-quantity';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WC_Max_Quantity_Loader. Orchestrates the hooks of the plugin.
     * - WC_Max_Quantity_i18n. Defines internationalization functionality.
     * - WC_Max_Quantity_Admin. Defines all hooks for the admin area.
     * - WC_Max_Quantity_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'includes/class-wc-max-quantity-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'includes/class-wc-max-quantity-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'admin/class-wc-max-quantity-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once WC_MAX_QUANTITY_PLUGIN_DIR . 'public/class-wc-max-quantity-public.php';

        $this->loader = new WC_Max_Quantity_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WC_Max_Quantity_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new WC_Max_Quantity_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WC_Max_Quantity_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // WooCommerce specific hooks
        $this->loader->add_action('before_woocommerce_init', $plugin_admin, 'declare_hpos_compatibility');
        $this->loader->add_filter('woocommerce_product_data_tabs', $plugin_admin, 'add_product_data_tab');
        $this->loader->add_action('woocommerce_product_data_panels', $plugin_admin, 'add_product_data_fields');
        $this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'save_product_data_fields');
        
        // Settings
        $this->loader->add_filter('woocommerce_settings_tabs_array', $plugin_admin, 'add_settings_tab', 50);
        $this->loader->add_action('woocommerce_settings_tabs_max_quantity', $plugin_admin, 'settings_tab');
        $this->loader->add_action('woocommerce_update_options_max_quantity', $plugin_admin, 'update_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new WC_Max_Quantity_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Cart validation
        $this->loader->add_filter('woocommerce_add_to_cart_validation', $plugin_public, 'validate_add_to_cart', 10, 3);
        $this->loader->add_action('woocommerce_check_cart_items', $plugin_public, 'check_cart_items');
        
        // Frontend data output
        $this->loader->add_action('woocommerce_single_product_summary', $plugin_public, 'output_product_data', 25);
        $this->loader->add_action('woocommerce_after_shop_loop_item', $plugin_public, 'output_shop_product_data', 25);
        $this->loader->add_action('woocommerce_before_cart', $plugin_public, 'output_cart_validation_data');
        
        // Checkout validation
        $this->loader->add_action('woocommerce_check_cart_items', $plugin_public, 'prevent_checkout_if_exceeded');
        $this->loader->add_action('woocommerce_before_checkout_process', $plugin_public, 'prevent_checkout_if_exceeded');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    WC_Max_Quantity_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
