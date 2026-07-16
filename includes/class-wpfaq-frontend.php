<?php
/**
 * Single product page frontend integration.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues frontend assets and renders the FAQ accordion on product pages.
 */
final class WPFAQ_Frontend {

	/**
	 * Whether hooks have been registered.
	 *
	 * @var bool
	 */
	private $hooks_registered = false;

	/**
	 * Registers frontend hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( $this->hooks_registered ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'render_after_summary' ), 15 );
		$this->hooks_registered = true;
	}

	/**
	 * Enqueues accordion assets on single product pages only.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'wpfaq-frontend',
			WPFAQ_URL . 'assets/css/frontend.css',
			array(),
			WPFAQ_VERSION
		);

		wp_enqueue_script(
			'wpfaq-frontend',
			WPFAQ_URL . 'assets/js/frontend.js',
			array(),
			WPFAQ_VERSION,
			true
		);
	}

	/**
	 * Renders the FAQ accordion after the product summary.
	 *
	 * @return void
	 */
	public function render_after_summary() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$wpfaq_product_id = get_the_ID();

		if ( ! $wpfaq_product_id ) {
			wpfaq_log( 'The FAQ accordion could not resolve a product id after the summary.' );
			return;
		}

		if ( 'after_summary' !== wpfaq_get_display_location( $wpfaq_product_id ) ) {
			return;
		}

		$wpfaq_faqs = wpfaq_get_faqs( $wpfaq_product_id );

		if ( empty( $wpfaq_faqs ) ) {
			return;
		}

		wpfaq_get_template(
			'faq-accordion.php',
			array(
				'faqs'       => $wpfaq_faqs,
				'show_title' => true,
				'product_id' => $wpfaq_product_id,
			)
		);
	}
}
