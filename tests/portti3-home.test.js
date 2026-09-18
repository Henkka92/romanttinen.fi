/**
 * 1.4.4 Portti 3 homepage + hivelee 3×3 peels contract.
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
var recipientJs = read('assets/js/recipient.js');
var templates = read('includes/class-templates.php');
var readme = read('README.md');

assert('version is 1.4.4',
  /Version:\s+1\.4\.4/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.4\.4'/.test(plugin)
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

assert('soft peels locked as 3 sections (opened demo)',
  /'title'\s*=>\s*'Kävely'/.test(homeClass) &&
  /Aloitetaan ilman kiirettä\. Suunta selviää matkalla\./.test(homeClass) &&
  /'title'\s*=>\s*'Pöytä'/.test(homeClass) &&
  /Varasin meille paikan\. Pukeudu niin että uskallat\./.test(homeClass) &&
  /'title'\s*=>\s*'Lopuksi'/.test(homeClass) &&
  /Jos ilta venyy, se on tarkoitus\./.test(homeClass) &&
  /'title'\s*=>\s*'Keittiö'/.test(homeClass) &&
  /Sinun ei tarvitse tuoda mitään — paitsi itsesi\./.test(homeClass) &&
  /'title'\s*=>\s*'Hieronta'/.test(homeClass) &&
  /Hartiat ensin\. Kiire jää oven taakse\./.test(homeClass) &&
  /'title'\s*=>\s*'Ilta'/.test(homeClass) &&
  /Kynttilät\. Hidas musiikki\. Ei kelloa\./.test(homeClass) &&
  /'title'\s*=>\s*'Tänään'/.test(homeClass) &&
  /20 vuotta sinua\. Tänä iltana en kerro kaikkea etukäteen\./.test(homeClass) &&
  /'title'\s*=>\s*'Minne'/.test(homeClass) &&
  /Auto odottaa\. Loppu on yllätys\./.test(homeClass) &&
  /'title'\s*=>\s*'Miksi'/.test(homeClass) &&
  /Koska valitsisin sinut uudestaan\./.test(homeClass)
);

assert('each demo has 3 sections not 1 stacked osio',
  (homeClass.match(/'title'\s*=>\s*'Kävely'/) || []).length === 1 &&
  /'sections'\s*=>/.test(homeClass) &&
  !/'osio_title'/.test(homeClass)
);

assert('each demo section has 3 escalating peel levels',
  /'title'\s*=>\s*'Kävely'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Pöytä'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Lopuksi'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Keittiö'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Hieronta'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Ilta'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Tänään'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Minne'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass) &&
  /'title'\s*=>\s*'Miksi'[\s\S]*?'levels'\s*=>\s*\[[\s\S]*?'[^']+',\s*'[^']+',\s*'[^']+'/.test(homeClass)
);

assert('Aino Pöytä L3 locked (no Ravintola X)',
  /Pöytä ikkunan vieressä — nimesi on listalla\./.test(homeClass) &&
  !/Ravintola/.test(homeClass + partial)
);

assert('recipient peel reveals +1 while deeper levels remain',
  /depth < maxDepth/.test(recipientJs) &&
  /Haluatko kuulla lisää\?/.test(recipientJs) &&
  /progress\.depth\[String\(idx\)\] = cur \+ 1/.test(recipientJs)
);

assert('homepage card is one link (not nested Avaa demo href)',
  /<a class="romant-home-demo-card/.test(partial) &&
  /<span class="romant-home-demo-link">Avaa demo<\/span>/.test(partial) &&
  !/<a class="romant-home-demo-link"/.test(partial)
);

assert('no old placeholder demo copy',
  !/Illalliselle kaupungilla/.test(partial + homeClass) &&
  !/Hierontaöljy on jo lämmin/.test(partial + homeClass) &&
  !/yhä yksi salaisuus/.test(partial + homeClass) &&
  !/Esimerkiksi/.test(partial)
);

assert('demo cards render name + title + 1 line on whole-card link',
  /romant-home-demo-name/.test(partial) &&
  /Avaa demo/.test(partial) &&
  /demo_url\(/.test(partial) &&
  !/Aloitetaan ilman kiirettä/.test(partial) &&
  !/Hartiat ensin/.test(partial) &&
  !/Koska valitsisin sinut/.test(partial) &&
  !/Pöytä ikkunan vieressä/.test(partial)
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

assert('README version 1.4.4',
  /1\.4\.4/.test(readme)
);

if (fails) {
  process.exit(1);
}
console.log('portti3-home contract: all passed');
