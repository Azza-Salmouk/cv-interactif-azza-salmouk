/* script.js
   CV interactif Option B
   - theme persistant (localStorage)
   - smooth anchors with header offset
   - skill bar animation on scroll (IntersectionObserver)
   - skill filtering
   - show/hide extra details
   - timeline interactive
   - export PDF via html2pdf (fallback window.print)
*/

(function () {
  'use strict';

  /* helpers */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from((ctx || document).querySelectorAll(sel));

  /* THEME */
  const THEME_KEY = 'cv_theme';
  const themeToggle = document.getElementById('themeToggle');

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      themeToggle && themeToggle.setAttribute('aria-pressed', 'true');
    } else {
      document.documentElement.removeAttribute('data-theme');
      themeToggle && themeToggle.setAttribute('aria-pressed', 'false');
    }
  }

  let savedTheme = localStorage.getItem(THEME_KEY) || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  applyTheme(savedTheme);

  themeToggle && themeToggle.addEventListener('click', function () {
    savedTheme = localStorage.getItem(THEME_KEY) === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, savedTheme);
    applyTheme(savedTheme);
  });

  /* SMOOTH ANCHORS with offset */
  function scrollToHashWithOffset(hash) {
    if (!hash) return;
    const el = document.querySelector(hash);
    if (!el) return;
    const headerOffset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-offset')) || 24;
    const rect = el.getBoundingClientRect();
    const absoluteTop = window.pageYOffset + rect.top;
    window.scrollTo({ top: absoluteTop - headerOffset, behavior: 'smooth' });
  }

  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const hash = a.getAttribute('href');
    if (hash.length > 1) {
      e.preventDefault();
      scrollToHashWithOffset(hash);
      history.replaceState(null, '', hash);
    }
  });

  if (location.hash) {
    setTimeout(() => scrollToHashWithOffset(location.hash), 60);
  }

  /* SKILL BARS ANIMATION */
  const skillBars = $$('.skill-bar');
  if ('IntersectionObserver' in window && skillBars.length) {
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const bar = entry.target;
          const fill = bar.querySelector('.skill-bar-fill');
          const value = Math.min(100, parseInt(bar.dataset.value || 0));
          if (fill) {
            requestAnimationFrame(() => { fill.style.width = value + '%'; });
            bar.setAttribute('aria-valuenow', String(value));
          }
          obs.unobserve(bar);
        }
      });
    }, { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

    skillBars.forEach(b => io.observe(b));
  } else {
    // fallback
    skillBars.forEach(b => {
      const fill = b.querySelector('.skill-bar-fill');
      const value = b.dataset.value || 0;
      if (fill) fill.style.width = value + '%';
      b.setAttribute('aria-valuenow', String(value));
    });
  }

  /* SKILL FILTERS */
  const filters = $$('.skill-filter');
  const skillBlocks = $$('.skill-block');

  function setActiveFilter(filter) {
    filters.forEach(btn => btn.classList.toggle('active', btn.dataset.filter === filter));
    skillBlocks.forEach(block => {
      if (filter === 'all' || block.dataset.category === filter) {
        block.style.display = '';
      } else {
        block.style.display = 'none';
      }
    });
  }

  filters.forEach(btn => btn.addEventListener('click', () => setActiveFilter(btn.dataset.filter)));
  setActiveFilter('all');

  /* SHOW/HIDE DETAILS */
  document.addEventListener('click', (e) => {
    const m = e.target.closest('.more-skill');
    if (!m) return;
    const block = m.closest('.skill-block');
    if (!block) return;
    const expanded = block.classList.toggle('expanded');
    m.setAttribute('aria-expanded', expanded ? 'true' : 'false');
  });

  /* TIMELINE INTERACTION */
  const timelineItems = $$('.tl-item');
  timelineItems.forEach(item => {
    item.addEventListener('click', () => {
      timelineItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
      const top = item.getBoundingClientRect().top + window.pageYOffset;
      window.scrollTo({ top: top - 80, behavior: 'smooth' });
    });
    item.addEventListener('keyup', (ev) => { if (ev.key === 'Enter' || ev.key === ' ') item.click(); });
  });

  /* EXPORT PDF (html2pdf) + fallback print */
  const exportBtn = document.getElementById('exportPdfBtn');
  const printBtn = document.getElementById('printBtn');

  function hideOnExportToggle(hide = true) {
    const els = document.querySelectorAll('.btn, .header-actions, .cv-nav');
    els.forEach(el => {
      if (hide) el.classList.add('hide-on-export');
      else el.classList.remove('hide-on-export');
    });
  }

  if (exportBtn) {
    exportBtn.addEventListener('click', async () => {
      exportBtn.disabled = true;
      exportBtn.textContent = 'Préparation du PDF...';
      hideOnExportToggle(true);

      const element = document.querySelector('.cv-wrap') || document.body;
      const opt = {
        margin:       12,
        filename:     'CV-Azza-Salmouk.pdf',
        image:        { type: 'jpeg', quality: 0.95 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      try {
        if (window.html2pdf) {
          await html2pdf().set(opt).from(element).save();
        } else {
          window.print();
        }
      } catch (err) {
        console.error('Export PDF failed:', err);
        window.print();
      } finally {
        hideOnExportToggle(false);
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="fa fa-file-pdf"></i> Télécharger PDF';
      }
    });
  }

  if (printBtn) {
    printBtn.addEventListener('click', () => window.print());
  }

  /* small reveal for cards */
  const cards = $$('.card');
  if ('IntersectionObserver' in window && cards.length) {
    const cardIo = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) en.target.classList.add('in-view');
      });
    }, { threshold: 0.12 });
    cards.forEach(c => cardIo.observe(c));
  } else {
    cards.forEach(c => c.classList.add('in-view'));
  }

})();
