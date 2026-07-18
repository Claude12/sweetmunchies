let toastEl = null;
let hideTimer = null;

function getToastEl() {
  if (toastEl) return toastEl;
  toastEl = document.createElement('div');
  toastEl.className = 'toast';
  toastEl.setAttribute('role', 'status');
  toastEl.setAttribute('aria-live', 'polite');

  const message = document.createElement('span');
  message.className = 'toast__message';
  toastEl.appendChild(message);

  const link = document.createElement('a');
  link.className = 'toast__link';
  link.textContent = 'View Cart';
  toastEl.appendChild(link);

  // The single-product page has a fixed sticky Add to Cart bar below tablet
  // width that otherwise sits directly under the toast's default offset.
  if (document.querySelector('[data-sticky-cta]')) {
    toastEl.classList.add('toast--has-sticky-cta');
  }

  document.body.appendChild(toastEl);
  return toastEl;
}

/**
 * @param {string} message
 * @param {{ cartUrl?: string }} [options] Pass cartUrl to show a "View Cart"
 * link; omitted when there's nowhere useful to send the customer (e.g. the
 * customer is already on the cart page).
 */
export function showToast(message, options = {}) {
  const el = getToastEl();
  const messageEl = el.querySelector('.toast__message');
  const linkEl = el.querySelector('.toast__link');

  clearTimeout(hideTimer);
  messageEl.textContent = message;

  const cartUrl = options.cartUrl ?? window.wc_add_to_cart_params?.cart_url ?? '';
  linkEl.hidden = !cartUrl;
  if (cartUrl) {
    linkEl.href = cartUrl;
  }

  el.classList.remove('is-visible', 'is-hiding');
  // Force reflow so the entrance animation restarts if a toast is already showing.
  void el.offsetWidth;
  el.classList.add('is-visible');

  hideTimer = setTimeout(() => {
    el.classList.add('is-hiding');
    el.classList.remove('is-visible');
  }, 3800);
}

/**
 * Wires WooCommerce's native AJAX add-to-cart event (fired on document.body
 * via jQuery by the plugin's own wc-add-to-cart.js) to a toast confirmation.
 * The cart itself is still added to for real — this only adds feedback.
 *
 * Only fires when a real $button is present, i.e. an actual add-to-cart
 * click. The cart page also triggers this same event purely to sync the
 * header cart-count fragment after a quantity change — that call passes no
 * $button, so it's ignored here rather than showing a spurious toast.
 */
function cartToasts() {
  if (!window.jQuery) return;

  window.jQuery(document.body).on('added_to_cart', (event, fragments, cartHash, $button) => {
    const button = $button && $button[0];
    if (!button) return;

    const card = button.closest('.product-card');
    const productPage = button.closest('.product-page');
    const name = card
      ? card.querySelector('.product-card__name')?.textContent.trim()
      : productPage?.querySelector('.product-page__title')?.textContent.trim();
    showToast(`${name || 'Item'} added to cart`);
  });
}

export default cartToasts;
