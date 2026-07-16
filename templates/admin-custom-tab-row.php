<?php
/**
 * Single admin custom-tab row (title + content).
 *
 * Used both to render saved rows and, with an __INDEX__ placeholder, as the
 * hidden clone template read by assets/js/admin.js.
 *
 * Expects $args: index (int|string), title (string), content (string).
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpfaq_index    = isset( $args['index'] ) ? $args['index'] : '__INDEX__';
$wpfaq_title    = isset( $args['title'] ) ? $args['title'] : '';
$wpfaq_content  = isset( $args['content'] ) ? $args['content'] : '';
$wpfaq_field_id = 'wpfaq_custom_tabs_' . $wpfaq_index;
?>
<div class="wpfaq-row" data-wpfaq-row="custom-tab">
	<span class="wpfaq-row__handle dashicons dashicons-move" aria-hidden="true"></span>
	<div class="wpfaq-row__fields">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => $wpfaq_field_id . '_title',
				'name'              => 'wpfaq_custom_tabs[' . $wpfaq_index . '][title]',
				'label'             => __( 'Tab title', 'woo-product-faq' ),
				'value'             => $wpfaq_title,
				'wrapper_class'     => 'wpfaq-row__field',
				'custom_attributes' => array( 'data-wpfaq-field' => 'title' ),
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'id'                => $wpfaq_field_id . '_content',
				'name'              => 'wpfaq_custom_tabs[' . $wpfaq_index . '][content]',
				'label'             => __( 'Tab content', 'woo-product-faq' ),
				'value'             => $wpfaq_content,
				'wrapper_class'     => 'wpfaq-row__field',
				'custom_attributes' => array( 'data-wpfaq-field' => 'content' ),
				'rows'              => 4,
			)
		);
		?>
	</div>
	<button type="button" class="button-link wpfaq-row__remove" data-wpfaq-action="remove">
		<span class="dashicons dashicons-trash" aria-hidden="true"></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Remove this tab', 'woo-product-faq' ); ?></span>
	</button>
</div>
