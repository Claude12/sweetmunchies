function animations() {
  // ─── Defaults ─────────────────────────────────────────────
  var DEFAULTS = {
    offset: 0.1, // IntersectionObserver threshold
    once: true, // animate only once (set false to re-animate on re-entry)
  };

  // ─── Helpers ──────────────────────────────────────────────

  /**
   * Read a numeric attribute, return undefined if absent / NaN.
   */
  function numAttr($el, attr) {
    var val = parseFloat($el.attr(attr));
    return isNaN(val) ? undefined : val;
  }

  /**
   * Trigger the animation on a single element.
   */
  function trigger($el) {
    var delay = numAttr($el, "animate-delay");
    var duration = numAttr($el, "animate-duration");

    if (delay !== undefined) {
      $el.css("animation-delay", delay + "ms");
    }
    if (duration !== undefined) {
      $el.css("animation-duration", duration + "ms");
    }

    $el.addClass("animated");
  }

  // ─── IntersectionObserver path (modern browsers) ──────────
  function initObserver($elements) {
    $elements.each(function () {
      var $el = $(this);
      var offset = numAttr($el, "animate-offset");
      var threshold = offset !== undefined ? offset : DEFAULTS.offset;

      var observer = new IntersectionObserver(
        function (entries, obs) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              trigger($(entry.target));
              if (DEFAULTS.once) {
                obs.unobserve(entry.target);
              }
            } else if (!DEFAULTS.once) {
              // re-entering: remove class so it can replay
              $(entry.target).removeClass("animated");
            }
          });
        },
        { threshold: threshold },
      );

      observer.observe(this);
    });
  }

  // ─── Fallback for very old browsers (scroll + resize) ─────
  function isInViewport(el) {
    var rect = el.getBoundingClientRect();
    var wh = window.innerHeight || document.documentElement.clientHeight;
    var ww = window.innerWidth || document.documentElement.clientWidth;
    var offset = parseFloat($(el).attr("animate-offset")) || DEFAULTS.offset;
    var visH = rect.height * offset;
    var visW = rect.width * offset;
    return (
      rect.top + visH < wh &&
      rect.left + visW < ww &&
      rect.bottom - visH > 0 &&
      rect.right - visW > 0
    );
  }

  function legacyCheck($elements) {
    $elements.each(function () {
      var $el = $(this);
      if (!$el.hasClass("animated") && isInViewport(this)) {
        trigger($el);
      }
    });
  }

  function initLegacy($elements) {
    legacyCheck($elements); // check on load

    var $win = $(window);
    var tid;

    $win.on("scroll.animate resize.animate", function () {
      clearTimeout(tid);
      tid = setTimeout(function () {
        legacyCheck($elements);
      }, 50);
    });
  }

  // ─── Init ─────────────────────────────────────────────────
  $(function () {
    var $elements = $("[animate]");

    if (!$elements.length) return;

    // Apply any animate-delay helpers that match CSS classes
    $elements.each(function () {
      var $el = $(this);
      var delay = numAttr($el, "animate-delay");
      var dur = numAttr($el, "animate-duration");

      if (delay !== undefined) $el.css("animation-delay", delay + "ms");
      if (dur !== undefined) $el.css("animation-duration", dur + "ms");
    });

    if ("IntersectionObserver" in window) {
      initObserver($elements);
    } else {
      initLegacy($elements);
    }
  });
}

export default animations;
