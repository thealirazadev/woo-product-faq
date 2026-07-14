<?php
/**
 * Shared plugin functions.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes a structured debug log entry.
 *
 * @param string $message Log message.
 * @param array  $context Structured context without sensitive values.
 * @return void
 */
function wpfaq_log( $message, $context = array() ) {
	if ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG ) {
		return;
	}

	if ( ! is_string( $message ) || '' === trim( $message ) ) {
		return;
	}

	if ( ! is_array( $context ) ) {
		$context = array( 'context_type' => gettype( $context ) );
	}

	$encoded_context = wp_json_encode( $context );

	if ( false === $encoded_context ) {
		$encoded_context = '{}';
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is the plugin's centralized, debug-only logger.
	if ( false === error_log( '[woo-product-faq] ' . $message . ' ' . $encoded_context ) ) {
		return;
	}
}

/**
 * Loads the plugin translation catalog.
 *
 * @return void
 */
function wpfaq_load_textdomain() {
	$relative_path = dirname( plugin_basename( WPFAQ_PATH . 'woo-product-faq.php' ) ) . '/languages';
	$loaded        = load_plugin_textdomain( 'woo-product-faq', false, $relative_path );
	$locale        = determine_locale();

	if ( false === $loaded && 'en_US' !== $locale ) {
		wpfaq_log(
			'Translation catalog could not be loaded.',
			array( 'locale' => sanitize_key( $locale ) )
		);
	}
}
