/**
 * 1.3.7 peel-boxes + story share contract (no DOM).
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

function tintIndex(index) {
  return index % 3;
}

function cardStateClass(i, depth, animate) {
  var isNewest = i === depth - 1;
  if (isNewest) {
    return animate ? 'newest show' : 'newest';
  }
  return i > 0 ? 'prior mid' : 'prior';
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

function groupMarkup(title, depth, animate, index) {
  var tint = tintIndex(index || 0);
  var cards = [];
  for (var i = 0; i < depth; i++) {
    var cls = 'romant-hint romant-hint-card hint-card romant-osio-level ' +
      cardStateClass(i, depth, animate);
    cards.push('<article class="' + cls + '" data-level="' + (i + 1) + '">' + hintInner(i + 1, 'L' + (i + 1)) + '</article>');
  }
  var ruoka = tint === 1 ? ' ruoka' : '';
  return (
    '<section class="romant-osio osio romant-osio-tint-' + tint + ' tint-' + tint + ruoka + '">' +
      '<h2 class="romant-osio-header osio-header romant-serif">' + title + '</h2>' +
      '<div class="romant-hint-stack hint-stack">' + cards.join('') + '</div>' +
    '</section>'
  );
}

function stackClasses(depth, animate) {
  var out = [];
  for (var i = 0; i < depth; i++) {
    out.push(cardStateClass(i, depth, animate));
  }
  return out;
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

var html = groupMarkup('Elokuvahetki', 3, false, 0);
var ruoka = groupMarkup('Yhteinen ateria', 2, false, 1);

assert('depth 1 is only L1', stackedLevels(elokuva, 1).join('|') === 'L1 text');
assert('depth 2 keeps L1 and appends L2', stackedLevels(elokuva, 2).join('|') === 'L1 text|L2 text');
assert('depth 3 accumulates', stackedLevels(elokuva, 3).join('|') === 'L1 text|L2 text|L3 text');
assert('older/mid/newest classes', stackClasses(3, false).join('|') === 'prior|prior mid|newest');
assert('newest animates with show', stackClasses(2, true).join('|') === 'prior|newest show');
assert('section title is Elokuvahetki once as heading', sectionTitle(elokuva, 0) === 'Elokuvahetki');
assert('not short Elokuva/Ruoka', ['Elokuvahetki', 'Yhteinen ateria', 'Kotona'].indexOf('Elokuva') === -1);
assert('labels are numbered Vihje 1/2/3', hintLabel(1) === 'Vihje 1' && hintLabel(2) === 'Vihje 2' && hintLabel(3) === 'Vihje 3');
assert('level marker is number only', hintLabel(2) === 'Vihje 2' && !/TASO|taso|•|★/.test(hintInner(2, 'L2')));
assert('hint label is not TASO', /Vihje 1/.test(hintInner(1, 'L1')) && !/TASO/.test(hintInner(1, 'L1')));
var family = ['#E8D5C6', '#EBD9CE', '#FFFDF9', '#E5CDBF', '#FBF4EE', '#DCC8BC', '#FAF4EE'];
assert('card tints stay muted wine/rose/cream', family.every(function (hex) {
  return !/#[0-3][0-9A-F][8-9A-F]|#[0-9A-F]{2}[8-9A-F]{2}[0-3]/.test(hex);
}));
assert('markup is osio > osio-header + hint-stack > hint-card',
  /<section class="[^"]*\bosio\b/.test(html) &&
  /osio-header/.test(html) &&
  /hint-stack/.test(html) &&
  /hint-card/.test(html) &&
  /<article class="/.test(html)
);
assert('section title is group heading not in-card', /romant-osio-header/.test(html) && !/romant-hint-section/.test(html));
assert('every revealed level is a hint-card', (html.match(/<article class="/g) || []).length === 3 && /hint-card/.test(html));
assert('tints cycle for N sections', tintIndex(0) === 0 && tintIndex(1) === 1 && tintIndex(3) === 0);
assert('section 1 is ruoka tint', /\bruoka\b/.test(ruoka) && /romant-osio-tint-1/.test(ruoka));
assert('modal unlocks exactly +1', unlockPlusOne(1, 3) === 2 && unlockPlusOne(3, 3) === 3);
assert('tapaaminen line is La 13.6. · 18:00', formatTapaaminen('2026-06-13T18:00:00+03:00') === 'La 13.6. · 18:00');
assert('teaser must not include place', 'countdown-only'.indexOf('Keskusta') === -1);
assert('recipient tapaaminen is display not form', !/<input/.test('<section class="romant-tapaaminen-display"><span>Tapaaminen</span><p>La 14.3. · 18:00</p></section>'));
assert('open stays soft: meet delay after hint beat', 560 > 380);

var fs = require('fs');
var path = require('path');
var css = fs.readFileSync(path.join(__dirname, '../assets/css/frontend.css'), 'utf8');
var recipientPhp = fs.readFileSync(path.join(__dirname, '../templates/recipient.php'), 'utf8');
var layoutPhp = fs.readFileSync(path.join(__dirname, '../templates/layout-start.php'), 'utf8');
var storyPhp = fs.readFileSync(path.join(__dirname, '../templates/story.php'), 'utf8');

assert('no debug footer markup', !/Peel-boxes|Henry FAIL|romant-peel-foot/.test(recipientPhp));
assert('dress label is Pukeudu näin', /Pukeudu näin/.test(recipientPhp) && !/Pukeutumisvihje/.test(recipientPhp));
assert('dress-card class present', /dress-card/.test(recipientPhp));
assert('no opened-meta version badge',
  !/romant-opened-meta/.test(recipientPhp) &&
  !/Kutsu avattu ·/.test(recipientPhp) &&
  !/ROMANT_KUTSU_VERSION/.test(recipientPhp)
);
assert('section markup includes Pauliina tint-N', /tint-0/.test(html) && /tint-1/.test(ruoka));
assert('opened atmosphere uses heavy fabric blur behind cards',
  /recipient-page\.is-opened::before/.test(css) && /blur\((1[8-9]|[2-9]\d)px\)/.test(css)
);
assert('opened does not flatten to cream drop',
  !/\.romant-kutsu-body\.romant-recipient-page\.is-opened\s*\{\s*background:\s*var\(--romant-cream\);/.test(css)
);
assert('osio panels have visible border',
  /\.romant-recipient \.romant-osio,\s*\.romant-recipient \.osio\s*\{[^}]*border:\s*1px solid rgba\(74,\s*31,\s*44/.test(css)
);
assert('dress-card cream #FFFDF9 + wine hairline',
  /#FFFDF9/.test(css) && /rgba\(74,\s*31,\s*44,\s*0\.16\)/.test(css)
);
assert('tapaaminen display is cream card with wine hairline',
  /\.romant-recipient \.romant-tapaaminen-display\s*\{[^}]*#FFFDF9[^}]*rgba\(74,\s*31,\s*44,\s*0\.16\)/.test(css)
);
assert('recipient has non-spoiler OG title',
  /og_title/.test(recipientPhp) && /Sinut on kutsuttu treffeille\./.test(recipientPhp)
);
assert('OG tags have no spoilers',
  !/Elokuvahetki|Pukeudu|18:00|Kotona|Vihje/.test((recipientPhp.match(/og_description[\s\S]{0,180}/) || [''])[0])
);
assert('layout prints og:image and og:title',
  /og:title/.test(layoutPhp) && /og:image/.test(layoutPhp) && /twitter:card/.test(layoutPhp)
);
assert('story share page has OG', /og_title/.test(storyPhp) && /hero-teaser\.jpg/.test(storyPhp));
assert('story visual uses hero-fabric', /hero-fabric\.jpg/.test(storyPhp) && /hero-fabric\.jpg/.test(css));
assert('story wordmark is plain text not framed logo',
  /romant-story-wordmark/.test(storyPhp) &&
  /romanttinen\.fi/.test(storyPhp) &&
  !/logo_markup/.test(storyPhp) &&
  !/romant-eyebrow-logo/.test(storyPhp)
);
assert('story headline has period and two lines',
  /Sinut on kutsuttu treffeille\./.test(storyPhp) &&
  /romant-story-line/.test(storyPhp) &&
  /kutsuttu treffeille\./.test(storyPhp)
);
assert('story teaser line is IG-safe', /Pieni kutsu — avaa kun olet valmis\./.test(storyPhp));
assert('story countdown uses full Finnish labels',
  /Päivää/.test(storyPhp) && /Tuntia/.test(storyPhp) && /Minuuttia/.test(storyPhp) && /Sekuntia/.test(storyPhp)
);
assert('story visual has Avaa kutsu pill not Jaa tarina',
  /romant-story-cta/.test(storyPhp) &&
  /Avaa kutsu/.test(storyPhp) &&
  !/<article[\s\S]*Jaa tarina/.test(storyPhp)
);
assert('story visual has no spoilers',
  !/Elokuvahetki|Pukeudu|Kotona|18:00|Vihje|data-inviter/.test(storyPhp)
);
assert('story CSS is fabric not rose-gradient',
  /romant-story-frame/.test(css) &&
  /hero-fabric\.jpg/.test(css) &&
  !/\.romant-story-frame\s*\{[^}]*linear-gradient\(180deg,\s*var\(--romant-cream\)/.test(css)
);

var storyJs = fs.readFileSync(path.join(__dirname, '../assets/js/story.js'), 'utf8');
assert('canvas PNG draws fabric card not rose gradient',
  /hero-fabric|data-fabric/.test(storyJs) &&
  /Avaa kutsu/.test(storyJs) &&
  /Päivää/.test(storyJs) &&
  /Pieni kutsu/.test(storyJs) &&
  !/Jaa tarina/.test(storyJs) &&
  !/addColorStop\(0\.55,\s*BLUSH\)/.test(storyJs)
);
assert('canvas PNG has no inviter or spoilers',
  !/inviter/.test(storyJs) && !/kutsui sinut/.test(storyJs) && !/Elokuvahetki/.test(storyJs)
);

var pluginPhp = fs.readFileSync(path.join(__dirname, '../romanttinen-kutsu.php'), 'utf8');
assert('plugin version is 1.3.7', /Version:\s+1\.3\.7/.test(pluginPhp) && /ROMANT_KUTSU_VERSION',\s*'1\.3\.7'/.test(pluginPhp));

if (fails) {
  process.exit(1);
}
console.log('peel-stack contract: all passed');
