# WooCommerce template overrides

Empty on purpose. WooCommerce falls back to its own plugin templates for
anything not present here — that's the correct default.

To override a template, copy the **single file** you need from
`wp-content/plugins/woocommerce/templates/` into this folder, keeping the
same relative path (e.g. `woocommerce/content-product.php`,
`woocommerce/single-product/price.php`). Never copy the whole `templates/`
tree — every file you don't actually customise is one that goes stale and
silently diverges when WooCommerce updates its own templates.

Check the template version comment (`@version`) at the top of any file you
copy, and re-diff it against the plugin's version after major WooCommerce
updates.
