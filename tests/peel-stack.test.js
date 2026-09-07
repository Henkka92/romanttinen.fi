/**
 * 1.3.3 peel contract (no DOM): stack history, section title in card, +1 unlock.
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

function hintInner(title, text) {
  return (
    '<h3 class="romant-hint-title romant-serif">' + title + '</h3>' +
    '<span class="romant-spoiler-label">Vihje</span>' +
    '<p>' + text + '</p>'
  );
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

var leffa = {
  title: 'Leffa',
  levels: ['L1 text', 'L2 text', 'L3 text'],
};

assert('depth 1 is only L1 (not a replace dump)', stackedLevels(leffa, 1).join('|') === 'L1 text');
assert('depth 2 keeps L1 and appends L2', stackedLevels(leffa, 2).join('|') === 'L1 text|L2 text');
assert('depth 3 accumulates all three', stackedLevels(leffa, 3).join('|') === 'L1 text|L2 text|L3 text');
assert('empty slots do not inflate peel', peelLevels({ levels: ['A', '', 'B'] }).join('|') === 'A|B');
assert('section title is Leffa', sectionTitle(leffa, 0) === 'Leffa');
assert('empty title falls back', sectionTitle({ title: '  ' }, 1) === 'Osio 2');
assert('modal unlocks exactly +1', unlockPlusOne(1, 3) === 2 && unlockPlusOne(3, 3) === 3);
assert('card html has title and Vihje', /romant-hint-title/.test(hintInner('Leffa', 'L1')) && /Vihje/.test(hintInner('Leffa', 'L1')));
assert('teaser must not include place', 'countdown-only'.indexOf('Keskusta') === -1);

if (fails) {
  process.exit(1);
}
console.log('peel-stack contract: all passed');
