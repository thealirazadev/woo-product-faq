<?php
/**
 * Plugin Name: WooCommerce Product FAQ
 * Plugin URI:  https://github.com/thealirazadev/woo-product-faq
 * Description: Adds per-product FAQs and custom tabs to WooCommerce products.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.9
 * License:     MIT
 * License URI: https://opensource.org/license/mit/
 * Text Domain: woo-product-faq
 * Domain Path: /languages
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPFAQ_VERSION', '1.0.0' );
define( 'WPFAQ_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPFAQ_URL', plugin_dir_url( __FILE__ ) );
