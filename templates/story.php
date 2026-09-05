<?php
/**
 * Story share page — teaser only (IG-safe): wordmark, title, countdown.
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

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-story-page">
    <p class="romant-lead romant-story-help">Ota kuvakaappaus tai lataa PNG — sopii Instagram Storiesiin. Ei spoilereita.</p>

    <div class="romant-story-frame" id="romant-story-frame"
         data-title="<?php echo esc_attr($story_title); ?>"
         data-inviter="<?php echo esc_attr($data['inviter_name'] ?? ''); ?>"
         data-romant-countdown="<?php echo esc_attr($data['datetime']); ?>">
        <div class="romant-story-inner">
            <?php echo Romant_Kutsu_Templates::logo_markup('romant-eyebrow-logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if (!empty($data['inviter_name'])) : ?>
                <p class="romant-inviter"><?php echo esc_html($data['inviter_name']); ?> kutsui sinut</p>
            <?php endif; ?>
            <h1 class="romant-serif"><?php echo esc_html($story_title); ?></h1>
            <div class="romant-countdown" aria-live="polite">
                <div class="romant-cd-unit"><span data-cd="d">–</span><small>pv</small></div>
                <div class="romant-cd-unit"><span data-cd="h">–</span><small>t</small></div>
                <div class="romant-cd-unit"><span data-cd="m">–</span><small>min</small></div>
                <div class="romant-cd-unit"><span data-cd="s">–</span><small>s</small></div>
            </div>
            <p class="romant-countdown-done" hidden>Hetki on täällä.</p>
            <p class="romant-story-brand">romanttinen.fi</p>
        </div>
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
