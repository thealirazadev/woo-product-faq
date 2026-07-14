# Engineering Rules: woo-product-faq

These rules are binding for every change to this plugin. When something here conflicts with a quick shortcut, follow these rules.

## Conventions

- Coding standard: WordPress Coding Standards (WPCS). Run PHPCS before every commit; code must be clean against `phpcs.xml.dist`.
- Preferred libraries/patterns:
  - Use WordPress and WooCommerce core APIs: `get_post_meta` / `update_post_meta`, `wp_enqueue_script` / `wp_enqueue_style`, `wp_nonce_field` / `check_admin_referer`, `current_user_can`, the `sanitize_*` / `esc_*` families, `wp_kses_post`, and `__()` / `esc_html__()`.
  - Reuse jQuery UI Sortable (bundled with WordPress) for admin row reordering. Do not add a new drag-and-drop library.
  - Register product admin fields via `woocommerce_product_data_tabs` and `woocommerce_product_data_panels`; save via `woocommerce_process_product_meta`; render product page output via `woocommerce_product_tabs` and `woocommerce_after_single_product_summary`.
- What to avoid:
  - No JS framework or build step for shipped code (no React, Vue, webpack). Plain JS and CSS only.
  - No direct `$_POST` access without sanitizing; no raw SQL; no `$wpdb` queries in v1 (post meta only).
  - No output of unescaped user data; no `echo` of meta without an `esc_*` / `wp_kses_post` wrapper.
  - No global functions or hooks without the `wpfaq_` prefix.
- Naming:
  - Text domain: `woo-product-faq` (matches the plugin slug and folder).
  - Function/hook prefix: `wpfaq_` (e.g. `wpfaq_get_faqs()`, filter `wpfaq_accordion_html`).
  - Constant prefix: `WPFAQ_` (e.g. `WPFAQ_VERSION`, `WPFAQ_PATH`, `WPFAQ_URL`).
  - Class prefix: `WPFAQ_` in PascalCase (e.g. `WPFAQ_Admin`), file named `class-wpfaq-admin.php`.
  - Files: lowercase with hyphens; class files prefixed `class-`; templates end `.php` in `templates/`.
  - Variables/functions: `snake_case` per WPCS. Meta keys underscore-prefixed and namespaced: `_wpfaq_faqs`, `_wpfaq_display_location`, `_wpfaq_custom_tabs`.
  - Nonce: field name `wpfaq_faqs_nonce`, action `wpfaq_save_faqs`.
- Commits: Conventional Commits (`feat`, `fix`, `chore`, `docs`, `refactor`, `test`, `perf`) with a short imperative subject (for example `feat: add repeatable FAQ rows to product data panel`).
- ONE COMMIT PER FEATURE / TASK. Never batch multiple features into one commit. Each commit in `docs/phases.md` maps to exactly one commit.
- Pin exact dependency versions in `composer.json` (no `^`/`~` ranges for dev tooling), commit `composer.lock`, and declare `Requires at least`, `Requires PHP`, and `WC requires at least` in the plugin header and `readme.txt`.
- Migration rule: v1 uses post meta and needs no migrations. If a custom table is ever introduced, it must be created with `dbDelta`, guarded by a versioned schema option (`wpfaq_db_version`), and upgraded through an idempotent routine run on `plugins_loaded`. Do not add a table without a written decision in `docs/memory.md`.

## Error handling & logging

- Every external/boundary call handles failure: check the return of `update_post_meta` where correctness depends on it, verify `$product`/`$post` is not null before use, and confirm WooCommerce functions exist before calling.
- Friendly user errors vs detailed logs: never surface stack traces, meta keys, or PHP notices to shop owners or shoppers. Admin-facing problems use `WP_Admin_Notices` / settings errors with plain language; the frontend simply renders nothing rather than a broken widget.
- No stack traces to users: `WP_DEBUG_DISPLAY` must be off in production; the plugin must not `echo` errors.
- One consistent error format: internal failures are logged via a single helper `wpfaq_log( $message, $context = array() )` that wraps `error_log` (only when `WP_DEBUG` is true) with a `[woo-product-faq]` prefix and a JSON-encoded context array. All logging goes through this helper.
- Structured logging from day one: `wpfaq_log` is added in Phase 1 and used at every failure branch (missing WooCommerce, failed save, unexpected meta shape).

## Security

- No hardcoded secrets. This plugin stores no credentials and has no `.env`.
- Nonce checks: every save verifies `wpfaq_faqs_nonce` against action `wpfaq_save_faqs` with `check_admin_referer` / `wp_verify_nonce` before touching meta.
- Capability checks: saving requires `current_user_can( 'edit_post', $post_id )`. The FAQ panel is only rendered for users who can edit products.
- Escape on output: all displayed values use the correct escaper. Plain text uses `esc_html`; attributes use `esc_attr`; the FAQ answer and custom tab content (rich text) use `wp_kses_post`; URLs use `esc_url`.
- Sanitize on input: questions and tab titles use `sanitize_text_field`; answers and tab content use `wp_kses_post`; the display location is validated against the allowed enum (`tab`, `after_summary`) and defaults to `tab` on any other value.
- Validate server-side: never trust client order or indices; drop empty rows, reindex arrays server-side, and cap the number of rows to a sane limit (documented in code) to prevent abuse.
- Documented protected actions/capabilities: writing `_wpfaq_faqs`, `_wpfaq_display_location`, and `_wpfaq_custom_tabs` requires `edit_post` on the target product plus a valid `wpfaq_save_faqs` nonce. There are no other privileged actions.

## Simplicity (YAGNI / KISS)

- Write the minimum code that satisfies the current phase. Prefer WordPress/WooCommerce core helpers over new code.
- Rule of three: do not extract an abstraction until the same logic appears three times.
- No new wrapper/factory/manager/utils class or generic "helpers" grab-bag without explicit approval noted in `docs/memory.md`. The only classes are `WPFAQ_Plugin`, `WPFAQ_Admin`, `WPFAQ_Frontend`, `WPFAQ_Tabs`.
- No unused flags, options, filters, or config. Every hook and option must be exercised by a shipped feature.
- Pause and justify past ~150 lines: if a single function or method approaches ~150 lines, stop and split it or explain why in the PR description.
- Use existing libraries: reuse jQuery UI Sortable, WooCommerce field helpers (`woocommerce_wp_text_input`, `woocommerce_wp_textarea_input`, `woocommerce_wp_select`) rather than hand-rolling admin markup.

## Code style

- Sparse, human comments that explain "why", not "what". No commented-out code.
- Concise docstrings: every function/class/file has a short WPCS-style DocBlock with `@param`/`@return` where relevant.
- No emoji anywhere in code, comments, docs, or commits.
- No AI/authorship mentions anywhere (no "generated by", no co-author trailers).
- Conventional Commits for every commit (see Conventions).

## Boundaries

- No wholesale file delete or rewrite. Make targeted, reviewable edits.
- Never change `docs/PRD.md` or `docs/architecture.md` without flagging it first and recording the reason in `docs/memory.md`.
- No new runtime or dev dependency without explicit approval recorded in `docs/memory.md`.
- Ask when ambiguous rather than guessing at product behavior.
- Stop after 2 failed fix attempts on the same problem and report what was tried instead of continuing to churn.
- Scope changes are routed explicitly: fold into the current phase only if trivial and on-theme; otherwise open a new phase or add to the Backlog section in `docs/phases.md`. Do not silently expand scope.
