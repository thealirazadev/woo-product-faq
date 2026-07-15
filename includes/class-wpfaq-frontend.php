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
}
