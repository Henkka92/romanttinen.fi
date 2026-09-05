/**
 * Recipient progressive peli: per-section unlock depth.
 *
 * Progress is stored in sessionStorage (key: romant_peli_{token}), NOT localStorage
 * and NOT a long-lived cookie. Reason: localStorage/cookie keyed only by invite token
 * shared unlock state across all testers/tabs on the same browser, which broke QA.
 * sessionStorage keeps progress for the current tab/session after "Avaa kutsu", but
 * each new browser session starts fresh at the teaser (depth 1 per section).
 */
(function () {
  'use strict';

  var cfg = window.romantPeli || {};
  var STORAGE_PREFIX = 'romant_peli_';

  function storageKey(token) {
    return STORAGE_PREFIX + token;
  }

  /** Expire any legacy cookie left by older builds (no longer read or written). */
  function clearLegacyCookie(name) {
    try {
      document.cookie = name + '=; path=/; max-age=0; SameSite=Lax';
    } catch (e) { /* ignore */ }
  }

  function loadProgress(token) {
    var key = storageKey(token);
    // Drop long-lived cookie + localStorage from v1.1.0 so they cannot re-contaminate state.
    clearLegacyCookie(key);
    try {
      localStorage.removeItem(key);
    } catch (e0) { /* ignore */ }
    var raw = '';
    try {
      raw = sessionStorage.getItem(key) || '';
    } catch (e) { /* private mode */ }
    if (!raw) {
      return { opened: false, depth: {} };
    }
    try {
      var parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') {
        return { opened: false, depth: {} };
      }
      return {
        opened: !!parsed.opened,
        depth: parsed.depth && typeof parsed.depth === 'object' ? parsed.depth : {}
      };
    } catch (e2) {
      return { opened: false, depth: {} };
    }
  }

  function saveProgress(token, progress) {
    var key = storageKey(token);
    var raw = JSON.stringify(progress);
    try {
      sessionStorage.setItem(key, raw);
    } catch (e) { /* ignore */ }
    // Intentionally do NOT write localStorage or cookies (shared across QA sessions).
  }

  function track(type, token, extra) {
    if (!cfg.ajaxUrl || !cfg.nonce) return;
    var body = new FormData();
    body.append('action', 'romant_track_event');
    body.append('nonce', cfg.nonce);
    body.append('token', token);
    body.append('type', type);
    if (extra) {
      if (extra.section) body.append('section', extra.section);
      if (typeof extra.section_index === 'number') {
        body.append('section_index', String(extra.section_index));
      }
    }
    try {
      if (navigator.sendBeacon) {
        navigator.sendBeacon(cfg.ajaxUrl, body);
        return;
      }
    } catch (e) { /* fall through */ }
    fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' }).catch(function () {});
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /** Depth starts at 1 after open; never auto-unlock level 2+. */
  function getDepth(progress, index) {
    var d = progress.depth[String(index)];
    var n = parseInt(d, 10);
    return Number.isFinite(n) && n >= 1 ? n : 1;
  }

  /**
   * Render only levels[0 .. depth-1] for each section.
   * "Haluatko kuulla lisää?" only when more levels remain.
   */
  function renderSections(container, sections, progress) {
    container.innerHTML = '';
    sections.forEach(function (sec, index) {
      var depth = getDepth(progress, index);
      var levels = sec.levels || [];
      var maxDepth = levels.length;
      if (depth > maxDepth) depth = maxDepth;
      if (depth < 1) depth = 1;

      var wrap = document.createElement('div');
      wrap.className = 'romant-osio';
      wrap.setAttribute('data-section-index', String(index));

      var title = document.createElement('h2');
      title.className = 'romant-osio-title romant-serif';
      title.textContent = sec.title || ('Osio ' + (index + 1));
      wrap.appendChild(title);

      // Only unlocked levels: indices 0 .. depth-1
      for (var i = 0; i < depth; i++) {
        var text = levels[i];
        if (text === undefined || text === '') continue;
        var block = document.createElement('div');
        block.className = 'romant-spoiler romant-osio-level';
        block.setAttribute('data-level', String(i + 1));
        block.innerHTML =
          '<span class="romant-spoiler-label">Taso ' + (i + 1) + '</span>' +
          '<p>' + escapeHtml(text) + '</p>';
        wrap.appendChild(block);
      }

      if (depth < maxDepth) {
        var more = document.createElement('button');
        more.type = 'button';
        more.className = 'romant-btn romant-btn-secondary romant-btn-sm romant-more-btn';
        more.setAttribute('data-want-more', '');
        more.setAttribute('data-section-index', String(index));
        more.textContent = 'Haluatko kuulla lisää?';
        wrap.appendChild(more);
      }

      container.appendChild(wrap);
    });
  }

  function init() {
    var card = document.getElementById('romant-invite');
    if (!card) return;

    var token = card.getAttribute('data-romant-token') || '';
    var rawSections = card.getAttribute('data-romant-sections') || '[]';
    var sections = [];
    try {
      sections = JSON.parse(rawSections);
    } catch (e) {
      sections = [];
    }
    if (!Array.isArray(sections)) sections = [];

    var progress = loadProgress(token);
    var openBtn = document.getElementById('romant-avaa-kutsu');
    var teaser = document.getElementById('romant-teaser-actions');
    var reveal = document.getElementById('romant-reveal');
    var container = document.getElementById('romant-peli-sections');
    var modal = document.getElementById('romant-reveal-modal');
    var modalYes = document.getElementById('romant-modal-yes');
    var modalNo = document.getElementById('romant-modal-no');
    var pendingIndex = -1;

    // Teaser gate: game content stays hidden until opened (HTML + CSS [hidden]).
    function showGame() {
      if (teaser) teaser.hidden = true;
      if (reveal) reveal.hidden = false;
      if (container) renderSections(container, sections, progress);
    }

    function keepTeaser() {
      if (teaser) teaser.hidden = false;
      if (reveal) {
        reveal.hidden = true;
        if (container) container.innerHTML = '';
      }
    }

    function openInvite() {
      var wasOpened = progress.opened;
      progress.opened = true;
      // First open in this session: force every section to depth 1 (ignore any stale depth).
      // Later opens in-session keep existing depth so return within the tab stays progressive.
      sections.forEach(function (_, i) {
        var k = String(i);
        if (!wasOpened) {
          progress.depth[k] = 1;
        } else if (!progress.depth[k] || progress.depth[k] < 1) {
          progress.depth[k] = 1;
        }
      });
      saveProgress(token, progress);
      showGame();
      if (!wasOpened) {
        track('open', token);
      }
    }

    if (progress.opened) {
      showGame();
    } else {
      keepTeaser();
    }

    if (openBtn) {
      openBtn.addEventListener('click', function () {
        openInvite();
      });
    }

    function closeModal() {
      pendingIndex = -1;
      if (modal && typeof modal.close === 'function') {
        modal.close();
      } else if (modal) {
        modal.removeAttribute('open');
      }
    }

    function openModal(index) {
      pendingIndex = index;
      if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
      } else if (modal) {
        modal.setAttribute('open', '');
      }
    }

    if (container) {
      container.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-want-more]');
        if (!btn) return;
        var idx = parseInt(btn.getAttribute('data-section-index') || '-1', 10);
        if (idx < 0) return;
        openModal(idx);
      });
    }

    var unlocking = false;
    if (modalYes) {
      modalYes.addEventListener('click', function () {
        if (unlocking) return;
        if (pendingIndex < 0) {
          closeModal();
          return;
        }
        unlocking = true;
        var idx = pendingIndex;
        var sec = sections[idx];
        var cur = getDepth(progress, idx);
        var max = (sec && sec.levels) ? sec.levels.length : cur;
        // Confirm modal unlocks exactly +1 level.
        if (cur < max) {
          progress.depth[String(idx)] = cur + 1;
          saveProgress(token, progress);
          track('reveal', token, {
            section: sec ? (sec.title || '') : '',
            section_index: idx
          });
        }
        closeModal();
        if (container) renderSections(container, sections, progress);
        unlocking = false;
      });
    }

    if (modalNo) {
      modalNo.addEventListener('click', function () {
        closeModal();
      });
    }

    if (modal) {
      modal.addEventListener('cancel', function () {
        pendingIndex = -1;
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
