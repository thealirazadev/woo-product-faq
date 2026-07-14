# Launch Checklist: woo-product-faq

Check every item before shipping a release.

## General

- [ ] Production environment values set (plugin header version bumped; `WPFAQ_VERSION` matches header; `readme.txt` stable tag matches).
- [ ] Debug off in production: `WP_DEBUG` and `WP_DEBUG_DISPLAY` false; no `error_log` output leaking to users.
- [ ] Error tracking connected: `wpfaq_log` verified to write only when `WP_DEBUG` is on; site-level error monitoring in place.
- [ ] Loading states everywhere: admin add/remove/reorder gives immediate feedback; frontend renders server-side with no blank flash.
- [ ] 404/500 pages exist: theme's 404 and server 500 pages render correctly with the plugin active (plugin does not fatal on missing product/meta).
- [ ] Mobile checked: accordion and product tabs usable and readable on small screens; touch targets adequate.

## Project-specific

- [ ] Accessibility verified: keyboard-only operation of the accordion, visible focus, correct `aria-expanded`/`aria-controls`, and WCAG 2.1 AA contrast on Storefront and Twenty Twenty-Four.
- [ ] No-JS fallback confirmed: with JavaScript disabled, all FAQ answers are visible and no markup is broken.
- [ ] Security pass: nonce (`wpfaq_save_faqs`) and `edit_post` capability enforced on save; all output escaped; all input sanitized; content passes through `wp_kses_post`.
- [ ] Uninstall clean: uninstalling removes `_wpfaq_faqs`, `_wpfaq_display_location`, and `_wpfaq_custom_tabs` and leaves no orphaned options.
- [ ] Compatibility: tested with the required WooCommerce (7.0+) and WordPress (6.0+) versions; `WC requires at least`, `Requires at least`, and `Requires PHP` headers accurate; PHPCS clean and PHPUnit green.
- [ ] i18n complete: `languages/woo-product-faq.pot` regenerated and all user-facing strings translatable.
