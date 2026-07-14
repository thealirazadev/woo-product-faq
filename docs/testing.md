# Testing: woo-product-faq

## Strategy

Testing has two layers: automated PHP tests for the data and integration logic, and a manual QA pass for the UI, accessibility, and browser behavior that automated tests do not cover well.

### Automated (PHPUnit + WordPress test suite)

Cover the logic that must not regress:

- Save path (`WPFAQ_Admin::save()`): valid rows persist; empty rows are dropped and arrays reindexed; invalid display-location values fall back to `tab`; a missing/invalid nonce leaves meta unchanged; a user without `edit_post` cannot write meta.
- Sanitization: questions/titles are sanitized text; answers/content pass through `wp_kses_post` (allowed HTML kept, scripts stripped).
- Data helpers (`wpfaq_get_faqs`, `wpfaq_get_display_location`, `wpfaq_get_custom_tabs`): correct shape and defaults when meta is absent or malformed.
- Tab registration (`WPFAQ_Tabs::add_tabs()`): FAQ tab present only when location is `tab` and FAQs exist; custom tabs appended in order; nothing added for a product with no data.
- Uninstall cleanup: `_wpfaq_*` meta removed.

These run against the standard WordPress PHPUnit test suite (scaffolded with WP-CLI) with WooCommerce loaded in `tests/bootstrap.php`.

### Static analysis / lint

- PHP_CodeSniffer with WordPress Coding Standards (`phpcs.xml.dist`) enforces coding standards, escaping/sanitizing, prefixing, and text-domain usage. Lint is part of the definition of done for every feature.

### Manual QA

Cover what automated tests cannot: accordion interaction, keyboard operation, visible focus, contrast against the active theme, drag-reorder in the admin, no-JS fallback, reduced-motion, and rendering inside real WooCommerce tabs. The manual checklists per phase live in `docs/phases.md`; the cross-cutting unhappy-path and empty-state checks live in that file's "Phase verification" section. Do the manual pass on at least one block theme (Twenty Twenty-Four) and Storefront.

## Exact commands

Run all from the plugin root directory.

- Install dev dependencies (first time): `composer install`
- Run the full test suite: `composer run test`
- Run a single test file: `vendor/bin/phpunit tests/test-save-faqs.php`
- Lint against WordPress Coding Standards: `composer run lint`
- Auto-fix fixable lint issues: `composer run lint:fix`
- Regenerate translation template: `wp i18n make-pot . languages/woo-product-faq.pot`
- Build the distributable zip: `composer run build`

The `composer.json` scripts map as: `test` -> `phpunit`, `lint` -> `phpcs`, `lint:fix` -> `phpcbf`, `build` -> `wp dist-archive`.

First-time test-suite setup (once per environment): `bin/install-wp-tests.sh wordpress_test root '' localhost latest` (scaffolded via `wp scaffold plugin-tests`).

## Definition of "done" gate

A feature is not done until, on the touched code:

1. `composer run lint` is clean (no PHPCS errors).
2. `composer run test` passes (all green).
3. The relevant manual checklist items in `docs/phases.md` pass.
4. The browser console and PHP debug log show no new warnings or notices on the affected screens.

Build and tests must pass before a feature is committed and before a phase is marked complete.
