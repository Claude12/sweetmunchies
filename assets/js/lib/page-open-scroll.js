import { HEADER_SCROLL_OFFSET } from './smooth-scroll';

// Reads the Navigation Timing entry to tell a fresh link click apart from a
// back/forward restore or a manual reload — only a real "opened this page"
// navigation should trigger the auto-scroll below. Falls back to the older
// (deprecated but still supported) performance.navigation API, and treats
// "can't tell" as "don't scroll" — this is a nice-to-have, not worth a wrong
// guess fighting the user's own back-button expectations. (Back/forward is
// further covered by the browser itself on top of this: a bfcache restore
// never re-fires DOMContentLoaded at all, so this module doesn't even run.)
function isFreshNavigation() {
  const [entry] = performance.getEntriesByType ? performance.getEntriesByType('navigation') : [];

  if (entry) {
    return entry.type === 'navigate';
  }

  if (performance.navigation) {
    return performance.navigation.type === 0; // TYPE_NAVIGATE
  }

  return false;
}

// Pages that auto-scroll past their banner on a fresh open — each entry
// names the page root (so this can't accidentally fire on an unrelated page
// reusing the target class) and what to land on. Shop/category lands on the
// toolbar just above the grid; the product page lands on the gallery+details
// layout just below its own (bannerless) page-banner. Add future pages here
// rather than duplicating the guard logic below per page.
const SCROLL_TARGETS = [
  { root: '.shop-page', target: '.shop-page__toolbar', requires: '.product-grid__list' },
  { root: '.product-page', target: '.product-page__layout' },
];

function findScrollTarget() {
  for (const { root, target, requires } of SCROLL_TARGETS) {
    const rootEl = document.querySelector(root);

    if (!rootEl) {
      continue;
    }

    if (requires && !rootEl.querySelector(requires)) {
      continue;
    }

    const targetEl = rootEl.querySelector(target);

    if (targetEl) {
      return targetEl;
    }
  }

  return null;
}

function pageOpenScroll() {
  const scrollTarget = findScrollTarget();

  if (!scrollTarget) {
    return;
  }

  // A hash in the URL is the user (or a link) asking for a specific spot —
  // never override that. Reduced-motion preference opts out of the whole
  // effect, not just the animation, since a sudden jump is exactly the kind
  // of motion that setting is meant to prevent.
  if (window.location.hash) {
    return;
  }

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  if (!isFreshNavigation()) {
    return;
  }

  // Two rAFs: the first runs after the initial layout pass, the second after
  // whatever that layout pass queued (webfont swap reflow, category-pill
  // wrapping) has actually painted — measuring one frame too early has
  // produced an off-by-a-row target position during testing.
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      // The load was slow enough that the user started scrolling themselves
      // before we got here — respect that instead of yanking them back.
      if (window.scrollY > 4) {
        return;
      }

      const top = scrollTarget.getBoundingClientRect().top + window.scrollY - HEADER_SCROLL_OFFSET;

      // Nothing worth scrolling for (e.g. a short viewport where the target
      // is already on screen).
      if (top <= 4) {
        return;
      }

      window.scrollTo({ top, behavior: 'smooth' });
    });
  });
}

export default pageOpenScroll;
