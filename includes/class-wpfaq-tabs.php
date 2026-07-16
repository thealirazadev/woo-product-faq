<?php
/**
 * Product tabs integration.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the FAQ product tab on the frontend.
 */
final class WPFAQ_Tabs {

	/**
	 * Whether hooks have been registered.
	 *
	 * @var bool
	 */
	private $hooks_registered = false;

	/**
	 * Registers the product tabs filter.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( $this->hooks_registered ) {
			return;
		}

		add_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs' ) );
		$this->hooks_registered = true;
	}

	/**
	 * Adds the FAQ tab when the display location is "tab" and FAQs exist.
	 *
	 * @param array $tabs WooCommerce product tabs.
	 * @return array
	 */
	public function add_tabs( $tabs ) {
		if ( ! is_array( $tabs ) ) {
			wpfaq_log( 'Product tabs had an unexpected shape.' );
			return array();
		}

		$wpfaq_product_id = get_the_ID();

		if ( ! $wpfaq_product_id ) {
			return $tabs;
		}

		if ( 'tab' !== wpfaq_get_display_location( $wpfaq_product_id ) ) {
			return $tabs;
		}

		$wpfaq_faqs = wpfaq_get_faqs( $wpfaq_product_id );

		if ( empty( $wpfaq_faqs ) ) {
			return $tabs;
		}

		$tabs['wpfaq_faq'] = array(
			'title'    => __( 'FAQ', 'woo-product-faq' ),
			'priority' => 30,
			'callback' => array( $this, 'render_faq_tab' ),
		);

		return $tabs;
	}

	/**
	 * Renders the FAQ tab content.
	 *
	 * @return void
	 */
	public function render_faq_tab() {
		$wpfaq_product_id = get_the_ID();

		if ( ! $wpfaq_product_id ) {
			wpfaq_log( 'The FAQ tab could not resolve a product id.' );
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
				'show_title' => false,
				'product_id' => $wpfaq_product_id,
			)
		);
	}
}
