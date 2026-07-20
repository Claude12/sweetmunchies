<?php

declare(strict_types=1);

/**
 * RankMath SEO integration.
 *
 * Only loaded when RankMath is active (see functions.php).
 *
 * @package sweetmunchies
 */

defined('ABSPATH') || exit;

/**
 * FAQPage schema for the Contact page, built from the same `faq_accordion`
 * ACF rows that render the visible accordion (see inc/blocks/faq-accordion.php)
 * — one source of truth, so the schema can never drift out of sync with what
 * a visitor actually sees.
 */
add_filter('rank_math/json_ld', function (array $data, $jsonld) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $jsonld required by RankMath's hook signature
	if (! is_page('contact')) {
		return $data;
	}

	$sections = get_field('content_sections', get_the_ID()) ?: [];
	$faq_items = [];

	foreach ($sections as $section) {
		if (($section['acf_fc_layout'] ?? '') !== 'faq_accordion') {
			continue;
		}

		foreach ($section['items'] ?? [] as $item) {
			if (empty($item['question']) || empty($item['answer'])) {
				continue;
			}

			$faq_items[] = [
				'@type' => 'Question',
				'name' => wp_strip_all_tags($item['question']),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text' => wp_strip_all_tags($item['answer']),
				],
			];
		}
	}

	if (! $faq_items) {
		return $data;
	}

	$data['sweetmunchiesFAQPage'] = [
		'@type' => 'FAQPage',
		'mainEntity' => $faq_items,
	];

	return $data;
}, 10, 2);

/**
 * RankMath's ACF content analyzer only reads the edit-screen's live field
 * values, not the rendered frontend HTML — and it only wraps a field's
 * content in a real <hN> tag (instead of <p>) if that field's ACF *key* is
 * registered here, mapped to a heading level. Every `heading` sub-field
 * below genuinely renders as that tag on the frontend (see inc/blocks/*.php)
 * — this filter is what tells RankMath's "Focus Keyword in subheading"
 * check to see them as headings too, instead of flagging a false positive.
 */
add_filter('rank_math/acf/config', function (array $config): array {
	$config['headlines'] = [
		'field_68f30200b012' => 1, // home_hero.heading -> <h1>
		'field_68f30400d011' => 2, // featured_products.heading -> <h2>
		'field_68f30500e011' => 2, // shop_by_occasion.heading -> <h2>
		'field_68f30600f011' => 2, // best_sellers.heading -> <h2>
		'field_68f30700g011' => 2, // testimonials.heading -> <h2>
		'field_68f30900i011' => 2, // whatsapp_cta.heading -> <h2>
		'field_68f5a100l011' => 2, // feature_grid.heading -> <h2>
		'field_68f5a100l022' => 3, // feature_grid.items.title -> <h3>
		'field_68f5a200m011' => 2, // cta_banner.heading -> <h2>
		'field_68f5b200o011' => 2, // faq_accordion.heading -> <h2>
	];

	return $config;
});

/**
 * RankMath's file-based sitemap cache calls FS_CHMOD_FILE, a constant that
 * only WordPress's admin-side filesystem bootstrap (wp-admin/includes/file.php)
 * defines — on a plain frontend sitemap request it's undefined, which fatals.
 * Forcing the transient-backed "db" cache mode avoids that code path entirely
 * without touching plugin files.
 */
add_filter('rank_math/sitemap/cache_mode', function () {
	return 'db';
});
