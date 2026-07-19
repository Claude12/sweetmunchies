<?php

declare(strict_types=1);

/**
 * RankMath SEO integration.
 *
 * Only loaded when RankMath is active (see functions.php).
 *
 * @package sweetmunchies
 */

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
 * RankMath's file-based sitemap cache calls FS_CHMOD_FILE, a constant that
 * only WordPress's admin-side filesystem bootstrap (wp-admin/includes/file.php)
 * defines — on a plain frontend sitemap request it's undefined, which fatals.
 * Forcing the transient-backed "db" cache mode avoids that code path entirely
 * without touching plugin files.
 */
add_filter('rank_math/sitemap/cache_mode', function () {
	return 'db';
});
