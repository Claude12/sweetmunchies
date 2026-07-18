let toastEl = null;
let hideTimer = null;

function getToastEl() {
  if (toastEl) return toastEl;
  toastEl = document.createElement('div');
  toastEl.className = 'toast';
  toastEl.setAttribute('role', 'status');
  toastEl.setAttribute('aria-live', 'polite');
  document.body.appendChild(toastEl);
  return toastEl;
}

export function showToast(message) {
  const el = getToastEl();

  clearTimeout(hideTimer);
  el.textContent = message;
  el.classList.remove('is-visible', 'is-hiding');
  // Force reflow so the entrance animation restarts if a toast is already showing.
  void el.offsetWidth;
  el.classList.add('is-visible');

  hideTimer = setTimeout(() => {
    el.classList.add('is-hiding');
    el.classList.remove('is-visible');
  }, 2200);
}

/**
 * Wires WooCommerce's native AJAX add-to-cart event (fired on document.body
 * via jQuery by the plugin's own wc-add-to-cart.js) to a toast confirmation.
 * The cart itself is still added to for real — this only adds feedback.
 */
function cartToasts() {
  if (!window.jQuery) return;

  window.jQuery(document.body).on('added_to_cart', (event, fragments, cartHash, $button) => {
    const button = $button && $button[0];
    const card = button ? button.closest('.product-card') : null;
    const name = card ? card.querySelector('.product-card__name')?.textContent.trim() : null;
    showToast(`${name || 'Item'} added to cart`);
  });
}

export default cartToasts;
