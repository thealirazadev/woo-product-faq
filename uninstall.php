<?php
/**
 * Uninstall routine.
 *
 * Removes all plugin post meta from every product. Runs only when the
 * plugin is deleted via the WordPress admin, never on deactivation.
 *
 * @package Woo_Product_FAQ
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( '_wpfaq_faqs' );
delete_post_meta_by_key( '_wpfaq_display_location' );
delete_post_meta_by_key( '_wpfaq_custom_tabs' );
