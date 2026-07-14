# Memory: woo-product-faq

Running log of what is done, in progress, and decided. Keep entries short and dated.

## Completed

- 2026-07-15: Planning documentation created (README, PRD, architecture, rules, design, phases, testing, launch checklist).

## In progress

- (none)

## Decisions log

- 2026-07-15: Development tooling uses exact-pinned PHPCS 3.13.5, WPCS 3.3.0, PHPCompatibilityWP 2.1.8, PHPUnit 9.6.35, and PHPUnit Polyfills 4.0.0. The polyfills package is part of the WordPress test-suite scaffolding needed to run the supported PHPUnit versions across PHP releases.
- 2026-07-15: Storage uses WordPress post meta only (`_wpfaq_faqs`, `_wpfaq_display_location`, `_wpfaq_custom_tabs`); no custom tables in v1 to stay simple. A custom table would require `dbDelta` with a versioned `wpfaq_db_version` option and an upgrade routine, and must be recorded here before adoption.
- 2026-07-15: Prefix set to `wpfaq_` (functions/hooks), `WPFAQ_` (constants/classes); text domain `woo-product-faq`.
- 2026-07-15: Frontend accordion is vanilla JS + CSS (no framework, no build step); admin reorder reuses WordPress-bundled jQuery UI Sortable to avoid a new dependency.
- 2026-07-15: Only four classes allowed without approval: `WPFAQ_Plugin`, `WPFAQ_Admin`, `WPFAQ_Frontend`, `WPFAQ_Tabs`. Data access lives in prefixed functions in `wpfaq-functions.php`.
- 2026-07-15: FAQs attach to the parent product only; variations do not get separate FAQs in v1.
- 2026-07-15: FAQ JSON-LD structured data and a shortcode/block are deferred to backlog, not v1.
