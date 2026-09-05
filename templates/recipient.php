<?php
/**
 * Recipient view: /kutsu/{token}/ — teaser + progressive peli.
 *
 * Server prints teaser only. Section level texts are passed as JSON for JS;
 * visible HTML for levels is rendered client-side after "Avaa kutsu", and only
 * unlocked depths (levels[0..depth-1]) are shown.
 *
 * @var WP_Post $post
 * @var array   $data
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title = 'Sinut on kutsuttu treffeille · romanttinen';
$share_url  = Romant_Kutsu_Rewrite::recipient_url($data['token']);
$story_url  = Romant_Kutsu_Rewrite::story_url($data['token']);
$sections   = $data['sections'] ?? [];
if (!is_array($sections)) {
    $sections = [];
}
$saate = (string) ($data['saate'] ?? '');

// Prepare JSON for progressive UI (all levels in data attr; JS reveals by depth).
$sections_payload = [];
foreach ($sections as $si => $sec) {
    $levels_clean = [];
    foreach ($sec['levels'] ?? [] as $lv) {
        $t = (string) $lv;
        if ($t === '' && $levels_clean === []) {
            continue;
        }
        $levels_clean[] = $t;
    }
    while (count($levels_clean) > 0 && end($levels_clean) === '') {
        array_pop($levels_clean);
    }
    if ($levels_clean === []) {
        continue;
    }
    $sections_payload[] = [
        'id'     => (string) ($sec['id'] ?? (string) $si),
        'title'  => (string) ($sec['title'] ?? ''),
        'levels' => $levels_clean,
    ];
}

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-recipient">
    <article class="romant-invite-card"
             id="romant-invite"
             data-romant-countdown="<?php echo esc_attr($data['datetime']); ?>"
             data-romant-token="<?php echo esc_attr($data['token']); ?>"
             data-romant-sections="<?php echo esc_attr(wp_json_encode($sections_payload, JSON_UNESCAPED_UNICODE)); ?>">

        <?php echo Romant_Kutsu_Templates::logo_markup('romant-eyebrow-logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <?php if (!empty($data['inviter_name'])) : ?>
            <p class="romant-inviter"><?php echo esc_html($data['inviter_name']); ?> kutsui sinut</p>
        <?php endif; ?>

        <h1 class="romant-serif">Sinut on kutsuttu treffeille</h1>

        <div class="romant-countdown" aria-live="polite">
            <div class="romant-cd-unit"><span data-cd="d">–</span><small>pv</small></div>
            <div class="romant-cd-unit"><span data-cd="h">–</span><small>t</small></div>
            <div class="romant-cd-unit"><span data-cd="m">–</span><small>min</small></div>
            <div class="romant-cd-unit"><span data-cd="s">–</span><small>s</small></div>
        </div>
        <p class="romant-countdown-done" hidden>Hetki on täällä.</p>

        <?php if ($saate !== '') : ?>
            <p class="romant-saate" id="romant-saate"><?php echo esc_html($saate); ?></p>
        <?php endif; ?>

        <div class="romant-teaser-actions" id="romant-teaser-actions">
            <button type="button" class="romant-btn romant-btn-primary romant-btn-lg" id="romant-avaa-kutsu">
                Avaa kutsu
            </button>
            <div class="romant-actions romant-teaser-share">
                <a class="romant-btn romant-btn-secondary" href="<?php echo esc_url($story_url); ?>">Jaa tarina</a>
                <button type="button" class="romant-btn romant-btn-ghost" data-romant-copy-text="<?php echo esc_attr($share_url); ?>">
                    Kopioi linkki
                </button>
            </div>
        </div>

        <?php // Game / sections: hidden until Avaa kutsu (JS removes hidden). No levels printed as HTML. ?>
        <div class="romant-reveal" id="romant-reveal" hidden>
            <div id="romant-peli-sections" class="romant-peli-sections"></div>

            <?php if ($data['dress'] !== '') : ?>
                <div class="romant-dress">
                    <span class="romant-spoiler-label">Pukeutumisvihje</span>
                    <p><?php echo esc_html($data['dress']); ?></p>
                </div>
            <?php endif; ?>

            <div class="romant-actions">
                <a class="romant-btn romant-btn-primary" href="<?php echo esc_url($story_url); ?>">Jaa tarina</a>
                <button type="button" class="romant-btn romant-btn-secondary" data-romant-copy-text="<?php echo esc_attr($share_url); ?>">
                    Kopioi linkki
                </button>
            </div>
        </div>
    </article>
</div>

<dialog class="romant-modal" id="romant-reveal-modal" aria-labelledby="romant-modal-title">
    <div class="romant-modal-inner">
        <h2 id="romant-modal-title" class="romant-serif">Haluatko kuulla lisää?</h2>
        <p class="romant-modal-body">Voit pitää jännityksen — tai avata seuraavan vihjeen.</p>
        <div class="romant-modal-actions">
            <button type="button" class="romant-btn romant-btn-primary" id="romant-modal-yes">
                Kerro lisää
            </button>
            <button type="button" class="romant-btn romant-btn-ghost" id="romant-modal-no">
                Pidän jännityksen
            </button>
        </div>
    </div>
</dialog>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
