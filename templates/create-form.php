<?php
/**
 * Portti 2 craft (shortcode + /kutsu/uusi/). Pauliina cream-craft lock.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$sections     = Romant_Kutsu_CPT::default_craft_sections();
$craft_editor = true;
$invite_title = Romant_Kutsu_CPT::CRAFT_INVITE_TITLE;
$location     = 'Kotona';
$default_dt   = Romant_Kutsu_CPT::default_craft_datetime();
$dt_local     = $default_dt->format('Y-m-d\TH:i');
$dt_date      = $default_dt->format('Y-m-d');
$dt_time      = $default_dt->format('H:i');
$tapaaminen   = Romant_Kutsu_CPT::format_tapaaminen($default_dt->format('c'));
$pohjat       = Romant_Kutsu_CPT::craft_templates();
?>
<div class="romant-wrap romant-create romant-craft" data-romant-craft>
    <header class="romant-craft-top">
        <p class="romant-wordmark">romanttinen.fi</p>
        <span class="romant-luonnos-pill">Luonnos</span>
    </header>

    <p class="romant-craft-eyebrow">Lahjakutsu · Portti 2</p>
    <h1 class="romant-serif romant-craft-title">Rakenna kutsu</h1>
    <p class="romant-lead romant-craft-lead">Valitse tunnelma, muokkaa pehmeitä oletuksia — kuin pakkaisit lahjaa, et täyttäisi lomaketta.</p>

    <form class="romant-form romant-craft-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-romant-sections-form data-romant-craft-form>
        <input type="hidden" name="action" value="romant_create_kutsu" />
        <?php wp_nonce_field('romant_create_kutsu', 'romant_create_nonce'); ?>
        <input type="hidden" name="romant_inviter_name" id="romant_inviter_name" value="" />
        <input type="hidden" name="romant_saate" value="" />
        <input type="hidden" name="romant_dress" value="" />
        <input type="hidden" name="romant_datetime" id="romant_datetime" value="<?php echo esc_attr($dt_local); ?>" data-craft-datetime />

        <div class="romant-pohja" data-romant-pohja>
            <p class="romant-kicker">Pohja</p>
            <div class="romant-pohja-toggles" role="group" aria-label="Pohja">
                <?php foreach ($pohjat as $key => $pohja) : ?>
                    <button type="button"
                            class="romant-pohja-btn<?php echo $key === 'kotitreffit' ? ' is-selected' : ''; ?>"
                            data-pohja="<?php echo esc_attr($key); ?>"
                            aria-pressed="<?php echo $key === 'kotitreffit' ? 'true' : 'false'; ?>">
                        <?php echo esc_html($pohja['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <section class="romant-card romant-summary-card is-collapsed" data-craft-summary>
            <p class="romant-kicker">Kutsun nimi</p>
            <label class="romant-summary-title-label" for="romant_invite_title">
                <span class="screen-reader-text">Kutsun nimi</span>
                <input type="text" id="romant_invite_title" name="romant_invite_title"
                       class="romant-summary-title" maxlength="80"
                       value="<?php echo esc_attr($invite_title); ?>"
                       placeholder="Ilta kahdelle" />
            </label>
            <p class="romant-summary-line" data-craft-summary-line>
                <?php echo esc_html($tapaaminen['line'] !== '' ? $tapaaminen['line'] . ' · ' . $location : ''); ?>
            </p>
            <div class="romant-summary-meta">
                <label class="romant-summary-field">
                    <span class="screen-reader-text">Päivä</span>
                    <input type="date" data-craft-date value="<?php echo esc_attr($dt_date); ?>" />
                </label>
                <span class="romant-summary-dot" aria-hidden="true">·</span>
                <label class="romant-summary-field">
                    <span class="screen-reader-text">Aika</span>
                    <input type="time" data-craft-time value="<?php echo esc_attr($dt_time); ?>" />
                </label>
                <span class="romant-summary-dot" aria-hidden="true">·</span>
                <label class="romant-summary-field romant-summary-place">
                    <span class="screen-reader-text">Paikka</span>
                    <input type="text" id="romant_location" name="romant_location" maxlength="120"
                           autocomplete="off" data-craft-place
                           value="<?php echo esc_attr($location); ?>"
                           placeholder="Lisää paikka…" />
                </label>
            </div>
        </section>

        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — $sections / $craft_editor in scope.
        include ROMANT_KUTSU_PATH . 'templates/partials-sections-editor.php';
        ?>

        <p class="romant-craft-later">Hanki &amp; lähetys myöhemmin. Nyt keskitytään kutsun tunnelmaan — toimitus ja maksu seuraavassa askeleessa.</p>

        <button type="submit" class="romant-btn romant-btn-primary romant-btn-lg">Tallenna luonnos</button>
        <button type="submit" class="romant-craft-continue">tai jatka viimeistelyyn</button>
    </form>
</div>
