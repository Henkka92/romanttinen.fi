/**
 * 1.4.4 Portti 4 Luottamus contract (seed / ALV / stub OFF / digi / legal / kuitti).
 * Run: node tests/portti4-trust.test.js
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
var settings = read('includes/class-settings.php');
var pluginClass = read('includes/class-plugin.php');
var rewrite = read('includes/class-rewrite.php');
var forms = read('includes/class-forms.php');
var payment = read('includes/class-payment.php');
var manage = read('templates/manage.php');
var terms = read('templates/kayttoehdot.php');
var privacy = read('templates/tietosuoja.php');
var templates = read('includes/class-templates.php');
var layoutEnd = read('templates/layout-end.php');
var story = read('templates/story.php');
var home = read('templates/home.php');
var partial = read('templates/partials-home.php');
var homeClass = read('includes/class-home.php');
var readme = read('README.md');
var css = read('assets/css/frontend.css');

assert('version stays 1.4.4 (peels + Portti 4 in one zip)',
  /Version:\s+1\.4\.4/.test(plugin) && /ROMANT_KUTSU_VERSION',\s*'1\.4\.4'/.test(plugin)
);

assert('stub constant defaults false',
  /ROMANTTINEN_STUB_PAYMENTS',\s*false/.test(plugin)
);

assert('Kelaus seed defaults locked',
  /DEFAULT_COMPANY_NAME\s*=\s*'Kelaus Finland Oy'/.test(settings) &&
  /DEFAULT_BUSINESS_ID\s*=\s*'2806633-5'/.test(settings) &&
  /DEFAULT_COMPANY_ADDRESS\s*=\s*'Heiniläntie 37, 08500 Lohja'/.test(settings) &&
  /DEFAULT_COMPANY_EMAIL\s*=\s*'henry@kelaus\.fi'/.test(settings) &&
  /DEFAULT_VAT_RATE\s*=\s*'25\.5'/.test(settings)
);

assert('maybe_seed + activate stub OFF',
  /function maybe_seed/.test(settings) &&
  /romant_kutsu_trust_seeded/.test(settings) &&
  /maybe_seed\(\)/.test(pluginClass) &&
  /add_option\('romant_kutsu_stub_payments',\s*'0'\)/.test(pluginClass) &&
  !/add_option\('romant_kutsu_stub_payments',\s*'1'\)/.test(pluginClass) &&
  /'default'\s*=>\s*'0'/.test(settings) &&
  /get_option\(self::OPTION_STUB,\s*'0'\)/.test(settings)
);

assert('price VAT line helper locked',
  /function get_price_vat_line/.test(settings) &&
  /sis\. ALV/.test(settings) &&
  /vat_rate_display/.test(settings)
);

assert('pay UI one line 4,90 € sis. ALV 25,5 %',
  /get_price_vat_line\(\)/.test(manage) &&
  /romant-price-vat/.test(manage) &&
  /Hanki jaettava linkki/.test(manage) &&
  !/Hanki jaettava linkki \(<\?php echo esc_html\(\$price_disp\); \?>\)/.test(manage)
);

assert('digi cancel checkbox + KSL 6:16 locked',
  /romant_digi_cancel/.test(manage) &&
  /Ymmärrän, että kutsu on digitaalinen sisältö, joka toimitetaan heti\. Peruuttamisoikeutta ei ole \(KSL 6:16\)\./.test(manage) &&
  /pay_error === 'digi'/.test(manage) &&
  /romant_digi_cancel/.test(payment) &&
  /pay_error=digi/.test(payment)
);

assert('pay form Käyttöehdot + Tietosuoja',
  /terms_url\(\)/.test(manage) &&
  /privacy_url\(\)/.test(manage) &&
  />Käyttöehdot</.test(manage) &&
  />Tietosuoja</.test(manage)
);

assert('legal routes /kayttoehdot/ /tietosuoja/',
  /\^kayttoehdot\/\?\$/.test(rewrite) &&
  /\^tietosuoja\/\?\$/.test(rewrite) &&
  /function terms_url/.test(rewrite) &&
  /home_url\('\/kayttoehdot\/'\)/.test(rewrite) &&
  /function privacy_url/.test(rewrite) &&
  /home_url\('\/tietosuoja\/'\)/.test(rewrite) &&
  /case 'kayttoehdot'/.test(rewrite) &&
  /case 'tietosuoja'/.test(rewrite)
);

assert('legal templates locked seller + ALV + KSL',
  /Kelaus|get_company_block/.test(terms) &&
  /KSL 6:16/.test(terms) &&
  /get_price_vat_line/.test(terms) &&
  /Visma Pay/.test(terms) &&
  /Rekisterinpitäjä/.test(privacy) &&
  /get_company_block/.test(privacy) &&
  /sessionStorage/.test(privacy)
);

assert('receipt locked: VAT + seller + digi + legal URLs',
  /get_price_vat_line/.test(forms) &&
  /get_company_block/.test(forms) &&
  /Kutsu on digitaalinen sisältö\. Peruuttamisoikeutta ei ole, koska sisältö toimitetaan heti maksun jälkeen \(KSL 6:16\)\./.test(forms) &&
  /Käyttöehdot: \{\$terms_url\}/.test(forms) &&
  /Tietosuoja: \{\$privacy_url\}/.test(forms) &&
  /Treffikutsu valmis — /.test(forms)
);

assert('legal footer helper; hidden on story; on home partial',
  /function legal_footer_markup/.test(templates) &&
  />Käyttöehdot</.test(templates) &&
  />Tietosuoja</.test(templates) &&
  /legal_footer_markup/.test(layoutEnd) &&
  /\$hide_legal_footer = true/.test(story) &&
  /\$hide_legal_footer = true/.test(home) &&
  /legal_footer_markup/.test(partial)
);

assert('homepage Nea hero copy unchanged (price pill still 4,90 € kun lähetät)',
  /4,90 € kun lähetät/.test(partial) &&
  /Kutsu mielitiettysi treffeille tavalla, joka jää mieleen\./.test(partial)
);

assert('1.4.4 peels not dropped (Pöytä L3 + 3×3)',
  /Pöytä ikkunan vieressä — nimesi on listalla\./.test(homeClass) &&
  /'Kävely'/.test(homeClass) &&
  /'Pöytä'/.test(homeClass) &&
  /'Lopuksi'/.test(homeClass)
);

assert('CSS: vat line + legal check + footer wine',
  /\.romant-price-vat/.test(css) &&
  /\.romant-legal-check/.test(css) &&
  /\.romant-legal-footer/.test(css) &&
  /#4A1F2C/.test(css)
);

assert('README 1.4.4 includes Portti 4 + stub OFF + legal routes',
  /1\.4\.4/.test(readme) &&
  /Portti 4/.test(readme) &&
  /Kelaus Finland Oy/.test(readme) &&
  /4,90 € sis\. ALV 25,5 %/.test(readme) &&
  /\/kayttoehdot\//.test(readme) &&
  /\/tietosuoja\//.test(readme) &&
  /Stub.*pois|stub OFF|oletus pois/i.test(readme)
);

if (fails) {
  process.exit(1);
}
console.log('portti4-trust contract: all passed');
