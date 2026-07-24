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
 * Sweet Munchies is mobile / delivery-only — there is no shopfront, which is why
 * `local_address.streetAddress` is deliberately blank and the business type is
 * LocalBusiness rather than Store. A service-area business still has to say
 * *where* it serves, though, and RankMath has no setting for it, so declare
 * `areaServed` here. Keep this consistent with the Google Business Profile's
 * service area — a mismatch between the two is what tends to get SAB profiles
 * flagged.
 */
add_filter('rank_math/json_ld', function (array $data, $jsonld) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $jsonld required by RankMath's hook signature
	foreach ($data as $key => $entity) {
		$type = (array) ($entity['@type'] ?? []);

		if (!array_intersect($type, ['LocalBusiness', 'Organization'])) {
			continue;
		}

		$data[$key]['areaServed'] = [
			['@type' => 'City', 'name' => 'Mutare'],
			['@type' => 'AdministrativeArea', 'name' => 'Manicaland'],
			['@type' => 'Country', 'name' => 'Zimbabwe'],
		];
	}

	return $data;
}, 20, 2);

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
		'field_68f5d100q011' => 2, // process_detail.heading -> <h2>
		'field_68f5d100q022' => 3, // process_detail.items.title -> <h3>
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

/**
 * `brand` is a recommended property on Google's Product structured data, but
 * RankMath only fills it from the product_brand taxonomy — which this shop
 * doesn't use, since every product is our own.
 *
 * This deliberately inlines `@type` + `name` instead of pointing an `@id` at
 * the Organization node RankMath already emits. A bare `{"@id": ...}` reference
 * is legal JSON-LD and does resolve within the graph, but Google's Product
 * parser does not follow references for `brand` — it reported it as "Invalid
 * object type for field 'brand'" in Search Console's Merchant listings report.
 * The `@id` is kept alongside so the node still merges with the Organization
 * for consumers that *do* resolve references.
 */
add_filter('rank_math/snippet/rich_snippet_product_entity', function (array $entity): array {
	if (!isset($entity['brand'])) {
		$entity['brand'] = [
			'@type' => 'Brand',
			'@id'   => home_url('/#organization'),
			'name'  => get_bloginfo('name'),
		];
	}

	// Quote-on-request products (no price set, so not purchasable) make RankMath
	// emit `"offers": null`. Google reads that as an *invalid value* rather than
	// an absent field, which is the louder Search Console error of the two — so
	// drop the key entirely and let the product fall back to a plain, valid
	// Product node with no offer.
	if (array_key_exists('offers', $entity) && empty($entity['offers'])) {
		unset($entity['offers']);
	}

	// `validFrom` — the date this offer started being valid — is a Merchant
	// listing recommendation RankMath doesn't fill. The product's publish date
	// is the honest answer: prices here are set once when the box is added and
	// are not run as time-boxed promotions. Guarded to `Offer` because variable
	// products emit an AggregateOffer, where a single start date would be a
	// claim about several prices at once.
	if (isset($entity['offers']['@type']) && 'Offer' === $entity['offers']['@type']) {
		$published = get_post_datetime(get_queried_object_id());

		if ($published) {
			$entity['offers']['validFrom'] = $published->format('Y-m-d');
		}
	}

	return $entity;
});
