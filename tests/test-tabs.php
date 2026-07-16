<?php
/**
 * Tests for WPFAQ_Tabs::add_tabs().
 *
 * @package Woo_Product_FAQ
 */

/**
 * Covers FAQ tab registration based on display location and saved FAQs.
 */
class WPFAQ_Tabs_Test extends WC_Unit_Test_Case {

	/**
	 * Tabs component under test.
	 *
	 * @var WPFAQ_Tabs
	 */
	private $tabs;

	/**
	 * Product post ID used across tests.
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Creates a product and visits it so get_the_ID() resolves in the filter.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->tabs = new WPFAQ_Tabs();
		$product    = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$this->product_id = $product->save();

		$this->go_to( get_permalink( $this->product_id ) );
	}

	/**
	 * The FAQ tab is added when location is 'tab' and FAQs exist.
	 *
	 * @return void
	 */
	public function test_faq_tab_added_when_location_is_tab_and_faqs_exist() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_faqs',
			array(
				array(
					'question' => 'A question?',
					'answer'   => 'An answer.',
				),
			)
		);

		$tabs = $this->tabs->add_tabs( array() );

		$this->assertArrayHasKey( 'wpfaq_faq', $tabs );
	}

	/**
	 * No tab is added when there are no saved FAQs.
	 *
	 * @return void
	 */
	public function test_no_tab_added_when_no_faqs_saved() {
		$tabs = $this->tabs->add_tabs( array() );

		$this->assertArrayNotHasKey( 'wpfaq_faq', $tabs );
	}

	/**
	 * No tab is added when location is 'after_summary', even with FAQs saved.
	 *
	 * @return void
	 */
	public function test_no_tab_added_when_location_is_after_summary() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_faqs',
			array(
				array(
					'question' => 'A question?',
					'answer'   => 'An answer.',
				),
			)
		);
		update_post_meta( $this->product_id, '_wpfaq_display_location', 'after_summary' );

		$tabs = $this->tabs->add_tabs( array() );

		$this->assertArrayNotHasKey( 'wpfaq_faq', $tabs );
	}

	/**
	 * An unexpected non-array $tabs argument is handled without a fatal.
	 *
	 * @return void
	 */
	public function test_handles_non_array_tabs_argument() {
		$this->assertSame( array(), $this->tabs->add_tabs( null ) );
	}

	/**
	 * Custom tabs are appended in stored order with the saved title.
	 *
	 * @return void
	 */
	public function test_custom_tabs_appended_in_order() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_custom_tabs',
			array(
				array(
					'title'   => 'Shipping',
					'content' => 'Ships in 3 days.',
				),
				array(
					'title'   => 'Returns',
					'content' => '30-day returns.',
				),
			)
		);

		$tabs = $this->tabs->add_custom_tabs( array() );
		$keys = array_keys( $tabs );

		$this->assertSame( array( 'wpfaq_custom_tab_0', 'wpfaq_custom_tab_1' ), $keys );
		$this->assertSame( 'Shipping', $tabs['wpfaq_custom_tab_0']['title'] );
		$this->assertSame( 'Returns', $tabs['wpfaq_custom_tab_1']['title'] );
	}

	/**
	 * No custom tabs are appended when none are saved.
	 *
	 * @return void
	 */
	public function test_no_custom_tabs_appended_when_none_saved() {
		$this->assertSame( array(), $this->tabs->add_custom_tabs( array() ) );
	}

	/**
	 * An unexpected non-array $tabs argument is handled without a fatal.
	 *
	 * @return void
	 */
	public function test_add_custom_tabs_handles_non_array_tabs_argument() {
		$this->assertSame( array(), $this->tabs->add_custom_tabs( null ) );
	}
}
