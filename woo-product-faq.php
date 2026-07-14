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

/**
 * Checks whether WooCommerce is available.
 *
 * @return bool
 */
function wpfaq_has_woocommerce() {
	return class_exists( 'WooCommerce' ) || defined( 'WC_VERSION' );
}

/**
 * Checks whether WooCommerce is in an active plugin list.
 *
 * @return bool
 */
function wpfaq_has_active_woocommerce() {
	$active_plugins = get_option( 'active_plugins', array() );

	if ( is_array( $active_plugins ) && in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
		return true;
	}

	if ( ! is_multisite() ) {
		return false;
	}

	$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

	return is_array( $network_plugins ) && isset( $network_plugins['woocommerce/woocommerce.php'] );
}

/**
 * Prevents activation when WooCommerce is unavailable.
 *
 * @return void
 */
function wpfaq_activate() {
	if ( wpfaq_has_active_woocommerce() ) {
		return;
	}

	if ( ! function_exists( 'deactivate_plugins' ) ) {
		$wpfaq_plugin_functions = ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_readable( $wpfaq_plugin_functions ) ) {
			require_once $wpfaq_plugin_functions;
		}
	}

	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	}

	add_action( 'admin_notices', 'wpfaq_render_dependency_notice' );
}

/**
 * Stops the plugin from booting when WooCommerce becomes unavailable.
 *
 * @return void
 */
function wpfaq_check_woocommerce() {
	if ( wpfaq_has_woocommerce() ) {
		return;
	}

	add_action( 'admin_notices', 'wpfaq_render_dependency_notice' );
}

/**
 * Renders the WooCommerce dependency notice.
 *
 * @return void
 */
function wpfaq_render_dependency_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<?php esc_html_e( 'WooCommerce Product FAQ requires WooCommerce to be installed and active.', 'woo-product-faq' ); ?>
		</p>
	</div>
	<?php
}

register_activation_hook( __FILE__, 'wpfaq_activate' );
add_action( 'plugins_loaded', 'wpfaq_check_woocommerce', 1 );
