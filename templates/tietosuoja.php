<?php
/**
 * /tietosuoja/ — Portti 4 LOCKED copy.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title  = 'Tietosuoja · romanttinen';
$allow_index = true;
$body_class  = 'romant-craft-page romant-legal-page';

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-legal">
    <header class="romant-hero">
        <?php echo Romant_Kutsu_Templates::wordmark_markup(); ?>
        <h1 class="romant-serif">Tietosuoja</h1>
        <p class="romant-lead">romanttinen.fi · treffikutsu</p>
    </header>

    <article class="romant-card romant-legal-card">
        <h2 class="romant-serif romant-card-title">Rekisterinpitäjä</h2>
        <p><?php echo nl2br(esc_html(Romant_Kutsu_Settings::get_company_block())); ?></p>

        <h2 class="romant-serif romant-card-title">Mitä tietoja keräämme</h2>
        <p>
            Kutsun sisältö (nimet, aika, paikka, vihjeet), sähköpostiosoite kuittia ja
            hallintalinkkiä varten, sekä maksun tila. Maksukorttitietoja ei tallenneta
            meille — ne käsittelee Visma Pay.
        </p>

        <h2 class="romant-serif romant-card-title">Mihin tietoja käytetään</h2>
        <p>
            Toimitamme kutsun, kuitin ja hallintalinkin, vahvistamme maksun ja vastaamme
            tukeen. Emme myy tietoja eteenpäin.
        </p>

        <h2 class="romant-serif romant-card-title">Luovutukset</h2>
        <p>
            Maksunvälittäjä Visma Pay käsittelee maksun. Palvelinympäristö (WordPress)
            tallentaa kutsun siihen asti, kun se on tarpeen palvelun tuottamiseksi.
        </p>

        <h2 class="romant-serif romant-card-title">Säilytys</h2>
        <p>
            Kutsun tiedot säilytetään, kunnes poistat kutsun tai pyydät poistoa.
            Vastaanottajan eteneminen (vihjeiden avaus) tallennetaan vain hänen
            selaimeensa (sessionStorage) — ei meille.
        </p>

        <h2 class="romant-serif romant-card-title">Oikeutesi</h2>
        <p>
            Voit pyytää pääsyä, oikaisua tai poistoa. Ota yhteyttä:
            <?php echo esc_html(Romant_Kutsu_Settings::company_email()); ?>.
        </p>

        <p class="romant-hint">
            <a href="<?php echo esc_url(Romant_Kutsu_Rewrite::terms_url()); ?>">Käyttöehdot</a>
        </p>
    </article>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
