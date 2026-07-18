function productAddToCart() {
  const form = document.querySelector('.product-page__form');

  if (!form) {
    return;
  }

  const basePrice = parseFloat(form.dataset.basePrice) || 0;
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
  const originalPriceHtml = priceEl?.innerHTML;
  const currencySymbol = priceEl?.querySelector('.woocommerce-Price-currencySymbol')?.textContent || '$';

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

  giftCheckbox?.addEventListener('change', () => {
    if (giftTextarea) {
      giftTextarea.hidden = !giftCheckbox.checked;
    }
    if (giftHint) {
      giftHint.hidden = !giftCheckbox.checked;
    }
    updatePriceDisplay();
    updateTotal();
  });

  qtyInput?.addEventListener('change', updateTotal);
  qtyInput?.addEventListener('input', updateTotal);

  const submitToCart = async () => {
    if (!submitButton || submitButton.classList.contains('is-loading')) {
      return;
    }

    submitButton.classList.remove('is-added');
    submitButton.classList.add('is-loading');
    if (labelEl) {
      labelEl.textContent = 'Adding…';
    }

    const formData = new FormData(form);
    formData.set('product_id', productId);

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });
      const data = await response.json();

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
}

export default productAddToCart;
