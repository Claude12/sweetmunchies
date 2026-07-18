import { showToast } from './toast';

function formatPrice(amount, currencySymbol) {
  return currencySymbol + amount.toFixed(2);
}

function buildWhatsappMessage(items, currencySymbol) {
  if (!items.length) {
    return '';
  }

  let total = 0;
  const lines = items.map((item) => {
    const lineTotal = item.qty * item.unitPrice;
    total += lineTotal;

    let line = `${item.qty}x ${item.name} — ${formatPrice(lineTotal, currencySymbol)}`;
    if (item.giftMessage) {
      line += ` (+ photo & message: "${item.giftMessage}")`;
    }
    return line;
  });

  return (
    'Hi Sweet Munchies! I\'d like to order:\n\n' +
    `- ${lines.join('\n- ')}\n\n` +
    `Total: ${formatPrice(total, currencySymbol)}\n\n` +
    'I\'ll share my delivery details and the photo (if any) here.\n\n' +
    'If everything above looks correct, please hit send to confirm your order!'
  );
}

function cartPage() {
  const root = document.querySelector('.cart-page');

  if (!root) {
    return;
  }

  const itemsList = root.querySelector('.cart-page__items');
  const whatsappBox = root.querySelector('.cart-page__whatsapp');
  const whatsappLink = root.querySelector('[data-whatsapp-link]');
  const subtotalEl = root.querySelector('[data-cart-subtotal]');
  const totalEl = root.querySelector('[data-cart-total]');
  const headerCount = document.querySelector('.header__cart-count');

  if (!itemsList || !whatsappBox) {
    return;
  }

  const updateUrl = whatsappBox.dataset.ajaxUpdateUrl;
  const removeUrl = whatsappBox.dataset.ajaxRemoveUrl;
  const whatsappNumber = whatsappBox.dataset.whatsappNumber;

  const currencySymbol =
    root.querySelector('.woocommerce-Price-currencySymbol')?.textContent || '$';

  const readItems = () =>
    Array.from(itemsList.querySelectorAll('.cart-page__item')).map((row) => ({
      row,
      key: row.dataset.cartItemKey,
      name: row.querySelector('.cart-page__item-name')?.textContent.trim() || '',
      unitPrice: parseFloat(row.dataset.unitPrice) || 0,
      giftMessage: row.dataset.giftMessage || '',
      qtyInput: row.querySelector('.cart-page__qty-input'),
      priceEl: row.querySelector('[data-line-total]'),
    })).map((entry) => ({
      ...entry,
      qty: parseInt(entry.qtyInput?.value, 10) || 1,
    }));

  const updateTotals = () => {
    const items = readItems();
    let total = 0;

    items.forEach((item) => {
      const lineTotal = item.qty * item.unitPrice;
      total += lineTotal;

      if (item.priceEl) {
        item.priceEl.textContent = formatPrice(lineTotal, currencySymbol);
      }
    });

    if (subtotalEl) {
      subtotalEl.textContent = formatPrice(total, currencySymbol);
    }
    if (totalEl) {
      totalEl.textContent = formatPrice(total, currencySymbol);
    }

    if (whatsappLink && whatsappNumber) {
      const message = buildWhatsappMessage(items, currencySymbol);
      whatsappLink.href = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
    }

    return items;
  };

  const applyFragments = (eventName, data) => {
    if (window.jQuery && data?.fragments) {
      window.jQuery(document.body).trigger(eventName, [data.fragments, data.cart_hash]);
    } else if (headerCount && typeof data?.fragments?.['.header__cart-count'] === 'string') {
      const temp = document.createElement('div');
      temp.innerHTML = data.fragments['.header__cart-count'];
      const newSpan = temp.firstElementChild;
      if (newSpan) {
        headerCount.replaceWith(newSpan);
      }
    }
  };

  const updateQuantity = async (cartItemKey, quantity) => {
    if (!updateUrl) {
      return;
    }

    const formData = new FormData();
    formData.set('cart_item_key', cartItemKey);
    formData.set('quantity', String(quantity));

    try {
      const response = await fetch(updateUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const data = await response.json();
      applyFragments('added_to_cart', data);
    } catch {
      // Optimistic UI already reflects the change; a failed background sync
      // just means the next full page load will re-fetch the real cart.
    }
  };

  const removeItem = async (row) => {
    const cartItemKey = row.dataset.cartItemKey;
    const itemName = row.querySelector('.cart-page__item-name')?.textContent.trim();

    if (!removeUrl || !cartItemKey) {
      return;
    }

    const formData = new FormData();
    formData.set('cart_item_key', cartItemKey);

    try {
      const response = await fetch(removeUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const data = await response.json();
      applyFragments('removed_from_cart', data);
    } catch {
      // Row is removed from the DOM regardless; a failed background sync
      // just means the next full page load will re-fetch the real cart.
    }

    showToast(`${itemName || 'Item'} removed from cart`, { cartUrl: '' });
    row.remove();

    if (!itemsList.querySelector('.cart-page__item')) {
      window.location.reload();
      return;
    }

    updateTotals();
  };

  itemsList.addEventListener('click', (event) => {
    const row = event.target.closest('.cart-page__item');

    if (!row) {
      return;
    }

    if (event.target.closest('[data-remove-item]')) {
      removeItem(row);
      return;
    }

    const input = row.querySelector('.cart-page__qty-input');
    const min = parseInt(input?.min, 10) || 1;

    if (event.target.closest('[data-qty-decrease]')) {
      input.value = String(Math.max(min, (parseInt(input.value, 10) || 1) - 1));
    } else if (event.target.closest('[data-qty-increase]')) {
      input.value = String((parseInt(input.value, 10) || 1) + 1);
    } else {
      return;
    }

    updateTotals();
    updateQuantity(row.dataset.cartItemKey, input.value);
  });

  itemsList.addEventListener('change', (event) => {
    const row = event.target.closest('.cart-page__item');
    const input = event.target.closest('.cart-page__qty-input');

    if (!row || !input) {
      return;
    }

    const min = parseInt(input.min, 10) || 1;
    if ((parseInt(input.value, 10) || 0) < min) {
      input.value = String(min);
    }

    updateTotals();
    updateQuantity(row.dataset.cartItemKey, input.value);
  });

  updateTotals();
}

export default cartPage;
