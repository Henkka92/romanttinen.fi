/**
 * 1.4.0 Portti 2 craft contract (Pauliina fabric/bokeh + Mari Muu + wordmark home).
 * Run: node tests/craft-portti2.test.js
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
var cpt = read('includes/class-cpt.php');
var create = read('templates/create-form.php');
var editor = read('templates/partials-sections-editor.php');
var manageJs = read('assets/js/manage.js');
var css = read('assets/css/frontend.css');
var forms = read('includes/class-forms.php');
var managePhp = read('templates/manage.php');
var home = read('templates/home.php');
var homePartial = read('templates/partials-home.php');
var preview = read('tests/craft-preview.html');
var templatesPhp = read('includes/class-templates.php');
var recipientPhp = read('templates/recipient.php');
var storyPhp = read('templates/story.php');
var homeClass = read('includes/class-home.php');

assert('version is 1.4.5', /Version:\s+1\.4\.5/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.4\.5'/.test(plugin));

assert('header wordmark + Luonnos pill',
  /wordmark_markup\(/.test(create) && /romant-luonnos-pill/.test(create) && /Luonnos/.test(create)
);
assert('wordmark helper links home /',
  /function wordmark_markup/.test(templatesPhp) &&
  /home_url\('\/'\)/.test(templatesPhp)
);
assert('wordmark → home on every surface',
  /wordmark_markup\(/.test(create) &&
  /wordmark_markup\('romant-home-wordmark'\)/.test(homePartial) &&
  /partials-home\.php/.test(home) &&
  /partials-home\.php/.test(homeClass) &&
  /wordmark_markup\(/.test(managePhp) &&
  /wordmark_markup\(/.test(recipientPhp) &&
  /wordmark_markup\('romant-story-wordmark'\)/.test(storyPhp) &&
  /<a class="romant-wordmark" href="\/">romanttinen\.fi<\/a>/.test(preview)
);
assert('eyebrow Lahjakutsu · Portti 2', /Lahjakutsu · Portti 2/.test(create));
assert('H1 is Rakenna kutsu', /Rakenna kutsu/.test(create) && !/<h1[^>]*>Luo kutsu/.test(create));
assert('locked lead',
  /Valitse tunnelma, muokkaa pehmeitä oletuksia — kuin pakkaisit lahjaa, et täyttäisi lomaketta\./.test(create)
);

assert('pohja max 3 Kotitreffit + Kaupungilla + Muu',
  /data-pohja=/.test(create) &&
  /'kotitreffit'/.test(cpt) &&
  /'kaupungilla'/.test(cpt) &&
  /'muu'/.test(cpt) &&
  /'label'\s*=>\s*'Muu'/.test(cpt) &&
  /data-pohja="muu"/.test(preview) &&
  !/'kolmas'|'custom'|'oma'/.test(cpt)
);
assert('Kotitreffit selected on open',
  /kotitreffit' \? ' is-selected'/.test(create) || /=== 'kotitreffit' \? ' is-selected'/.test(create)
);

assert('summary Kutsun nimi / Ilta kahdelle',
  /Kutsun nimi/.test(create) && /Ilta kahdelle/.test(create)
);

assert('default Nea blurbs',
  /Valitaan leffa, tehdään popcornit — ja katsotaan yhdessä\./.test(cpt) &&
  /Kokataan jotain hyvää tai varataan pöytä\./.test(cpt) &&
  /Rauhallista aikaa yhdessä, ilman kiirettä\./.test(cpt)
);
assert('create uses default_craft_sections not empty form',
  /default_craft_sections\(/.test(create) && !/empty_section\(/.test(create)
);
assert('OLETUS + napauta muokataksesi',
  /Oletus/.test(editor) && /napauta muokataksesi/.test(editor)
);
assert('help Voit muokata sisältöä vapaasti',
  /Voit muokata sisältöä vapaasti\./.test(editor)
);
assert('craft-selite one line near osiot, not hero',
  /romant-craft-selite/.test(editor) &&
  /Saaja avaa vihjeet yksi kerrallaan \(max 3 \/ osio\)\./.test(editor) &&
  /craft_editor/.test(editor) &&
  /romant-craft-selite/.test(preview) &&
  !/romant-craft-title[\s\S]{0,400}Saaja avaa vihjeet/.test(create) &&
  !/romant-craft-lead[\s\S]{0,200}Saaja avaa vihjeet/.test(create)
);
assert('dashed later note',
  /Hanki &amp; lähetys myöhemmin/.test(create) || /Hanki & lähetys myöhemmin/.test(create)
);
assert('wine CTA Tallenna luonnos + jatka viimeistelyyn',
  /Tallenna luonnos/.test(create) && /tai jatka viimeistelyyn/.test(create)
);

assert('Kaupungilla L1 locked',
  /Kävelylle — suunta selviää myöhemmin\./.test(cpt) &&
  /Pöytä odottaa, mutta missä…/.test(cpt) &&
  /Ota mukaan jotain lämmintä\./.test(cpt)
);
assert('Kotitreffit soft L1 locked',
  /Valitaan jotain kevyttä — ei se ensimmäinen arvaus\./.test(cpt) &&
  /Syödään hyvin kotona, rauhassa\./.test(cpt) &&
  /Illan lopuksi ei kiirettä mihinkään\./.test(cpt)
);

assert('placeholders locked',
  /Kirjoita vihje saajalle…/.test(cpt) &&
  /Pieni vihje — älä paljasta kaikkea/.test(cpt) &&
  /Seuraava kerros…/.test(cpt)
);

assert('extra chips locked (no free planner)',
  /EXTRA_SECTION_TITLES = \['Kaupungilla', 'Pieni salaisuus', 'Hellää huomiota'\]/.test(cpt) &&
  /data-add-chip/.test(editor) &&
  !/data-add-section/.test(editor) &&
  !/Lisää osio/.test(editor)
);
assert('JS has no blank add-section planner',
  !/data-add-section/.test(manageJs) &&
  /data-add-chip/.test(manageJs) &&
  /applyTemplate/.test(manageJs)
);
assert('JS refuses non-curated chip titles',
  /DEFAULT_TITLES\.concat\(EXTRA_TITLES\)/.test(manageJs) &&
  /allowed\.indexOf\(title\) === -1/.test(manageJs)
);
assert('craft extra chips stay the locked three (no default-title chips)',
  /isCraft\(editor\) \? EXTRA_TITLES\.slice\(\)/.test(manageJs)
);

assert('cream cards + wine tokens',
  /#FFFDF9/.test(css) &&
  /romant-pohja-btn\.is-selected/.test(css) &&
  /#4A1F2C/.test(css) &&
  /romant-craft-title/.test(css)
);
assert('craft page uses hero-fabric + bokeh not flat cream',
  /romant-kutsu-body\.romant-craft-page/.test(css) &&
  /hero-fabric\.jpg/.test(css) &&
  /radial-gradient\(circle at 14% 7%/.test(css) &&
  !/\.romant-kutsu-body\.romant-craft-page\s*\{\s*background:\s*#F3E6D8/.test(css) &&
  !/\.romant-craft-page\s*\{\s*background:\s*linear-gradient\(180deg,\s*#F3E6D8/.test(css)
);

assert('create does not require inviter (finalize later)',
  !/handle_create[\s\S]{0,800}Kirjoita kutsujan nimi/.test(forms)
);
assert('pay / manage surfaces still present',
  /Hanki jaettava linkki/.test(managePhp) &&
  /romant_pay_kutsu/.test(managePhp) &&
  /romant_inviter_name/.test(managePhp)
);

assert('Portti 3 home uses locked hero + demos',
  /Kutsu mielitiettysi treffeille tavalla, joka jää mieleen\./.test(homePartial) &&
  /Demokutsut/.test(homePartial) &&
  !/Rakenna kutsu/.test(homePartial)
);

assert('Mari: Käytä esimerkkiä control',
  /Käytä esimerkkiä/.test(editor) &&
  /data-use-example/.test(editor) &&
  /data-use-example/.test(manageJs) &&
  /exampleL1|example_l1/.test(manageJs) &&
  /useExample/.test(read('includes/class-templates.php'))
);
assert('Mari: Kaupungilla renames its 3 curated sections',
  /Mari: Kaupungilla may rename/.test(cpt) &&
  /'kaupungilla' => \[[\s\S]*?'title' => 'Kaupungilla'[\s\S]*?'title' => 'Yhteinen ateria'[\s\S]*?'title' => 'Pieni salaisuus'/.test(cpt) &&
  !/'kaupungilla' => \[[\s\S]*?'title' => 'Elokuvahetki'/.test(cpt) &&
  !/'kaupungilla' => \[[\s\S]*?'title' => 'Kotona'/.test(cpt)
);
assert('Mari package max 3 pohjat, no free planner',
  /craft_templates\(/.test(cpt) &&
  /'muu'/.test(cpt) &&
  !/data-add-section/.test(editor) &&
  /Käytä esimerkkiä/.test(editor)
);
assert('Mari: Muu is three empty sections + placeholders, no prefills',
  /Mari: Muu/.test(cpt) &&
  /'muu' => \[[\s\S]*?'title' => ''[\s\S]*?'l1'\s*=>\s*''[\s\S]*?'title' => ''[\s\S]*?'l1'\s*=>\s*''[\s\S]*?'title' => ''[\s\S]*?'l1'\s*=>\s*''/.test(cpt)
);
var muuBlock = (cpt.split("'muu' =>")[1] || '').split('function kotitreffit_soft_l1')[0] || '';
assert('Mari: Muu block has no Nea/Kaupungilla prefills',
  muuBlock !== '' &&
  !/Valitaan leffa/.test(muuBlock) &&
  !/Kävelylle/.test(muuBlock) &&
  !/Elokuvahetki/.test(muuBlock) &&
  !/Pieni salaisuus/.test(muuBlock)
);
assert('preview Muu template is empty titles + empty l1',
  /muu:\s*\{[\s\S]*?sections:\s*\[[\s\S]*?title:\s*''[\s\S]*?l1:\s*''[\s\S]*?title:\s*''[\s\S]*?l1:\s*''[\s\S]*?title:\s*''[\s\S]*?l1:\s*''/.test(preview)
);
assert('Käytä esimerkkiä + napauta muokataksesi stay interactive',
  /data-use-example/.test(editor) &&
  /data-napauta/.test(editor) &&
  /function applyExample/.test(manageJs) &&
  /classList\.remove\('is-collapsed'\)/.test(manageJs) &&
  /data-use-example/.test(preview) &&
  /napauta muokataksesi/.test(preview)
);

var defCraft = cpt.split('function default_craft_sections')[1] || '';
defCraft = defCraft.split('function sample_sections')[0] || '';
assert('Mari FAIL D: default_craft_sections L1 empty (titles kept)',
  /DEFAULT_SECTION_TITLES/.test(defCraft) &&
  /'levels'\s*=>\s*\[\s*''\s*,\s*''\s*,\s*''\s*\]/.test(defCraft) &&
  !/DEFAULT_SECTION_BLURBS/.test(defCraft)
);
assert('Mari FAIL D: Nea examples stay in templates for Käytä esimerkkiä',
  /function example_l1/.test(cpt) &&
  /function applyExample/.test(manageJs) &&
  /first\.value = example/.test(manageJs)
);
assert('Mari FAIL D: applyTemplate / addChip do not prefill L1',
  /buildSectionCard\(si, \{[\s\S]*?l1:\s*''/.test(manageJs) &&
  /buildSectionCard\(n, \{[\s\S]*?l1:\s*''/.test(manageJs)
);
assert('Mari FAIL D: empty hint placeholder is Kirjoita vihje saajalle…',
  /function placeholderFor[\s\S]*?return value \? PH\.l1 : PH\.empty/.test(manageJs) &&
  /\$ph_empty/.test(editor) &&
  /placeholder="Kirjoita vihje saajalle…"/.test(preview) &&
  /PLACEHOLDER_EMPTY = 'Kirjoita vihje saajalle…'/.test(cpt)
);
assert('Mari FAIL D: preview L1 textareas start empty',
  /data-level-text><\/textarea>/.test(preview) &&
  !/data-level-text>Valitaan leffa/.test(preview) &&
  !/data-level-text>Kokataan jotain/.test(preview) &&
  !/data-level-text>Rauhallista aikaa/.test(preview)
);

if (fails) {
  process.exit(1);
}
console.log('craft-portti2 contract: all passed');
