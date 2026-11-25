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

These run against the standard WordPress PHPUnit test suite with WooCommerce loaded in `tests/bootstrap.php`. WooCommerce's own test framework is not part of its plugin download, so `tests/class-wc-unit-test-case-fallback.php` provides `WC_Unit_Test_Case` as a subclass of `WP_UnitTestCase` when it is absent.

### Static analysis / lint

- PHP_CodeSniffer with WordPress Coding Standards (`phpcs.xml.dist`) enforces coding standards, escaping/sanitizing, prefixing, and text-domain usage. Lint is part of the definition of done for every feature.

### Manual QA

Cover what automated tests cannot: accordion interaction, keyboard operation, visible focus, contrast against the active theme, drag-reorder in the admin, no-JS fallback, reduced-motion, and rendering inside real WooCommerce tabs. Do the manual pass on at least one block theme (Twenty Twenty-Four) and Storefront.

Checklist:

- Activate with WooCommerce inactive: a notice is shown and nothing fatals.
- Add three FAQ rows, reorder by drag, save, reload: order and content are preserved. A blank row is gone after save; removing a middle row keeps the rest in order.
- Default display location: the FAQ appears in a product tab. Switch to "After product summary" and save: the tab is gone and the accordion renders under the summary. A product with no FAQs shows no FAQ tab in either mode.
- Add two custom tabs, reorder, save: both appear in that order. A tab with an empty title or empty content is dropped; allowed HTML renders and script tags are stripped.
- Keyboard only: Tab reaches each trigger, Enter and Space toggle it, focus stays visible, and `aria-expanded` flips.
- JavaScript disabled: all answers are visible and the markup is intact. Reduced motion on: no chevron animation.
- Uninstall: the three `_wpfaq_*` meta keys are removed from products.

Unhappy paths and edge cases:

- Wrong input: a non-enum display location and HTML or script in question and answer fields.
- Empty forms: save a product with no FAQ rows and with all-blank rows.
- No network: the product page renders server-side without any external request.
- Duplicate submit: double-click Update; no duplicated rows persist.
- Refresh mid-action: reload the editor with unsaved added rows; no partial or corrupt meta.
- Long inputs: a very long question, answer, or custom tab title wraps and does not break the layout or the tabs bar.

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

First-time test-suite setup (once per environment): `bin/install-wp-tests.sh wordpress_test root '' localhost latest`. The script downloads WordPress core, the WordPress PHPUnit library, and the latest stable WooCommerce into `/tmp/wordpress` and `/tmp/wordpress-tests-lib` (override with `WP_CORE_DIR`, `WP_TESTS_DIR`, and `WC_VERSION`).

## Continuous integration

`.github/workflows/ci.yml` runs on every push and pull request to `main`, on Ubuntu with PHP 8.2 and a MySQL 8.0 service:

1. `composer install`
2. `composer lint` (PHPCS against `phpcs.xml.dist`)
3. `bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest true` (installs the WordPress test library and WooCommerce)
4. `composer test` (PHPUnit)

## Definition of "done" gate

A change is not done until, on the touched code:

1. `composer run lint` is clean (no PHPCS errors).
2. `composer run test` passes (all green).
3. The relevant manual checklist items above pass.
4. The browser console and PHP debug log show no new warnings or notices on the affected screens.
