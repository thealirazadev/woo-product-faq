# Product Requirements: woo-product-faq

## What we're building

A WooCommerce plugin that gives store owners a per-product FAQ editor in the product data metabox and renders those FAQs as an accessible accordion on the single product page, either after the product summary or inside a dedicated FAQ product tab. The same admin surface also lets owners define extra custom product tabs (title + content) that appear alongside WooCommerce's default Description and Additional information tabs. All content is stored in WordPress post meta on the product, saved with nonce and capability checks, escaped on output, and sanitized on input.

## Target user

WooCommerce store owners and shop managers who want to answer common product questions directly on the product page to reduce pre-sale support requests, without editing theme templates or writing code. Secondary beneficiary: shoppers using assistive technology or keyboard navigation, who must be able to read and operate the FAQ accordion.

## Core features (prioritized)

1. Product FAQ editor (highest priority). A "FAQ" panel in the WooCommerce product data metabox containing repeatable question/answer rows that can be added, removed, and reordered, saved to post meta.
2. Accessible frontend accordion. Render the saved FAQs as a collapsible accordion on the single product page using vanilla JS and CSS, meeting WCAG 2.1 AA keyboard and ARIA requirements.
3. Display location control. A per-product setting to show the FAQ accordion inside a dedicated FAQ product tab or after the single product summary.
4. Extra custom product tabs. Repeatable custom tabs (title + rich content) registered into WooCommerce's product tabs.
5. Internationalization and clean uninstall. Text-domain-wrapped strings, a bundled `.pot`, and removal of plugin post meta on uninstall.

## Non-goals / out of scope

- No custom database tables; storage is post meta only.
- No global/site-wide FAQ library or FAQ reuse across products (FAQs are per product).
- No FAQ schema/JSON-LD structured data output in v1 (candidate for backlog).
- No REST API, block editor (Gutenberg) blocks, or shortcode in v1.
- No frontend search, filtering, or voting on FAQs.
- No settings page beyond the per-product controls in the product metabox.
- No support for product variations having distinct FAQs (FAQs attach to the parent product).
- No AJAX-loaded FAQ content; FAQs render server-side on page load.

## Success criteria per core feature

### 1. Product FAQ editor
- A "FAQ" tab appears in the product data metabox for simple and variable products.
- An owner can add multiple rows, type a question and answer, reorder rows by dragging, remove a row, save the product, and see the exact rows and order persisted after reload.
- Saving is rejected (data unchanged) if the nonce is missing/invalid or the current user lacks `edit_post` capability for that product.
- Empty rows (blank question and blank answer) are dropped on save and do not persist.

### 2. Accessible frontend accordion
- On a product with saved FAQs, the accordion renders with each question as a button and each answer in an associated region.
- Each header button exposes `aria-expanded` reflecting its state and `aria-controls` pointing to its panel; panels are hidden when collapsed.
- The accordion is fully operable by keyboard (Enter/Space toggle, Tab order correct) with a visible focus indicator, and works with JavaScript disabled (panels default to visible/expanded, no broken UI).
- A product with no FAQs renders nothing (no empty container, heading, or tab).

### 3. Display location control
- The FAQ panel includes a "Display location" select with options "Product tab" (default) and "After product summary".
- Choosing "Product tab" makes a "FAQ" tab appear in the product tabs; choosing "After product summary" removes that tab and renders the accordion under the summary instead.
- The choice persists per product and takes effect on the frontend without editing theme files.

### 4. Extra custom product tabs
- An owner can add one or more custom tabs, each with a title and content, reorder them, and remove them.
- Saved custom tabs appear on the single product page in the product tabs area in the defined order, with titles and content escaped appropriately for display.
- A custom tab with an empty title or empty content is dropped on save.

### 5. Internationalization and clean uninstall
- All user-facing strings pass through translation functions with the `woo-product-faq` text domain and appear in `languages/woo-product-faq.pot`.
- Uninstalling the plugin removes `_wpfaq_faqs`, `_wpfaq_display_location`, and `_wpfaq_custom_tabs` meta from all products and leaves no orphaned plugin options.
