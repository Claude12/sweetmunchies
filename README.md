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
│   ├── js/                Vanilla JS, bundled by webpack via gulp — main.js, lib/, blocks/
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
2. Create `inc/blocks/{layout-name}.php` (hyphenated). The renderer runs each
   layout through ACF's `have_rows()`/`the_row()` loop before including the
   template, so **use `get_sub_field()`** for every field in the block, never
   `get_field()` — `get_sub_field()` only reads from the row ACF currently has
   open, and only works inside that loop. `$block_index` (0-based, set via
   `set_query_var()`) is available via `get_query_var('block_index')`, handy
   for things like eager-loading only the first block's image.
3. Add a matching SCSS partial under `assets/scss/components/_{name}.scss`
   and `@use` it from `assets/scss/style.scss`.
4. If the block needs its own JS, add `assets/js/blocks/{name}.js` and import
   it from `assets/js/main.js`; if it needs extra CSS/JS assets that shouldn't
   load site-wide (e.g. a carousel library), register them in
   `sweetmunchies_block_assets()` in `functions.php` under the layout's
   `acf_fc_layout` key — only pages using that layout will get them enqueued.
5. `cd assets && gulp build` (or leave `gulp watch` running).

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
  z-index scale), `_functions.scss` (`rem()` etc.), `_mixins.scss` (the
  `min-breakpoint()` mobile-first media query mixin)
- `base/` — `_reset.scss`, `_typography.scss`, `_colors.scss` (brand + neutral
  + semantic color tokens as SCSS variables), `_global.scss` (emits the `:root`
  CSS custom properties, see below)
- `components/`, `layout/`, `utilities/` — one partial per component/region,
  BEM class naming only (`.block__element--modifier`) — **no utility classes**
  (no `.d-flex`, `.color-primary`, etc.); one-off layout needs stay scoped
  inside the component that needs them

`_colors.scss` and `_variables.scss` define colors and spacers as SCSS
variables — these are the single source of truth, but components should
almost never reference them (`$color-primary`, `$spacer-24`) directly.
Instead `base/_global.scss` mirrors every one of them onto a `:root { }` block
as a real CSS custom property (`--color-primary`, `--spacer-24`, …), and
components use `var(--color-primary)` / `var(--spacer-24)`. This keeps colors/
spacing runtime-readable (future customizer options, JS, browser devtools)
while still having one file to edit for a re-theme.

Breakpoints (`mobile`/`tablet`/`desktop`/`wide` = 576/768/1024/1440px) stay
SCSS-only, via the `min-breakpoint()` mixin (`@include min-breakpoint(tablet) { … }`)
— **CSS custom properties cannot be used inside an `@media` condition** in any
browser (`@media (min-width: var(--x))` is invalid CSS), so breakpoints can't
follow the same var()-everywhere pattern as colors/spacing. `--breakpoint-*`
custom properties are still exposed on `:root` for JS (`matchMedia`/
`getComputedStyle`) to read, just never for use inside `@media` itself.

Swap the neutrals/aliases in `_colors.scss` for the client's actual palette
once one exists (see [Design reference](#design-reference) below) — components
should reference the semantic custom properties (`var(--color-text)`), not
raw hex values, so a re-theme only touches `_colors.scss`.

JS is vanilla only — no jQuery, no frameworks — bundled by webpack via gulp
(Babel for syntax, no polyfills beyond what `browserslist` targets):

- `assets/js/main.js` — entry point; imports and initializes everything
- `assets/js/lib/` — shared utilities used across the whole site (currently
  `smooth-scroll.js`, `animations.js`)
- `assets/js/blocks/` — one file per block that needs its own JS, imported
  from `main.js` (empty for now — `image_text_block` doesn't need JS)

## Design reference

`wp-content/themes/design/` (sibling to this theme, outside the theme's own
git repo) holds the exported design: `Sweet Munchies.dc.html` /
`ProductCard.dc.html` (rendered design-canvas exports), product/logo images
under `uploads/`, and a brief (`uploads/Sweet Munchies.docx`). Treat the
`.dc.html` files as the visual source of truth for spacing, color, type and
responsive behaviour — compare rendered blocks against them rather than
guessing, per `.cursorrules`. The real brand palette — pink/red (`#D2144C`, darker `#A80F3D`), a dark green
secondary (`#15321B`), gold accent (`#FBBD2B`), on cream backgrounds
(`#FBF4E9`, `#EDE1D2`) — plus type (`Poppins` for body/UI, `Caveat` for script
accents) is now wired in: see `assets/scss/base/_colors.scss` for the full
token set and `assets/scss/base/_typography.scss` for the font-size/weight/
line-height scale, both extracted directly from the `.dc.html` export. The
Google Fonts `<link>`s in `header.php` load the real families/weights
(Poppins 400/500/600/700/800, Caveat 400/700). Border-radius and box-shadow
values from the export were **not** turned into global tokens yet — the
export uses a wide, ad-hoc spread of both (radii from 8px to a 100px pill,
several distinct shadow tints) rather than a clean scale, so those are left
as a per-component decision to make once a given section is actually being
built against the design, rather than forcing a token scale that doesn't
match what's there.

## PHP conventions

Every PHP file starts with `declare(strict_types=1);` (first statement after
the `<?php` tag) plus a short docblock — this is enforced by `.cursorrules`
for anything added going forward. `strict_types` only affects type coercion
for calls made *from* that file, so it's safe to add file-by-file without
touching behaviour elsewhere.

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
  the `@use` imports) — no header/footer visual design has been built yet.
  The design export is now available (see [Design reference](#design-reference))
  so this is no longer "waiting on a design", just not yet built.
- `_colors.scss`/`_typography.scss` and the Google Fonts `<link>`s in
  `header.php` now hold the real extracted palette/type — see
  [Design reference](#design-reference). Border-radius and box-shadow are
  still not tokenized (deliberately — see that section for why).
- The `socials` links in `header.php`/`footer.php` currently render as plain
  text links (WhatsApp/LinkedIn/Facebook) — no icon graphics, by design, so
  they can be rebuilt against whatever icon treatment the new design uses.
- Comments and the widgetized sidebar (`sidebar.php`, `comments.php`) are
  left as-is from the starter theme and untouched by this cleanup — remove
  them if the site won't use blog comments or a sidebar.
- WooCommerce's "Coming soon" mode (Settings → Site visibility) is currently
  **off** — it was left on from initial plugin setup and made the whole site
  render WooCommerce's default coming-soon page instead of this theme's
  templates. Worth knowing if the site ever appears to "lose" the theme
  again — check that setting first.
