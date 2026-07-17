function setupMobileSubmenus(list) {
  if (!list) return;

  list.querySelectorAll(':scope li.menu-item-has-children').forEach((li) => {
    if (li.querySelector(':scope > .submenu-toggle')) return;

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'submenu-toggle';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Toggle submenu');
    toggle.innerHTML =
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    li.appendChild(toggle);

    toggle.addEventListener('click', () => {
      const isOpen = li.classList.toggle('is-expanded');
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
  });
}

function lockBodyScroll(shouldLock) {
  document.body.classList.toggle('no-scroll', shouldLock);
}

function header() {
  const menuToggle = document.querySelector('[data-menu-toggle]');
  const mobileDrawer = document.querySelector('[data-mobile-nav]');
  const menuClosers = document.querySelectorAll('[data-menu-close]');
  const searchToggle = document.querySelector('[data-search-toggle]');
  const searchOverlay = document.querySelector('[data-search-overlay]');
  const searchClosers = document.querySelectorAll('[data-search-close]');
  const searchInput = searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;

  const isSearchOpen = () => !!searchOverlay && searchOverlay.classList.contains('is-open');
  const isMenuOpen = () => !!mobileDrawer && mobileDrawer.classList.contains('is-open');
  const syncBodyScroll = () => lockBodyScroll(isSearchOpen() || isMenuOpen());

  if (menuToggle && mobileDrawer) {
    setupMobileSubmenus(mobileDrawer.querySelector('.mobile-drawer__list'));

    const openMenu = () => {
      mobileDrawer.classList.add('is-open');
      menuToggle.setAttribute('aria-expanded', 'true');
      syncBodyScroll();
    };

    const closeMenu = () => {
      mobileDrawer.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      syncBodyScroll();
    };

    menuToggle.addEventListener('click', openMenu);
    menuClosers.forEach((el) => el.addEventListener('click', closeMenu));

    mobileDrawer.querySelectorAll('.mobile-drawer__list > li:not(.menu-item-has-children) > a').forEach((link) => {
      link.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isMenuOpen()) closeMenu();
    });
  }

  if (searchToggle && searchOverlay) {
    const openSearch = () => {
      searchOverlay.classList.add('is-open');
      searchToggle.setAttribute('aria-expanded', 'true');
      syncBodyScroll();
      if (searchInput) searchInput.focus();
    };

    const closeSearch = () => {
      searchOverlay.classList.remove('is-open');
      searchToggle.setAttribute('aria-expanded', 'false');
      syncBodyScroll();
    };

    searchToggle.addEventListener('click', openSearch);
    searchClosers.forEach((el) => el.addEventListener('click', closeSearch));

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isSearchOpen()) closeSearch();
    });
  }
}

export default header;
