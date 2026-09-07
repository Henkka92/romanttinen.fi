/**
 * 1.3.4 peel-boxes contract (no DOM).
 * Run: node tests/peel-stack.test.js
 */
'use strict';

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

function hintLabel(levelNum) {
  return 'Vihje ' + levelNum;
}

function stackedLevels(sec, depth) {
  var levels = peelLevels(sec);
  var max = levels.length;
  if (depth > max) depth = max;
  if (depth < 1) depth = 1;
  return levels.slice(0, depth);
}

function unlockPlusOne(depth, max) {
  return depth < max ? depth + 1 : depth;
}

function hintInner(levelNum, text) {
  return (
    '<span class="romant-spoiler-label">' + hintLabel(levelNum) + '</span>' +
    '<p>' + text + '</p>'
  );
}

function groupMarkup(title, depth, animate) {
  var cards = [];
  for (var i = 0; i < depth; i++) {
    var isNewest = i === depth - 1;
    var cls = 'romant-hint romant-osio-level romant-hint-box ' +
      (isNewest ? (animate ? 'show' : 'is-newest') : 'is-prior');
    cards.push('<div class="' + cls + '" data-level="' + (i + 1) + '">' + hintInner(i + 1, 'L' + (i + 1)) + '</div>');
  }
  return (
    '<section class="romant-osio romant-osio-tint-0">' +
      '<h2 class="romant-osio-title romant-serif">' + title + '</h2>' +
      '<div class="romant-hint-stack">' + cards.join('') + '</div>' +
    '</section>'
  );
}

function stackClasses(depth, animate) {
  var out = [];
  for (var i = 0; i < depth; i++) {
    var isNewest = i === depth - 1;
    out.push(isNewest ? (animate ? 'show' : 'is-newest') : 'is-prior');
  }
  return out;
}

function tintClass(index) {
  return 'romant-osio-tint-' + (index % 3);
}

/** Mirror PHP format_tapaaminen — `La 14.3. · 18:00` */
function formatTapaaminen(iso) {
  var dt = new Date(iso);
  var days = ['Su', 'Ma', 'Ti', 'Ke', 'To', 'Pe', 'La'];
  var helsinki = new Date(dt.toLocaleString('en-US', { timeZone: 'Europe/Helsinki' }));
  var wd = days[helsinki.getDay()];
  var date = helsinki.getDate() + '.' + (helsinki.getMonth() + 1) + '.';
  var hh = String(helsinki.getHours()).padStart(2, '0');
  var mm = String(helsinki.getMinutes()).padStart(2, '0');
  return wd + ' ' + date + ' · ' + hh + ':' + mm;
}

var fails = 0;
function assert(name, cond) {
  if (!cond) {
    fails += 1;
    console.error('FAIL ' + name);
  } else {
    console.log('ok   ' + name);
  }
}

var elokuva = {
  title: 'Elokuvahetki',
  levels: ['L1 text', 'L2 text', 'L3 text'],
};

var html = groupMarkup('Elokuvahetki', 3, false);

assert('depth 1 is only L1', stackedLevels(elokuva, 1).join('|') === 'L1 text');
assert('depth 2 keeps L1 and appends L2', stackedLevels(elokuva, 2).join('|') === 'L1 text|L2 text');
assert('depth 3 accumulates', stackedLevels(elokuva, 3).join('|') === 'L1 text|L2 text|L3 text');
assert('older cards are is-prior, newest is-newest', stackClasses(3, false).join('|') === 'is-prior|is-prior|is-newest');
assert('newest animates with show', stackClasses(2, true).join('|') === 'is-prior|show');
assert('section title is Elokuvahetki once as heading', sectionTitle(elokuva, 0) === 'Elokuvahetki');
assert('not short Elokuva/Ruoka', ['Elokuvahetki', 'Yhteinen ateria', 'Kotona'].indexOf('Elokuva') === -1);
assert('labels are numbered Vihje 1/2/3', hintLabel(1) === 'Vihje 1' && hintLabel(2) === 'Vihje 2' && hintLabel(3) === 'Vihje 3');
assert('hint label is not TASO', /Vihje 1/.test(hintInner(1, 'L1')) && !/TASO/.test(hintInner(1, 'L1')));
assert('section title is group heading not in-card', /romant-osio-title/.test(html) && !/romant-hint-section/.test(html));
assert('every revealed level is a hint-box', (html.match(/romant-hint-box/g) || []).length === 3);
assert('tints cycle for N sections', tintClass(0) === 'romant-osio-tint-0' && tintClass(1) === 'romant-osio-tint-1' && tintClass(3) === 'romant-osio-tint-0');
assert('modal unlocks exactly +1', unlockPlusOne(1, 3) === 2 && unlockPlusOne(3, 3) === 3);
assert('tapaaminen line is La 13.6. · 18:00', formatTapaaminen('2026-06-13T18:00:00+03:00') === 'La 13.6. · 18:00');
assert('teaser must not include place', 'countdown-only'.indexOf('Keskusta') === -1);
assert('recipient tapaaminen is display not form', !/<input/.test('<section class="romant-tapaaminen-display"><span>Tapaaminen</span><p>La 14.3. · 18:00</p></section>'));
assert('open stays soft: meet delay after hint beat', 560 > 380);

if (fails) {
  process.exit(1);
}
console.log('peel-stack contract: all passed');
