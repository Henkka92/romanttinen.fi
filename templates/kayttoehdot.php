<?php
/**
 * /kayttoehdot/ — Portti 4 LOCKED copy.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title  = 'Käyttöehdot · romanttinen';
$allow_index = true;
$body_class  = 'romant-craft-page romant-legal-page';

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-legal">
    <header class="romant-hero">
        <?php echo Romant_Kutsu_Templates::wordmark_markup(); ?>
        <h1 class="romant-serif">Käyttöehdot</h1>
        <p class="romant-lead">romanttinen.fi · treffikutsu</p>
    </header>

    <article class="romant-card romant-legal-card">
        <h2 class="romant-serif romant-card-title">Palveluntarjoaja</h2>
        <p><?php echo nl2br(esc_html(Romant_Kutsu_Settings::get_company_block())); ?></p>

        <h2 class="romant-serif romant-card-title">Palvelu</h2>
        <p>
            Romanttinen on digitaalinen treffikutsu. Luot kutsun, maksat jakolinkin
            ja vastaanottaja avaa vihjeet vaiheittain. Luonnos ja esikatselu ovat ilmaisia.
        </p>

        <h2 class="romant-serif romant-card-title">Hinta ja maksaminen</h2>
        <p>
            Jakolinkin hinta on <strong><?php echo esc_html(Romant_Kutsu_Settings::get_price_vat_line()); ?></strong>.
            Maksu veloitetaan, kun hankit jaettavan linkin. Maksunvälittäjä on Visma Pay.
        </p>

        <h2 class="romant-serif romant-card-title">Toimitus</h2>
        <p>
            Digitaalinen sisältö toimitetaan heti maksun jälkeen: saat jakolinkin ja kuitin
            sähköpostiin. Palvelu alkaa välittömästi.
        </p>

        <h2 class="romant-serif romant-card-title">Peruuttamisoikeus</h2>
        <p>
            Kutsu on digitaalinen sisältö, joka toimitetaan heti. Peruuttamisoikeutta ei ole
            (kuluttajansuojalaki 6 luku 16 § / KSL 6:16), kun vahvistat tämän ennen maksua.
        </p>

        <h2 class="romant-serif romant-card-title">Vastuu</h2>
        <p>
            Palvelu tarjotaan sellaisenaan. Emme vastaa vastaanottajan laitteen, verkon tai
            kolmannen osapuolen (esim. Visma Pay) häiriöistä. Säilytä hallintalinkki —
            se on avaimesi kutsuun.
        </p>

        <h2 class="romant-serif romant-card-title">Sovellettava laki</h2>
        <p>Näihin ehtoihin sovelletaan Suomen lakia.</p>

        <p class="romant-hint">
            <a href="<?php echo esc_url(Romant_Kutsu_Rewrite::privacy_url()); ?>">Tietosuoja</a>
        </p>
    </article>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
