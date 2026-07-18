function productPoa() {
  const wrapper = document.querySelector('.product-page__poa');

  if (!wrapper) {
    return;
  }

  const whatsappNumber = wrapper.dataset.whatsappNumber;
  const productName = wrapper.dataset.productName;
  const textarea = wrapper.querySelector('.product-page__poa-message');
  const button = wrapper.querySelector('[data-poa-button]');

  const buildMessage = () => {
    const description = textarea?.value.trim();
    const sendInstruction = 'If everything above looks correct, please hit send and we\'ll get back to you with a quote!';

    if (!description) {
      return `Hi Sweet Munchies! I'm interested in the ${productName} — could you help me with pricing?\n\n${sendInstruction}`;
    }

    return `Hi Sweet Munchies! I'm interested in the ${productName}.\n\nHere's what I have in mind:\n${description}\n\n${sendInstruction}`;
  };

  const updateHref = () => {
    if (!button) {
      return;
    }

    button.href = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(buildMessage())}`;
  };

  textarea?.addEventListener('input', updateHref);
  updateHref();
}

export default productPoa;
