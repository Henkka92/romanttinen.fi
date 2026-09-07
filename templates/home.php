<?php
/**
 * Marketing homepage (standalone).
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title  = 'romanttinen — treffikutsu hetkeksi';
$allow_index = true;
$body_class  = 'romant-home-body';
$create_url  = Romant_Kutsu_Rewrite::create_url();

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-home">
    <header class="romant-home-header">
        <p class="romant-wordmark romant-home-wordmark">romanttinen.fi</p>
    </header>

    <section class="romant-home-hero" aria-labelledby="romant-home-headline">
        <div class="romant-home-copy">
            <h1 id="romant-home-headline">Kutsu mielitiettysi treffeille tavalla, joka jää mieleen.</h1>
            <p class="romant-home-sub">Luo henkilökohtainen treffikutsu, joka paljastuu vaiheittain. Luo ilmaiseksi. Maksat 4,90 € vasta, kun lähetät kutsun.</p>
            <div class="romant-home-ctas">
                <a class="romant-btn romant-btn-primary" href="<?php echo esc_url($create_url); ?>">
                    Luo oma kutsu
                </a>
            </div>
        </div>
        <?php
        $preview_href = $create_url;
        include ROMANT_KUTSU_PATH . 'templates/partials-home-peel-preview.php';
        ?>
    </section>

    <section class="romant-home-steps" id="nain-se-toimii" aria-labelledby="romant-home-steps-heading">
        <h2 id="romant-home-steps-heading" class="romant-home-steps-heading">Näin se toimii</h2>
        <ol class="romant-home-steps-list">
            <li class="romant-home-step">
                <span class="romant-home-step-num">1</span>
                <span class="romant-home-step-body">
                    <strong class="romant-home-step-title">Luo kutsu</strong>
                    <span class="romant-home-step-desc">Valitse aika ja kirjoita omat vihjeesi.</span>
                </span>
            </li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li class="romant-home-step">
                <span class="romant-home-step-num">2</span>
                <span class="romant-home-step-body">
                    <strong class="romant-home-step-title">Jaa linkki</strong>
                    <span class="romant-home-step-desc">Maksat vasta, kun kutsu on valmis.</span>
                </span>
            </li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li class="romant-home-step">
                <span class="romant-home-step-num">3</span>
                <span class="romant-home-step-body">
                    <strong class="romant-home-step-title">Saaja avaa vihjeet</strong>
                    <span class="romant-home-step-desc">Jännitys kasvaa saajan tahtiin.</span>
                </span>
            </li>
        </ol>
    </section>

    <footer class="romant-home-footer">romanttinen</footer>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
