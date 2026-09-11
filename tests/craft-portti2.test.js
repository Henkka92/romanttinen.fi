/**
 * 1.3.9 Portti 2 craft contract (Pauliina cream lock + Mari FAIL D empty L1).
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
var preview = read('tests/craft-preview.html');

assert('version is 1.3.9', /Version:\s+1\.3\.9/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.3\.9'/.test(plugin));

assert('header wordmark + Luonnos pill',
  /romanttinen\.fi/.test(create) && /romant-luonnos-pill/.test(create) && /Luonnos/.test(create)
);
assert('eyebrow Lahjakutsu · Portti 2', /Lahjakutsu · Portti 2/.test(create));
assert('H1 is Rakenna kutsu', /Rakenna kutsu/.test(create) && !/<h1[^>]*>Luo kutsu/.test(create));
assert('locked lead',
  /Valitse tunnelma, muokkaa pehmeitä oletuksia — kuin pakkaisit lahjaa, et täyttäisi lomaketta\./.test(create)
);

assert('pohja max 2 Kotitreffit + Kaupungilla',
  /data-pohja=/.test(create) &&
  /'kotitreffit'/.test(cpt) &&
  /'kaupungilla'/.test(cpt) &&
  (cpt.match(/'kotitreffit'|'kaupungilla'/g) || []).length >= 2 &&
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

assert('cream cards + wine tokens',
  /#FFFDF9/.test(css) &&
  /romant-pohja-btn\.is-selected/.test(css) &&
  /#4A1F2C/.test(css) &&
  /romant-craft-title/.test(css)
);

assert('create does not require inviter (finalize later)',
  !/handle_create[\s\S]{0,800}Kirjoita kutsujan nimi/.test(forms)
);
assert('pay / manage surfaces still present',
  /Hanki jaettava linkki/.test(managePhp) &&
  /romant_pay_kutsu/.test(managePhp) &&
  /romant_inviter_name/.test(managePhp)
);

assert('Portti 3 home not rebuilt',
  /Kutsu mielitiettysi treffeille tavalla, joka jää mieleen\./.test(home) &&
  !/Rakenna kutsu/.test(home)
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
assert('Mari package still max 2 pohjat, no free planner',
  /craft_templates\(/.test(cpt) &&
  !/data-add-section/.test(editor) &&
  /Käytä esimerkkiä/.test(editor)
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
assert('Mari FAIL D: L1 placeholder stays locked copy when empty',
  /function placeholderFor[\s\S]*?return PH\.l1/.test(manageJs) &&
  !/\$ph_empty/.test(editor) &&
  /placeholder="Pieni vihje — älä paljasta kaikkea"/.test(preview)
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
