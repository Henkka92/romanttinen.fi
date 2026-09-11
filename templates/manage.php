<?php
/**
 * Manage + preview + pay: /kutsu/hallitse/{manage_key}/
 *
 * @var WP_Post $post
 * @var array   $data
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title = 'Hallitse kutsua · romanttinen';
$body_class = 'romant-craft-page';
$manage_key = $data['manage_key'];
$paid       = !empty($data['paid']) && $data['token'] !== '';
$price_disp = Romant_Kutsu_Settings::get_price_display();
$dt_local   = Romant_Kutsu_Forms::datetime_local_value($data['datetime']);
$checklist  = Romant_Kutsu_Templates::checklist_items();
$share_url  = $paid ? Romant_Kutsu_Rewrite::recipient_url($data['token']) : '';
$sections   = $data['sections'] ?? [];
if (!is_array($sections) || $sections === []) {
    $sections = Romant_Kutsu_CPT::default_craft_sections();
}

$saved       = isset($_GET['saved']);
$paid_flash  = isset($_GET['paid']);
$pay_error   = isset($_GET['pay_error']) ? sanitize_text_field(wp_unslash((string) $_GET['pay_error'])) : '';
$email_sent  = isset($_GET['email_sent']);
$email_error = isset($_GET['email_error']) ? sanitize_text_field(wp_unslash((string) $_GET['email_error'])) : '';
$stored_email = isset($data['email']) ? (string) $data['email'] : '';
$saate        = (string) ($data['saate'] ?? '');
$location     = (string) ($data['location'] ?? '');
$tapaaminen   = Romant_Kutsu_CPT::format_tapaaminen((string) ($data['datetime'] ?? ''));

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
?>
<div class="romant-wrap romant-manage">
    <header class="romant-hero">
        <p class="romant-wordmark">romanttinen.fi</p>
        <h1 class="romant-serif">Hallitse kutsua</h1>
        <p class="romant-lead">
            Tallenna tämä linkki — se on ainoa tapa muokata kutsua.
            <?php if (!$paid) : ?>
                Esikatselu on ilmainen. Jakolinkki avautuu maksulla (<?php echo esc_html($price_disp); ?>).
            <?php endif; ?>
        </p>
    </header>

    <?php if ($saved) : ?>
        <div class="romant-alert romant-alert-ok" role="status">Muutokset tallennettu.</div>
    <?php endif; ?>
    <?php if ($paid_flash) : ?>
        <div class="romant-alert romant-alert-ok" role="status">Maksu vastaanotettu! Jakolinkki on valmis.</div>
    <?php endif; ?>
    <?php if ($pay_error === 'email') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Anna kelvollinen sähköpostiosoite ennen maksua — lähetämme kuitin ja hallintalinkin siihen.
        </div>
    <?php elseif ($pay_error === 'no_visma') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Visma Pay -tunnuksia ei ole asetettu. Lisää API-avain ja private key Asetukset → Romanttinen -sivulla, tai pidä Stub payments päällä demoon.
        </div>
    <?php elseif ($pay_error === 'visma_charge') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Maksun luonti Visma Payssa epäonnistui. Tarkista API-avain ja private key, tai yritä uudelleen.
        </div>
    <?php elseif ($pay_error === 'bad_authcode') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Maksun paluuviestin allekirjoitus ei täsmää. Älä maksa uudelleen — ota yhteyttä tukeen.
        </div>
    <?php elseif ($pay_error === 'payment_failed' || $pay_error === 'not_settled') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Maksua ei vahvistettu. Voit yrittää uudelleen tai käyttää toista maksutapaa.
        </div>
    <?php elseif ($pay_error === 'status_check_failed') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Maksun tilaa ei voitu tarkistaa juuri nyt. Odota hetki — ilmoitus voi vielä saapua.
        </div>
    <?php elseif ($pay_error !== '') : ?>
        <div class="romant-alert romant-alert-err" role="alert">
            Maksussa tapahtui virhe. Yritä uudelleen tai ota Stub payments käyttöön stagingissa.
        </div>
    <?php endif; ?>
    <?php if ($email_sent && $paid_flash) : ?>
        <div class="romant-alert romant-alert-ok" role="status">Kuitti lähetettiin sähköpostiisi (sisältää hallintalinkin).</div>
    <?php elseif ($email_sent) : ?>
        <div class="romant-alert romant-alert-ok" role="status">Hallintalinkki lähetettiin sähköpostiisi.</div>
    <?php endif; ?>
    <?php if ($email_error === 'invalid') : ?>
        <div class="romant-alert romant-alert-err" role="alert">Anna kelvollinen sähköpostiosoite.</div>
    <?php elseif ($email_error === 'rate') : ?>
        <div class="romant-alert romant-alert-err" role="alert">Odota hetki ennen kuin lähetät uudelleen.</div>
    <?php elseif ($email_error === 'fail') : ?>
        <div class="romant-alert romant-alert-err" role="alert">Sähköpostin lähetys epäonnistui. Tarkista osoite tai yritä myöhemmin.</div>
    <?php elseif ($email_error === 'not_paid') : ?>
        <div class="romant-alert romant-alert-err" role="alert">Hallintalinkin voi lähettää vasta maksun jälkeen.</div>
    <?php elseif ($email_error !== '') : ?>
        <div class="romant-alert romant-alert-err" role="alert">Sähköpostin lähetys epäonnistui. Yritä uudelleen.</div>
    <?php endif; ?>


    <?php if (!Romant_Kutsu_CPT::sections_offer_peel($sections)) : ?>
        <div class="romant-alert romant-alert-err" role="status">
            Peel («Haluatko kuulla lisää?» / Kerro lisää) vaatii vähintään <strong>2 tasoa</strong> yhdessä osiossa.
            Täytä taso 2 (ja mieluiten 3) ennen jakolinkkitestiä — muuten avauksen jälkeen näkyy vain L1.
        </div>
    <?php endif; ?>

    <ol class="romant-checklist" aria-label="Tarkistuslista">
        <?php
        $has_section = false;
        foreach ($sections as $sec) {
            if (($sec['title'] ?? '') !== '' && (($sec['levels'][0] ?? '') !== '')) {
                $has_section = true;
                break;
            }
        }
        $steps_done = [
            $data['datetime'] !== '',
            $has_section,
            true, // preview always available
            $paid,
        ];
        foreach ($checklist as $i => $item) :
            $cls = $steps_done[$i] ? 'is-done' : '';
            if (!$steps_done[$i] && ($i === 0 || $steps_done[$i - 1])) {
                $cls = 'is-current';
            }
            ?>
            <li class="<?php echo esc_attr($cls); ?>">
                <span class="romant-check-num"><?php echo esc_html((string) ($i + 1)); ?></span>
                <?php echo esc_html($item); ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <?php /* Share / pay / send CTA first (esp. mobile) — after header, flashes, checklist */ ?>
    <div class="romant-manage-cta">
        <?php if ($paid) : ?>
            <section class="romant-share-box">
                <h2 class="romant-serif">Jakolinkki</h2>
                <p class="romant-share-url"><code id="romant-share-url"><?php echo esc_html($share_url); ?></code></p>
                <div class="romant-actions">
                    <button type="button" class="romant-btn romant-btn-secondary" data-romant-copy="#romant-share-url">Kopioi linkki</button>
                    <a class="romant-btn romant-btn-ghost" href="<?php echo esc_url($share_url); ?>" target="_blank" rel="noopener">Avaa kutsu</a>
                </div>
            </section>

            <section class="romant-email-box">
                <h2 class="romant-serif">Hallintalinkki sähköpostiin</h2>
                <p class="romant-hint">Lähetä salainen hallintalinkki itsellesi, jotta et menetä <?php echo esc_html($price_disp); ?>:n työtä.</p>
                <form class="romant-form romant-email-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="romant_send_manage_email" />
                    <input type="hidden" name="manage_key" value="<?php echo esc_attr($manage_key); ?>" />
                    <?php wp_nonce_field('romant_send_manage_email', 'romant_email_nonce'); ?>
                    <label for="romant_email_manage">Lähetä hallintalinkki sähköpostiini</label>
                    <input type="email" id="romant_email_manage" name="romant_email"
                           value="<?php echo esc_attr($stored_email); ?>"
                           placeholder="oma@example.com" autocomplete="email" required />
                    <button type="submit" class="romant-btn romant-btn-secondary">Lähetä hallintalinkki</button>
                </form>
            </section>
        <?php else : ?>
            <section class="romant-pay-box">
                <h2 class="romant-serif">Hanki jaettava linkki</h2>
                <p class="romant-hint">Esikatselu on ilmainen. Jakolinkki avautuu maksulla (<?php echo esc_html($price_disp); ?>).</p>
                <form class="romant-pay-form romant-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="romant_pay_kutsu" />
                    <input type="hidden" name="manage_key" value="<?php echo esc_attr($manage_key); ?>" />
                    <?php wp_nonce_field('romant_pay_kutsu', 'romant_pay_nonce'); ?>
                    <label for="romant_inviter_name_pay">Kutsujan nimi <span class="req">*</span></label>
                    <input type="text" id="romant_inviter_name_pay" name="romant_inviter_name" required maxlength="80"
                           autocomplete="name"
                           value="<?php echo esc_attr($data['inviter_name'] ?? ''); ?>"
                           placeholder="Esim. Henry" />
                    <p class="romant-hint">Näkyy saajalle teaserissa (“X kutsui sinut”).</p>
                    <?php
                    $receipt_prefill = trim((string) ($data['receipt_name'] ?? ''));
                    if ($receipt_prefill === '') {
                        $receipt_prefill = (string) ($data['inviter_name'] ?? '');
                    }
                    ?>
                    <label for="romant_receipt_name_pay">Nimi kuittiin</label>
                    <input type="text" id="romant_receipt_name_pay" name="romant_receipt_name" maxlength="80"
                           autocomplete="name"
                           value="<?php echo esc_attr($receipt_prefill); ?>"
                           placeholder="Esim. Henry Manni" />
                    <p class="romant-hint">Oletuksena sama kuin kutsujan nimi — voit vaihtaa jos kuitti menee toiselle.</p>
                    <label for="romant_email_pay">Sähköposti kuittia varten <span class="req">*</span></label>
                    <input type="email" id="romant_email_pay" name="romant_email" required
                           value="<?php echo esc_attr($stored_email); ?>"
                           placeholder="oma@example.com" autocomplete="email" />
                    <p class="romant-hint">Pakollinen — lähetämme kuitin ja hallintalinkin maksun jälkeen.</p>
                    <button type="submit" class="romant-btn romant-btn-primary romant-btn-lg">
                        Hanki jaettava linkki (<?php echo esc_html($price_disp); ?>)
                    </button>
                    <?php if (Romant_Kutsu_Settings::stub_payments_enabled()) : ?>
                        <p class="romant-hint">Stub-tila: maksu merkitään automaattisesti maksetuksi (ei Vismaa).</p>
                    <?php endif; ?>
                </form>
            </section>
        <?php endif; ?>
    </div>

    <div class="romant-manage-grid">
        <section class="romant-panel romant-edit-panel">
            <h2 class="romant-serif">Muokkaa</h2>
            <form class="romant-form romant-craft-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-romant-sections-form>
                <input type="hidden" name="action" value="romant_update_kutsu" />
                <input type="hidden" name="manage_key" value="<?php echo esc_attr($manage_key); ?>" />
                <?php wp_nonce_field('romant_update_kutsu', 'romant_update_nonce'); ?>

                <section class="romant-card">
                    <h2 class="romant-serif romant-card-title">Perustiedot</h2>
                    <label for="romant_inviter_name">Kutsujan nimi <span class="req">*</span></label>
                    <input type="text" id="romant_inviter_name" name="romant_inviter_name" required maxlength="80"
                           autocomplete="name"
                           value="<?php echo esc_attr($data['inviter_name'] ?? ''); ?>"
                           placeholder="Esim. Alex" />
                    <label for="romant_saate">Saate (valinnainen)</label>
                    <textarea id="romant_saate" name="romant_saate" rows="2" maxlength="400"
                              placeholder="Lyhyt tervehdys teaserissa — ei spoilereita…"><?php echo esc_textarea($saate); ?></textarea>
                    <label for="romant_datetime">Päivä ja aika (Suomi) <span class="req">*</span></label>
                    <input type="datetime-local" id="romant_datetime" name="romant_datetime"
                           value="<?php echo esc_attr($dt_local); ?>" required />
                    <label for="romant_location">Paikka</label>
                    <input type="text" id="romant_location" name="romant_location" maxlength="120"
                           autocomplete="off" placeholder="Lisää paikka…"
                           value="<?php echo esc_attr($location); ?>" />
                    <p class="romant-hint">Tapaaminen näkyy saajalle vasta avauksen jälkeen — ei teaserissa.</p>
                </section>

                <?php include ROMANT_KUTSU_PATH . 'templates/partials-sections-editor.php'; ?>

                <section class="romant-card">
                    <h2 class="romant-serif romant-card-title">Pukeutumisvihje</h2>
                    <label for="romant_dress">Pukeutumisvihje (valinnainen)</label>
                    <textarea id="romant_dress" name="romant_dress" rows="2" maxlength="300"><?php echo esc_textarea($data['dress']); ?></textarea>
                </section>

                <button type="submit" class="romant-btn romant-btn-primary romant-btn-lg">Tallenna muutokset</button>
            </form>
        </section>

        <section class="romant-panel romant-preview" aria-label="Esikatselu">
            <h2 class="romant-serif">Näin kutsu näyttää</h2>
            <div class="romant-preview-card" data-romant-countdown="<?php echo esc_attr($data['datetime']); ?>">
                <p class="romant-wordmark">romanttinen.fi</p>
                <?php if (!empty($data['inviter_name'])) : ?>
                    <p class="romant-inviter"><?php echo esc_html($data['inviter_name']); ?> kutsui sinut</p>
                <?php endif; ?>
                <h3 class="romant-serif">Sinut on kutsuttu treffeille</h3>
                <?php if ($saate !== '') : ?>
                    <p class="romant-saate"><?php echo esc_html($saate); ?></p>
                <?php endif; ?>
                <div class="romant-countdown" aria-live="polite">
                    <div class="romant-cd-unit"><span data-cd="d">–</span><small>pv</small></div>
                    <div class="romant-cd-unit"><span data-cd="h">–</span><small>t</small></div>
                    <div class="romant-cd-unit"><span data-cd="m">–</span><small>min</small></div>
                    <div class="romant-cd-unit"><span data-cd="s">–</span><small>s</small></div>
                </div>
                <p class="romant-preview-note">Avauksen jälkeen: Tapaaminen + kunkin osion 1. taso (tasot 2+ avautuvat saajalle).</p>
                <?php if ($tapaaminen['line'] !== '') : ?>
                    <section class="romant-tapaaminen romant-tapaaminen-display is-restored" aria-label="Tapaaminen">
                        <span class="romant-tapaaminen-label">Tapaaminen</span>
                        <p class="romant-tapaaminen-when"><?php echo esc_html($tapaaminen['line']); ?></p>
                        <?php if ($location !== '') : ?>
                            <p class="romant-tapaaminen-place"><?php echo esc_html($location); ?></p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                <?php foreach ($sections as $sec) :
                    $st = (string) ($sec['title'] ?? '');
                    $lv = (string) (($sec['levels'][0] ?? ''));
                    if ($st === '' && $lv === '') {
                        continue;
                    }
                    ?>
                    <div class="romant-spoiler romant-osio-preview">
                        <span class="romant-spoiler-label"><?php echo esc_html($st !== '' ? $st : 'Osio'); ?></span>
                        <p><?php echo esc_html($lv !== '' ? $lv : '—'); ?></p>
                        <?php if (count($sec['levels'] ?? []) > 1) : ?>
                            <p class="romant-more-hint">Haluatko kuulla lisää?</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($data['dress'] !== '') : ?>
                    <div class="romant-dress">
                        <span class="romant-spoiler-label">Pukeutumisvihje</span>
                        <p><?php echo esc_html($data['dress']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
