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
		$this->hooks_registered = true;
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
			<div class="options_group wpfaq-rows-group">
				<h2><?php esc_html_e( 'FAQ', 'woo-product-faq' ); ?></h2>
				<?php $this->render_faq_rows( $post->ID ); ?>
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
}
