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

$wpfaq_functions_file = WPFAQ_PATH . 'includes/wpfaq-functions.php';

if ( ! is_readable( $wpfaq_functions_file ) ) {
	return;
}

require_once $wpfaq_functions_file;

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

	if ( ! is_array( $active_plugins ) ) {
		wpfaq_log( 'The active plugin list had an unexpected shape.' );
		$active_plugins = array();
	}

	if ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
		return true;
	}

	if ( ! is_multisite() ) {
		return false;
	}

	$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

	if ( ! is_array( $network_plugins ) ) {
		wpfaq_log( 'The network plugin list had an unexpected shape.' );
		return false;
	}

	return isset( $network_plugins['woocommerce/woocommerce.php'] );
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

	wpfaq_log( 'Plugin activation was rejected because WooCommerce was unavailable.' );

	if ( ! function_exists( 'deactivate_plugins' ) ) {
		$wpfaq_plugin_functions = ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_readable( $wpfaq_plugin_functions ) ) {
			require_once $wpfaq_plugin_functions;
		} else {
			wpfaq_log( 'The WordPress plugin functions file was unavailable during activation.' );
		}
	}

	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	} else {
		wpfaq_log( 'WordPress could not deactivate the plugin after a rejected activation.' );
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

	wpfaq_log( 'WooCommerce was unavailable during plugin bootstrap.' );

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

/**
 * Loads and starts plugin components.
 *
 * @return void
 */
function wpfaq_boot_plugin() {
	if ( ! wpfaq_has_woocommerce() ) {
		return;
	}

	$wpfaq_class_files = array(
		WPFAQ_PATH . 'includes/class-wpfaq-admin.php',
		WPFAQ_PATH . 'includes/class-wpfaq-frontend.php',
		WPFAQ_PATH . 'includes/class-wpfaq-tabs.php',
		WPFAQ_PATH . 'includes/class-wpfaq-plugin.php',
	);

	foreach ( $wpfaq_class_files as $wpfaq_class_file ) {
		if ( ! is_readable( $wpfaq_class_file ) ) {
			wpfaq_log( 'A required plugin class file was unavailable.', array( 'file' => basename( $wpfaq_class_file ) ) );
			return;
		}

		require_once $wpfaq_class_file;
	}

	if ( ! class_exists( 'WPFAQ_Plugin' ) || ! class_exists( 'WPFAQ_Admin' ) || ! class_exists( 'WPFAQ_Frontend' ) || ! class_exists( 'WPFAQ_Tabs' ) ) {
		wpfaq_log( 'A required plugin class could not be loaded.' );
		return;
	}

	$wpfaq_plugin = WPFAQ_Plugin::get_instance();

	if ( ! $wpfaq_plugin instanceof WPFAQ_Plugin ) {
		wpfaq_log( 'The plugin orchestrator could not be initialized.' );
		return;
	}

	$wpfaq_plugin->register_hooks();
}

register_activation_hook( __FILE__, 'wpfaq_activate' );
add_action( 'plugins_loaded', 'wpfaq_check_woocommerce', 1 );
add_action( 'plugins_loaded', 'wpfaq_boot_plugin', 20 );
add_action( 'init', 'wpfaq_load_textdomain' );
