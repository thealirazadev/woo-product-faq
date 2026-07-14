<?php
/**
 * Bootstrap tests.
 *
 * @package Woo_Product_FAQ
 */

/**
 * Verifies the plugin bootstrap contract.
 */
class WPFAQ_Bootstrap_Test extends WP_UnitTestCase {

	/**
	 * Verifies version and path constants.
	 *
	 * @return void
	 */
	public function test_bootstrap_defines_expected_constants() {
		$this->assertSame( '1.0.0', WPFAQ_VERSION );
		$this->assertSame( plugin_dir_path( dirname( __DIR__ ) . '/woo-product-faq.php' ), WPFAQ_PATH );
		$this->assertNotSame( '', WPFAQ_URL );
	}
}
