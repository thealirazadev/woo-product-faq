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
 * Loads a plugin template file, exposing $args to it.
 *
 * @param string $template_name Template file name relative to templates/.
 * @param array  $args          Variables the template can read from $args.
 * @return void
 */
function wpfaq_get_template( $template_name, $args = array() ) {
	$template_name = ltrim( (string) $template_name, '/' );
	$template_path = WPFAQ_PATH . 'templates/' . $template_name;

	if ( ! is_readable( $template_path ) ) {
		wpfaq_log( 'A template file was unavailable.', array( 'template' => $template_name ) );
		return;
	}

	if ( ! is_array( $args ) ) {
		$args = array();
	}

	include $template_path;
}

/**
 * Reads the saved FAQ rows for a product.
 *
 * @param int $product_id Product post ID.
 * @return array List of ['question' => string, 'answer' => string], empty when none saved.
 */
function wpfaq_get_faqs( $product_id ) {
	$product_id = absint( $product_id );

	if ( ! $product_id ) {
		return array();
	}

	$wpfaq_faqs = get_post_meta( $product_id, '_wpfaq_faqs', true );

	if ( ! is_array( $wpfaq_faqs ) ) {
		return array();
	}

	$wpfaq_valid_faqs = array();

	foreach ( $wpfaq_faqs as $wpfaq_faq ) {
		if ( ! is_array( $wpfaq_faq ) || ! isset( $wpfaq_faq['question'], $wpfaq_faq['answer'] ) ) {
			continue;
		}

		$wpfaq_valid_faqs[] = array(
			'question' => (string) $wpfaq_faq['question'],
			'answer'   => (string) $wpfaq_faq['answer'],
		);
	}

	return $wpfaq_valid_faqs;
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
