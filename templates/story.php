<?php
/**
 * Story share page — IG teaser visual (1080×1920): fabric, cream card, countdown.
 * No spoilers, no date/place/hints, no “Jaa tarina” on the image.
 *
 * @var WP_Post $post
 * @var array   $data
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title = 'Jaa tarina · romanttinen';
$share_url  = Romant_Kutsu_Rewrite::recipient_url($data['token']);
$story_title = 'Sinut on kutsuttu treffeille.';
$story_teaser = 'Pieni kutsu — avaa kun olet valmis.';
$og_title       = 'Sinut on kutsuttu treffeille.';
$og_description = 'Sinulle on lähetetty treffikutsu. Avaa, kun olet valmis.';
$og_image       = ROMANT_KUTSU_URL . 'assets/img/hero-teaser.jpg';
$og_url         = $share_url;
$fabric_url     = ROMANT_KUTSU_URL . 'assets/img/hero-fabric.jpg';
$body_class     = 'romant-story-share';

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-story-page">
    <p class="romant-lead romant-story-help">Ota kuvakaappaus tai lataa PNG — sopii Instagram Storiesiin. Ei spoilereita.</p>

    <div class="romant-story-frame" id="romant-story-frame"
         data-title="<?php echo esc_attr($story_title); ?>"
         data-teaser="<?php echo esc_attr($story_teaser); ?>"
         data-cta="Avaa kutsu"
         data-fabric="<?php echo esc_url($fabric_url); ?>"
         data-romant-countdown="<?php echo esc_attr($data['datetime']); ?>">
        <article class="romant-story-card">
            <p class="romant-wordmark romant-story-wordmark">romanttinen.fi</p>
            <div class="romant-story-body">
                <h1 class="romant-serif romant-story-headline">Sinut on<br>kutsuttu treffeille.</h1>
                <div class="romant-story-rule" aria-hidden="true">
                    <svg class="romant-story-arc" viewBox="0 0 28 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 12 Q14 2 27 12" stroke="currentColor" stroke-width="1.15" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <p class="romant-story-teaser"><?php echo esc_html($story_teaser); ?></p>
                <div class="romant-countdown" aria-live="polite">
                    <div class="romant-cd-unit"><span data-cd="d">–</span><small>Päivää</small></div>
                    <div class="romant-cd-unit"><span data-cd="h">–</span><small>Tuntia</small></div>
                    <div class="romant-cd-unit"><span data-cd="m">–</span><small>Minuuttia</small></div>
                    <div class="romant-cd-unit"><span data-cd="s">–</span><small>Sekuntia</small></div>
                </div>
                <p class="romant-countdown-done" hidden>Hetki on täällä.</p>
                <span class="romant-story-cta">Avaa kutsu</span>
            </div>
            <p class="romant-story-brand">romanttinen.fi</p>
        </article>
    </div>

    <div class="romant-actions">
        <button type="button" class="romant-btn romant-btn-primary" id="romant-download-story">Lataa PNG</button>
        <button type="button" class="romant-btn romant-btn-secondary" data-romant-copy-text="<?php echo esc_attr($share_url); ?>">Kopioi linkki</button>
        <a class="romant-btn romant-btn-ghost" href="<?php echo esc_url($share_url); ?>">Takaisin kutsuun</a>
    </div>
    <canvas id="romant-story-canvas" width="1080" height="1920" hidden></canvas>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
