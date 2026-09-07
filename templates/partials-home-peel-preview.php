<?php
/**
 * Decorative homepage peel-stack preview (Pauliina 1.3.4). Display only.
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
        <p class="romant-tapaaminen-when">La 14.3. · 18:00</p>
        <p class="romant-tapaaminen-place">Kotona</p>
    </section>
    <section class="romant-osio romant-osio-tint-0" aria-hidden="true">
        <h2 class="romant-osio-title romant-serif">Elokuvahetki</h2>
        <div class="romant-hint-stack">
            <div class="romant-hint romant-osio-level romant-hint-box is-prior">
                <span class="romant-spoiler-label">Vihje 1</span>
                <p>Elokuva — mutta ei se, jota arvaat ensimmäisenä.</p>
            </div>
            <div class="romant-hint romant-osio-level romant-hint-box is-newest">
                <span class="romant-spoiler-label">Vihje 2</span>
                <p>Paikat on varattu — loppu selviää perillä.</p>
            </div>
        </div>
    </section>
    <span class="romant-btn romant-btn-ghost romant-more-btn">Haluatko kuulla lisää?</span>
</a>
