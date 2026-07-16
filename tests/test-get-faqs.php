<?php
/**
 * Tests for wpfaq_get_faqs().
 *
 * @package Woo_Product_FAQ
 */

/**
 * Covers shape and default-value behavior of the FAQ data helper.
 */
class WPFAQ_Get_Faqs_Test extends WC_Unit_Test_Case {

	/**
	 * Product post ID used across tests.
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Creates a product for each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$this->product_id = $product->save();
	}

	/**
	 * A product with no saved meta returns an empty array.
	 *
	 * @return void
	 */
	public function test_returns_empty_array_when_no_meta_saved() {
		$this->assertSame( array(), wpfaq_get_faqs( $this->product_id ) );
	}

	/**
	 * Saved rows are returned in stored order with their original shape.
	 *
	 * @return void
	 */
	public function test_returns_saved_rows_in_order() {
		$rows = array(
			array(
				'question' => 'First question?',
				'answer'   => 'First answer.',
			),
			array(
				'question' => 'Second question?',
				'answer'   => 'Second answer.',
			),
		);

		update_post_meta( $this->product_id, '_wpfaq_faqs', $rows );

		$this->assertSame( $rows, wpfaq_get_faqs( $this->product_id ) );
	}

	/**
	 * A non-array meta value is treated as no FAQs.
	 *
	 * @return void
	 */
	public function test_returns_empty_array_when_meta_is_malformed() {
		update_post_meta( $this->product_id, '_wpfaq_faqs', 'not-an-array' );

		$this->assertSame( array(), wpfaq_get_faqs( $this->product_id ) );
	}

	/**
	 * Rows missing a question or answer key are dropped.
	 *
	 * @return void
	 */
	public function test_drops_rows_missing_expected_keys() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_faqs',
			array(
				array( 'question' => 'Only a question?' ),
				array(
					'question' => 'Kept question?',
					'answer'   => 'Kept answer.',
				),
				'not-an-array-row',
			)
		);

		$this->assertSame(
			array(
				array(
					'question' => 'Kept question?',
					'answer'   => 'Kept answer.',
				),
			),
			wpfaq_get_faqs( $this->product_id )
		);
	}

	/**
	 * A zero or invalid product id returns an empty array without a lookup.
	 *
	 * @return void
	 */
	public function test_returns_empty_array_for_invalid_product_id() {
		$this->assertSame( array(), wpfaq_get_faqs( 0 ) );
	}
}
