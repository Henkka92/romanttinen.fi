/**
 * Copy helpers + sections editor (create / manage).
 */
(function () {
  'use strict';

  var cfg = window.romantManage || {};
  var MAX_SECTIONS = cfg.maxSections || 3;
  var DEFAULT_LEVELS = cfg.defaultLevels || 3;
  var SOFT_MAX = cfg.softMaxLevels || 10;
  var HARD_MAX = cfg.hardMaxLevels || 20;
  var i18n = cfg.i18n || {};

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

  function reindexSections(editor) {
    var list = editor.querySelector('[data-sections-list]');
    if (!list) return;
    var cards = list.querySelectorAll('[data-section-card]');
    cards.forEach(function (card, si) {
      card.setAttribute('data-index', String(si));
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
        }
        var labText = row.querySelector('[data-level-label]');
        if (labText) {
          labText.textContent = (i18n.level || 'Taso') + ' ' + (li + 1);
        }
        var reqEl = row.querySelector('[data-level-req]');
        if (reqEl) reqEl.hidden = li !== 0;
      });
      var removeBtn = card.querySelector('[data-remove-section]');
      if (removeBtn) removeBtn.hidden = cards.length <= 1;
      updateSoftCap(card);
    });
    var addSec = editor.querySelector('[data-add-section]');
    if (addSec) addSec.hidden = cards.length >= MAX_SECTIONS;
  }

  function updateSoftCap(card) {
    var rows = card.querySelectorAll('[data-level-row]');
    var warn = card.querySelector('[data-soft-cap-warn]');
    if (warn) warn.hidden = rows.length <= SOFT_MAX;
    var addBtn = card.querySelector('[data-add-level]');
    if (addBtn) addBtn.hidden = rows.length >= HARD_MAX;
  }

  function buildLevelRow(si, li, required) {
    var row = document.createElement('div');
    row.className = 'romant-level-row';
    row.setAttribute('data-level-row', '');
    var label = document.createElement('label');
    var labSpan = document.createElement('span');
    labSpan.setAttribute('data-level-label', '');
    labSpan.textContent = (i18n.level || 'Taso') + ' ' + (li + 1);
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
    ta.placeholder = i18n.levelPh || 'Kirjoita tämän tason teksti…';
    ta.setAttribute('data-level-text', '');
    if (required) ta.setAttribute('required', 'required');
    label.appendChild(ta);
    row.appendChild(label);
    return row;
  }

  function buildSectionCard(si) {
    var card = document.createElement('div');
    card.className = 'romant-section-card';
    card.setAttribute('data-section-card', '');
    card.setAttribute('data-index', String(si));

    var head = document.createElement('div');
    head.className = 'romant-section-card-head';

    var titleLabel = document.createElement('label');
    titleLabel.className = 'romant-section-title-label';
    titleLabel.appendChild(document.createTextNode(i18n.sectionTitle || 'Osion otsikko'));

    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'romant_sections[' + si + '][id]';
    idInput.value = randomId();
    idInput.setAttribute('data-section-id', '');

    var titleInput = document.createElement('input');
    titleInput.type = 'text';
    titleInput.name = 'romant_sections[' + si + '][title]';
    titleInput.maxLength = 80;
    titleInput.placeholder = i18n.sectionPh || 'Esim. Leffa';
    titleInput.required = true;
    titleInput.setAttribute('data-section-title', '');

    titleLabel.appendChild(idInput);
    titleLabel.appendChild(titleInput);

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'romant-btn romant-btn-ghost romant-btn-sm';
    removeBtn.setAttribute('data-remove-section', '');
    removeBtn.textContent = i18n.removeSection || 'Poista osio';

    head.appendChild(titleLabel);
    head.appendChild(removeBtn);

    var levels = document.createElement('div');
    levels.className = 'romant-section-levels';
    levels.setAttribute('data-section-levels', '');
    for (var i = 0; i < DEFAULT_LEVELS; i++) {
      levels.appendChild(buildLevelRow(si, i, i === 0));
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

    card.appendChild(head);
    card.appendChild(levels);
    card.appendChild(warn);
    card.appendChild(addLevel);
    return card;
  }

  function initSectionsEditor(editor) {
    editor.addEventListener('click', function (e) {
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
        levels.appendChild(buildLevelRow(si, count, false));
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

      var addSec = e.target.closest('[data-add-section]');
      if (addSec && editor.contains(addSec)) {
        e.preventDefault();
        var list2 = editor.querySelector('[data-sections-list]');
        var n = list2.querySelectorAll('[data-section-card]').length;
        if (n >= MAX_SECTIONS) {
          alert(i18n.maxSections || 'Enintään 3 osiota.');
          return;
        }
        list2.appendChild(buildSectionCard(n));
        reindexSections(editor);
      }
    });

    editor.querySelectorAll('[data-section-card]').forEach(updateSoftCap);
    reindexSections(editor);
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
    var lastSynced = inviter.value;
    if (receipt.value && receipt.value !== inviter.value) {
      locked = true;
    }
    receipt.addEventListener('input', function () {
      locked = receipt.value.trim() !== '' && receipt.value !== inviter.value;
    });
    inviter.addEventListener('input', function () {
      if (locked) return;
      receipt.value = inviter.value;
      lastSynced = inviter.value;
    });
  }

  function init() {
    document.querySelectorAll('[data-romant-sections-editor]').forEach(initSectionsEditor);
    initReceiptNameSync();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
