<?php
/**
 * Create invite form (shortcode + /kutsu/uusi/).
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}
$checklist = Romant_Kutsu_Templates::checklist_items();
$sections  = [Romant_Kutsu_CPT::empty_section()];
?>
<div class="romant-wrap romant-create">
    <header class="romant-hero">
        <?php echo Romant_Kutsu_Templates::logo_markup('romant-eyebrow-logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <h1 class="romant-serif">Luo kutsu</h1>
        <p class="romant-lead">Luo luonnos ilmaiseksi. Jakolinkki avautuu maksun jälkeen.</p>
    </header>

    <ol class="romant-checklist" aria-label="Tarkistuslista">
        <?php foreach ($checklist as $i => $item) : ?>
            <li class="<?php echo $i === 0 ? 'is-current' : ''; ?>">
                <span class="romant-check-num"><?php echo esc_html((string) ($i + 1)); ?></span>
                <?php echo esc_html($item); ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <form class="romant-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-romant-sections-form>
        <input type="hidden" name="action" value="romant_create_kutsu" />
        <?php wp_nonce_field('romant_create_kutsu', 'romant_create_nonce'); ?>

        <fieldset>
            <legend>Kutsujan nimi</legend>
            <label for="romant_inviter_name">Kutsujan nimi <span class="req">*</span></label>
            <input type="text" id="romant_inviter_name" name="romant_inviter_name" required maxlength="80"
                   autocomplete="name" placeholder="Esim. Alex" />
            <p class="romant-hint">Näkyy vastaanottajalle teaserissa — lisää luottamusta (ei spam).</p>
        </fieldset>

        <fieldset>
            <legend>Saate</legend>
            <label for="romant_saate">Saate (valinnainen)</label>
            <textarea id="romant_saate" name="romant_saate" rows="2" maxlength="400"
                      placeholder="Lyhyt tervehdys teaserissa — ei spoilereita…"></textarea>
            <p class="romant-hint">Näkyy hiljaa otsikon alla ennen kuin kutsu avataan.</p>
        </fieldset>

        <fieldset>
            <legend>Valitse aika</legend>
            <label for="romant_datetime">Päivä ja aika (Suomi)</label>
            <input type="datetime-local" id="romant_datetime" name="romant_datetime" required />
        </fieldset>

        <?php
        // phpcs:ignore WordPress.FAKESECRET_k1l2m3n4o5p6q7r8s9t0 -- $sections in scope for partial.
        include ROMANT_KUTSU_PATH . 'templates/partials-sections-editor.php';
        ?>

        <fieldset>
            <legend>Pukeutumisvihje</legend>
            <label for="romant_dress">Pukeutumisvihje (valinnainen)</label>
            <textarea id="romant_dress" name="romant_dress" rows="2" maxlength="300"
                      placeholder="Esim. jotain pehmeää ja kaunista…"></textarea>
        </fieldset>

        <button type="submit" class="romant-btn romant-btn-primary">Tallenna luonnos</button>
    </form>
</div>
