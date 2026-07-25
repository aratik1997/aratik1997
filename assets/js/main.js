document.addEventListener('DOMContentLoaded', () => {
  // Dark / light theme toggle (theme itself is applied pre-paint by an
  // inline script in <head>; this just wires up the button + persistence).
  const themeToggle = document.getElementById('theme-toggle');
  const iconDark = document.getElementById('theme-icon-dark');
  const iconLight = document.getElementById('theme-icon-light');

  function syncThemeIcon() {
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    if (iconDark) iconDark.classList.toggle('hidden', isLight);
    if (iconLight) iconLight.classList.toggle('hidden', !isLight);
  }
  syncThemeIcon();

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('theme', next);
      syncThemeIcon();
    });
  }

  // Project category filter
  const filterButtons = document.querySelectorAll('.project-filter-btn');
  const projectCards = document.querySelectorAll('.project-card');
  filterButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      filterButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      projectCards.forEach((card) => {
        const show = filter === 'all' || card.dataset.category === filter;
        card.classList.toggle('filtered-out', !show);
      });
    });
  });

  // Scroll-reveal for elements marked .fade-up
  const revealEls = document.querySelectorAll('.fade-up');
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15 }
  );
  revealEls.forEach((el) => revealObserver.observe(el));

  // Animate skill progress bars up to their target % when scrolled into view
  const barFills = document.querySelectorAll('.bar-fill');
  const barObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const target = entry.target;
          target.style.width = `${target.dataset.percent || 0}%`;
          barObserver.unobserve(target);
        }
      });
    },
    { threshold: 0.4 }
  );
  barFills.forEach((el) => barObserver.observe(el));

  // Active nav link + scroll progress bar
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');
  const progressBar = document.getElementById('scroll-progress');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach((section) => {
      const top = section.offsetTop - 120;
      if (window.scrollY >= top) {
        current = section.id;
      }
    });
    navLinks.forEach((link) => {
      link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
    });

    if (progressBar) {
      const scrollable = document.documentElement.scrollHeight - window.innerHeight;
      const pct = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
      progressBar.style.width = `${pct}%`;
    }
  });

  // Mobile nav toggle
  const navToggle = document.getElementById('nav-toggle');
  const mobileNav = document.getElementById('mobile-nav');
  if (navToggle && mobileNav) {
    navToggle.addEventListener('click', () => {
      mobileNav.classList.toggle('hidden');
    });
  }

  // Typewriter effect cycling through role phrases in the hero
  const typedEl = document.getElementById('typed-title');
  const phrases = (window.TYPED_PHRASES || []).filter(Boolean);
  if (typedEl && phrases.length) {
    let phraseIndex = 0;
    let charIndex = 0;
    let deleting = false;

    const tick = () => {
      const current = phrases[phraseIndex];
      if (!deleting) {
        charIndex++;
        typedEl.textContent = current.slice(0, charIndex);
        if (charIndex === current.length) {
          deleting = true;
          setTimeout(tick, 1600);
          return;
        }
      } else {
        charIndex--;
        typedEl.textContent = current.slice(0, charIndex);
        if (charIndex === 0) {
          deleting = false;
          phraseIndex = (phraseIndex + 1) % phrases.length;
        }
      }
      setTimeout(tick, deleting ? 35 : 70);
    };
    tick();
  }

  // World map of visited countries
  const mapEl = document.getElementById('travel-map');
  if (mapEl && typeof jsVectorMap !== 'undefined' && window.VISITED_COUNTRIES) {
    // Singapore is too small to exist as a filled path in this map's dataset,
    // so it's shown as a marker pin instead of a highlighted region.
    const markerCoords = { SG: { name: 'Singapore', coords: [1.3521, 103.8198] } };
    const visitedRegions = window.VISITED_COUNTRIES.filter((code) => !markerCoords[code]);
    const markers = {};
    window.VISITED_COUNTRIES.forEach((code) => {
      if (markerCoords[code]) markers[code] = markerCoords[code];
    });

    // Real national flag colors, in stripe order, for each visited country —
    // used to paint each region with its own flag instead of one flat color.
    const FLAG_COLORS = {
      BD: ['#006A4E', '#F42A41'],
      IN: ['#FF9933', '#FFFFFF', '#138808'],
      MY: ['#CC0001', '#FFFFFF', '#010066', '#FFCC00'],
      SA: ['#006C35', '#FFFFFF'],
      AE: ['#FF0000', '#00732F', '#FFFFFF', '#000000'],
      SG: ['#EF3340', '#FFFFFF'],
      TH: ['#A51931', '#F4F5F8', '#2D2A4A'],
    };

    const map = new jsVectorMap({
      selector: '#travel-map',
      map: 'world',
      zoomButtons: false,
      selectedRegions: visitedRegions,
      markers,
      regionStyle: {
        initial: { fill: '#05070a', stroke: '#2a2f3a', strokeWidth: 0.6 },
        // Normal, temporary hover — reverts the moment the mouse leaves.
        // (This is applied before `selected`, so it has no visible effect
        // on visited countries, which keep their flag-gradient fill.)
        hover: { fill: '#334155', cursor: 'pointer' },
        selected: { fill: '#22D3EE' },
        selectedHover: { fillOpacity: 0.85 },
      },
      markerStyle: {
        initial: { fill: '#EF3340', stroke: '#0D1117', strokeWidth: 2, r: 6 },
        hover: { fillOpacity: 0.85, cursor: 'pointer' },
      },
      backgroundColor: 'transparent',
      onRegionTooltipShow(event, tooltip, code) {
        const visited = visitedRegions.includes(code);
        if (visited) tooltip.text(`${tooltip.text()} — Visited`, false);
      },
      onMarkerTooltipShow(event, tooltip) {
        tooltip.text(`${tooltip.text()} — Visited`, false);
      },
    });

    // Build a <linearGradient> per visited country from its flag colors and
    // paint that country's region with it, so "visited" reads as that
    // country's actual flag rather than one flat highlight color.
    const svg = mapEl.querySelector('svg');
    if (svg) {
      const svgNS = 'http://www.w3.org/2000/svg';
      let defs = svg.querySelector('defs');
      if (!defs) {
        defs = document.createElementNS(svgNS, 'defs');
        svg.insertBefore(defs, svg.firstChild);
      }

      Object.keys(FLAG_COLORS).forEach((code) => {
        const colors = FLAG_COLORS[code];
        const gradientId = `flag-${code}`;
        const gradient = document.createElementNS(svgNS, 'linearGradient');
        gradient.setAttribute('id', gradientId);
        gradient.setAttribute('x1', '0');
        gradient.setAttribute('y1', '0');
        gradient.setAttribute('x2', '0');
        gradient.setAttribute('y2', '1');
        colors.forEach((color, i) => {
          const stop = document.createElementNS(svgNS, 'stop');
          stop.setAttribute('offset', `${(i / (colors.length - 1 || 1)) * 100}%`);
          stop.setAttribute('stop-color', color);
          gradient.appendChild(stop);
        });
        defs.appendChild(gradient);

        const fillUrl = `url(#${gradientId})`;
        const region = map.regions[code];
        if (region) {
          const shape = region.element.shape;
          // jsVectorMap gives every region its own `style` wrapper object,
          // but only shallow-copies it from the shared config — so
          // `style.initial` and `style.selected` are the SAME nested object
          // reference for every single region on the map. Mutating them in
          // place (style.initial.fill = ...) was overwriting that one
          // shared object each time through this loop, so by the end every
          // country — not just the visited ones — showed whichever flag was
          // processed last. Assigning a new object instead only affects
          // this region's own reference to it.
          shape.style.initial = { ...shape.style.initial, fill: fillUrl };
          shape.style.selected = { ...shape.style.selected, fill: fillUrl };
          shape.updateStyle();
        }

        const marker = map._markers && map._markers[code];
        if (marker) {
          // Small circle marker — a gradient still reads fine at this size.
          const mshape = marker.element.shape;
          mshape.style.initial = { ...mshape.style.initial, fill: fillUrl };
          mshape.updateStyle();
        }
      });
    }

    // jsVectorMap sizes itself once at construction time and never again —
    // it doesn't watch its own container, so on mobile (where the container
    // width can still be settling, or changes on rotation/breakpoint) it's
    // left rendering at a stale size that overflows or clips. Re-measure
    // whenever the container's actual size changes.
    let resizeTimer;
    const scheduleMapResize = () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => map.updateSize(), 150);
    };
    window.addEventListener('resize', scheduleMapResize);
    window.addEventListener('orientationchange', scheduleMapResize);
    if (typeof ResizeObserver !== 'undefined') {
      new ResizeObserver(scheduleMapResize).observe(mapEl);
    }
    // Also correct the very first paint, in case web fonts or the Tailwind
    // CDN's utility injection nudged layout after the map's initial size read.
    setTimeout(() => map.updateSize(), 300);
  }
});
