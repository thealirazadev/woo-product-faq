# woo-product-faq

A WooCommerce plugin that lets store owners attach FAQ (question/answer) pairs to individual products and renders them as an accessible, keyboard-operable accordion on the single product page. FAQs can be shown after the product summary or inside a dedicated FAQ product tab, and the plugin can also register extra custom product tabs (title + rich content) per product. All data is stored in WordPress post meta with nonce and capability checks; the frontend accordion is built with vanilla JavaScript and CSS that inherits the active theme's typography and spacing.

## Features

- Repeatable FAQ field group (question + answer) inside a dedicated "FAQ" panel in the WooCommerce product data metabox.
- Add, remove, and drag-to-reorder FAQ rows in the admin without a page reload.
- Accessible frontend accordion (aria-expanded, aria-controls, roles, full keyboard support, visible focus).
- Per-product display location: render inside a dedicated FAQ product tab or after the single product summary.
- Extra custom product tabs (title + WYSIWYG content) rendered through WooCommerce's product tabs.
- Escaped output, sanitized input, server-side validation, and nonce + capability protected saves.
- Translation-ready via a text domain and a bundled `.pot` file.
- Clean uninstall that removes plugin post meta.

## Tech stack

- PHP 7.4+ (WordPress plugin, procedural bootstrap + small OOP orchestrator classes).
- WordPress 6.0+ and WooCommerce 7.0+ (hooks: `woocommerce_product_data_tabs`, `woocommerce_product_data_panels`, `woocommerce_process_product_meta`, `woocommerce_product_tabs`, `woocommerce_after_single_product_summary`).
- Vanilla JavaScript (no framework) and plain CSS for the frontend accordion and admin repeater.
- jQuery UI Sortable (bundled with WordPress) for admin row reordering only.
- WordPress post meta for storage (no custom tables).
- Dev tooling: Composer, PHP_CodeSniffer with WordPress Coding Standards, PHPUnit with the WordPress test suite.

## Prerequisites

- A local WordPress install (6.0+) with WooCommerce (7.0+) active.
- PHP 7.4 or newer with the standard WordPress extensions.
- Composer (for dev dependencies, linting, and tests).
- WP-CLI recommended for scaffolding the test suite and generating the `.pot` file.

## Install

1. Copy or clone this repository into `wp-content/plugins/woo-product-faq` of a WordPress install.
2. From the plugin directory, install dev dependencies: `composer install`.
3. In WordPress admin, activate "WooCommerce Product FAQ" from Plugins. Activation is blocked with an admin notice if WooCommerce is not active.

## Run / build

- This is a WordPress plugin; "running" means loading it inside a WordPress + WooCommerce site. There is no separate dev server.
- Lint against WordPress Coding Standards: `composer run lint`.
- Auto-fix fixable lint issues: `composer run lint:fix`.
- Regenerate the translation template: `wp i18n make-pot . languages/woo-product-faq.pot`.
- Produce a distributable zip (excludes dev files per `.distignore`): `composer run build` (wraps `wp dist-archive`).

## Test

- Run the full PHP test suite: `composer run test` (wraps `vendor/bin/phpunit`).
- Lint must pass and tests must be green before any feature is considered done (see `docs/testing.md`).
- Manual QA checklists live in `docs/phases.md` and must be walked before shipping a phase.

## Project structure

```
woo-product-faq/
  woo-product-faq.php      Main plugin bootstrap (header, constants, includes, activation guard)
  uninstall.php           Removes plugin post meta on uninstall
  includes/               Orchestrator + admin/frontend/tabs classes + helper functions
  templates/              Frontend accordion + custom tab markup, overridable by themes
  assets/                 css/ and js/ for admin repeater and frontend accordion
  languages/              woo-product-faq.pot translation template
  tests/                  PHPUnit tests
  docs/                   Planning and handoff documentation
```

## License

License: MIT
