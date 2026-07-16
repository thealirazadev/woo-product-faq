<?php
/**
 * Tests for WPFAQ_Admin::save().
 *
 * @package Woo_Product_FAQ
 */

/**
 * Covers nonce/capability gating, sanitization, empty-row dropping, and
 * reindexing behavior of the FAQ save path.
 */
class WPFAQ_Save_Faqs_Test extends WC_Unit_Test_Case {

	/**
	 * Admin component under test.
	 *
	 * @var WPFAQ_Admin
	 */
	private $admin;

	/**
	 * Product post ID used across tests.
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Creates a product and an authorized admin user for each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->admin = new WPFAQ_Admin();
		$product     = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$this->product_id = $product->save();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Clears superglobals between tests.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset( $_POST['wpfaq_faqs_nonce'], $_POST['wpfaq_faqs'], $_POST['wpfaq_custom_tabs'], $_POST['wpfaq_display_location'] );
		parent::tearDown();
	}

	/**
	 * Populates $_POST with a valid nonce for the save action.
	 *
	 * @return void
	 */
	private function set_valid_nonce() {
		$_POST['wpfaq_faqs_nonce'] = wp_create_nonce( 'wpfaq_save_faqs' );
	}

	/**
	 * Valid rows persist in submitted order.
	 *
	 * @return void
	 */
	public function test_valid_rows_are_saved_in_order() {
		$this->set_valid_nonce();
		$_POST['wpfaq_faqs'] = array(
			array(
				'question' => 'First question?',
				'answer'   => 'First answer.',
			),
			array(
				'question' => 'Second question?',
				'answer'   => 'Second answer.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame(
			array(
				array(
					'question' => 'First question?',
					'answer'   => 'First answer.',
				),
				array(
					'question' => 'Second question?',
					'answer'   => 'Second answer.',
				),
			),
			$saved
		);
	}

	/**
	 * Rows with both fields blank are dropped and remaining rows reindexed.
	 *
	 * @return void
	 */
	public function test_blank_rows_are_dropped_and_remaining_rows_reindexed() {
		$this->set_valid_nonce();
		$_POST['wpfaq_faqs'] = array(
			array(
				'question' => '',
				'answer'   => '',
			),
			array(
				'question' => 'Kept question?',
				'answer'   => 'Kept answer.',
			),
			array(
				'question' => '   ',
				'answer'   => '',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame(
			array(
				array(
					'question' => 'Kept question?',
					'answer'   => 'Kept answer.',
				),
			),
			$saved
		);
	}

	/**
	 * Questions are stripped of tags; answers keep safe HTML but lose scripts.
	 *
	 * @return void
	 */
	public function test_fields_are_sanitized() {
		$this->set_valid_nonce();
		$_POST['wpfaq_faqs'] = array(
			array(
				'question' => 'Bold <strong>question</strong> <script>alert(1)</script>?',
				'answer'   => '<p>Safe</p><script>alert(1)</script>',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame( 'Bold question ?', $saved[0]['question'] );
		$this->assertSame( '<p>Safe</p>', $saved[0]['answer'] );
	}

	/**
	 * A missing nonce leaves existing meta unchanged.
	 *
	 * @return void
	 */
	public function test_missing_nonce_leaves_meta_unchanged() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_faqs',
			array(
				array(
					'question' => 'Existing?',
					'answer'   => 'Yes.',
				),
			)
		);

		$_POST['wpfaq_faqs'] = array(
			array(
				'question' => 'New question?',
				'answer'   => 'New answer.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame(
			array(
				array(
					'question' => 'Existing?',
					'answer'   => 'Yes.',
				),
			),
			$saved
		);
	}

	/**
	 * An invalid nonce leaves existing meta unchanged.
	 *
	 * @return void
	 */
	public function test_invalid_nonce_leaves_meta_unchanged() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_faqs',
			array(
				array(
					'question' => 'Existing?',
					'answer'   => 'Yes.',
				),
			)
		);

		$_POST['wpfaq_faqs_nonce'] = 'not-a-valid-nonce';
		$_POST['wpfaq_faqs']       = array(
			array(
				'question' => 'New question?',
				'answer'   => 'New answer.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame(
			array(
				array(
					'question' => 'Existing?',
					'answer'   => 'Yes.',
				),
			),
			$saved
		);
	}

	/**
	 * A user without edit_post capability cannot write meta.
	 *
	 * @return void
	 */
	public function test_user_without_capability_cannot_save() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->set_valid_nonce();
		$_POST['wpfaq_faqs'] = array(
			array(
				'question' => 'New question?',
				'answer'   => 'New answer.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertSame( '', $saved );
	}

	/**
	 * Row count is capped at WPFAQ_Admin::MAX_ROWS.
	 *
	 * @return void
	 */
	public function test_rows_are_capped_at_max() {
		$this->set_valid_nonce();
		$rows = array();

		for ( $i = 0; $i < WPFAQ_Admin::MAX_ROWS + 10; $i++ ) {
			$rows[] = array(
				'question' => 'Question ' . $i,
				'answer'   => 'Answer ' . $i,
			);
		}

		$_POST['wpfaq_faqs'] = $rows;

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_faqs', true );

		$this->assertCount( WPFAQ_Admin::MAX_ROWS, $saved );
	}

	/**
	 * Valid custom tabs persist with sanitized fields.
	 *
	 * @return void
	 */
	public function test_valid_custom_tabs_are_saved() {
		$this->set_valid_nonce();
		$_POST['wpfaq_custom_tabs'] = array(
			array(
				'title'   => 'Shipping <script>alert(1)</script>',
				'content' => '<p>Ships in 3 days.</p><script>alert(1)</script>',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_custom_tabs', true );

		$this->assertSame( 'Shipping', $saved[0]['title'] );
		$this->assertSame( '<p>Ships in 3 days.</p>', $saved[0]['content'] );
	}

	/**
	 * A custom tab with an empty title is dropped.
	 *
	 * @return void
	 */
	public function test_custom_tab_with_empty_title_is_dropped() {
		$this->set_valid_nonce();
		$_POST['wpfaq_custom_tabs'] = array(
			array(
				'title'   => '',
				'content' => 'Some content.',
			),
			array(
				'title'   => 'Kept tab',
				'content' => 'Kept content.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_custom_tabs', true );

		$this->assertCount( 1, $saved );
		$this->assertSame( 'Kept tab', $saved[0]['title'] );
	}

	/**
	 * A custom tab with empty content is dropped.
	 *
	 * @return void
	 */
	public function test_custom_tab_with_empty_content_is_dropped() {
		$this->set_valid_nonce();
		$_POST['wpfaq_custom_tabs'] = array(
			array(
				'title'   => 'Empty content tab',
				'content' => '   ',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_custom_tabs', true );

		$this->assertSame( array(), $saved );
	}

	/**
	 * A missing nonce leaves existing custom tab meta unchanged.
	 *
	 * @return void
	 */
	public function test_missing_nonce_leaves_custom_tabs_unchanged() {
		update_post_meta(
			$this->product_id,
			'_wpfaq_custom_tabs',
			array(
				array(
					'title'   => 'Existing tab',
					'content' => 'Existing content.',
				),
			)
		);

		$_POST['wpfaq_custom_tabs'] = array(
			array(
				'title'   => 'New tab',
				'content' => 'New content.',
			),
		);

		$this->admin->save( $this->product_id );

		$saved = get_post_meta( $this->product_id, '_wpfaq_custom_tabs', true );

		$this->assertSame(
			array(
				array(
					'title'   => 'Existing tab',
					'content' => 'Existing content.',
				),
			),
			$saved
		);
	}
}
