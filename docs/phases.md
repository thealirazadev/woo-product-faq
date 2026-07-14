# Phases: woo-product-faq

Phases are ordered so each one ships something useful and testable on its own. Complete a phase (definition of done + manual checklist + verification) before starting the next. One commit per feature/task, in the listed order.

---

## Phase 1: Plugin foundation and FAQ editor

Bootstrap the plugin and let a store owner add, edit, reorder, and save FAQ rows to a product. No frontend rendering yet.

### Definition of done
- Plugin activates only when WooCommerce is active; otherwise a clear admin notice appears and the plugin does not fatally error.
- A "FAQ" tab and panel appear in the product data metabox for products.
- The panel shows a repeatable list of question/answer rows with Add, Remove, and drag-to-reorder.
- Saving the product persists rows to `_wpfaq_faqs` (nonce + `edit_post` capability + sanitize), drops empty rows, and reindexes order.
- Reloading the product shows the same rows in the same order.
- `wpfaq_log` helper exists and is used at failure branches.

### Manual test checklist
- Activate with WooCommerce off: notice shown, no fatal.
- Activate with WooCommerce on: FAQ tab visible on a product.
- Add three rows, fill them, reorder by drag, save, reload: order and content preserved.
- Add a row, leave it blank, save: blank row is gone after reload.
- Remove a middle row, save, reload: remaining rows intact and correctly ordered.

### Commits
- `chore: scaffold plugin bootstrap, header, and constants`
- `feat: block activation when WooCommerce is inactive with admin notice`
- `chore: load text domain and add structured wpfaq_log helper`
- `feat: register FAQ product data tab and panel`
- `feat: render repeatable FAQ question and answer rows`
- `feat: add admin JS for add, remove, and sortable reorder of FAQ rows`
- `feat: save FAQ rows to post meta with nonce, capability, and sanitization`
- `test: cover FAQ save sanitization and empty-row dropping`

---

## Phase 2: Frontend accordion

Render saved FAQs as an accessible accordion under the product summary.

### Definition of done
- On product pages with FAQs, the accordion renders via `woocommerce_after_single_product_summary` using `templates/faq-accordion.php`.
- Frontend assets enqueue only on single product pages.
- Each item has a trigger button with `aria-expanded` and `aria-controls`, and a panel region; toggling works by mouse and keyboard (Enter/Space).
- With JS disabled, all panels are visible; with JS on, they collapse and become interactive.
- A product with no FAQs renders nothing.

### Manual test checklist
- Product with FAQs: accordion appears; clicking a question expands/collapses it; `aria-expanded` flips.
- Keyboard: Tab to a trigger, press Enter and Space, panel toggles; focus ring visible.
- Disable JavaScript: all answers visible, no broken markup.
- Product with no FAQs: nothing renders.
- Long question and long answer text wrap and do not overflow the column.

### Commits
- `feat: add data helpers to read FAQ meta`
- `feat: enqueue frontend accordion assets on product pages`
- `feat: render FAQ accordion after single product summary`
- `feat: add accessible accordion behavior in frontend JS`
- `feat: style accordion collapsed, expanded, hover, and focus states`
- `test: cover FAQ data helper output`

---

## Phase 3: Display location control (FAQ product tab)

Let owners choose whether the accordion shows after the summary or inside a dedicated FAQ product tab.

### Definition of done
- The FAQ panel has a "Display location" select (`tab` default, `after_summary`) saved to `_wpfaq_display_location`.
- When `tab`, a "FAQ" tab appears via `woocommerce_product_tabs` and renders the accordion; the after-summary output is suppressed.
- When `after_summary`, the accordion renders under the summary and no FAQ tab appears.
- Invalid/missing location values fall back to `tab`.

### Manual test checklist
- Default product: FAQ shows in a product tab.
- Switch to "After product summary", save: FAQ tab gone, accordion under summary.
- Switch back to "Product tab", save: FAQ tab returns.
- Product with no FAQs: no FAQ tab in either mode.

### Commits
- `feat: add display location select to FAQ panel and save it`
- `feat: register FAQ product tab when display location is tab`
- `feat: suppress after-summary render when FAQ tab is used`
- `test: cover display location default and tab registration`

---

## Phase 4: Extra custom product tabs

Let owners define additional product tabs (title + content).

### Definition of done
- The FAQ panel includes a repeatable custom-tabs list (title + content) with Add, Remove, reorder.
- Saved to `_wpfaq_custom_tabs` with sanitize (title `sanitize_text_field`, content `wp_kses_post`), empty rows dropped, order preserved.
- Custom tabs render via `woocommerce_product_tabs` in the defined order with escaped output.

### Manual test checklist
- Add two custom tabs, reorder, save, reload: order and content preserved.
- Frontend: both custom tabs appear with correct titles and content.
- Add a tab with empty title, save: dropped after reload.
- Content with allowed HTML renders; script tags are stripped.

### Commits
- `feat: render repeatable custom tab rows in FAQ panel`
- `feat: save custom tabs to post meta with sanitization`
- `feat: register custom product tabs on the frontend`
- `test: cover custom tab save and rendering`

---

## Phase 5: Accessibility, i18n, and hardening

Polish accessibility, complete translation readiness, and clean up on uninstall.

### Definition of done
- Full keyboard operation and visible focus verified against the default theme; contrast meets WCAG 2.1 AA.
- `prefers-reduced-motion` honored.
- All strings wrapped in translation functions; `languages/woo-product-faq.pot` generated.
- `uninstall.php` removes `_wpfaq_faqs`, `_wpfaq_display_location`, `_wpfaq_custom_tabs` for all products.
- PHPCS clean against `phpcs.xml.dist`; PHPUnit green.

### Manual test checklist
- Navigate the whole accordion with keyboard only; focus always visible.
- Reduced-motion setting on: no non-essential animation.
- Switch site language with a test translation: strings translate.
- Uninstall the plugin: plugin meta removed from products.
- Run PHPCS and PHPUnit: both pass.

### Commits
- `fix: ensure visible focus and reduced-motion handling in accordion`
- `refactor: wrap all user-facing strings in text domain`
- `chore: generate translation template pot file`
- `feat: remove plugin post meta on uninstall`
- `test: cover uninstall meta cleanup`

---

## Phase verification (run after every phase)

- Run the app: activate on a WooCommerce site and load an affected admin screen and a single product page.
- Run tests: `composer run test` passes.
- Run lint: `composer run lint` is clean.
- Check the browser console and PHP debug log for warnings/notices on the touched screens.
- Unhappy paths:
  - Wrong input: non-enum display location, HTML/script in question and answer fields.
  - Empty forms: save a product with no FAQ rows and with all-blank rows.
  - No network: confirm the product page renders server-side without any external request.
  - Duplicate submit: double-click Update; no duplicated rows persisted.
  - Refresh mid-action: reload the editor with unsaved added rows; confirm no partial/corrupt meta.
- Empty states: product with no FAQs and no custom tabs renders nothing on the frontend and shows the admin empty-state hint.
- Long inputs: very long question, very long answer, and a custom tab with a long title wrap correctly and do not break layout or the tabs bar.

## Backlog

(Empty. Add out-of-scope or deferred items here as they arise.)
