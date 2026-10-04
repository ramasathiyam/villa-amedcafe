// Vanilla-JS behavior for the site: scroll-reveal animation, mobile nav panel, desktop
// "Dining" dropdown, and the drag/swipe card-grid carousel. Ported from the Next.js
// reference's Reveal/RevealGroup, MobileMenu, NavDropdown, and CardGrid components —
// deliberately framework-free (no dependency), per the "JS only if needed" rule.

function initReveal() {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const elements = document.querySelectorAll("[data-reveal], [data-reveal-group]");
  if (reduceMotion || elements.length === 0) return;

  if (typeof IntersectionObserver === "undefined") return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.setAttribute("data-visible", "true");
        } else {
          entry.target.removeAttribute("data-visible");
        }
      });
    },
    { threshold: 0.05 }
  );

  elements.forEach((el) => {
    const rect = el.getBoundingClientRect();
    const alreadyInView = rect.top < window.innerHeight * 0.92 && rect.bottom > 0;
    el.setAttribute("data-armed", "true");
    if (alreadyInView) el.setAttribute("data-visible", "true");
    observer.observe(el);
  });
}

function initMobileMenu() {
  const toggle = document.querySelector("[data-mobile-toggle]");
  const panel = document.querySelector("[data-mobile-panel]");
  const closeBtn = document.querySelector("[data-mobile-close]");
  if (!toggle || !panel) return;

  function open() {
    panel.hidden = false;
    toggle.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    closeBtn?.focus();
  }

  function close() {
    panel.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
    toggle.focus();
    panel.querySelectorAll("[data-submenu-panel]").forEach((el) => {
      el.hidden = true;
    });
  }

  toggle.addEventListener("click", open);
  closeBtn?.addEventListener("click", close);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !panel.hidden) close();
  });

  panel.querySelectorAll("[data-submenu-trigger]").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const targetId = trigger.getAttribute("data-submenu-trigger");
      const submenu = panel.querySelector(`[data-submenu-panel="${targetId}"]`);
      if (!submenu) return;
      const expanded = !submenu.hidden;
      submenu.hidden = expanded;
      trigger.setAttribute("aria-expanded", String(!expanded));
    });
  });

  panel.querySelectorAll("a").forEach((link) => link.addEventListener("click", close));
}

function initNavDropdowns() {
  document.querySelectorAll("[data-nav-dropdown]").forEach((item) => {
    const trigger = item.querySelector("[data-dropdown-trigger]");
    if (!trigger) return;

    function setOpen(isOpen) {
      if (isOpen) {
        item.setAttribute("data-open", "true");
      } else {
        item.removeAttribute("data-open");
      }
    }

    item.addEventListener("mouseenter", () => setOpen(true));
    item.addEventListener("mouseleave", () => setOpen(false));

    trigger.addEventListener("click", () => {
      const isOpen = item.hasAttribute("data-open");
      setOpen(!isOpen);
      if (isOpen) trigger.blur();
    });

    item.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        setOpen(false);
        link.blur();
      });
    });

    document.addEventListener("mousedown", (event) => {
      if (!item.contains(event.target)) setOpen(false);
    });
  });
}

function initCardGrids() {
  document.querySelectorAll("[data-card-grid]").forEach((grid) => {
    const track = grid.querySelector("[data-card-track]");
    const dotsWrap = grid.querySelector("[data-card-dots]");
    if (!track) return;

    let dragging = false;
    let dragMoved = false;
    let pointerCaptured = false;
    let activePointerId = null;
    let startX = 0;
    let startY = 0;
    let startScrollLeft = 0;
    // Below this many px of pointer movement, treat it as a click/tap, not a drag —
    // cards now contain real links (title/image), so a drag that crosses this threshold
    // must not also fire navigation.
    const DRAG_CLICK_THRESHOLD = 6;

    track.addEventListener("pointerdown", (event) => {
      dragging = true;
      dragMoved = false;
      pointerCaptured = false;
      activePointerId = event.pointerId;
      startX = event.clientX;
      startY = event.clientY;
      startScrollLeft = track.scrollLeft;
      track.classList.add("is-dragging");
      // Deliberately NOT calling setPointerCapture here. Capturing on pointerdown reroutes
      // the click that follows to the capturing element (track) instead of the actual <a>
      // under the pointer, which silently swallows every plain click/tap — capture is only
      // taken below, once movement past the threshold confirms this is really a drag.
    });
    track.addEventListener("pointermove", (event) => {
      if (!dragging) return;
      const dx = event.clientX - startX;
      const dy = event.clientY - startY;
      if (!dragMoved && Math.hypot(dx, dy) > DRAG_CLICK_THRESHOLD) {
        dragMoved = true;
        try {
          track.setPointerCapture(activePointerId);
          pointerCaptured = true;
        } catch {
          // pointer already gone
        }
      }
      if (dragMoved) {
        track.scrollLeft = startScrollLeft - dx;
      }
    });
    function endDrag(event) {
      dragging = false;
      track.classList.remove("is-dragging");
      if (pointerCaptured) {
        try {
          track.releasePointerCapture(event.pointerId);
        } catch {
          // pointer already released
        }
        pointerCaptured = false;
      }
    }
    track.addEventListener("pointerup", endDrag);
    track.addEventListener("pointercancel", endDrag);

    // Capture phase so this runs before a card link's own navigation — suppresses the
    // click/tap that can follow a real drag/swipe (touch in particular still synthesizes
    // one), without touching the scroll logic above. Always clears dragMoved afterward
    // (not only when it was true) so a drag that ends without ever producing a click can't
    // leave it set and swallow the next, unrelated click. Arrows/dots are outside the
    // linked cards entirely, so they're unaffected.
    track.addEventListener(
      "click",
      (event) => {
        if (dragMoved) {
          event.preventDefault();
          event.stopPropagation();
        }
        dragMoved = false;
      },
      true
    );

    if (!dotsWrap) return;
    const dots = Array.from(dotsWrap.querySelectorAll("[data-dot-index]"));
    const prevBtn = dotsWrap.querySelector('[data-arrow="prev"]');
    const nextBtn = dotsWrap.querySelector('[data-arrow="next"]');

    function setActive(index) {
      dots.forEach((dot, i) => {
        dot.classList.toggle("is-active", i === index);
        dot.setAttribute("aria-current", i === index ? "true" : "false");
      });
    }

    let frame = 0;
    track.addEventListener(
      "scroll",
      () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
          const slides = Array.from(track.children);
          let closest = 0;
          let closestDist = Infinity;
          slides.forEach((slide, i) => {
            const dist = Math.abs(slide.offsetLeft - track.scrollLeft);
            if (dist < closestDist) {
              closestDist = dist;
              closest = i;
            }
          });
          setActive(closest);
        });
      },
      { passive: true }
    );

    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        const slide = track.children[index];
        if (slide) track.scrollTo({ left: slide.offsetLeft, behavior: "smooth" });
      });
    });

    function scrollByDirection(direction) {
      const first = track.children[0];
      const step = first ? first.getBoundingClientRect().width : track.clientWidth;
      track.scrollBy({ left: direction * step, behavior: "smooth" });
    }
    prevBtn?.addEventListener("click", () => scrollByDirection(-1));
    nextBtn?.addEventListener("click", () => scrollByDirection(1));
  });
}

// Room Detail hero: swaps which stacked .hero-slider-image has .is-active via arrows/dots.
// Only wired up for heroes with 2+ images (hero-slider.blade.php only renders the
// arrows/dots markup in that case), so a single-image hero never runs this at all.
function initHeroSlider() {
  document.querySelectorAll("[data-hero-slider]").forEach((hero) => {
    const images = Array.from(hero.querySelectorAll(".hero-slider-image"));
    const dots = Array.from(hero.querySelectorAll("[data-hero-dot-index]"));
    const prevBtn = hero.querySelector('[data-hero-arrow="prev"]');
    const nextBtn = hero.querySelector('[data-hero-arrow="next"]');
    if (images.length < 2) return;

    let index = 0;

    function setActive(newIndex) {
      index = (newIndex + images.length) % images.length;
      images.forEach((img, i) => img.classList.toggle("is-active", i === index));
      dots.forEach((dot, i) => {
        dot.classList.toggle("is-active", i === index);
        dot.setAttribute("aria-current", i === index ? "true" : "false");
      });
    }

    dots.forEach((dot, i) => dot.addEventListener("click", () => setActive(i)));
    prevBtn?.addEventListener("click", () => setActive(index - 1));
    nextBtn?.addEventListener("click", () => setActive(index + 1));
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initReveal();
  initMobileMenu();
  initNavDropdowns();
  initCardGrids();
  initHeroSlider();
});
