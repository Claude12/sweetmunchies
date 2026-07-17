# Sweet Munchies

Custom WordPress theme for Sweet Munchies. Based on Automattic's [_s](https://underscores.me/)
starter theme, rebuilt around an ACF Pro flexible-content architecture with
WooCommerce support.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- **Advanced Custom Fields PRO** — required. Every page, post and the 404
  template render their content entirely through ACF flexible content fields;
  without the plugin active those templates render blank (an admin notice
  will say so).
- **WooCommerce** — optional but supported. `inc/woocommerce.php` only loads
  when the plugin is active.
- Node 20.x + npm 10.x, for the front-end build (see [Front-end build](#front-end-build)).

## Directory structure

```
sweetmunchies/
├── acf-json/              ACF Local JSON — field group source of truth, synced to/from wp-admin
├── assets/                Front-end source (SCSS, JS) + build tooling, NOT enqueued directly
│   ├── scss/              7-1-style architecture: abstracts/base/components/layout/utilities
│   ├── js/                Source JS, bundled by webpack via gulp
│   └── gulpfile.js         Build pipeline (see Front-end build below)
├── dist/                  Compiled CSS/JS — THIS is what functions.php enqueues, and it IS committed
├── inc/
│   ├── acf.php             ACF local-json path, flexible-content renderer, admin dependency notice
│   ├── blocks/             One template per flexible-content layout (see Adding a new block)
│   ├── woocommerce.php     WooCommerce theme integration (loaded only if WC is active)
│   ├── template-tags.php   Custom template tags
│   ├── template-functions.php
│   └── jetpack.php         Loaded only if Jetpack is active
├── template-parts/        Partial templates used by index.php/search.php/etc.
├── woocommerce/           WooCommerce template overrides (empty by default — see its README)
├── images/                Static theme images — not build output, empty until the new design needs assets
├── functions.php
├── style.css              Theme metadata header only — see note below
└── woocommerce.php         Root WC template (shop/product/cart/checkout)
```

## Content model: ACF flexible content

Every page, post and the 404 page render a single flexible content field,
**Block** (`content_sections`), defined in `acf-json/group_675fd99cc44be.json`.
Each layout added to that field becomes a "block" editors can stack in any
order.

Rendering goes through one shared helper — `sweetmunchies_render_flexible_content()`
in [inc/acf.php](inc/acf.php) — used identically by `page.php`, `single.php`
and `404.php`. It maps each layout's `acf_fc_layout` key (underscores) to a
template of the same name (hyphens) in `inc/blocks/`, e.g. layout
`image_text_block` → `inc/blocks/image-text-block.php`.

### Adding a new block

1. Add a layout to the `content_sections` field group in wp-admin (ACF UI) —
   saving with Local JSON sync on writes the change straight to `acf-json/`.
2. Create `inc/blocks/{layout-name}.php` (hyphenated). Inside it, `$section`
   and `$block_index` are available as query vars — set by the renderer,
   not by the template itself.
3. Add a matching SCSS partial under `assets/scss/components/_{name}.scss`
   and `@use` it from `assets/scss/style.scss`.
4. `cd assets && gulp build` (or leave `gulp watch` running).

Currently there is one layout: **50/50 Block** (`image_text_block`) — a
two-column image/text section with a color picker background, used by
[inc/blocks/image-text-block.php](inc/blocks/image-text-block.php).

### Theme Settings (ACF Options Page)

Site-wide values live under **Theme Settings** in wp-admin (registered via
`acf-json/ui_options_page_*.json`, fields in `acf-json/group_675f29858b6ee.json`):
site logo, primary nav CTA (link field), and a `socials` group (LinkedIn,
WhatsApp, Facebook URLs) consumed by `header.php`/`footer.php`.

### ACF Local JSON

`inc/acf.php` explicitly points ACF's save/load path at `acf-json/` (matches
ACF's own default — set explicitly so the sync location is documented, not
implied). Commit everything under `acf-json/` — it's the portable source of
truth for every field group and options page, and lets field changes made in
one environment sync to another without exporting/importing manually.

## WooCommerce

- `add_theme_support('woocommerce')` plus gallery zoom/lightbox/slider support
  is declared in `functions.php`.
- `inc/woocommerce.php` disables WooCommerce's bundled stylesheet
  (`woocommerce_enqueue_styles` filter) — all WC styling instead lives in
  `assets/scss/components/_woocommerce.scss`, currently a bare-bones baseline
  for the product grid, star ratings and buttons.
- The theme wraps WooCommerce's default content in the same
  `<main id="primary" class="site-main">` markup as the rest of the site, via
  the `woocommerce_before_main_content` / `woocommerce_after_main_content`
  hooks — no template override needed for that part.
- To customize a specific WooCommerce template (product card, cart, checkout
  step, etc.), copy the **single file** from the plugin's `templates/`
  directory into `woocommerce/`, keeping the same relative path. See
  [woocommerce/README.md](woocommerce/README.md) for the full convention —
  never copy the whole `templates/` tree.

## Front-end build

Source lives in `assets/`; **compiled output in `dist/` is what actually
gets enqueued** (`functions.php: sweetmunchies_scripts()`) and **is committed
to the repo** — this theme deploys straight to shared hosting with no build
step in the deploy pipeline, so `dist/` has to ship pre-built. Always run a
build before committing changes under `assets/`.

```bash
cd assets
npm install
gulp build   # one-off minified build
gulp watch   # rebuild on save while developing
```

SCSS follows a 7-1-style structure:

- `abstracts/` — `_variables.scss` (spacers, gutters, transitions, a `z()`
  z-index scale), `_functions.scss` (`rem()` etc.), `_mixins.scss` (breakpoints)
- `base/` — `_reset.scss`, `_typography.scss`, `_colors.scss` (brand + neutral
  + semantic color tokens), `_global.scss`
- `components/`, `layout/`, `utilities/` — one partial per component/region,
  BEM class naming (`.block__element--modifier`)

`_colors.scss` currently defines the real brand color (`$color-primary`)
plus a neutral greyscale and semantic aliases (`$color-text`,
`$color-background`, `$color-border`, `$color-success/error/warning`) as a
placeholder scaffold — swap the neutrals/aliases for the client's actual
palette once one exists; components should reference the semantic names, not
raw hex values, so a re-theme only touches this one file.

## Style.css note

`style.css` at the theme root exists only because WordPress reads its theme
metadata (name, version, text domain) from that file's header comment — it
is **never enqueued** and has no other CSS in it. All real styles are
authored in `assets/scss/` and compiled to `dist/css/style.css`.

## Security headers

`functions.php: sweetmunchies_security_headers()` sends HSTS, X-Content-Type-Options,
X-Frame-Options, Referrer-Policy, Permissions-Policy and COOP headers on
every request via `send_headers`.

## Known gaps

- `screenshot.png` is still a placeholder — replace with an actual theme
  screenshot (1200×900) before launch.
- `assets/scss/layout/_header.scss` and `_footer.scss` are empty stubs (just
  the `@use` imports) — no header/footer visual design has been built yet;
  this is intentional, waiting on the new design.
- The `socials` links in `header.php`/`footer.php` currently render as plain
  text links (WhatsApp/LinkedIn/Facebook) — no icon graphics, by design, so
  they can be rebuilt against whatever icon treatment the new design uses.
- Comments and the widgetized sidebar (`sidebar.php`, `comments.php`) are
  left as-is from the starter theme and untouched by this cleanup — remove
  them if the site won't use blog comments or a sidebar.
