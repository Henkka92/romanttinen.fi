/**
 * 1.3.7 Jaa tarina / story share contract (no DOM).
 * Run: node tests/story-share.test.js
 */
'use strict';

var fs = require('fs');
var path = require('path');

var fails = 0;
function assert(name, cond) {
  if (!cond) {
    fails += 1;
    console.error('FAIL ' + name);
  } else {
    console.log('ok   ' + name);
  }
}

var root = path.join(__dirname, '..');
var storyPhp = fs.readFileSync(path.join(root, 'templates/story.php'), 'utf8');
var storyJs = fs.readFileSync(path.join(root, 'assets/js/story.js'), 'utf8');
var css = fs.readFileSync(path.join(root, 'assets/css/frontend.css'), 'utf8');
var plugin = fs.readFileSync(path.join(root, 'romanttinen-kutsu.php'), 'utf8');

assert('plugin version is 1.3.7',
  /Version:\s+1\.3\.7/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.3\.7'/.test(plugin)
);

assert('story page has locked headline', /Sinut on kutsuttu/.test(storyPhp) && /treffeille/.test(storyPhp));
assert('story page has Romanttinen serif logo', /Romanttinen/.test(storyPhp));
assert('story page has locked teaser line', /Pieni kutsu — avaa kun olet valmis\./.test(storyPhp));
assert('story page has underlined Avaa kutsu', /Avaa kutsu/.test(storyPhp));
assert('story page footer is romanttinen.fi', /romanttinen\.fi/.test(storyPhp));
assert('decorative countdown uses ·· placeholders', /··/.test(storyPhp));
assert('countdown labels are päivää / tuntia / min',
  /päivää/.test(storyPhp) && /tuntia/.test(storyPhp) && />min</.test(storyPhp)
);
assert('canvas is 1080×1920', /width="1080"/.test(storyPhp) && /height="1920"/.test(storyPhp));

assert('no live countdown hook on story page', !/data-romant-countdown/.test(storyPhp));
assert('no inviter / date / place spoilers on story page',
  !/inviter/.test(storyPhp) &&
  !/datetime/.test(storyPhp) &&
  !/location/.test(storyPhp) &&
  !/data-cd/.test(storyPhp) &&
  !/Treffikutsu sinulle/.test(storyPhp)
);
assert('story share page has OG', /og_title/.test(storyPhp) && /hero-teaser\.jpg/.test(storyPhp));

assert('PNG renderer is wine/cream not rose-gradient',
  /#4A1A23/.test(storyJs) &&
  /#E8DED1/.test(storyJs) &&
  !/createLinearGradient/.test(storyJs) &&
  !/#d9b8bc/.test(storyJs) &&
  !/#C9A090/.test(storyJs) &&
  !/#EBD9CE/.test(storyJs)
);
assert('PNG uses decorative ·· countdown not live dates',
  /··/.test(storyJs) &&
  /päivää/.test(storyJs) &&
  /tuntia/.test(storyJs) &&
  !/Date\.parse/.test(storyJs) &&
  !/countdownParts/.test(storyJs) &&
  !/data-inviter/.test(storyJs)
);
assert('PNG copy is locked teaser',
  /Pieni kutsu — avaa kun olet valmis\./.test(storyJs) &&
  /Avaa kutsu/.test(storyJs) &&
  /Romanttinen/.test(storyJs) &&
  /romanttinen\.fi/.test(storyJs)
);
assert('PNG draws bokeh not a flat fill-only background',
  /createRadialGradient/.test(storyJs) && /BOKEH/.test(storyJs)
);

assert('story frame is wine not rose-gradient',
  /\.romant-story-frame\s*\{[\s\S]*?#4A1A23/.test(css) &&
  !/\.romant-story-frame\s*\{[^}]*linear-gradient\(180deg,\s*var\(--romant-cream\)/.test(css) &&
  !/\.romant-story-frame\s*\{[^}]*--romant-blush/.test(css)
);
assert('story card is cream', /\.romant-story-card\s*\{[\s\S]*?#E8DED1/.test(css));
assert('story orbs are bokeh (blurred lights)',
  /\.romant-story-orb/.test(css) && /blur\(/.test(css)
);

if (fails) {
  process.exit(1);
}
console.log('story-share contract: all passed');
