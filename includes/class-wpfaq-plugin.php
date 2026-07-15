<?php
/**
 * Plugin orchestrator.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates plugin components.
 */
final class WPFAQ_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var WPFAQ_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Admin component.
	 *
	 * @var WPFAQ_Admin
	 */
	private $admin;

	/**
	 * Frontend component.
	 *
	 * @var WPFAQ_Frontend
	 */
	private $frontend;

	/**
	 * Whether component hooks have been registered.
	 *
	 * @var bool
	 */
	private $hooks_registered = false;

	/**
	 * Returns the singleton instance.
	 *
	 * @return WPFAQ_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Creates plugin components.
	 */
	private function __construct() {
		$this->admin    = new WPFAQ_Admin();
		$this->frontend = new WPFAQ_Frontend();
	}

	/**
	 * Registers component hooks once.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( $this->hooks_registered ) {
			return;
		}

		$this->admin->register_hooks();
		$this->frontend->register_hooks();
		$this->hooks_registered = true;
	}
}
