/**
 * Recipient progressive peli: per-section unlock depth.
 *
 * Progress is stored in sessionStorage (key: romant_peli_{token}), NOT localStorage
 * and NOT a long-lived cookie. Reason: localStorage/cookie keyed only by invite token
 * shared unlock state across all testers/tabs on the same browser, which broke QA.
 * sessionStorage keeps progress for the current tab/session after "Avaa kutsu", but
 * each new browser session starts fresh at the teaser (depth 1 per section).
 *
 * Display stacks unlocked levels (1..depth); the newest appends with giftIn.
 * Open + peel use Pauliina's giftIn (~.38s). Modal uses the same motion (~.32s).
 * Modal still unlocks exactly +1. Section title lives inside each hint card.
 */
(function () {
  'use strict';

  var cfg = window.romantPeli || {};
  var STORAGE_PREFIX = 'romant_peli_';
  /** Demo lock: hint giftIn .38s; modal .32s. Do not rush open. */
  var HINT_MS = 380;
  var MODAL_MS = 320;
  /** After the *Nyt saat tietää…* beat — Tapaaminen stays secondary. */
  var MEET_DELAY_MS = 560;

  function storageKey(token) {
    return STORAGE_PREFIX + token;
  }

  function prefersReducedMotion() {
    try {
      return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    } catch (e) {
      return false;
    }
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

  /** Non-empty levels only — empty slots must not inflate peel depth. */
  function peelLevels(sec) {
    var raw = (sec && sec.levels) ? sec.levels : [];
    var out = [];
    for (var i = 0; i < raw.length; i++) {
      var t = raw[i];
      if (t !== undefined && t !== null && String(t) !== '') {
        out.push(String(t));
      }
    }
    return out;
  }

  function sectionTitle(sec, index) {
    var t = sec && sec.title ? String(sec.title).trim() : '';
    return t || ('Osio ' + (index + 1));
  }

  /**
   * Render unlocked hints as a stack (levels 0..depth-1). Previous stay visible;
   * the newest appends below. opts.animate = giftIn on the newest card only.
   * "Haluatko kuulla lisää?" only when more non-empty levels remain.
   */
  function renderSections(container, sections, progress, opts) {
    opts = opts || {};
    var animate = !!opts.animate && !prefersReducedMotion();
    container.innerHTML = '';
    sections.forEach(function (sec, index) {
      var depth = getDepth(progress, index);
      var levels = peelLevels(sec);
      var maxDepth = levels.length;
      if (depth > maxDepth) depth = maxDepth;
      if (depth < 1) depth = 1;

      var wrap = document.createElement('div');
      wrap.className = 'romant-osio' + (sections.length < 2 ? ' is-solo' : '');
      if (depth > 1) {
        wrap.classList.add('is-stacked');
      }
      wrap.setAttribute('data-section-index', String(index));

      var titleText = sectionTitle(sec, index);
      var stack = document.createElement('div');
      stack.className = 'romant-hint-stack';

      for (var i = 0; i < depth; i++) {
        var text = levels[i];
        if (text === undefined || text === '') {
          continue;
        }
        var levelNum = i + 1;
        var isNewest = i === depth - 1;
        var block = document.createElement('div');
        block.className = 'romant-hint romant-osio-level';
        block.setAttribute('data-level', String(levelNum));
        block.innerHTML =
          '<span class="romant-hint-section">' + escapeHtml(titleText) + '</span>' +
          '<span class="romant-spoiler-label">Vihje</span>' +
          '<p>' + escapeHtml(text) + '</p>';
        if (isNewest) {
          block.classList.add(animate ? 'show' : 'is-newest');
        } else {
          block.classList.add('is-prior');
        }
        stack.appendChild(block);
      }
      wrap.appendChild(stack);

      if (depth < maxDepth) {
        var more = document.createElement('button');
        more.type = 'button';
        more.className = 'romant-btn romant-btn-ghost romant-more-btn';
        more.setAttribute('data-want-more', '');
        more.setAttribute('data-section-index', String(index));
        more.textContent = 'Haluatko kuulla lisää?';
        wrap.appendChild(more);
      }

      container.appendChild(wrap);
    });
  }

  function resetMeetingEl(meet) {
    if (!meet) return;
    meet.classList.remove('show', 'is-restored', 'is-pending');
  }

  function init() {
    var card = document.getElementById('romant-invite');
    if (!card) return;

    var page = document.getElementById('romant-recipient-page');
    var chrome = document.getElementById('romant-recipient-chrome');
    var moment = document.getElementById('romant-gift-moment');
    var cue = document.getElementById('romant-reveal-cue');
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
    var unwrapping = false;
    var unlocking = false;
    var meetTimer = 0;

    function clearMeetTimer() {
      if (meetTimer) {
        window.clearTimeout(meetTimer);
        meetTimer = 0;
      }
    }

    function markMeeting(animate) {
      var meet = document.getElementById('romant-tapaaminen');
      clearMeetTimer();
      resetMeetingEl(meet);
      if (!meet) return;
      if (animate && !prefersReducedMotion()) {
        meet.classList.add('is-pending');
        meetTimer = window.setTimeout(function () {
          meet.classList.remove('is-pending');
          meet.classList.add('show');
          meetTimer = 0;
        }, MEET_DELAY_MS);
        return;
      }
      meet.classList.add('is-restored');
    }

    function setPageState(state) {
      if (card) card.setAttribute('data-romant-state', state);
      if (page) page.setAttribute('data-romant-state', state);
      document.body.classList.remove('is-teaser', 'is-unwrapping', 'is-opened');
      if (state) {
        document.body.classList.add('is-' + state);
      }
      if (chrome) {
        chrome.hidden = state !== 'opened';
      }
    }

    function hideTeaserChrome() {
      if (teaser) {
        teaser.hidden = true;
        teaser.classList.remove('is-unwrapping');
      }
      if (moment) {
        moment.hidden = true;
        moment.classList.remove('is-unwrapping');
      }
      card.classList.remove('is-unwrapping');
    }

    function revealGame(opts) {
      opts = opts || {};
      var animate = !!opts.animate;
      var showCue = !!opts.showCue;
      if (cue) {
        cue.hidden = !showCue;
      }
      if (reveal) {
        reveal.hidden = false;
      }
      if (container) {
        renderSections(container, sections, progress, { animate: animate });
      }
      markMeeting(animate);
      setPageState('opened');
    }

    // Teaser gate: game content stays hidden until opened (HTML + CSS [hidden]).
    function showGame(opts) {
      opts = opts || {};
      hideTeaserChrome();
      revealGame({
        animate: !!opts.animate,
        showCue: !!opts.showCue
      });
    }

    function keepTeaser() {
      if (teaser) {
        teaser.hidden = false;
        teaser.classList.remove('is-unwrapping');
      }
      if (moment) {
        moment.hidden = false;
        moment.classList.remove('is-unwrapping');
      }
      card.classList.remove('is-unwrapping');
      if (reveal) {
        reveal.hidden = true;
        if (container) container.innerHTML = '';
      }
      if (cue) cue.hidden = true;
      if (chrome) chrome.hidden = true;
      var meet = document.getElementById('romant-tapaaminen');
      clearMeetTimer();
      resetMeetingEl(meet);
      setPageState('teaser');
    }

    function unwrapThenShow() {
      if (prefersReducedMotion()) {
        showGame({ animate: false, showCue: true });
        return;
      }
      unwrapping = true;
      setPageState('unwrapping');
      card.classList.add('is-unwrapping');
      if (moment) moment.classList.add('is-unwrapping');
      if (teaser) teaser.classList.add('is-unwrapping');
      if (openBtn) openBtn.disabled = true;
      // Hide open CTA, then giftIn the first hint (not a dump of all levels).
      if (reveal) reveal.hidden = false;
      if (cue) cue.hidden = false;
      if (container) {
        renderSections(container, sections, progress, { animate: true });
      }
      markMeeting(true);
      window.setTimeout(function () {
        hideTeaserChrome();
        setPageState('opened');
        if (openBtn) openBtn.disabled = false;
        unwrapping = false;
      }, HINT_MS);
    }

    function openInvite() {
      if (unwrapping) return;
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
      if (!wasOpened) {
        unwrapThenShow();
        track('open', token);
      } else {
        showGame({ animate: false, showCue: false });
      }
    }

    if (progress.opened) {
      showGame({ animate: false, showCue: false });
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
      if (modal) {
        modal.classList.remove('is-present');
      }
      if (modal && typeof modal.close === 'function') {
        modal.close();
      } else if (modal) {
        modal.removeAttribute('open');
      }
    }

    function openModal(index) {
      pendingIndex = index;
      if (modal) {
        modal.classList.add('is-present');
      }
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
        var max = sec ? peelLevels(sec).length : cur;
        var didUnlock = false;
        // Confirm modal unlocks exactly +1 level; previous cards stay in the stack.
        if (cur < max) {
          progress.depth[String(idx)] = cur + 1;
          saveProgress(token, progress);
          didUnlock = true;
          track('reveal', token, {
            section: sec ? (sec.title || '') : '',
            section_index: idx
          });
        }
        closeModal();
        if (container) {
          renderSections(container, sections, progress, { animate: didUnlock });
        }
        if (cue) cue.hidden = true;
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
        modal.classList.remove('is-present');
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
