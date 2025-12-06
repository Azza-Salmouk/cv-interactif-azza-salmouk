(function () {
  'use strict';

  /* --- Helpers --- */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from((ctx || document).querySelectorAll(sel));

  async function safeJsonFetch(url, options = {}) {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch {
        console.error('Non-JSON response from', url, text);
        return { ok: false, error: 'Invalid JSON' };
      }
    } catch (err) {
      console.error('Fetch failed', url, err);
      return { ok: false, error: err.message };
    }
  }

  /* --- Theme --- */
  const THEME_KEY = 'cv_theme';
  const themeToggle = $('#themeToggle');

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      themeToggle?.setAttribute('aria-pressed', 'true');
    } else {
      document.documentElement.removeAttribute('data-theme');
      themeToggle?.setAttribute('aria-pressed', 'false');
    }
  }

  let savedTheme = localStorage.getItem(THEME_KEY) || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  applyTheme(savedTheme);

  themeToggle?.addEventListener('click', () => {
    savedTheme = localStorage.getItem(THEME_KEY) === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, savedTheme);
    applyTheme(savedTheme);
  });

  /* --- Smooth Scroll --- */
  function scrollToHashWithOffset(hash) {
    if (!hash) return;
    const el = document.querySelector(hash);
    if (!el) return;
    const headerOffset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-offset')) || 24;
    const absoluteTop = window.pageYOffset + el.getBoundingClientRect().top;
    window.scrollTo({ top: absoluteTop - headerOffset, behavior: 'smooth' });
  }

  document.addEventListener('click', e => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const hash = a.getAttribute('href');
    if (hash.length > 1) {
      e.preventDefault();
      scrollToHashWithOffset(hash);
      history.replaceState(null, '', hash);
    }
  });

  if (location.hash) setTimeout(() => scrollToHashWithOffset(location.hash), 60);

  /* --- Skill Bars --- */
  function animateSkillBars() {
    const skillBars = $$('.skill-bar');
    if ('IntersectionObserver' in window && skillBars.length) {
      const io = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const bar = entry.target;
            const fill = bar.querySelector('.skill-bar-fill');
            const value = Math.min(100, parseInt(bar.dataset.value || 0));
            if (fill) requestAnimationFrame(() => fill.style.width = value + '%');
            bar.setAttribute('aria-valuenow', String(value));
            obs.unobserve(bar);
          }
        });
      }, { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
      skillBars.forEach(b => io.observe(b));
    } else {
      skillBars.forEach(b => {
        const fill = b.querySelector('.skill-bar-fill');
        const value = b.dataset.value || 0;
        if (fill) fill.style.width = value + '%';
        b.setAttribute('aria-valuenow', String(value));
      });
    }
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  /* --- Load CV from server --- */
  async function loadAllPublic() {
    try {
      const [p, s, e, pr] = await Promise.all([
        safeJsonFetch('/api/profile.php'),
        safeJsonFetch('/api/skills.php'),
        safeJsonFetch('/api/experiences.php'),
        safeJsonFetch('/api/projects.php')
      ]);

      // Profile
      if (p.ok) {
        $('.big-name') && ($('.big-name').textContent = p.profile.fullname);
        $('.subtitle') && ($('.subtitle').textContent = p.profile.title || '');
        const contact = $('.contact');
        if (contact) contact.innerHTML = `
          <li><i class="fa fa-envelope"></i> <a href="mailto:${p.profile.email}">${p.profile.email}</a></li>
          <li><i class="fa fa-location-dot"></i> ${p.profile.location}</li>
          <li><i class="fa fa-phone"></i> ${p.profile.phone}</li>
          <li><i class="fa fa-link"></i> <a href="${p.profile.linkedin}" target="_blank">LinkedIn</a></li>`;
        const about = $('#profil p');
        if (about) about.textContent = p.profile.about;
      }

      // Skills
      if (s.ok) {
        const container = $('.skills-bars') || $('.skill-grid') || $('#competences .skill-grid');
        if (container) {
          container.innerHTML = '';
          s.skills.forEach(skill => {
            const el = document.createElement('div');
            el.className = 'bar-block';
            el.dataset.category = skill.category;
            el.innerHTML = `<span>${escapeHtml(skill.name)}</span>
              <div class="bar">
                <div class="skill-bar skill-bar-fill" data-value="${skill.value}" style="width:${skill.value}%"></div>
              </div>`;
            container.appendChild(el);
          });
          animateSkillBars();
        }
      }

      // Experiences
      if (e.ok) {
        const tl = $('.timeline');
        if (tl) {
          tl.innerHTML = '';
          e.experiences.forEach(exp => {
            const li = document.createElement('li');
            li.className = 'tl-item';
            li.innerHTML = `<div class="tl-content">
              <span class="tl-date">${exp.start_date} – ${exp.end_date}</span>
              <h3>${escapeHtml(exp.title)} — ${escapeHtml(exp.company)}</h3>
              <p>${escapeHtml(exp.summary)}</p>
            </div>`;
            tl.appendChild(li);
          });
        }
      }

      // Projects
      if (pr.ok) {
        const projList = $('.project-list') || $('#projets .project-list');
        if (projList) {
          projList.innerHTML = '';
          pr.projects.forEach(pj => {
            const art = document.createElement('article');
            art.className = 'project-card';
            art.innerHTML = `<div class="proj-body">
              <h3 class="proj-title">${escapeHtml(pj.title)}</h3>
              <p class="proj-desc">${escapeHtml(pj.description)}</p>
              <p class="proj-meta">${(pj.tags || '').split(',').map(t => `<span class="tag">${escapeHtml(t.trim())}</span>`).join('')}</p>
            </div>`;
            projList.appendChild(art);
          });
        }
      }

    } catch (err) {
      console.error('loadAllPublic error', err);
    }
  }

  /* --- PDF buttons --- */
  $('#serverPdfBtn')?.addEventListener('click', async () => {
    const btn = $('#serverPdfBtn');
    btn.disabled = true; btn.textContent = "Génération...";
    try {
      const res = await fetch('/api/generate_pdf.php');
      if (!res.ok) throw new Error('Erreur serveur');
      const blob = await res.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = "CV-Azza-Salmouk.pdf"; a.click();
      window.URL.revokeObjectURL(url);
    } catch (err) { alert("Erreur génération PDF serveur"); console.error(err); }
    btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf"></i> PDF Serveur';
  });

  $('#exportPdfBtn')?.addEventListener('click', async () => {
    const btn = $('#exportPdfBtn'); btn.disabled = true; btn.textContent = 'Préparation du PDF...';
    const element = document.querySelector('.cv-wrap') || document.body;
    try {
      if (window.html2pdf) {
        await html2pdf().set({
          margin: 12, filename: 'CV-Azza-Salmouk.pdf',
          image: { type: 'jpeg', quality: 0.95 },
          html2canvas: { scale: 2, useCORS: true },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).from(element).save();
      } else { window.print(); }
    } catch (err) { console.error('Export PDF failed', err); window.print(); }
    btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf"></i> Télécharger PDF';
  });

  document.addEventListener('DOMContentLoaded', loadAllPublic);
})();
