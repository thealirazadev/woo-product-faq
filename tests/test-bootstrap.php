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

	/**
	 * Activation without WooCommerce stops the request, so WordPress never marks the plugin active.
	 *
	 * Deactivating from inside the activation hook is not enough: activate_plugin() appends the
	 * plugin to the active list after the hook returns.
	 *
	 * @return void
	 */
	public function test_activation_without_woocommerce_dies_before_the_plugin_is_activated() {
		update_option( 'active_plugins', array() );

		$this->expectException( 'WPDieException' );
		$this->expectExceptionMessage( 'requires WooCommerce' );

		wpfaq_activate();
	}

	/**
	 * Activation with WooCommerce active returns normally and leaves the active list alone.
	 *
	 * @return void
	 */
	public function test_activation_with_woocommerce_active_proceeds() {
		update_option( 'active_plugins', array( 'woocommerce/woocommerce.php' ) );

		$this->assertNull( wpfaq_activate() );
		$this->assertSame( array( 'woocommerce/woocommerce.php' ), get_option( 'active_plugins' ) );
	}
}
