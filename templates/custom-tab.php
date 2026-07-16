<?php
/**
 * Single custom tab content wrapper.
 *
 * Expects $args: content (string, rich HTML already sanitized on save).
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpfaq_content = isset( $args['content'] ) ? $args['content'] : '';
?>
<div class="wpfaq-custom-tab">
	<?php echo wp_kses_post( wpautop( $wpfaq_content ) ); ?>
</div>
