<?php
/**
 * Single admin FAQ row (question + answer).
 *
 * Used both to render saved rows and, with an __INDEX__ placeholder, as the
 * hidden clone template read by assets/js/admin.js.
 *
 * Expects $args: index (int|string), question (string), answer (string).
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpfaq_index    = isset( $args['index'] ) ? $args['index'] : '__INDEX__';
$wpfaq_question = isset( $args['question'] ) ? $args['question'] : '';
$wpfaq_answer   = isset( $args['answer'] ) ? $args['answer'] : '';
$wpfaq_field_id = 'wpfaq_faqs_' . $wpfaq_index;
?>
<div class="wpfaq-row" data-wpfaq-row="faq">
	<span class="wpfaq-row__handle dashicons dashicons-move" aria-hidden="true"></span>
	<div class="wpfaq-row__fields">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => $wpfaq_field_id . '_question',
				'name'              => 'wpfaq_faqs[' . $wpfaq_index . '][question]',
				'label'             => __( 'Question', 'woo-product-faq' ),
				'value'             => $wpfaq_question,
				'wrapper_class'     => 'wpfaq-row__field',
				'custom_attributes' => array( 'data-wpfaq-field' => 'question' ),
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'id'                => $wpfaq_field_id . '_answer',
				'name'              => 'wpfaq_faqs[' . $wpfaq_index . '][answer]',
				'label'             => __( 'Answer', 'woo-product-faq' ),
				'value'             => $wpfaq_answer,
				'wrapper_class'     => 'wpfaq-row__field',
				'custom_attributes' => array( 'data-wpfaq-field' => 'answer' ),
				'rows'              => 3,
			)
		);
		?>
	</div>
	<button type="button" class="button-link wpfaq-row__remove" data-wpfaq-action="remove">
		<span class="dashicons dashicons-trash" aria-hidden="true"></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Remove this FAQ', 'woo-product-faq' ); ?></span>
	</button>
</div>
