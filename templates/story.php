<?php
/**
 * Story share page — locked teaser visual (IG 1080×1920, no spoilers).
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
$story_title = 'Sinut on kutsuttu treffeille';
$og_title       = 'Sinut on kutsuttu treffeille.';
$og_description = 'Sinulle on lähetetty treffikutsu. Avaa, kun olet valmis.';
$og_image       = ROMANT_KUTSU_URL . 'assets/img/hero-teaser.jpg';
$og_url         = $share_url;

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-story-page">
    <p class="romant-lead romant-story-help">Ota kuvakaappaus tai lataa PNG — sopii Instagram Storiesiin. Ei spoilereita.</p>

    <div class="romant-story-frame" id="romant-story-frame">
        <div class="romant-story-bokeh" aria-hidden="true">
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
            <span class="romant-story-orb"></span>
        </div>
        <article class="romant-story-card">
            <p class="romant-story-logo">Romanttinen</p>
            <span class="romant-story-rule" aria-hidden="true"></span>
            <h1 class="romant-serif romant-story-headline">Sinut on kutsuttu<br>treffeille</h1>
            <p class="romant-story-teaser">Pieni kutsu — avaa kun olet valmis.</p>
            <div class="romant-story-cd" aria-hidden="true">
                <div class="romant-story-cd-unit"><span>··</span><small>päivää</small></div>
                <div class="romant-story-cd-unit"><span>··</span><small>tuntia</small></div>
                <div class="romant-story-cd-unit"><span>··</span><small>min</small></div>
            </div>
            <p class="romant-story-cta">Avaa kutsu</p>
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
