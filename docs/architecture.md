# Architecture: woo-product-faq

## App flow and architecture

This is a self-contained WordPress plugin. There is no separate backend service or SPA. Behavior is split across three lifecycle contexts, all bootstrapped from one main plugin file.

1. Bootstrap (`woo-product-faq.php`): defines constants, checks that WooCommerce is active, loads the text domain, requires the `includes/` files, and instantiates the orchestrator `WPFAQ_Plugin` on `plugins_loaded`.
2. Admin (product edit screen): `WPFAQ_Admin` adds a "FAQ" tab and panel to the WooCommerce product data metabox, renders the repeatable FAQ rows, the display-location select, and the repeatable custom-tabs rows, enqueues `assets/js/admin.js` + `assets/css/admin.css`, and handles saving on `woocommerce_process_product_meta` with a nonce check, capability check, and sanitization.
3. Frontend (single product page): `WPFAQ_Frontend` enqueues `assets/js/frontend.js` + `assets/css/frontend.css` only on product pages, and renders the accordion. `WPFAQ_Tabs` filters `woocommerce_product_tabs` to add the FAQ tab (when display location is "Product tab") and any custom tabs. When display location is "After product summary", `WPFAQ_Frontend` renders the accordion on `woocommerce_after_single_product_summary`.

Admin write flow:

```
Product edit screen
  -> WPFAQ_Admin::render_faq_panel()  (repeater + select + custom tabs UI)
  -> user edits rows (admin.js: add / remove / jQuery UI Sortable reorder)
  -> Update/Publish
  -> woocommerce_process_product_meta
  -> WPFAQ_Admin::save()
       verify nonce (wpfaq_faqs_nonce / action wpfaq_save_faqs)
       current_user_can('edit_post', $post_id)
       sanitize each field, drop empty rows, reindex
       update_post_meta(_wpfaq_faqs, _wpfaq_display_location, _wpfaq_custom_tabs)
```

Frontend read flow:

```
Single product page
  -> wpfaq_get_faqs($product_id) / wpfaq_get_display_location() / wpfaq_get_custom_tabs()
  -> if location == 'tab': WPFAQ_Tabs::add_tabs() adds FAQ tab callback -> templates/faq-accordion.php
     if location == 'after_summary': WPFAQ_Frontend::render_after_summary() -> templates/faq-accordion.php
  -> WPFAQ_Tabs::add_tabs() also appends custom tabs -> templates/custom-tab.php
  -> frontend.js wires accordion buttons (aria-expanded, keyboard, panel show/hide)
```

## Proposed folder / file tree

```
woo-product-faq/
  woo-product-faq.php              Plugin header, constants (WPFAQ_VERSION, WPFAQ_PATH, WPFAQ_URL),
                                   WooCommerce-active guard, text domain, requires, boot WPFAQ_Plugin
  uninstall.php                    Deletes _wpfaq_* post meta for all products on uninstall
  readme.txt                       WordPress.org-style plugin readme (distribution metadata)
  composer.json                    Dev deps + scripts (lint, lint:fix, test, build)
  phpcs.xml.dist                   PHPCS ruleset extending WordPress-Extra + WordPress-Docs
  phpunit.xml.dist                 PHPUnit config
  .distignore                      Files excluded from the built zip

  includes/
    class-wpfaq-plugin.php         Singleton orchestrator; instantiates Admin, Frontend, Tabs; hooks init
    class-wpfaq-admin.php          Product data tab/panel registration, render, sanitize + save
    class-wpfaq-frontend.php       Asset enqueue on product pages; after-summary render
    class-wpfaq-tabs.php           woocommerce_product_tabs filter (FAQ tab + custom tabs)
    wpfaq-functions.php            Data helpers (wpfaq_get_faqs, wpfaq_get_display_location,
                                   wpfaq_get_custom_tabs) + wpfaq_get_template() loader

  templates/
    faq-accordion.php              Accordion markup (headings, buttons, panels, ARIA)
    custom-tab.php                 Single custom tab content wrapper
    admin-faq-row.php              One admin FAQ row (also used as JS clone template)
    admin-custom-tab-row.php       One admin custom-tab row (also used as JS clone template)

  assets/
    css/
      admin.css                    Repeater layout, drag handle, remove button
      frontend.css                 Accordion collapsed/expanded/hover/focus states, transitions
    js/
      admin.js                     Add/remove rows, jQuery UI Sortable reorder, reindex field names
      frontend.js                  Accordion toggle, ARIA sync, keyboard handling, no-JS fallback

  languages/
    woo-product-faq.pot            Translation template

  tests/
    bootstrap.php                  Loads WP test suite + plugin
    test-save-faqs.php             Sanitize/save + empty-row drop + capability tests
    test-get-faqs.php              Data helper tests
    test-tabs.php                  Tab registration tests

  docs/                            Planning + handoff documentation (this folder)
  README.md                        Root project doc
```

## Tech stack with rationale

- WordPress plugin in PHP: the requirement is a WooCommerce extension; a plugin is the only correct delivery form and integrates through documented action/filter hooks without theme edits.
- Small OOP orchestrator + procedural helpers: one class per responsibility (Admin, Frontend, Tabs) keeps concerns separated and testable, while data access stays in simple prefixed functions to avoid over-engineering. No abstract base classes or managers.
- Post meta storage (no custom tables): FAQ and tab data is small, per-product, and always read in the product context, so post meta is the simplest correct fit. It ships with core caching (`get_post_meta`) and needs no migration code. If a future feature ever required cross-product querying at scale, a custom table would be introduced via `dbDelta` with a versioned schema stored in an option (`wpfaq_db_version`) and an upgrade routine; that is explicitly out of scope for v1.
- Vanilla JS + CSS for the frontend: the accordion is small and must not depend on the theme's framework or ship a bundler. Plain JS keeps the footprint minimal and avoids compatibility risk. jQuery UI Sortable (already bundled with WordPress) is reused only for admin reordering, so no new dependency is added.
- WooCommerce hooks (`woocommerce_product_data_tabs`, `woocommerce_product_data_panels`, `woocommerce_process_product_meta`, `woocommerce_product_tabs`, `woocommerce_after_single_product_summary`): these are the stable, documented integration points for product admin fields and product page output.
- Composer + PHPCS/WPCS + PHPUnit for tooling: WordPress Coding Standards enforcement and the standard WP test suite are the conventional, expected quality gates for a distributable plugin.

## Data model (entities / relationships)

All entities are stored as post meta on a WooCommerce `product` post. No custom tables.

- FAQ item: `{ question: string, answer: string }`. Stored as an ordered array under meta key `_wpfaq_faqs`. Array order is the display order.
- Display location: a single string enum, meta key `_wpfaq_display_location`, one of `tab` (default) or `after_summary`.
- Custom tab: `{ title: string, content: string }`. Stored as an ordered array under meta key `_wpfaq_custom_tabs`. Array order is the tab order.

Relationships: one product has zero-or-more FAQ items, exactly one display-location value, and zero-or-more custom tabs. Deleting the product deletes its post meta via WordPress core. Variations do not have their own FAQs; only the parent product does.

Meta key reference (all underscore-prefixed so they stay out of the default custom fields UI):

| Meta key | Type | Values |
| --- | --- | --- |
| `_wpfaq_faqs` | array | list of `['question' => string, 'answer' => string]` |
| `_wpfaq_display_location` | string | `tab` (default) or `after_summary` |
| `_wpfaq_custom_tabs` | array | list of `['title' => string, 'content' => string]` |

## Where state lives

- Persistent state: WordPress post meta on the product (the three keys above). This is the single source of truth.
- Request-scoped admin state: form field values during a product save, validated and sanitized in `WPFAQ_Admin::save()` before persisting.
- Client-side UI state (admin): the current set and order of repeater rows in the DOM, managed by `admin.js`; serialized into named form fields (`wpfaq_faqs[<index>][question]`, etc.) on submit.
- Client-side UI state (frontend): each accordion item's expanded/collapsed state, held only in the DOM (button `aria-expanded` + panel `hidden`), managed by `frontend.js`. Nothing is persisted client-side; no cookies or local storage.

## External dependencies and required env vars

- Runtime dependency: WooCommerce must be active (checked at bootstrap; activation is blocked with an admin notice otherwise).
- Bundled-with-WordPress dependency: jQuery and jQuery UI Sortable (admin only).
- Dev dependencies (Composer): `squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`, `phpcompatibility/phpcompatibility-wp`, `phpunit/phpunit`, and the WordPress test-suite scaffolding.
- Environment variables: none. This plugin has no `.env` and reads no secrets. All configuration is per-product post meta set through the WooCommerce product editor.
