<?php
/**
 * Fallback for WooCommerce's test base class.
 *
 * These tests only need WooCommerce loaded, not its test helpers, and the WooCommerce plugin
 * download does not include its test framework. When that framework is absent, the WordPress
 * base test case stands in.
 *
 * @package Woo_Product_FAQ
 */

if ( ! class_exists( 'WC_Unit_Test_Case' ) ) {
	/**
	 * Base test case used when WooCommerce's own test framework is not installed.
	 */
	abstract class WC_Unit_Test_Case extends WP_UnitTestCase {
	}
}
