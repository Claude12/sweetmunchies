function faqAccordion() {
  document.querySelectorAll('.faq-accordion__list').forEach((list) => {
    const items = list.querySelectorAll('.faq-accordion__item');

    items.forEach((item) => {
      const button = item.querySelector('.faq-accordion__question');

      button.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');

        items.forEach((other) => {
          other.classList.remove('is-open');
          other.querySelector('.faq-accordion__question').setAttribute('aria-expanded', 'false');
        });

        if (!isOpen) {
          item.classList.add('is-open');
          button.setAttribute('aria-expanded', 'true');
        }
      });
    });
  });
}

export default faqAccordion;
