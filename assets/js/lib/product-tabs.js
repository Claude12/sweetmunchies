function productTabs() {
  document.querySelectorAll('.product-page__tabs').forEach((tabs) => {
    const buttons = tabs.querySelectorAll('.product-page__tab');
    const panels = tabs.querySelectorAll('.product-page__tab-panel');

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const target = button.dataset.tab;

        buttons.forEach((other) => {
          other.classList.remove('is-active');
          other.setAttribute('aria-selected', 'false');
        });
        button.classList.add('is-active');
        button.setAttribute('aria-selected', 'true');

        panels.forEach((panel) => {
          panel.classList.toggle('is-active', panel.dataset.tabPanel === target);
        });
      });
    });
  });
}

export default productTabs;
