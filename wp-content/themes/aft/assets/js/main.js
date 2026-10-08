/**
 * AFT — interaction layer.
 * Vanilla, no dependencies. Everything degrades gracefully and respects
 * prefers-reduced-motion.
 */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $  = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ------------------------------------------------ sticky header ---- */
  var header = $('#aft-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-stuck', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* -------------------------------------------- announcement bar ----- */
  var topbar = $('#aft-topbar');
  var tbClose = $('[data-aft-topbar-close]');
  if (topbar && tbClose) {
    try {
      if (sessionStorage.getItem('aftTopbarClosed') === '1') topbar.hidden = true;
    } catch (e) {}
    tbClose.addEventListener('click', function () {
      topbar.hidden = true;
      try { sessionStorage.setItem('aftTopbarClosed', '1'); } catch (e) {}
    });
  }

  /* ------------------------------------------------------- search ---- */
  var sToggle = $('[data-aft-search-toggle]');
  var sPanel = $('#aft-search');
  if (sToggle && sPanel) {
    sToggle.addEventListener('click', function () {
      var open = sPanel.classList.toggle('is-open');
      sToggle.setAttribute('aria-expanded', String(open));
      if (open) {
        var input = $('input[type="search"]', sPanel);
        if (input) setTimeout(function () { input.focus(); }, 60);
      }
    });
  }

  /* ------------------------------------------------ mobile drawer ---- */
  var drawer = $('#aft-drawer');
  if (drawer) {
    var opener = $('[data-aft-drawer-open]');
    var lastFocus = null;

    var openDrawer = function () {
      lastFocus = document.activeElement;
      drawer.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      if (opener) opener.setAttribute('aria-expanded', 'true');
      var first = $('a, button', drawer.querySelector('.aft-drawer__panel'));
      if (first) setTimeout(function () { first.focus(); }, 80);
    };
    var closeDrawer = function () {
      drawer.classList.remove('is-open');
      document.body.style.overflow = '';
      if (opener) opener.setAttribute('aria-expanded', 'false');
      if (lastFocus) lastFocus.focus();
    };

    if (opener) opener.addEventListener('click', openDrawer);
    $$('[data-aft-drawer-close]').forEach(function (el) {
      el.addEventListener('click', closeDrawer);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer();
    });

    // Focus trap.
    drawer.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab' || !drawer.classList.contains('is-open')) return;
      var f = $$('a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])',
        drawer.querySelector('.aft-drawer__panel')).filter(function (el) {
          return el.offsetParent !== null;
        });
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  }

  /* --------------------------------------------- reveal on scroll ---- */
  var revealables = $$('[data-reveal]');
  if (revealables.length) {
    if (reduced || !('IntersectionObserver' in window)) {
      revealables.forEach(function (el) { el.classList.add('is-in'); });
    } else {
      var ro = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('is-in'); ro.unobserve(en.target); }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
      revealables.forEach(function (el) { ro.observe(el); });
    }
  }

  /* ------------------------------------------- progress bar fills ---- */
  var fills = $$('[data-fill]');
  if (fills.length) {
    var setFill = function (el) { el.style.width = Math.max(0, Math.min(100, +el.dataset.fill)) + '%'; };
    if (reduced || !('IntersectionObserver' in window)) {
      fills.forEach(setFill);
    } else {
      var fo = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { setFill(en.target); fo.unobserve(en.target); }
        });
      }, { threshold: 0.3 });
      fills.forEach(function (el) { fo.observe(el); });
    }
  }

  /* -------------------------------------------- counting statistics -- */
  var counters = $$('[data-count]');
  if (counters.length) {
    var runCount = function (el) {
      var target = parseFloat(el.dataset.count);
      if (isNaN(target)) { return; }
      var decimals = (String(el.dataset.count).split('.')[1] || '').length;
      if (reduced) { el.textContent = target.toFixed(decimals); return; }
      var dur = 1400, t0 = null;
      var tick = function (ts) {
        if (!t0) t0 = ts;
        var p = Math.min(1, (ts - t0) / dur);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = (target * eased).toFixed(decimals);
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target.toFixed(decimals);
      };
      requestAnimationFrame(tick);
    };
    if (!('IntersectionObserver' in window)) {
      counters.forEach(runCount);
    } else {
      var co = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { runCount(en.target); co.unobserve(en.target); }
        });
      }, { threshold: 0.45 });
      counters.forEach(function (el) { co.observe(el); });
    }
  }

  /* ----------------------------------------------- CIMA journey ------ */
  var stepsWrap = $('[data-steps]');
  if (stepsWrap) {
    var steps = $$('[data-step]', stepsWrap);
    var fill = $('[data-steps-fill]', stepsWrap);

    var activate = function (key, idx) {
      steps.forEach(function (b, i) {
        var on = i === idx;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-pressed', String(on));
      });
      $$('[data-step-panel]').forEach(function (p) {
        p.hidden = p.dataset.stepPanel !== key;
      });
      if (fill && !reduced) {
        fill.style.width = (idx / Math.max(1, steps.length - 1)) * 88 + '%';
      }
    };

    steps.forEach(function (b, i) {
      b.addEventListener('click', function () { activate(b.dataset.step, i); });
      b.addEventListener('keydown', function (e) {
        var n = null;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') n = steps[i + 1];
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') n = steps[i - 1];
        if (n) { e.preventDefault(); n.focus(); n.click(); }
      });
    });

    if (fill && !reduced && 'IntersectionObserver' in window) {
      var so = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { fill.style.width = '0%'; so.unobserve(en.target); }
        });
      }, { threshold: 0.3 });
      so.observe(stepsWrap);
    }
  }

  /* ------------------------------------------------ carousel rails --- */
  $$('[data-rail]').forEach(function (rail) {
    var name = rail.dataset.rail;
    var step = function () {
      var card = rail.firstElementChild;
      return card ? card.getBoundingClientRect().width + 20 : 320;
    };
    var prev = $('[data-rail-prev="' + name + '"]');
    var next = $('[data-rail-next="' + name + '"]');
    if (prev) prev.addEventListener('click', function () { rail.scrollBy({ left: -step(), behavior: reduced ? 'auto' : 'smooth' }); });
    if (next) next.addEventListener('click', function () { rail.scrollBy({ left: step(), behavior: reduced ? 'auto' : 'smooth' }); });
  });

  /* ---------------------------------------------------- parallax ----- */
  var px = $$('[data-parallax]');
  if (px.length && !reduced) {
    var ticking = false;
    var apply = function () {
      var vh = window.innerHeight;
      px.forEach(function (el) {
        var r = el.getBoundingClientRect();
        if (r.bottom < -200 || r.top > vh + 200) return;
        var speed = parseFloat(el.dataset.speed || '0.05');
        var mid = r.top + r.height / 2 - vh / 2;
        el.style.transform = 'translate3d(0,' + (-mid * speed).toFixed(2) + 'px,0)';
      });
      ticking = false;
    };
    var request = function () {
      if (!ticking) { ticking = true; requestAnimationFrame(apply); }
    };
    apply();
    window.addEventListener('scroll', request, { passive: true });
    window.addEventListener('resize', request);
  }

  /* ------------------------------------------- hero crossfade ------ */
  var hero = $('[data-hero]');
  if (hero) {
    var slides = $$('[data-hero-slide]', hero);
    var dots = $$('[data-hero-dot]', hero);
    var idx = 0, timer = null;
    var AUTO = parseInt(hero.dataset.heroInterval, 10) || 60000;

    var render = function () {
      slides.forEach(function (s, i) {
        var on = i === idx;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', String(!on));
        // Hidden heroes must stay out of the tab order.
        $$('a, button, input', s).forEach(function (el) {
          if (on) { el.removeAttribute('tabindex'); }
          else { el.setAttribute('tabindex', '-1'); }
        });
      });
      dots.forEach(function (d, i) {
        d.classList.toggle('is-active', i === idx);
        d.setAttribute('aria-selected', String(i === idx));
      });
    };

    var go = function (n) { idx = (n + slides.length) % slides.length; render(); };

    var stop = function () { if (timer) { clearInterval(timer); timer = null; } };
    var start = function () {
      if (reduced || slides.length < 2) return;
      stop();
      timer = setInterval(function () { go(idx + 1); }, AUTO);
    };

    var nextBtn = $('[data-hero-next]', hero);
    var prevBtn = $('[data-hero-prev]', hero);
    if (nextBtn) nextBtn.addEventListener('click', function () { go(idx + 1); start(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { go(idx - 1); start(); });
    dots.forEach(function (d, i) {
      d.addEventListener('click', function () { go(i); start(); });
    });

    // Only pause on hover where hover genuinely exists. On touch devices a tap
    // fires mouseenter but never mouseleave, which left rotation stopped for good.
    var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (canHover) {
      hero.addEventListener('mouseenter', stop);
      hero.addEventListener('mouseleave', start);
    }
    hero.addEventListener('focusin', stop);
    hero.addEventListener('focusout', function (e) {
      if (!hero.contains(e.relatedTarget)) start();
    });
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { stop(); } else { start(); }
    });

    hero.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); go(idx + 1); start(); }
      if (e.key === 'ArrowLeft')  { e.preventDefault(); go(idx - 1); start(); }
    });

    var x0 = null;
    hero.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 45) { go(idx + (dx < 0 ? 1 : -1)); }
      x0 = null; start();
    });

    render();
    start();
  }


  /* --------------------------- balance related reading with article ---- */
  var relatedPanel = $('[data-aft-related]');
  if (relatedPanel) {
    var relatedItems = $$('[data-aft-related-item]', relatedPanel);
    var balanceRelated = function () {
      if (!relatedItems.length) return;

      // Keep a useful fallback on mobile, where the sidebar is stacked below
      // the article instead of running alongside it.
      relatedItems.forEach(function (item, i) { item.hidden = i >= 4; });
      if (!window.matchMedia('(min-width: 1025px)').matches) return;

      var main = $('.aft-single__main');
      var aside = $('.aft-aside__inner');
      if (!main || !aside) return;

      // Reveal enough related cards to use the same vertical run as the post.
      // The first four remain the minimum; longer articles get more cards.
      var target = main.offsetHeight;
      for (var i = 4; i < relatedItems.length && aside.offsetHeight < target; i++) {
        relatedItems[i].hidden = false;
      }
    };
    var balanceTimer = null;
    var scheduleBalance = function () {
      if (balanceTimer) window.cancelAnimationFrame(balanceTimer);
      balanceTimer = window.requestAnimationFrame(function () {
        balanceTimer = null;
        balanceRelated();
      });
    };
    window.addEventListener('load', scheduleBalance);
    window.addEventListener('resize', scheduleBalance);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(scheduleBalance);
    scheduleBalance();
  }


  /* ------------------------------------------- article TOC highlight -- */
  var tocLinks = $$('.aft-toc a');
  if (tocLinks.length && 'IntersectionObserver' in window) {
    var map = {};
    var targets = [];
    tocLinks.forEach(function (a) {
      var id = decodeURIComponent((a.getAttribute('href') || '').slice(1));
      var el = id && document.getElementById(id);
      if (el) { map[id] = a; targets.push(el); }
    });
    var setCurrent = function (id) {
      tocLinks.forEach(function (a) { a.classList.remove('is-current'); });
      if (map[id]) { map[id].classList.add('is-current'); }
    };
    var visible = new Set();
    var tObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { visible.add(en.target.id); }
        else { visible.delete(en.target.id); }
      });
      // highlight the first heading currently on screen, in document order
      for (var i = 0; i < targets.length; i++) {
        if (visible.has(targets[i].id)) { setCurrent(targets[i].id); return; }
      }
    }, { rootMargin: '-15% 0px -70% 0px', threshold: 0 });
    targets.forEach(function (t) { tObs.observe(t); });
  }

})();