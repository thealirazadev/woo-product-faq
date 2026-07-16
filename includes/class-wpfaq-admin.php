<?php
/**
 * Product editor integration.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the product FAQ editor surface.
 */
final class WPFAQ_Admin {

	/**
	 * Maximum number of FAQ rows persisted per product.
	 *
	 * @var int
	 */
	const MAX_ROWS = 50;

	/**
	 * Whether hooks have been registered.
	 *
	 * @var bool
	 */
	private $hooks_registered = false;

	/**
	 * Registers WooCommerce admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( $this->hooks_registered ) {
			return;
		}

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_product_data_panel' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
		$this->hooks_registered = true;
	}

	/**
	 * Enqueues the repeater JS on the product edit screen only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post;

		if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script(
			'wpfaq-admin',
			WPFAQ_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			WPFAQ_VERSION,
			true
		);
	}

	/**
	 * Adds the FAQ tab to the product data metabox.
	 *
	 * @param array $tabs Product data tabs.
	 * @return array
	 */
	public function add_product_data_tab( $tabs ) {
		if ( ! is_array( $tabs ) ) {
			wpfaq_log( 'Product data tabs had an unexpected shape.' );
			return array();
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			return $tabs;
		}

		if ( isset( $tabs['wpfaq'] ) ) {
			wpfaq_log( 'The FAQ product data tab key was already registered.' );
			return $tabs;
		}

		$tabs['wpfaq'] = array(
			'label'    => __( 'FAQ', 'woo-product-faq' ),
			'target'   => 'wpfaq_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 80,
		);

		return $tabs;
	}

	/**
	 * Renders the FAQ product data panel shell.
	 *
	 * @return void
	 */
	public function render_product_data_panel() {
		global $post;

		if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
			wpfaq_log( 'The FAQ panel could not resolve a product post.' );
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_product' ) ) {
			wpfaq_log( 'WooCommerce product lookup was unavailable in the FAQ panel.' );
			return;
		}

		$wpfaq_product = wc_get_product( $post->ID );

		if ( ! $wpfaq_product instanceof WC_Product ) {
			wpfaq_log( 'The FAQ panel could not resolve a WooCommerce product.', array( 'post_id' => $post->ID ) );
			return;
		}
		?>
		<div id="wpfaq_product_data" class="panel woocommerce_options_panel hidden">
			<?php wp_nonce_field( 'wpfaq_save_faqs', 'wpfaq_faqs_nonce' ); ?>
			<div class="options_group">
				<?php
				woocommerce_wp_select(
					array(
						'id'      => 'wpfaq_display_location',
						'name'    => 'wpfaq_display_location',
						'label'   => __( 'Display location', 'woo-product-faq' ),
						'value'   => wpfaq_get_display_location( $post->ID ),
						'options' => array(
							'tab'           => __( 'Product tab', 'woo-product-faq' ),
							'after_summary' => __( 'After product summary', 'woo-product-faq' ),
						),
					)
				);
				?>
			</div>
			<div class="options_group wpfaq-rows-group">
				<h2><?php esc_html_e( 'FAQ', 'woo-product-faq' ); ?></h2>
				<?php $this->render_faq_rows( $post->ID ); ?>
			</div>
			<div class="options_group wpfaq-rows-group">
				<h2><?php esc_html_e( 'Custom tabs', 'woo-product-faq' ); ?></h2>
				<?php $this->render_custom_tab_rows( $post->ID ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the repeatable FAQ question/answer rows.
	 *
	 * @param int $post_id Product post ID.
	 * @return void
	 */
	private function render_faq_rows( $post_id ) {
		$wpfaq_faqs = get_post_meta( $post_id, '_wpfaq_faqs', true );

		if ( ! is_array( $wpfaq_faqs ) ) {
			$wpfaq_faqs = array();
		}
		?>
		<p class="wpfaq-empty-state" role="status"<?php echo empty( $wpfaq_faqs ) ? '' : ' hidden'; ?>>
			<?php esc_html_e( 'No FAQs yet. Add your first question.', 'woo-product-faq' ); ?>
		</p>
		<div class="wpfaq-rows" data-wpfaq-rows="faq">
			<?php
			foreach ( array_values( $wpfaq_faqs ) as $wpfaq_index => $wpfaq_faq ) {
				if ( ! is_array( $wpfaq_faq ) ) {
					continue;
				}

				wpfaq_get_template(
					'admin-faq-row.php',
					array(
						'index'    => $wpfaq_index,
						'question' => isset( $wpfaq_faq['question'] ) ? $wpfaq_faq['question'] : '',
						'answer'   => isset( $wpfaq_faq['answer'] ) ? $wpfaq_faq['answer'] : '',
					)
				);
			}
			?>
		</div>
		<p class="wpfaq-row-actions">
			<button type="button" class="button wpfaq-add-row" data-wpfaq-action="add-faq">
				<?php esc_html_e( 'Add FAQ', 'woo-product-faq' ); ?>
			</button>
		</p>
		<script type="text/html" id="wpfaq-faq-row-template">
			<?php wpfaq_get_template( 'admin-faq-row.php', array( 'index' => '__INDEX__' ) ); ?>
		</script>
		<?php
	}

	/**
	 * Renders the repeatable custom-tab title/content rows.
	 *
	 * @param int $post_id Product post ID.
	 * @return void
	 */
	private function render_custom_tab_rows( $post_id ) {
		$wpfaq_custom_tabs = get_post_meta( $post_id, '_wpfaq_custom_tabs', true );

		if ( ! is_array( $wpfaq_custom_tabs ) ) {
			$wpfaq_custom_tabs = array();
		}
		?>
		<p class="wpfaq-empty-state" role="status"<?php echo empty( $wpfaq_custom_tabs ) ? '' : ' hidden'; ?>>
			<?php esc_html_e( 'No custom tabs yet. Add your first tab.', 'woo-product-faq' ); ?>
		</p>
		<div class="wpfaq-rows" data-wpfaq-rows="custom-tab">
			<?php
			foreach ( array_values( $wpfaq_custom_tabs ) as $wpfaq_index => $wpfaq_tab ) {
				if ( ! is_array( $wpfaq_tab ) ) {
					continue;
				}

				wpfaq_get_template(
					'admin-custom-tab-row.php',
					array(
						'index'   => $wpfaq_index,
						'title'   => isset( $wpfaq_tab['title'] ) ? $wpfaq_tab['title'] : '',
						'content' => isset( $wpfaq_tab['content'] ) ? $wpfaq_tab['content'] : '',
					)
				);
			}
			?>
		</div>
		<p class="wpfaq-row-actions">
			<button type="button" class="button wpfaq-add-row" data-wpfaq-action="add-custom-tab">
				<?php esc_html_e( 'Add custom tab', 'woo-product-faq' ); ?>
			</button>
		</p>
		<script type="text/html" id="wpfaq-custom-tab-row-template">
			<?php wpfaq_get_template( 'admin-custom-tab-row.php', array( 'index' => '__INDEX__' ) ); ?>
		</script>
		<?php
	}

	/**
	 * Saves FAQ rows to post meta on product save.
	 *
	 * Verifies the nonce and edit capability before touching meta; on
	 * failure it silently leaves stored meta unchanged (no fatal, no
	 * partial write), matching how the rest of the product save behaves
	 * for other plugins' metabox fields.
	 *
	 * @param int $post_id Product post ID being saved.
	 * @return void
	 */
	public function save( $post_id ) {
		$post_id = absint( $post_id );

		if ( ! $post_id ) {
			return;
		}

		if ( ! isset( $_POST['wpfaq_faqs_nonce'] ) ) {
			return;
		}

		$wpfaq_nonce = sanitize_text_field( wp_unslash( $_POST['wpfaq_faqs_nonce'] ) );

		if ( ! wp_verify_nonce( $wpfaq_nonce, 'wpfaq_save_faqs' ) ) {
			wpfaq_log( 'FAQ save was rejected because the nonce was invalid.', array( 'post_id' => $post_id ) );
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wpfaq_log( 'FAQ save was rejected because the user lacked edit_post capability.', array( 'post_id' => $post_id ) );
			return;
		}

		$wpfaq_raw_rows = array();

		if ( isset( $_POST['wpfaq_faqs'] ) && is_array( $_POST['wpfaq_faqs'] ) ) {
			$wpfaq_raw_rows = wp_unslash( $_POST['wpfaq_faqs'] );
		}

		$wpfaq_sanitized_rows = $this->sanitize_faq_rows( $wpfaq_raw_rows );
		$wpfaq_updated        = update_post_meta( $post_id, '_wpfaq_faqs', $wpfaq_sanitized_rows );

		if ( false === $wpfaq_updated && get_post_meta( $post_id, '_wpfaq_faqs', true ) !== $wpfaq_sanitized_rows ) {
			wpfaq_log( 'FAQ rows failed to save.', array( 'post_id' => $post_id ) );
		}

		$wpfaq_location_raw = isset( $_POST['wpfaq_display_location'] )
			? sanitize_text_field( wp_unslash( $_POST['wpfaq_display_location'] ) )
			: 'tab';
		$wpfaq_location     = 'after_summary' === $wpfaq_location_raw ? 'after_summary' : 'tab';

		update_post_meta( $post_id, '_wpfaq_display_location', $wpfaq_location );
	}

	/**
	 * Sanitizes raw FAQ rows: drops blank rows, sanitizes fields, reindexes,
	 * and caps the row count to prevent abuse.
	 *
	 * @param array $wpfaq_raw_rows Raw, unslashed row data keyed by submitted index.
	 * @return array List of ['question' => string, 'answer' => string].
	 */
	private function sanitize_faq_rows( $wpfaq_raw_rows ) {
		$wpfaq_sanitized = array();

		foreach ( $wpfaq_raw_rows as $wpfaq_raw_row ) {
			if ( count( $wpfaq_sanitized ) >= self::MAX_ROWS ) {
				break;
			}

			if ( ! is_array( $wpfaq_raw_row ) ) {
				continue;
			}

			$wpfaq_question = isset( $wpfaq_raw_row['question'] ) ? sanitize_text_field( $wpfaq_raw_row['question'] ) : '';
			$wpfaq_answer   = isset( $wpfaq_raw_row['answer'] ) ? wp_kses_post( $wpfaq_raw_row['answer'] ) : '';

			if ( '' === trim( $wpfaq_question ) && '' === trim( wp_strip_all_tags( $wpfaq_answer ) ) ) {
				continue;
			}

			$wpfaq_sanitized[] = array(
				'question' => $wpfaq_question,
				'answer'   => $wpfaq_answer,
			);
		}

		return array_values( $wpfaq_sanitized );
	}
}
