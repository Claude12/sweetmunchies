function quantityStepper() {
  document.querySelectorAll('.product-page__qty-stepper').forEach((stepper) => {
    const input = stepper.querySelector('.product-page__qty-input');
    const decreaseBtn = stepper.querySelector('[data-qty-decrease]');
    const increaseBtn = stepper.querySelector('[data-qty-increase]');

    if (!input) {
      return;
    }

    const setQty = (value) => {
      const min = parseInt(input.min, 10) || 1;
      input.value = String(Math.max(min, value));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    decreaseBtn?.addEventListener('click', () => {
      setQty((parseInt(input.value, 10) || 1) - 1);
    });

    increaseBtn?.addEventListener('click', () => {
      setQty((parseInt(input.value, 10) || 1) + 1);
    });

    input.addEventListener('input', () => {
      const min = parseInt(input.min, 10) || 1;
      if ((parseInt(input.value, 10) || 0) < min) {
        input.value = String(min);
      }
    });
  });
}

export default quantityStepper;
