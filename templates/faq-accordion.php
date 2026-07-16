<?php
/**
 * FAQ accordion markup.
 *
 * Renders with all panels visible and expanded so content is available with
 * JavaScript disabled; assets/js/frontend.js collapses items on load and
 * wires the interactive toggle (progressive enhancement).
 *
 * Expects $args: faqs (array of ['question' => string, 'answer' => string]),
 * show_title (bool, whether to print the "FAQ" heading), product_id (int,
 * used to build unique element ids).
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpfaq_faqs = isset( $args['faqs'] ) && is_array( $args['faqs'] ) ? $args['faqs'] : array();

if ( empty( $wpfaq_faqs ) ) {
	return;
}

$wpfaq_show_title = ! empty( $args['show_title'] );
$wpfaq_product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
?>
<div class="wpfaq-accordion">
	<?php if ( $wpfaq_show_title ) : ?>
		<h2 class="wpfaq-accordion__title"><?php esc_html_e( 'FAQ', 'woo-product-faq' ); ?></h2>
	<?php endif; ?>
	<?php foreach ( array_values( $wpfaq_faqs ) as $wpfaq_index => $wpfaq_faq ) : ?>
		<?php
		$wpfaq_trigger_id = 'wpfaq-trigger-' . $wpfaq_product_id . '-' . $wpfaq_index;
		$wpfaq_panel_id   = 'wpfaq-panel-' . $wpfaq_product_id . '-' . $wpfaq_index;
		$wpfaq_question   = isset( $wpfaq_faq['question'] ) ? $wpfaq_faq['question'] : '';
		$wpfaq_answer     = isset( $wpfaq_faq['answer'] ) ? $wpfaq_faq['answer'] : '';
		?>
		<div class="wpfaq-accordion__item">
			<h3 class="wpfaq-accordion__heading">
				<button
					type="button"
					class="wpfaq-accordion__trigger"
					aria-expanded="true"
					aria-controls="<?php echo esc_attr( $wpfaq_panel_id ); ?>"
					id="<?php echo esc_attr( $wpfaq_trigger_id ); ?>"
				>
					<span class="wpfaq-accordion__trigger-text"><?php echo esc_html( $wpfaq_question ); ?></span>
					<span class="wpfaq-accordion__chevron" aria-hidden="true"></span>
				</button>
			</h3>
			<div
				class="wpfaq-accordion__panel"
				id="<?php echo esc_attr( $wpfaq_panel_id ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $wpfaq_trigger_id ); ?>"
			>
				<?php echo wp_kses_post( wpautop( $wpfaq_answer ) ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
