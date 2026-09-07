<?php
/**
 * Recipient view: /kutsu/{token}/ — teaser + peel history.
 *
 * Server prints teaser only. Level texts are JSON for JS. After "Avaa kutsu"
 * Tapaaminen (date + optional place) is always visible; hints stack as the
 * recipient peels (previous levels stay). Next hint requires the confirm
 * modal (+1). Depth stays in sessionStorage. Place is never on the teaser.
 *
 * @var WP_Post $post
 * @var array   $data
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title = 'Sinut on kutsuttu treffeille · romanttinen';
$body_class = 'romant-recipient-page is-teaser';
$share_url  = Romant_Kutsu_Rewrite::recipient_url($data['token']);
$story_url  = Romant_Kutsu_Rewrite::story_url($data['token']);
$fabric_url = ROMANT_KUTSU_URL . 'assets/img/hero-fabric.jpg';
$sections   = $data['sections'] ?? [];
if (!is_array($sections)) {
    $sections = [];
}
$saate      = (string) ($data['saate'] ?? '');
$location   = (string) ($data['location'] ?? '');
$tapaaminen = Romant_Kutsu_CPT::format_tapaaminen((string) ($data['datetime'] ?? ''));

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
<style>
    .romant-recipient-page { --romant-recipient-fabric: url('<?php echo esc_url($fabric_url); ?>'); }
</style>
<div class="romant-wrap romant-recipient" id="romant-recipient-page" data-romant-state="teaser">
    <header class="romant-recipient-chrome" id="romant-recipient-chrome" hidden>
        <p class="romant-wordmark">romanttinen.fi</p>
    </header>

    <article class="romant-invite-card"
             id="romant-invite"
             data-romant-countdown="<?php echo esc_attr($data['datetime']); ?>"
             data-romant-token="<?php echo esc_attr($data['token']); ?>"
             data-romant-sections="<?php echo esc_attr(wp_json_encode($sections_payload, JSON_UNESCAPED_UNICODE)); ?>">

        <div class="romant-gift-moment" id="romant-gift-moment">
            <p class="romant-wordmark">romanttinen.fi</p>

            <?php if (!empty($data['inviter_name'])) : ?>
                <p class="romant-inviter"><?php echo esc_html($data['inviter_name']); ?> kutsui sinut.</p>
            <?php endif; ?>

            <h1 class="romant-serif">Sinut on kutsuttu treffeille.</h1>

            <div class="romant-gift-rule" aria-hidden="true">
                <svg class="romant-gift-swirl" viewBox="0 0 36 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 8h9c2.2 0 3.4-5 5.8-5S20.6 13 23 13s3.6-5 5.8-5H34" stroke="currentColor" stroke-width="1.15" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="romant-countdown" aria-live="polite">
                <div class="romant-cd-unit"><span data-cd="d">–</span><small>Päivää</small></div>
                <div class="romant-cd-unit"><span data-cd="h">–</span><small>Tuntia</small></div>
                <div class="romant-cd-unit"><span data-cd="m">–</span><small>Minuuttia</small></div>
                <div class="romant-cd-unit"><span data-cd="s">–</span><small>Sekuntia</small></div>
            </div>
            <p class="romant-countdown-done" hidden>Hetki on täällä.</p>

            <?php if ($saate !== '') : ?>
                <p class="romant-saate" id="romant-saate"><?php echo esc_html($saate); ?></p>
            <?php endif; ?>
        </div>

        <div class="romant-teaser-actions" id="romant-teaser-actions">
            <button type="button" class="romant-btn romant-btn-primary romant-btn-lg romant-avaa-kutsu" id="romant-avaa-kutsu">
                Avaa kutsu
            </button>
        </div>

        <?php // Game: hidden until Avaa kutsu. Tapaaminen first; hints stack as depth grows. ?>
        <div class="romant-reveal" id="romant-reveal" hidden>
            <p class="romant-reveal-cue" id="romant-reveal-cue" hidden>Nyt saat tietää…</p>
            <?php if ($tapaaminen['line'] !== '') : ?>
                <section class="romant-tapaaminen" id="romant-tapaaminen" aria-label="Tapaaminen">
                    <h2 class="romant-serif romant-tapaaminen-title">Tapaaminen</h2>
                    <p class="romant-tapaaminen-when"><?php echo esc_html($tapaaminen['line']); ?></p>
                    <?php if ($location !== '') : ?>
                        <p class="romant-tapaaminen-place"><?php echo esc_html($location); ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <div id="romant-peli-sections" class="romant-peli-sections"></div>

            <?php if ($data['dress'] !== '') : ?>
                <div class="romant-dress romant-reveal-aside">
                    <span class="romant-spoiler-label">Pukeutumisvihje</span>
                    <p><?php echo esc_html($data['dress']); ?></p>
                </div>
            <?php endif; ?>

            <div class="romant-actions romant-reveal-aside">
                <a class="romant-btn romant-btn-ghost" href="<?php echo esc_url($story_url); ?>">Jaa tarina</a>
                <button type="button" class="romant-btn romant-btn-ghost" data-romant-copy-text="<?php echo esc_attr($share_url); ?>">
                    Kopioi linkki
                </button>
            </div>
        </div>
    </article>
</div>

<dialog class="romant-modal" id="romant-reveal-modal" aria-labelledby="romant-modal-title">
    <div class="romant-modal-inner">
        <h2 id="romant-modal-title" class="romant-serif">Haluatko kuulla lisää?</h2>
        <div class="romant-modal-rule" aria-hidden="true"></div>
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
