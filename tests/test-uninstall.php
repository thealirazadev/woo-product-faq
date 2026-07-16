<?php
/**
 * Tests for uninstall.php meta cleanup.
 *
 * @package Woo_Product_FAQ
 */

/**
 * Covers removal of plugin post meta from all products on uninstall.
 */
class WPFAQ_Uninstall_Test extends WP_UnitTestCase {

	/**
	 * Seeds two products with all three plugin meta keys, runs the
	 * uninstall routine, and asserts every key is gone from both.
	 *
	 * @return void
	 */
	public function test_uninstall_removes_plugin_meta_from_all_products() {
		$wpfaq_product_ids = array(
			self::factory()->post->create( array( 'post_type' => 'product' ) ),
			self::factory()->post->create( array( 'post_type' => 'product' ) ),
		);

		foreach ( $wpfaq_product_ids as $wpfaq_product_id ) {
			update_post_meta(
				$wpfaq_product_id,
				'_wpfaq_faqs',
				array(
					array(
						'question' => 'Q?',
						'answer'   => 'A.',
					),
				)
			);
			update_post_meta( $wpfaq_product_id, '_wpfaq_display_location', 'after_summary' );
			update_post_meta(
				$wpfaq_product_id,
				'_wpfaq_custom_tabs',
				array(
					array(
						'title'   => 'T',
						'content' => 'C',
					),
				)
			);
		}

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'woo-product-faq/woo-product-faq.php' );
		}

		require dirname( __DIR__ ) . '/uninstall.php';

		foreach ( $wpfaq_product_ids as $wpfaq_product_id ) {
			$this->assertSame( '', get_post_meta( $wpfaq_product_id, '_wpfaq_faqs', true ) );
			$this->assertSame( '', get_post_meta( $wpfaq_product_id, '_wpfaq_display_location', true ) );
			$this->assertSame( '', get_post_meta( $wpfaq_product_id, '_wpfaq_custom_tabs', true ) );
		}
	}
}
