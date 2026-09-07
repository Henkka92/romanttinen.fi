<?php
/**
 * Marketing homepage: front-page override + [romant_etusivu] shortcode.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Home {

    public const OPTION_USE_HOME = 'romant_kutsu_use_homepage';

    public function register(): void {
        add_shortcode('romant_etusivu', [$this, 'shortcode_home']);
        add_action('template_redirect', [$this, 'maybe_render_front'], 5);
    }

    /**
     * Setting ON (default) → replace front page with standalone marketing layout.
     * Kutsu routes still win (romant_route query var set earlier in rewrite).
     */
    public function maybe_render_front(): void {
        if (!self::is_enabled()) {
            return;
        }
        if (get_query_var('romant_route')) {
            return;
        }
        if (!is_front_page()) {
            return;
        }
        // Avoid hijacking admin / feeds / REST.
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        status_header(200);
        nocache_headers();
        Romant_Kutsu_Templates::render('home');
        exit;
    }

    public function shortcode_home(): string {
        // Embeddable fragment (theme chrome). Prefer full override for brand.
        Romant_Kutsu_Templates::enqueue_assets('home');
        $create_url = Romant_Kutsu_Rewrite::create_url();

        ob_start();
        ?>
        <div class="romant-kutsu-body romant-home-body" style="min-height:auto;background:transparent;">
            <div class="romant-home" style="min-height:auto;padding-top:1rem;padding-bottom:2rem;">
                <header class="romant-home-header">
                    <p class="romant-wordmark romant-home-wordmark">romanttinen.fi</p>
                </header>
                <section class="romant-home-hero" aria-labelledby="romant-sc-headline">
                    <div class="romant-home-copy">
                        <h1 id="romant-sc-headline">Kutsu mielitiettysi treffeille tavalla, joka jää mieleen.</h1>
                        <p class="romant-home-sub">Luo henkilökohtainen treffikutsu, joka paljastuu vaiheittain. Luo ilmaiseksi. Maksat 4,90 € vasta, kun lähetät kutsun.</p>
                        <div class="romant-home-ctas">
                            <a class="romant-btn romant-btn-primary" href="<?php echo esc_url($create_url); ?>">
                                Luo oma kutsu
                            </a>
                        </div>
                    </div>
                    <?php
                    $preview_href = $create_url;
                    include ROMANT_KUTSU_PATH . 'templates/partials-home-peel-preview.php';
                    ?>
                </section>
                <section class="romant-home-steps" id="nain-se-toimii" aria-labelledby="romant-sc-steps-heading">
                    <h2 id="romant-sc-steps-heading" class="romant-home-steps-heading">Näin se toimii</h2>
                    <ol class="romant-home-steps-list">
                        <li class="romant-home-step">
                            <span class="romant-home-step-num">1</span>
                            <span class="romant-home-step-body">
                                <strong class="romant-home-step-title">Luo kutsu</strong>
                                <span class="romant-home-step-desc">Valitse aika ja kirjoita omat vihjeesi.</span>
                            </span>
                        </li>
                        <li class="romant-home-step-arrow" aria-hidden="true">→</li>
                        <li class="romant-home-step">
                            <span class="romant-home-step-num">2</span>
                            <span class="romant-home-step-body">
                                <strong class="romant-home-step-title">Jaa linkki</strong>
                                <span class="romant-home-step-desc">Maksat vasta, kun kutsu on valmis.</span>
                            </span>
                        </li>
                        <li class="romant-home-step-arrow" aria-hidden="true">→</li>
                        <li class="romant-home-step">
                            <span class="romant-home-step-num">3</span>
                            <span class="romant-home-step-body">
                                <strong class="romant-home-step-title">Saaja avaa vihjeet</strong>
                                <span class="romant-home-step-desc">Jännitys kasvaa saajan tahtiin.</span>
                            </span>
                        </li>
                    </ol>
                </section>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function is_enabled(): bool {
        return get_option(self::OPTION_USE_HOME, '1') === '1';
    }
}
