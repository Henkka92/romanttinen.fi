<?php
/**
 * Portti 3 etusivu (Pauliina mock + Nea copy). Shared by standalone home and shortcode.
 *
 * @var string $create_url
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}
$create_url = $create_url ?? Romant_Kutsu_Rewrite::create_url();
$demos      = Romant_Kutsu_Home::demos();
?>
<div class="romant-home">
    <header class="romant-home-header">
        <?php echo Romant_Kutsu_Templates::wordmark_markup('romant-home-wordmark'); ?>
        <span class="romant-home-price-pill">4,90 € kun lähetät</span>
    </header>

    <section class="romant-home-hero" aria-labelledby="romant-home-headline">
        <div class="romant-home-copy">
            <h1 id="romant-home-headline">Kutsu mielitiettysi treffeille tavalla, joka jää mieleen.</h1>
            <p class="romant-home-sub">Luo treffikutsu, joka paljastuu vaiheittain. Ilmaiseksi — maksat 4,90&nbsp;€ vasta kun lähetät.</p>
            <div class="romant-home-ctas">
                <a class="romant-btn romant-btn-primary romant-home-cta" href="<?php echo esc_url($create_url); ?>">
                    Luo oma kutsu
                </a>
            </div>
            <p class="romant-home-cta-note">Ei tiliä. Valmis jaettavaksi minuuteissa.</p>
        </div>

        <div class="romant-home-teaser-wrap">
            <aside class="romant-home-teaser" aria-label="Esimerkki kutsusta">
                <p class="romant-home-teaser-kicker">Sinulle</p>
                <p class="romant-home-teaser-title">Sinut on kutsuttu treffeille</p>
                <div class="romant-home-teaser-cd" aria-hidden="true">
                    <div class="romant-home-cd-cell">
                        <span class="romant-home-cd-num">02</span>
                        <span class="romant-home-cd-label">pv</span>
                    </div>
                    <div class="romant-home-cd-cell">
                        <span class="romant-home-cd-num">14</span>
                        <span class="romant-home-cd-label">t</span>
                    </div>
                    <div class="romant-home-cd-cell">
                        <span class="romant-home-cd-num">37</span>
                        <span class="romant-home-cd-label">min</span>
                    </div>
                </div>
                <a class="romant-btn romant-btn-primary romant-home-teaser-btn" href="#demokutsut">Avaa kutsu</a>
                <p class="romant-home-teaser-foot">Avautuu askel askeleelta</p>
            </aside>
        </div>
    </section>

    <section class="romant-home-steps" id="nain-se-toimii" aria-labelledby="romant-home-steps-heading">
        <p id="romant-home-steps-heading" class="romant-home-kicker">Näin se toimii</p>
        <ol class="romant-home-steps-list">
            <li class="romant-home-step">
                <span class="romant-home-step-num">1</span>
                <span class="romant-home-step-body">
                    <a class="romant-home-step-link" href="<?php echo esc_url($create_url); ?>">Luo kutsu</a>
                    <span class="romant-home-step-desc">Valitse aika ja kirjoita vihjeet.</span>
                </span>
            </li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li class="romant-home-step">
                <span class="romant-home-step-num">2</span>
                <span class="romant-home-step-body">
                    <strong class="romant-home-step-title">Jaa linkki</strong>
                    <span class="romant-home-step-desc">Maksat vasta kun olet valmis.</span>
                </span>
            </li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li class="romant-home-step">
                <span class="romant-home-step-num">3</span>
                <span class="romant-home-step-body">
                    <strong class="romant-home-step-title">Saaja avaa vihjeet</strong>
                    <span class="romant-home-step-desc">Countdown + vihjeet omaan tahtiin.</span>
                </span>
            </li>
        </ol>
    </section>

    <section class="romant-home-demos" id="demokutsut" aria-labelledby="romant-home-demos-heading">
        <div class="romant-home-demos-heading">
            <h2 id="romant-home-demos-heading" class="romant-home-demos-title">Demokutsut</h2>
            <p class="romant-home-kicker romant-home-demos-kicker">Inspiraatiota</p>
        </div>
        <div class="romant-home-demo-grid">
            <?php foreach ($demos as $demo) :
                $slug  = (string) ($demo['slug'] ?? '');
                $href  = $slug !== '' ? Romant_Kutsu_Home::demo_url($slug) : '#';
                $strip = (string) ($demo['strip'] ?? 'fabric');
                $label = trim((string) ($demo['kicker'] ?? '') . ': ' . (string) ($demo['name'] ?? ''));
                ?>
            <a class="romant-home-demo-card romant-home-demo-card--<?php echo esc_attr($strip); ?>"
               href="<?php echo esc_url($href); ?>"
               aria-label="<?php echo esc_attr($label); ?>">
                <span class="romant-home-demo-strip" aria-hidden="true"></span>
                <span class="romant-home-demo-content">
                    <span class="romant-home-demo-meta"><?php echo esc_html((string) $demo['kicker']); ?></span>
                    <span class="romant-home-demo-name"><?php echo esc_html((string) $demo['name']); ?></span>
                    <span class="romant-home-demo-hints">«<?php echo esc_html((string) $demo['card_line']); ?>»</span>
                    <span class="romant-home-demo-link">Avaa demo</span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="romant-home-craft-note">Saaja avaa vihjeet yksi kerrallaan (max 3 / osio).</p>
    </section>
    <?php echo Romant_Kutsu_Templates::legal_footer_markup(); ?>
</div>
