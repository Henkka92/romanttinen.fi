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
$sections  = Romant_Kutsu_CPT::sample_sections();
?>
<div class="romant-wrap romant-create">
    <header class="romant-hero">
        <p class="romant-wordmark">romanttinen.fi</p>
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

    <form class="romant-form romant-craft-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-romant-sections-form>
        <input type="hidden" name="action" value="romant_create_kutsu" />
        <?php wp_nonce_field('romant_create_kutsu', 'romant_create_nonce'); ?>

        <section class="romant-card">
            <h2 class="romant-serif romant-card-title">Perustiedot</h2>
            <label for="romant_inviter_name">Kutsujan nimi <span class="req">*</span></label>
            <input type="text" id="romant_inviter_name" name="romant_inviter_name" required maxlength="80"
                   autocomplete="name" placeholder="Esim. Alex" />
            <label for="romant_saate">Saate (valinnainen)</label>
            <textarea id="romant_saate" name="romant_saate" rows="2" maxlength="400"
                      placeholder="Lyhyt tervehdys teaserissa — ei spoilereita…"></textarea>
            <label for="romant_datetime">Päivä ja aika (Suomi) <span class="req">*</span></label>
            <input type="datetime-local" id="romant_datetime" name="romant_datetime" required />
            <label for="romant_location">Paikka</label>
            <input type="text" id="romant_location" name="romant_location" maxlength="120"
                   autocomplete="off" placeholder="Lisää paikka…" />
            <p class="romant-hint">Tapaaminen näkyy saajalle vasta avauksen jälkeen — ei teaserissa.</p>
        </section>

        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $sections in scope for partial.
        include ROMANT_KUTSU_PATH . 'templates/partials-sections-editor.php';
        ?>

        <section class="romant-card">
            <h2 class="romant-serif romant-card-title">Pukeutumisvihje</h2>
            <label for="romant_dress">Pukeutumisvihje (valinnainen)</label>
            <textarea id="romant_dress" name="romant_dress" rows="2" maxlength="300"
                      placeholder="Esim. jotain pehmeää ja kaunista…"></textarea>
        </section>

        <button type="submit" class="romant-btn romant-btn-primary romant-btn-lg">Tallenna luonnos</button>
    </form>
</div>
