# Design decisions

## 1. Post meta is the only storage

Each product carries three meta keys: `_wpfaq_faqs` (ordered list of question and answer pairs), `_wpfaq_display_location` (`tab` or `after_summary`) and `_wpfaq_custom_tabs` (ordered list of title and content pairs). The data is small, belongs to one product, and is only ever read in that product's context, so post meta with core's object cache is the simplest correct fit. There are no custom tables, no `$wpdb` queries and no schema migrations. Array order is the display order, so nothing else is needed to sort rows. FAQs attach to the parent product; variations do not get their own.

## 2. Display location is a validated enum with a safe default

The FAQ panel has one select with two values. Both the save handler and `wpfaq_get_display_location()` accept only the literal `after_summary` and treat anything else (missing, empty, tampered) as `tab`, so a bad value can never put the accordion in an undefined place. In `tab` mode `WPFAQ_Tabs::add_tabs()` registers a FAQ product tab; in `after_summary` mode `WPFAQ_Frontend::render_after_summary()` renders under the summary and the tab is not registered. Either way, a product with no FAQs renders nothing: no empty tab, no heading, no container.

## 3. Saves are guarded and fail silently

`WPFAQ_Admin::save()` runs on `woocommerce_process_product_meta`. It returns without touching meta when the `wpfaq_faqs_nonce` field is missing, when the nonce does not verify against `wpfaq_save_faqs`, or when the user lacks `edit_post` for that product. A rejected save leaves stored meta exactly as it was, with no partial write and no error shown, which matches how other metabox fields behave during a product save. The panel itself is only rendered for users who can edit products.

## 4. Rows are normalized on the server, never trusted from the client

Submitted rows are walked in order, sanitized (`sanitize_text_field` for questions and titles, rich text for answers and content), stripped of empty entries and reindexed with `array_values`, so gaps left by removed rows never persist. Each list is capped at `WPFAQ_Admin::MAX_ROWS` (50) to bound the size of a single meta value. The emptiness rule differs on purpose: a FAQ row is dropped only when both question and answer are blank, while a custom tab is dropped when either the title or the content is blank, because a tab without a title or without content is useless on the product page. The answer and content checks run on `wp_strip_all_tags()` output, so a value that is only markup counts as empty. A FAQ row saved with an answer but no question stays in the editor so the merchant does not lose it, but `wpfaq_get_faqs()` skips it on the product page, because the accordion would otherwise render a button with no text and no accessible name.

## 5. Rich text drops script and style contents, not just their tags

`wp_kses_post()` removes disallowed `<script>` and `<style>` tags but keeps the text between them, so a pasted script would leave its source code visible in the answer. `wpfaq_sanitize_rich_text()` removes script and style elements together with their contents first and then runs `wp_kses_post()`. Output is escaped again at render time with `wp_kses_post( wpautop() )`, and plain fields use `esc_html` and `esc_attr`.

## 6. The accordion is progressive enhancement over server-rendered markup

`templates/faq-accordion.php` prints every panel visible with `aria-expanded="true"`, so the answers are readable with JavaScript disabled. `assets/js/frontend.js` collapses each item on load and toggles `aria-expanded` and the panel's `hidden` attribute on click. The triggers are native `<button>` elements inside `<h3>` headings, which gives Enter and Space activation, correct Tab order and heading navigation without custom key handlers. Panels use `role="region"` with `aria-labelledby`, and element ids include the product id so two accordions on one page cannot collide. The CSS inherits the theme's font and colour, keeps a `:focus` outline as a fallback for browsers without `:focus-visible`, and turns off the chevron transition under `prefers-reduced-motion: reduce`. There is no framework and no build step; assets load only on single product pages.

## 7. The admin repeater reuses WordPress's own tools

Reordering uses jQuery UI Sortable, which WordPress already bundles, with an explicit drag handle so text in a row stays selectable. The "add row" template is rendered from the same PHP template as saved rows with an `__INDEX__` placeholder, so new and existing rows cannot drift apart. After every add, remove and drop, field names are rewritten from DOM position (`wpfaq_faqs[i][question]` and so on), so the submitted order always matches what the merchant sees. Because the FAQ list and the custom tab list share one panel, the empty-state hint is looked up with `closest( '.wpfaq-rows-group' )`; a page-wide lookup would have toggled the other list's hint.

## 8. The plugin refuses to run without WooCommerce

On activation, `wpfaq_activate()` checks the site and network active plugin lists for WooCommerce and, if it is missing, stops the request with `wp_die()`, so WordPress shows the reason on its activation error screen and never adds the plugin to the active list. The first version called `deactivate_plugins()` and queued an admin notice instead, but `activate_plugin()` appends the plugin to the active list after the activation hook returns, so the plugin stayed active and the notice was lost in the redirect. At runtime, `plugins_loaded` priority 1 shows the notice if WooCommerce has gone away, and the boot at priority 20 returns before any class file is loaded. This turns a missing dependency into a notice instead of a fatal error on every request.

## 9. Errors go to a debug-only log, never to shoppers

All failure branches (unexpected hook arguments, unresolved products, rejected saves, a missing template) call `wpfaq_log()`, which writes one line with a `[woo-product-faq]` prefix and a JSON context array, and only when `WP_DEBUG` is on. Shoppers and merchants never see stack traces or meta keys; on the frontend, a failure simply renders nothing.

## 10. Uninstall removes every plugin meta key

`uninstall.php` runs only when the plugin is deleted, not on deactivation, and calls `delete_post_meta_by_key()` once per key. That clears all products in three core calls without querying products or writing SQL. The plugin stores no options, so nothing else is left behind.

## 11. Integration tests run against a real WooCommerce install

The tests use the WordPress PHPUnit suite with WooCommerce loaded on `muplugins_loaded`. The WooCommerce download from WordPress.org does not ship its `WC_Unit_Test_Case` framework, so `tests/class-wc-unit-test-case-fallback.php` defines it as a thin subclass of `WP_UnitTestCase` when it is absent; these tests only need WooCommerce loaded, not its helpers. The bootstrap also runs `WC_Install::install()` and then rebuilds `wp_roles()`; without that reload, product capabilities such as `edit_product` were missing on a fresh test database and the capability checks in the save tests failed.
