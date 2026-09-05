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
$teaser_url  = ROMANT_KUTSU_URL . 'assets/img/hero-teaser.jpg';
$fabric_url  = ROMANT_KUTSU_URL . 'assets/img/hero-fabric.jpg';

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-home">
    <header class="romant-home-header">
        <?php echo Romant_Kutsu_Templates::logo_markup('romant-home-brand'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </header>

    <section
        class="romant-home-hero"
        aria-labelledby="romant-home-headline"
        style="--romant-hero-fabric: url('<?php echo esc_url($fabric_url); ?>');"
    >
        <div class="romant-home-copy">
            <h1 id="romant-home-headline">Kutsu mielitiettysi treffeille — unohtumattomalla tavalla.</h1>
            <p class="romant-home-sub">Luonnos ilmaiseksi. 4,90 € vasta kun jaat linkin.</p>
            <div class="romant-home-ctas">
                <a class="romant-btn romant-btn-primary" href="<?php echo esc_url($create_url); ?>">
                    Luo luonnos ilmaiseksi
                </a>
            </div>
        </div>

        <a class="romant-home-teaser-card" href="<?php echo esc_url($create_url); ?>">
            <img
                class="romant-home-teaser-img"
                src="<?php echo esc_url($teaser_url); ?>"
                alt="Esimerkki treffikutsun teaserista"
                width="1536"
                height="1024"
                loading="eager"
                decoding="async"
            />
        </a>
    </section>

    <section class="romant-home-steps" id="nain-se-toimii" aria-labelledby="romant-home-steps-heading">
        <h2 id="romant-home-steps-heading" class="romant-home-steps-heading">Näin se toimii</h2>
        <ol class="romant-home-steps-list">
            <li><span class="romant-home-step-num">1</span> Luo kutsu</li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li><span class="romant-home-step-num">2</span> Jaa linkki</li>
            <li class="romant-home-step-arrow" aria-hidden="true">→</li>
            <li><span class="romant-home-step-num">3</span> Saaja avaa vihjeet</li>
        </ol>
    </section>

    <footer class="romant-home-footer">romanttinen</footer>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
