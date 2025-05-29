
=== WooCommerce Max Quantity Limiter ===
Contributors: yourname
Tags: woocommerce, quantity, limit, cart, products
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Set maximum quantity limits for WooCommerce products with customizable error messages.

== Description ==

WooCommerce Max Quantity Limiter allows you to set maximum quantity limits for individual WooCommerce products. When customers try to add more than the allowed quantity, they'll see a customizable error message using WooCommerce's native toast notification system.

**Features:**

* Set individual maximum quantity limits for each product
* Custom error messages per product or use a global default
* Integrates seamlessly with WooCommerce's notification system
* Works with simple and variable products
* Validates both during add-to-cart and in the cart
* Only activates when WooCommerce is installed

**How to Use:**

1. Install and activate the plugin (WooCommerce must be active)
2. Edit any product and go to the "Max Quantity" tab
3. Set the maximum quantity allowed for that product
4. Optionally customize the error message
5. Configure global default message in WooCommerce > Settings > Max Quantity

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-max-quantity` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure WooCommerce is installed and active
4. Configure settings under WooCommerce > Settings > Max Quantity

== Frequently Asked Questions ==

= Does this work with variable products? =

Yes, the plugin works with both simple and variable products.

= Can I set different messages for different products? =

Yes, you can set custom error messages for each product individually, or use the global default message.

= What happens if someone already has items in their cart? =

The plugin will validate the cart and automatically adjust quantities to the maximum allowed limit.

== Changelog ==

= 1.0.0 =
* Initial release
* Basic functionality for setting max quantities
* Custom error messages
* WooCommerce integration
