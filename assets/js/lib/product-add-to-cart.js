import { showToast } from './toast';

function productAddToCart() {
  const form = document.querySelector('.product-page__form');

  if (!form) {
    return;
  }

  let basePrice = parseFloat(form.dataset.basePrice) || 0;
  const giftPrice = parseFloat(form.dataset.giftPrice) || 0;
  const productId = form.dataset.productId;
  const ajaxUrl = form.dataset.ajaxUrl;

  const giftCheckbox = form.querySelector('.product-page__gift-checkbox');
  const giftTextarea = form.querySelector('.product-page__gift-message');
  const giftHint = form.querySelector('.product-page__gift-hint');
  const qtyInput = form.querySelector('.product-page__qty-input');
  const totalDisplay = form.querySelector('[data-total-display]');
  const submitButton = form.querySelector('.product-page__add-to-cart');
  const labelEl = submitButton?.querySelector('[data-add-to-cart-label]');
  const originalLabel = labelEl?.textContent || 'Add to Cart';

  const priceEl = document.querySelector('.product-page__price');
  let originalPriceHtml = priceEl?.innerHTML;
  const currencySymbol = priceEl?.querySelector('.woocommerce-Price-currencySymbol')?.textContent || '$';

  const whatsappLink = document.querySelector('[data-whatsapp-order]');

  const sizeButtons = form.querySelectorAll('.product-page__size-option');
  const sizeHint = form.querySelector('[data-size-hint]');
  let selectedVariationId = null;
  let selectedSizeLabel = '';

  const updatePriceDisplay = () => {
    if (!priceEl) {
      return;
    }

    if (giftCheckbox?.checked) {
      priceEl.textContent = currencySymbol + (basePrice + giftPrice).toFixed(2);
    } else if (originalPriceHtml !== undefined) {
      priceEl.innerHTML = originalPriceHtml;
    }
  };

  const updateTotal = () => {
    const qty = parseInt(qtyInput?.value, 10) || 1;
    const giftOn = Boolean(giftCheckbox?.checked);
    const total = (qty * (basePrice + (giftOn ? giftPrice : 0))).toFixed(2);
    const formatted = currencySymbol + total;

    if (totalDisplay) {
      totalDisplay.textContent = formatted;
    }

    document.querySelectorAll('[data-sticky-total]').forEach((el) => {
      el.textContent = formatted;
    });
  };

  const updateWhatsAppLink = () => {
    const number = whatsappLink?.dataset.whatsappNumber;

    if (!whatsappLink || !number) {
      return;
    }

    const qty = parseInt(qtyInput?.value, 10) || 1;
    const giftOn = Boolean(giftCheckbox?.checked);
    const unitPrice = basePrice + (giftOn ? giftPrice : 0);
    const unitPriceText = currencySymbol + unitPrice.toFixed(2);
    const totalText = currencySymbol + (qty * unitPrice).toFixed(2);
    const productName = whatsappLink.dataset.productName || '';
    const sizeSuffix = selectedSizeLabel ? ` - ${selectedSizeLabel}` : '';
    const giftMessage = giftOn ? giftTextarea?.value.trim() : '';
    const giftSuffix = giftMessage ? ` (+ photo & message: "${giftMessage}")` : '';

    const message = 'Hi Sweet Munchies! I\'d like to order:\n\n'
      + `- ${qty}x ${productName}${sizeSuffix} — ${unitPriceText}${giftSuffix}`
      + `\n\nTotal: ${totalText}`
      + '\n\nI\'ll share my delivery details and the photo (if any) here.\n\nIf everything above looks correct, please hit send to confirm your order!';

    whatsappLink.href = `https://wa.me/${encodeURIComponent(number)}?text=${encodeURIComponent(message)}`;
  };

  giftCheckbox?.addEventListener('change', () => {
    if (giftTextarea) {
      giftTextarea.hidden = !giftCheckbox.checked;
    }
    if (giftHint) {
      giftHint.hidden = !giftCheckbox.checked;
    }
    updatePriceDisplay();
    updateTotal();
    updateWhatsAppLink();
  });

  giftTextarea?.addEventListener('input', updateWhatsAppLink);
  qtyInput?.addEventListener('change', () => {
    updateTotal();
    updateWhatsAppLink();
  });
  qtyInput?.addEventListener('input', () => {
    updateTotal();
    updateWhatsAppLink();
  });

  const selectVariation = (button) => {
    selectedVariationId = button.dataset.variationId;
    selectedSizeLabel = button.dataset.sizeLabel || '';
    basePrice = parseFloat(button.dataset.price) || 0;

    sizeButtons.forEach((btn) => {
      btn.classList.toggle('is-selected', btn === button);
      btn.setAttribute('aria-pressed', btn === button ? 'true' : 'false');
    });

    if (sizeHint) {
      sizeHint.hidden = true;
    }

    if (priceEl) {
      originalPriceHtml = currencySymbol + basePrice.toFixed(2);
      priceEl.innerHTML = originalPriceHtml;
    }

    submitButton?.removeAttribute('disabled');
    document.querySelectorAll('[data-sticky-add-to-cart]').forEach((btn) => {
      btn.removeAttribute('disabled');
    });

    if (whatsappLink) {
      whatsappLink.classList.remove('is-disabled');
      whatsappLink.removeAttribute('aria-disabled');
      whatsappLink.removeAttribute('tabindex');
    }

    updatePriceDisplay();
    updateTotal();
    updateWhatsAppLink();
  };

  sizeButtons.forEach((button) => {
    button.addEventListener('click', () => selectVariation(button));
  });

  const submitToCart = async () => {
    if (!submitButton || submitButton.classList.contains('is-loading')) {
      return;
    }

    if (sizeButtons.length && !selectedVariationId) {
      return;
    }

    submitButton.classList.remove('is-added');
    submitButton.classList.add('is-loading');
    if (labelEl) {
      labelEl.textContent = 'Adding…';
    }

    const formData = new FormData(form);
    formData.set('product_id', selectedVariationId || productId);

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      const data = await response.json();

      // WC's add_to_cart endpoint reports failure (e.g. out of stock, since
      // stock management is enabled) as {error: true} — not an HTTP error.
      if (!response.ok || data?.error || !data?.fragments) {
        throw new Error('add_to_cart failed');
      }

      submitButton.classList.remove('is-loading');
      submitButton.classList.add('is-added');
      if (labelEl) {
        labelEl.textContent = 'Added to cart';
      }

      if (window.jQuery) {
        window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash, window.jQuery(submitButton)]);
      }

      setTimeout(() => {
        submitButton.classList.remove('is-added');
        if (labelEl) {
          labelEl.textContent = originalLabel;
        }
      }, 2000);
    } catch {
      submitButton.classList.remove('is-loading');
      if (labelEl) {
        labelEl.textContent = originalLabel;
      }
      showToast('Sorry — this item couldn\'t be added to your cart right now.', { cartUrl: '' });
    }
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    submitToCart();
  });

  document.querySelectorAll('[data-sticky-add-to-cart]').forEach((button) => {
    button.addEventListener('click', submitToCart);
  });

  const stickyCta = document.querySelector('[data-sticky-cta]');

  if (stickyCta && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      ([entry]) => {
        stickyCta.hidden = entry.isIntersecting;
      },
      { threshold: 0 }
    );
    observer.observe(submitButton);
  }

  updateTotal();
  updateWhatsAppLink();
}

export default productAddToCart;
