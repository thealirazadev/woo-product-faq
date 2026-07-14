<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Woo_Product_FAQ
 */

$wpfaq_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $wpfaq_tests_dir ) {
	$wpfaq_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $wpfaq_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- WordPress is not loaded yet.
	fwrite( STDERR, "WordPress test suite was not found. Run bin/install-wp-tests.sh first.\n" );
	exit( 1 );
}

if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) && file_exists( dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Required by the WordPress test suite.
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );
}

require_once $wpfaq_tests_dir . '/includes/functions.php';

/**
 * Loads WooCommerce and this plugin for integration tests.
 *
 * @return void
 */
function wpfaq_tests_load_plugin() {
	$woocommerce_file = getenv( 'WPFAQ_TESTS_WOOCOMMERCE' );

	if ( ! $woocommerce_file ) {
		$woocommerce_file = '/tmp/wordpress/wp-content/plugins/woocommerce/woocommerce.php';
	}

	if ( ! file_exists( $woocommerce_file ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- WordPress is not loaded yet.
		fwrite( STDERR, "WooCommerce was not found. Run bin/install-wp-tests.sh first.\n" );
		exit( 1 );
	}

	require $woocommerce_file;

	if ( ! class_exists( 'WC_Install' ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- WordPress is still bootstrapping.
		fwrite( STDERR, "WooCommerce's installer was not available.\n" );
		exit( 1 );
	}

	WC_Install::create_tables();
	require dirname( __DIR__ ) . '/woo-product-faq.php';
}

tests_add_filter( 'muplugins_loaded', 'wpfaq_tests_load_plugin' );

require $wpfaq_tests_dir . '/includes/bootstrap.php';
