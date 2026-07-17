function animations() {
  const DEFAULTS = {
    offset: 0.1, // IntersectionObserver threshold
    once: true, // animate only once (set false to re-animate on re-entry)
  };

  function numAttr(el, attr) {
    const val = parseFloat(el.getAttribute(attr));
    return isNaN(val) ? undefined : val;
  }

  function trigger(el) {
    const delay = numAttr(el, 'animate-delay');
    const duration = numAttr(el, 'animate-duration');

    if (delay !== undefined) {
      el.style.animationDelay = `${delay}ms`;
    }
    if (duration !== undefined) {
      el.style.animationDuration = `${duration}ms`;
    }

    el.classList.add('animated');
  }

  // ─── IntersectionObserver path (modern browsers) ──────────
  function initObserver(elements) {
    elements.forEach((el) => {
      const offset = numAttr(el, 'animate-offset');
      const threshold = offset !== undefined ? offset : DEFAULTS.offset;

      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            trigger(entry.target);
            if (DEFAULTS.once) {
              obs.unobserve(entry.target);
            }
          } else if (!DEFAULTS.once) {
            // re-entering: remove class so it can replay
            entry.target.classList.remove('animated');
          }
        });
      }, { threshold });

      observer.observe(el);
    });
  }

  // ─── Fallback for browsers without IntersectionObserver ───
  function isInViewport(el) {
    const rect = el.getBoundingClientRect();
    const wh = window.innerHeight || document.documentElement.clientHeight;
    const ww = window.innerWidth || document.documentElement.clientWidth;
    const offset = numAttr(el, 'animate-offset') ?? DEFAULTS.offset;
    const visH = rect.height * offset;
    const visW = rect.width * offset;
    return (
      rect.top + visH < wh &&
      rect.left + visW < ww &&
      rect.bottom - visH > 0 &&
      rect.right - visW > 0
    );
  }

  function legacyCheck(elements) {
    elements.forEach((el) => {
      if (!el.classList.contains('animated') && isInViewport(el)) {
        trigger(el);
      }
    });
  }

  function initLegacy(elements) {
    legacyCheck(elements); // check on load

    let tid;
    const onScrollOrResize = () => {
      clearTimeout(tid);
      tid = setTimeout(() => legacyCheck(elements), 50);
    };

    window.addEventListener('scroll', onScrollOrResize);
    window.addEventListener('resize', onScrollOrResize);
  }

  // ─── Init ─────────────────────────────────────────────────
  const elements = Array.from(document.querySelectorAll('[animate]'));

  if (!elements.length) return;

  // Apply any animate-delay helpers that match CSS classes
  elements.forEach((el) => {
    const delay = numAttr(el, 'animate-delay');
    const dur = numAttr(el, 'animate-duration');

    if (delay !== undefined) el.style.animationDelay = `${delay}ms`;
    if (dur !== undefined) el.style.animationDuration = `${dur}ms`;
  });

  if ('IntersectionObserver' in window) {
    initObserver(elements);
  } else {
    initLegacy(elements);
  }
}

export default animations;
