<?php

declare(strict_types=1);

/**
 * Frontend performance tweaks.
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

/**
 * jQuery core + migrate are registered by WordPress core with no loading
 * strategy, which makes them render-blocking <script> tags in <head>. Every
 * script that actually depends on jQuery on this site (WooCommerce's
 * add-to-cart, cart-fragments, blockUI, js-cookie) already requests the
 * `defer` strategy — but WordPress silently downgrades a deferred script
 * back to blocking whenever one of its dependencies isn't defer-compatible,
 * which is exactly what was happening here. Marking jQuery itself as
 * deferrable lets the whole chain load without blocking the initial render
 * (confirmed via Lighthouse: this was ~1.3s of the "render-blocking
 * requests" finding). Deferred scripts still execute in dependency order,
 * before DOMContentLoaded — same effective order as today, just non-blocking
 * — so this is safe as long as nothing calls jQuery synchronously from an
 * inline <head> script, which nothing on this site does.
 */
add_action('wp_enqueue_scripts', function () {
	wp_script_add_data('jquery-core', 'strategy', 'defer');
	wp_script_add_data('jquery-migrate', 'strategy', 'defer');
}, 20);

/**
 * The Ajax Search for WooCommerce plugin's header search widget
 * (`jquery-dgwt-wcas`) depends on `jquery` but was never itself marked
 * defer/async — it's a classic footer script. Classic scripts execute
 * synchronously at their position in the HTML, *during* parsing; deferred
 * scripts only run after the whole document has parsed. So once jQuery
 * itself became deferred (above), this script started running before jQuery
 * did, throwing "Uncaught ReferenceError: jQuery is not defined" on every
 * page (confirmed live via headless Chrome console output) — it's loaded
 * sitewide because the search box lives in the header. Deferring it too
 * restores correct relative execution order, since its only dependency
 * (jquery) is now itself defer-compatible.
 */
add_action('wp_enqueue_scripts', function () {
	wp_script_add_data('jquery-dgwt-wcas', 'strategy', 'defer');
}, 20);

/**
 * The Ajax Search for WooCommerce plugin's stylesheet (`dgwt-wcas-style`,
 * ~6.5KB gzipped) is render-blocking in <head> on every page, since the
 * search box lives in the header sitewide. Unlike the theme's own
 * stylesheet, this CSS only styles a small, self-contained, non-LCP widget
 * (search input + its dropdown/overlay, both collapsed/hidden until a
 * shopper interacts with them) — so loading it async via the standard
 * preload+swap technique carries negligible flash-of-unstyled-content risk:
 * at worst the plain unstyled input briefly renders before its border/icon
 * styling swaps in, milliseconds later. The <noscript> fallback keeps it a
 * normal blocking stylesheet for the rare no-JS visitor.
 */
add_filter('style_loader_tag', function (string $tag, string $handle): string {
	if ($handle !== 'dgwt-wcas-style') {
		return $tag;
	}

	$async_tag = str_replace(
		"rel='stylesheet'",
		"rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
		$tag
	);

	return $async_tag . '<noscript>' . $tag . '</noscript>';
}, 10, 2);
