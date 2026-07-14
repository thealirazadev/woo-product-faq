# Design: woo-product-faq

This plugin has two UI surfaces: the frontend FAQ accordion on the single product page, and the admin FAQ/custom-tabs editor in the product data metabox. Both must inherit the active theme (frontend) or WooCommerce admin (backend) look and feel rather than impose a bespoke visual system. The plugin ships minimal CSS that styles structure and states only.

## Frontend accordion

### Structure and markup

- Root: a `<div class="wpfaq-accordion">` containing a heading (`<h2 class="wpfaq-accordion__title">FAQ</h2>`) when rendered after the summary; inside a product tab the tab already provides the heading, so the inner root omits the extra title.
- Each item is a pair:
  - Header: an `<h3 class="wpfaq-accordion__heading">` wrapping a `<button type="button" class="wpfaq-accordion__trigger" aria-expanded="false" aria-controls="wpfaq-panel-<n>" id="wpfaq-trigger-<n>">`. The button contains the question text and a chevron indicator span (`aria-hidden="true"`).
  - Panel: a `<div class="wpfaq-accordion__panel" id="wpfaq-panel-<n>" role="region" aria-labelledby="wpfaq-trigger-<n>" hidden>` containing the answer.
- One accordion item per FAQ, in stored order.

### Visual states

- Collapsed (default): panel has the `hidden` attribute; trigger shows `aria-expanded="false"`; chevron points right/down at rest.
- Expanded: `hidden` removed; `aria-expanded="true"`; chevron rotated (via CSS transform) to indicate open.
- Hover: trigger background/label shifts subtly using the theme's link/text color; cursor is `pointer`. The hover treatment must not be the only affordance (it is reinforced by the chevron and focus state).
- Focus: a clearly visible focus ring on the trigger using `outline` (never `outline: none` without a replacement). The focus indicator meets a minimum 3:1 contrast against adjacent colors and remains visible for keyboard users.
- Active/pressed: brief background darken on the trigger for tactile feedback.
- Disabled: not applicable; triggers are always operable when rendered.

### Transitions

- The chevron rotates with a short transition (about 150-200ms, `ease`).
- Panel reveal uses `hidden` toggling for correctness and screen readers; an optional height/opacity transition may wrap the toggle but must never leave a panel visually open while `hidden` is set. Respect `prefers-reduced-motion: reduce` by disabling non-essential transitions.

### Spacing and typography

- Inherit the theme's font family, base font size, line height, and color for question and answer text. Do not set a hard-coded font family or color.
- Use relative units (`em`/`rem`) for internal padding so items scale with the theme's base size. Suggested rhythm: trigger padding around `0.75em 1em`, panel padding `0 1em 1em`, subtle `1px` divider between items using `currentColor` at low opacity or the theme border color.
- Full width of the tab/summary column; no fixed pixel widths.

### Accessibility (frontend)

- Semantics: headings wrap triggers so screen-reader users can navigate FAQs by heading. Panels use `role="region"` with `aria-labelledby` referencing the trigger.
- `aria-expanded` on each trigger reflects true state and updates on every toggle.
- `aria-controls` on each trigger references its panel `id`; ids are unique per accordion instance.
- Keyboard operation: Tab moves between triggers in DOM order; Enter and Space toggle the focused trigger; focus never gets trapped. (Arrow-key navigation between triggers is a documented enhancement, not required for v1.)
- Visible focus: a persistent, high-contrast focus indicator on triggers.
- Contrast: text and focus indicator must meet WCAG 2.1 AA (4.5:1 for text, 3:1 for the focus ring and non-text indicators). Because colors are inherited, verify against the shipped default theme (Storefront/Twenty Twenty-Four) during QA.
- No-JS fallback: with JavaScript disabled, all panels render visible (no `hidden`) and `aria-expanded="true"`, so content is never lost. `frontend.js` collapses them on load and wires interaction (progressive enhancement).

## Admin metabox UI

### Structure

- A "FAQ" tab is added to the product data tabs (icon class reusing a Dashicon), opening a panel (`#wpfaq_product_data` inside `.panel.woocommerce_options_panel`).
- The panel has three regions, top to bottom:
  1. Display location: a `woocommerce_wp_select` labelled "Display location" with options "Product tab" and "After product summary".
  2. FAQ rows: a repeatable list. Each row is a card with a drag handle, a question text input, an answer textarea, and a remove button. An "Add FAQ" button sits below the list.
  3. Custom tabs: a repeatable list. Each row has a drag handle, a title text input, a content textarea, and a remove button, with an "Add custom tab" button below.

### Row interactions (add / remove / reorder)

- Add: clicking "Add FAQ" (or "Add custom tab") clones the hidden row template (`templates/admin-faq-row.php` / `admin-custom-tab-row.php`), appends it, reindexes field `name` attributes, and moves focus to the new row's first input.
- Remove: each row has a "Remove" control; clicking it removes that row from the DOM and reindexes the remaining rows. Removing the last row leaves an empty-state hint ("No FAQs yet. Add your first question.").
- Reorder: rows are reorderable via jQuery UI Sortable using an explicit drag handle (not the whole row, so text can be selected). On drop, field indices are recomputed so array order matches visual order. The drag handle shows a `move` cursor and a grip Dashicon.
- Reindexing rule: field names follow `wpfaq_faqs[<i>][question]` / `wpfaq_faqs[<i>][answer]` and `wpfaq_custom_tabs[<i>][title]` / `wpfaq_custom_tabs[<i>][content]`; `<i>` is always recomputed from DOM position before submit so gaps from removals never persist.

### Visual states (admin)

- Rows use standard WooCommerce panel styling (`form-field` rows) so the UI matches the rest of the product data metabox.
- Hover on a row highlights the drag handle; the remove button uses a subtle destructive color and confirms nothing destructive is lost (rows are only removed from the unsaved form until the product is saved).
- Focus states use the WordPress admin default focus ring; do not override it away.
- Validation feedback: empty rows are allowed in the UI but silently dropped on save; no blocking client-side validation is required.

### Accessibility (admin)

- All inputs have associated `<label>` elements (via the WooCommerce field helpers).
- Add/Remove controls are real `<button>` elements with descriptive text (or `aria-label` where only an icon is shown).
- Drag handles include `aria-hidden` icons but the reorder feature is supplementary; the saved order also reflects DOM order, and keyboard users can still add/remove rows even if drag reordering is mouse-oriented (documented limitation; a keyboard reorder control is a backlog enhancement).
- The empty-state message is announced via a `role="status"` region so screen readers hear it when the list becomes empty.
