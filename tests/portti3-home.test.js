/**
 * 1.4.1 Portti 3 homepage contract (Pauliina mock + Nea demos v3).
 * Run: node tests/portti3-home.test.js
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
function read(rel) {
  return fs.readFileSync(path.join(root, rel), 'utf8');
}

var plugin = read('romanttinen-kutsu.php');
var home = read('templates/home.php');
var partial = read('templates/partials-home.php');
var homeClass = read('includes/class-home.php');
var rewrite = read('includes/class-rewrite.php');
var css = read('assets/css/frontend.css');
var recipient = read('templates/recipient.php');
var templates = read('includes/class-templates.php');
var readme = read('README.md');

assert('version is 1.4.1',
  /Version:\s+1\.4\.1/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.4\.1'/.test(plugin)
);

assert('standalone home renders Portti 3 partial',
  /partials-home\.php/.test(home) && /romant-home-body/.test(home)
);

assert('hero copy locked (Nea)',
  /Kutsu mielitiettysi treffeille tavalla, joka jää mieleen\./.test(partial) &&
  /Luo treffikutsu, joka paljastuu vaiheittain\. Ilmaiseksi — maksat 4,90&nbsp;€ vasta kun lähetät\./.test(partial) &&
  /Luo oma kutsu/.test(partial) &&
  /Ei tiliä\. Valmis jaettavaksi minuuteissa\./.test(partial) &&
  /4,90 € kun lähetät/.test(partial)
);

assert('primary CTA and step 1 go to /kutsu/uusi/',
  /create_url\(\)/.test(partial) &&
  /Luo kutsu/.test(partial) &&
  /function create_url/.test(rewrite) &&
  /home_url\('\/kutsu\/uusi\/'\)/.test(rewrite)
);

assert('teaser card copy locked',
  />Sinulle</.test(partial) &&
  /Sinut on kutsuttu treffeille/.test(partial) &&
  /Avaa kutsu/.test(partial) &&
  /Avautuu askel askeleelta/.test(partial) &&
  /#demokutsut/.test(partial)
);

assert('steps copy locked',
  /Näin se toimii/.test(partial) &&
  /Valitse aika ja kirjoita vihjeet\./.test(partial) &&
  /Maksat vasta kun olet valmis\./.test(partial) &&
  /Countdown \+ vihjeet omaan tahtiin\./.test(partial)
);

assert('craft-selite locked',
  /Saaja avaa vihjeet yksi kerrallaan \(max 3 \/ osio\)\./.test(partial)
);

assert('Nea v3 demo names + card lines verbatim',
  /Torstai vain meille/.test(homeClass) &&
  /Kävelylle — ja sitten pöytä, jota et vielä arvaa\./.test(homeClass) &&
  /Tule kotiin — illalla/.test(homeClass) &&
  /Kokkaan\. Sitten hartiat\. Loppu on meidän\./.test(homeClass) &&
  /Kaksikymmentä — ja vielä yksi juttu/.test(homeClass) &&
  /Älä kysy minne\. Laita se mekko, josta pidän\./.test(homeClass) &&
  /Aino kutsuu/.test(homeClass) &&
  /Elias kutsuu/.test(homeClass) &&
  /Mari kutsuu/.test(homeClass)
);

assert('soft peels locked (opened demo)',
  /Kävely — Aloitetaan ilman kiirettä\. Suunta selviää matkalla\./.test(homeClass) &&
  /Pöytä — Varasin meille paikan\. Pukeudu niin että uskallat\./.test(homeClass) &&
  /Lopuksi — Jos ilta venyy, se on tarkoitus\./.test(homeClass) &&
  /Keittiö — Sinun ei tarvitse tuoda mitään — paitsi itsesi\./.test(homeClass) &&
  /Hieronta — Hartiat ensin\. Kiire jää oven taakse\./.test(homeClass) &&
  /Ilta — Kynttilät\. Hidas musiikki\. Ei kelloa\./.test(homeClass) &&
  /Tänään — 20 vuotta sinua\. Tänä iltana en kerro kaikkea etukäteen\./.test(homeClass) &&
  /Minne — Auto odottaa\. Loppu on yllätys\./.test(homeClass) &&
  /Miksi — Koska valitsisin sinut uudestaan\./.test(homeClass)
);

assert('no old placeholder demo copy',
  !/Illalliselle kaupungilla/.test(partial + homeClass) &&
  !/Hierontaöljy on jo lämmin/.test(partial + homeClass) &&
  !/yhä yksi salaisuus/.test(partial + homeClass) &&
  !/Esimerkiksi/.test(partial)
);

assert('demo cards render name + hint + Avaa demo',
  /romant-home-demo-name/.test(partial) &&
  /Avaa demo/.test(partial) &&
  /demo_url\(/.test(partial)
);

assert('demo routes /kutsu/demo/{aino|elias|mari}/',
  /\^kutsu\/demo\/\(aino\|elias\|mari\)/.test(rewrite) &&
  /romant_route=demo/.test(rewrite) &&
  /case 'demo':/.test(rewrite) &&
  /demo_invite_data/.test(rewrite)
);

assert('opened demos reuse recipient peel, hide share',
  /'is_demo'\s*=>\s*true/.test(rewrite) &&
  /\$is_demo/.test(recipient) &&
  /Jaa tarina/.test(recipient) &&
  /if \(\!\$is_demo\)/.test(recipient)
);

assert('homepage fabric/bokeh matches craft',
  /romant-kutsu-body\.romant-home-body/.test(css) &&
  /hero-fabric\.jpg/.test(css) &&
  /radial-gradient\(circle at 14% 7%/.test(css) &&
  /romant-home-teaser-wrap/.test(css) &&
  /romant-home-demo-card--bokeh/.test(css)
);

assert('desktop LTR hero + mobile stack',
  /grid-template-columns:\s*1fr 420px/.test(css) &&
  /text-align:\s*center/.test(css) &&
  /#demokutsut/.test(partial)
);

assert('wordmark still home, sitewide helper unchanged',
  /wordmark_markup\('romant-home-wordmark'\)/.test(partial) &&
  /home_url\('\/'\)/.test(templates)
);

assert('mini-peel preview not on homepage',
  !/partials-home-peel-preview/.test(home) &&
  !/partials-home-peel-preview/.test(partial) &&
  !/partials-home-peel-preview/.test(homeClass)
);

assert('README version 1.4.1',
  /1\.4\.1/.test(readme)
);

if (fails) {
  process.exit(1);
}
console.log('portti3-home contract: all passed');
