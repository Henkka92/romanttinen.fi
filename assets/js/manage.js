/**
 * Copy helpers + sections editor (create / manage).
 * Portti 2 craft: curated pohjat + chips — no free planner.
 */
(function () {
  'use strict';

  var cfg = window.romantManage || {};
  var MAX_SECTIONS = cfg.maxSections || 3;
  var DEFAULT_LEVELS = cfg.defaultLevels || 3;
  var SOFT_MAX = cfg.softMaxLevels || 10;
  var HARD_MAX = cfg.hardMaxLevels || 20;
  var i18n = cfg.i18n || {};
  var DEFAULT_TITLES = cfg.defaultTitles || ['Elokuvahetki', 'Yhteinen ateria', 'Kotona'];
  var EXTRA_TITLES = cfg.extraTitles || ['Kaupungilla', 'Pieni salaisuus', 'Hellää huomiota'];
  var BLURBS = cfg.blurbs || {};
  var TEMPLATES = cfg.templates || {};
  var SOFT_KOTI = cfg.softKotitreffit || {};
  var PH = cfg.placeholders || {
    empty: 'Kirjoita vihje saajalle…',
    l1: 'Pieni vihje — älä paljasta kaikkea',
    l2: 'Seuraava kerros…'
  };

  function flash(btn, okText) {
    var prev = btn.textContent;
    btn.textContent = okText || 'Kopioitu!';
    setTimeout(function () { btn.textContent = prev; }, 1600);
  }

  async function copyText(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(text);
      return;
    }
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'absolute';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  }

  function randomId() {
    var a = new Uint8Array(8);
    if (window.crypto && crypto.getRandomValues) {
      crypto.getRandomValues(a);
    } else {
      for (var i = 0; i < 8; i++) a[i] = Math.floor(Math.random() * 256);
    }
    return Array.prototype.map.call(a, function (b) {
      return ('0' + b.toString(16)).slice(-2);
    }).join('');
  }

  function isCraft(editor) {
    return editor && editor.getAttribute('data-romant-craft') === '1';
  }

  function pohjaWrap(editor) {
    var form = editor ? editor.closest('form') : null;
    return form ? form.querySelector('[data-romant-pohja]') : null;
  }

  function exampleL1(title, editor) {
    var wrap = pohjaWrap(editor);
    var key = wrap ? (wrap.getAttribute('data-applied') || 'kotitreffit') : 'kotitreffit';
    var soft = !!(wrap && wrap.getAttribute('data-soft') === '1');
    if (key === 'kotitreffit' && soft && SOFT_KOTI[title]) {
      return SOFT_KOTI[title];
    }
    var tpl = TEMPLATES[key];
    if (tpl && tpl.sections) {
      for (var i = 0; i < tpl.sections.length; i++) {
        if (tpl.sections[i].title === title && tpl.sections[i].l1) {
          return tpl.sections[i].l1;
        }
      }
    }
    return BLURBS[title] || '';
  }

  function applyExample(card, editor) {
    if (!card) return;
    var titleInput = card.querySelector('[data-section-title]');
    var title = titleInput ? titleInput.value.trim() : '';
    var example = exampleL1(title, editor);
    if (!example) return;
    var first = card.querySelector('[data-level-text]');
    if (first) {
      first.value = example;
      first.placeholder = placeholderFor(0, example);
    }
    syncPreview(card);
    syncExampleButton(card, editor);
  }

  function syncExampleButton(card, editor) {
    var btn = card.querySelector('[data-use-example]');
    if (!btn) return;
    var titleInput = card.querySelector('[data-section-title]');
    var title = titleInput ? titleInput.value.trim() : '';
    btn.hidden = exampleL1(title, editor) === '';
  }

  function placeholderFor(li, value) {
    if (li === 0) {
      return value ? PH.l1 : PH.empty;
    }
    return PH.l2;
  }

  function weekdaysFi() {
    return ['Su', 'Ma', 'Ti', 'Ke', 'To', 'Pe', 'La'];
  }

  function formatSummaryLine(dateVal, timeVal, place) {
    if (!dateVal) return '';
    var parts = dateVal.split('-');
    if (parts.length < 3) return '';
    var dt = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    if (isNaN(dt.getTime())) return '';
    var wd = weekdaysFi()[dt.getDay()];
    var line = wd + ' ' + Number(parts[2]) + '.' + Number(parts[1]) + '.';
    if (timeVal) line += ' · ' + timeVal;
    if (place) line += ' · ' + place;
    return line;
  }

  function syncCraftDatetime(form) {
    if (!form) return;
    var dateEl = form.querySelector('[data-craft-date]');
    var timeEl = form.querySelector('[data-craft-time]');
    var hidden = form.querySelector('[data-craft-datetime]');
    var placeEl = form.querySelector('[data-craft-place]');
    var lineEl = form.querySelector('[data-craft-summary-line]');
    if (!dateEl || !timeEl || !hidden) return;
    var dateVal = dateEl.value || '';
    var timeVal = timeEl.value || '18:00';
    if (dateVal) hidden.value = dateVal + 'T' + timeVal;
    if (lineEl) {
      lineEl.textContent = formatSummaryLine(dateVal, timeVal, placeEl ? placeEl.value.trim() : '');
    }
  }

  function updateOletusCount(editor) {
    var el = editor.querySelector('[data-oletus-count]');
    if (!el) return;
    var n = editor.querySelectorAll('[data-section-card]').length;
    el.textContent = n + ' ' + (i18n.oletusta || 'oletusta');
  }

  function updateChips(editor) {
    var wrap = editor.querySelector('[data-section-chips]');
    if (!wrap) return;
    var used = [];
    editor.querySelectorAll('[data-section-title]').forEach(function (input) {
      used.push((input.value || '').trim());
    });
    var catalog = isCraft(editor) ? EXTRA_TITLES.concat(DEFAULT_TITLES) : DEFAULT_TITLES.concat(EXTRA_TITLES);
    var seen = {};
    var available = [];
    catalog.forEach(function (title) {
      if (seen[title]) return;
      seen[title] = true;
      if (used.indexOf(title) === -1) available.push(title);
    });
    wrap.innerHTML = '';
    var atMax = used.length >= MAX_SECTIONS;
    available.forEach(function (title) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'romant-chip';
      btn.setAttribute('data-add-chip', title);
      btn.textContent = title;
      if (atMax) btn.disabled = true;
      wrap.appendChild(btn);
    });
    wrap.hidden = available.length === 0;
  }

  function reindexSections(editor) {
    var list = editor.querySelector('[data-sections-list]');
    if (!list) return;
    var cards = list.querySelectorAll('[data-section-card]');
    cards.forEach(function (card, si) {
      card.setAttribute('data-index', String(si));
      var num = card.querySelector('[data-section-num]');
      if (num) num.textContent = String(si + 1);
      var idInput = card.querySelector('[data-section-id]');
      var titleInput = card.querySelector('[data-section-title]');
      if (idInput) idInput.name = 'romant_sections[' + si + '][id]';
      if (titleInput) titleInput.name = 'romant_sections[' + si + '][title]';
      var rows = card.querySelectorAll('[data-level-row]');
      rows.forEach(function (row, li) {
        var ta = row.querySelector('[data-level-text]');
        if (ta) {
          ta.name = 'romant_sections[' + si + '][levels][' + li + ']';
          if (li === 0) ta.setAttribute('required', 'required');
          else ta.removeAttribute('required');
          ta.placeholder = placeholderFor(li, (ta.value || '').trim());
        }
        var labText = row.querySelector('[data-level-label]');
        if (labText) {
          labText.textContent = li === 0
            ? (i18n.hint || 'Vihje')
            : ((i18n.level || 'Taso') + ' ' + (li + 1));
        }
        var reqEl = row.querySelector('[data-level-req]');
        if (reqEl) reqEl.hidden = li !== 0;
      });
      var removeBtn = card.querySelector('[data-remove-section]');
      if (removeBtn) removeBtn.hidden = cards.length <= 1;
      syncPreview(card);
      syncExampleButton(card, editor);
      updateSoftCap(card);
    });
    updateOletusCount(editor);
    updateChips(editor);
  }

  function updateSoftCap(card) {
    var rows = card.querySelectorAll('[data-level-row]');
    var warn = card.querySelector('[data-soft-cap-warn]');
    if (warn) warn.hidden = rows.length <= SOFT_MAX;
    var addBtn = card.querySelector('[data-add-level]');
    if (addBtn) addBtn.hidden = rows.length >= HARD_MAX;
  }

  function syncPreview(card) {
    var preview = card.querySelector('[data-section-preview]');
    var titleInput = card.querySelector('[data-section-title]');
    var first = card.querySelector('[data-level-text]');
    if (preview && first) preview.textContent = (first.value || '').trim();
    var badge = card.querySelector('[data-oletus-badge]');
    if (badge && titleInput) {
      badge.hidden = DEFAULT_TITLES.indexOf(titleInput.value.trim()) === -1;
    }
  }

  function buildLevelRow(si, li, required, value) {
    var row = document.createElement('div');
    row.className = 'romant-level-row';
    row.setAttribute('data-level-row', '');
    var label = document.createElement('label');
    var labSpan = document.createElement('span');
    labSpan.setAttribute('data-level-label', '');
    labSpan.textContent = li === 0
      ? (i18n.hint || 'Vihje')
      : ((i18n.level || 'Taso') + ' ' + (li + 1));
    label.appendChild(labSpan);
    label.appendChild(document.createTextNode(' '));
    var req = document.createElement('span');
    req.className = 'req';
    req.setAttribute('data-level-req', '');
    req.textContent = '*';
    req.hidden = !required;
    label.appendChild(req);
    var ta = document.createElement('textarea');
    ta.name = 'romant_sections[' + si + '][levels][' + li + ']';
    ta.rows = 2;
    ta.maxLength = 800;
    ta.value = value || '';
    ta.placeholder = placeholderFor(li, ta.value.trim());
    ta.setAttribute('data-level-text', '');
    if (required) ta.setAttribute('required', 'required');
    label.appendChild(ta);
    row.appendChild(label);
    return row;
  }

  function buildSectionCard(si, opts) {
    opts = opts || {};
    var title = opts.title || '';
    var l1 = opts.l1 || '';
    var collapsed = !!opts.collapsed;

    var card = document.createElement('div');
    card.className = 'romant-section-card romant-card' + (collapsed ? ' is-collapsed' : '');
    card.setAttribute('data-section-card', '');
    card.setAttribute('data-index', String(si));

    var head = document.createElement('div');
    head.className = 'romant-section-card-head';

    var num = document.createElement('span');
    num.className = 'romant-section-num';
    num.setAttribute('data-section-num', '');
    num.setAttribute('aria-hidden', 'true');
    num.textContent = String(si + 1);

    var titleLabel = document.createElement('label');
    titleLabel.className = 'romant-section-title-label';

    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'romant_sections[' + si + '][id]';
    idInput.value = randomId();
    idInput.setAttribute('data-section-id', '');

    var titleInput = document.createElement('input');
    titleInput.type = 'text';
    titleInput.name = 'romant_sections[' + si + '][title]';
    titleInput.maxLength = 80;
    titleInput.placeholder = i18n.sectionPh || 'Elokuvahetki';
    titleInput.required = true;
    titleInput.value = title;
    titleInput.setAttribute('data-section-title', '');

    var sr = document.createElement('span');
    sr.className = 'screen-reader-text';
    sr.textContent = i18n.sectionTitle || 'Osion otsikko';
    titleLabel.appendChild(sr);
    titleLabel.appendChild(idInput);
    titleLabel.appendChild(titleInput);

    var oletus = document.createElement('span');
    oletus.className = 'romant-oletus';
    oletus.setAttribute('data-oletus-badge', '');
    oletus.hidden = DEFAULT_TITLES.indexOf(title) === -1;
    oletus.textContent = 'Oletus';

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'romant-btn romant-btn-ghost romant-btn-sm';
    removeBtn.setAttribute('data-remove-section', '');
    removeBtn.textContent = i18n.removeSection || 'Poista';

    head.appendChild(num);
    head.appendChild(titleLabel);
    head.appendChild(oletus);
    head.appendChild(removeBtn);

    var preview = document.createElement('p');
    preview.className = 'romant-section-preview';
    preview.setAttribute('data-section-preview', '');
    preview.textContent = l1;

    var levels = document.createElement('div');
    levels.className = 'romant-section-levels';
    levels.setAttribute('data-section-levels', '');
    levels.appendChild(buildLevelRow(si, 0, true, l1));
    for (var i = 1; i < DEFAULT_LEVELS; i++) {
      var quiet = buildLevelRow(si, i, false, '');
      quiet.classList.add('is-quiet');
      levels.appendChild(quiet);
    }

    var warn = document.createElement('p');
    warn.className = 'romant-soft-cap-warn';
    warn.setAttribute('data-soft-cap-warn', '');
    warn.hidden = true;
    warn.textContent = i18n.softCapWarn || 'Pehmeä raja (10) ylitetty — pidä tasot maltillisina.';

    var addLevel = document.createElement('button');
    addLevel.type = 'button';
    addLevel.className = 'romant-btn romant-btn-secondary romant-btn-sm';
    addLevel.setAttribute('data-add-level', '');
    addLevel.textContent = i18n.addLevel || 'Lisää taso';

    var foot = document.createElement('div');
    foot.className = 'romant-section-foot';
    var useEx = document.createElement('button');
    useEx.type = 'button';
    useEx.className = 'romant-use-example';
    useEx.setAttribute('data-use-example', '');
    useEx.textContent = i18n.useExample || 'Käytä esimerkkiä';
    useEx.hidden = exampleL1(title, null) === '';
    var napauta = document.createElement('p');
    napauta.className = 'romant-napauta';
    napauta.setAttribute('data-napauta', '');
    napauta.textContent = i18n.napauta || 'napauta muokataksesi';
    foot.appendChild(useEx);
    foot.appendChild(napauta);

    card.appendChild(head);
    card.appendChild(preview);
    card.appendChild(levels);
    card.appendChild(warn);
    card.appendChild(addLevel);
    card.appendChild(foot);
    return card;
  }

  function applyTemplate(editor, key, opts) {
    opts = opts || {};
    var tpl = TEMPLATES[key];
    if (!tpl || !tpl.sections) return;
    var list = editor.querySelector('[data-sections-list]');
    if (!list) return;
    var sections = tpl.sections.slice(0, MAX_SECTIONS);
    list.innerHTML = '';
    sections.forEach(function (sec, si) {
      list.appendChild(buildSectionCard(si, {
        title: sec.title,
        l1: '',
        collapsed: isCraft(editor)
      }));
    });
    var form = editor.closest('form');
    var place = form ? form.querySelector('[data-craft-place]') : null;
    if (place && Object.prototype.hasOwnProperty.call(tpl, 'location')) {
      if (place.value === '' || place.value === 'Kotona' || tpl.location === 'Kotona') {
        place.value = tpl.location || '';
      }
    }
    reindexSections(editor);
    syncCraftDatetime(form);
  }

  function addChip(editor, title) {
    var list = editor.querySelector('[data-sections-list]');
    if (!list) return;
    var n = list.querySelectorAll('[data-section-card]').length;
    if (n >= MAX_SECTIONS) {
      alert(i18n.maxSections || 'Enintään 3 osiota.');
      return;
    }
    var allowed = DEFAULT_TITLES.concat(EXTRA_TITLES);
    if (allowed.indexOf(title) === -1) return;
    list.appendChild(buildSectionCard(n, {
      title: title,
      l1: '',
      collapsed: false
    }));
    reindexSections(editor);
  }

  function initSectionsEditor(editor) {
    editor.addEventListener('click', function (e) {
      var useExBtn = e.target.closest('[data-use-example]');
      if (useExBtn && editor.contains(useExBtn)) {
        e.preventDefault();
        applyExample(useExBtn.closest('[data-section-card]'), editor);
        return;
      }

      var cardHit = e.target.closest('[data-section-card]');
      if (cardHit && editor.contains(cardHit) && cardHit.classList.contains('is-collapsed')) {
        if (e.target.closest('[data-remove-section]')) {
          /* fall through */
        } else {
          e.preventDefault();
          cardHit.classList.remove('is-collapsed');
          var first = cardHit.querySelector('[data-level-text]');
          if (first) first.focus();
          return;
        }
      }

      var addLevel = e.target.closest('[data-add-level]');
      if (addLevel && editor.contains(addLevel)) {
        e.preventDefault();
        var card = addLevel.closest('[data-section-card]');
        if (!card) return;
        var levels = card.querySelector('[data-section-levels]');
        var count = levels.querySelectorAll('[data-level-row]').length;
        if (count >= HARD_MAX) {
          alert(i18n.hardCap || 'Enintään 20 tasoa osiossa.');
          return;
        }
        var si = parseInt(card.getAttribute('data-index') || '0', 10);
        levels.appendChild(buildLevelRow(si, count, false, ''));
        updateSoftCap(card);
        reindexSections(editor);
        return;
      }

      var removeSec = e.target.closest('[data-remove-section]');
      if (removeSec && editor.contains(removeSec)) {
        e.preventDefault();
        var card2 = removeSec.closest('[data-section-card]');
        var list = editor.querySelector('[data-sections-list]');
        if (!card2 || !list) return;
        if (list.querySelectorAll('[data-section-card]').length <= 1) return;
        card2.remove();
        reindexSections(editor);
        return;
      }

      var chip = e.target.closest('[data-add-chip]');
      if (chip && editor.contains(chip) && !chip.disabled) {
        e.preventDefault();
        addChip(editor, chip.getAttribute('data-add-chip') || '');
      }
    });

    editor.querySelectorAll('[data-section-card]').forEach(updateSoftCap);
    reindexSections(editor);
    editor.addEventListener('input', function (e) {
      var title = e.target.closest('[data-section-title]');
      var level = e.target.closest('[data-level-text]');
      if (!title && !level) return;
      if (!editor.contains(e.target)) return;
      var card = e.target.closest('[data-section-card]');
      if (card) syncPreview(card);
      if (title) updateChips(editor);
      if (level) {
        var li = Array.prototype.indexOf.call(
          card.querySelectorAll('[data-level-text]'),
          level
        );
        level.placeholder = placeholderFor(li, (level.value || '').trim());
      }
    });
  }

  function initPohja() {
    document.querySelectorAll('[data-romant-pohja]').forEach(function (wrap) {
      var form = wrap.closest('[data-romant-craft-form]') || wrap.closest('form');
      var editor = form ? form.querySelector('[data-romant-sections-editor]') : null;
      if (!editor) return;
      wrap.setAttribute('data-applied', 'kotitreffit');
      wrap.setAttribute('data-soft', '0');
      wrap.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pohja]');
        if (!btn || !wrap.contains(btn)) return;
        e.preventDefault();
        var key = btn.getAttribute('data-pohja') || '';
        if (!TEMPLATES[key]) return;
        var applied = wrap.getAttribute('data-applied') || '';
        if (applied === key) return;
        var soft = key === 'kotitreffit' && applied !== '';
        applyTemplate(editor, key, { soft: soft });
        wrap.setAttribute('data-applied', key);
        wrap.setAttribute('data-soft', soft ? '1' : '0');
        wrap.querySelectorAll('[data-pohja]').forEach(function (el) {
          var on = el === btn;
          el.classList.toggle('is-selected', on);
          el.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
      });
    });
  }

  function initCraftSummary() {
    document.querySelectorAll('[data-romant-craft-form]').forEach(function (form) {
      syncCraftDatetime(form);
      var summary = form.querySelector('[data-craft-summary]');
      if (summary) {
        summary.addEventListener('click', function (e) {
          if (summary.classList.contains('is-collapsed') && !e.target.closest('input')) {
            summary.classList.remove('is-collapsed');
            var dateEl = summary.querySelector('[data-craft-date]');
            if (dateEl) dateEl.focus();
          }
        });
      }
      form.addEventListener('input', function (e) {
        if (e.target.closest('[data-craft-date], [data-craft-time], [data-craft-place], [data-craft-summary]')) {
          syncCraftDatetime(form);
        }
      });
      form.addEventListener('change', function (e) {
        if (e.target.closest('[data-craft-date], [data-craft-time]')) {
          syncCraftDatetime(form);
        }
      });
    });
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-romant-copy]');
    if (btn) {
      var sel = btn.getAttribute('data-romant-copy');
      var el = sel ? document.querySelector(sel) : null;
      var text = el ? (el.textContent || '').trim() : '';
      if (!text) return;
      copyText(text).then(function () { flash(btn); }).catch(function () {});
      return;
    }

    var btn2 = e.target.closest('[data-romant-copy-text]');
    if (btn2) {
      var t = btn2.getAttribute('data-romant-copy-text') || '';
      if (!t) return;
      copyText(t).then(function () { flash(btn2); }).catch(function () {});
    }
  });

  /** Keep "Nimi kuittiin" in sync with kutsujan nimi until the user edits it. */
  function initReceiptNameSync() {
    var inviter = document.getElementById('romant_inviter_name_pay');
    var receipt = document.getElementById('romant_receipt_name_pay');
    if (!inviter || !receipt) return;
    var locked = false;
    if (receipt.value && receipt.value !== inviter.value) {
      locked = true;
    }
    receipt.addEventListener('input', function () {
      locked = receipt.value.trim() !== '' && receipt.value !== inviter.value;
    });
    inviter.addEventListener('input', function () {
      if (locked) return;
      receipt.value = inviter.value;
    });
  }

  function init() {
    document.querySelectorAll('[data-romant-sections-editor]').forEach(initSectionsEditor);
    initPohja();
    initCraftSummary();
    initReceiptNameSync();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
