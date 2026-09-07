<?php
/**
 * Decorative homepage peel-stack preview (Pauliina 1.3.3). Display only.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}
$preview_href = $preview_href ?? '#';
?>
<a class="romant-home-preview" href="<?php echo esc_url($preview_href); ?>" aria-label="Esimerkki avatusta kutsusta">
    <p class="romant-home-preview-kicker">Kutsu · esikatselu</p>
    <section class="romant-tapaaminen is-restored" aria-hidden="true">
        <span class="romant-tapaaminen-label">Tapaaminen</span>
        <p class="romant-tapaaminen-when">La 14.6. · 18:00</p>
        <p class="romant-tapaaminen-place">Paikka valinnainen</p>
    </section>
    <div class="romant-hint romant-osio-level is-prior" aria-hidden="true">
        <span class="romant-hint-section">Elokuvahetki</span>
        <span class="romant-spoiler-label">Vihje</span>
        <p>Elokuva — mutta ei se, jota arvaat ensimmäisenä.</p>
    </div>
    <div class="romant-hint romant-osio-level is-newest" aria-hidden="true">
        <span class="romant-hint-section">Yhteinen ateria</span>
        <span class="romant-spoiler-label">Vihje</span>
        <p>Syödään hyvin — ei pikaruokaa.</p>
    </div>
    <span class="romant-btn romant-btn-ghost romant-more-btn">Haluatko kuulla lisää?</span>
</a>
