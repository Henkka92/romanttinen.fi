<?php
/**
 * Marketing homepage: front-page override + [romant_etusivu] shortcode.
 * Portti 3 demos (Nea v3 + Satu PASS) live here — no CPT demo tokens yet.
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
        Romant_Kutsu_Templates::enqueue_assets('home');
        $create_url = Romant_Kutsu_Rewrite::create_url();

        ob_start();
        ?>
        <div class="romant-kutsu-body romant-home-body" style="min-height:auto;">
            <?php include ROMANT_KUTSU_PATH . 'templates/partials-home.php'; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function is_enabled(): bool {
        return get_option(self::OPTION_USE_HOME, '1') === '1';
    }

    public static function demo_url(string $slug): string {
        return home_url('/kutsu/demo/' . rawurlencode($slug) . '/');
    }

    /**
     * Locked Nea v3 demos (Satu PASS). Card = inviter kicker + kutsun nimi + korttirivi.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function demos(): array {
        return [
            'aino'  => [
                'slug'       => 'aino',
                'strip'      => 'fabric',
                'inviter'    => 'Aino',
                'kicker'     => 'Aino kutsuu',
                'scenario'   => 'Ensitreffit',
                'place'      => 'Kaupungilla',
                'name'       => 'Torstai vain meille',
                'card_line'  => 'Kävelylle — ja sitten pöytä, jota et vielä arvaa.',
                'location'   => 'Kaupungilla',
                'osio_title' => 'Kaupungilla',
                'levels'     => [
                    'Kävely — Aloitetaan ilman kiirettä. Suunta selviää matkalla.',
                    'Pöytä — Varasin meille paikan. Pukeudu niin että uskallat.',
                    'Lopuksi — Jos ilta venyy, se on tarkoitus.',
                ],
            ],
            'elias' => [
                'slug'       => 'elias',
                'strip'      => 'wine',
                'inviter'    => 'Elias',
                'kicker'     => 'Elias kutsuu',
                'scenario'   => 'Toiset treffit',
                'place'      => 'Kotona',
                'name'       => 'Tule kotiin — illalla',
                'card_line'  => 'Kokkaan. Sitten hartiat. Loppu on meidän.',
                'location'   => 'Kotona',
                'osio_title' => 'Kotona',
                'levels'     => [
                    'Keittiö — Sinun ei tarvitse tuoda mitään — paitsi itsesi.',
                    'Hieronta — Hartiat ensin. Kiire jää oven taakse.',
                    'Ilta — Kynttilät. Hidas musiikki. Ei kelloa.',
                ],
            ],
            'mari'  => [
                'slug'       => 'mari',
                'strip'      => 'bokeh',
                'inviter'    => 'Mari',
                'kicker'     => 'Mari kutsuu',
                'scenario'   => '20 v avioliitto',
                'place'      => 'Yllätys',
                'name'       => 'Kaksikymmentä — ja vielä yksi juttu',
                'card_line'  => 'Älä kysy minne. Laita se mekko, josta pidän.',
                'location'   => '',
                'osio_title' => 'Yllätys',
                'levels'     => [
                    'Tänään — 20 vuotta sinua. Tänä iltana en kerro kaikkea etukäteen.',
                    'Minne — Auto odottaa. Loppu on yllätys.',
                    'Miksi — Koska valitsisin sinut uudestaan.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function demo(string $slug): ?array {
        $all = self::demos();
        return $all[$slug] ?? null;
    }

    /**
     * Synthetic recipient payload for a locked demo (no CPT token).
     *
     * @param array<string, mixed> $demo
     * @return array<string, mixed>
     */
    public static function demo_invite_data(array $demo): array {
        $tz = new DateTimeZone('Europe/Helsinki');
        $dt = (new DateTimeImmutable('+2 days 19:00', $tz))->format('c');

        return [
            'id'              => 0,
            'datetime'        => $dt,
            'spoiler_pieni'   => '',
            'spoiler_melkein' => '',
            'spoiler_koko'    => '',
            'spoileritaso'    => 'pieni',
            'dress'           => '',
            'location'        => (string) ($demo['location'] ?? ''),
            'inviter_name'    => (string) ($demo['inviter'] ?? ''),
            'receipt_name'    => (string) ($demo['inviter'] ?? ''),
            'saate'           => '',
            'sections'        => [
                [
                    'id'     => substr(hash('sha256', 'romant-demo-' . (string) ($demo['slug'] ?? '')), 0, 16),
                    'title'  => (string) ($demo['osio_title'] ?? ''),
                    'levels' => array_values($demo['levels'] ?? []),
                ],
            ],
            'token'           => 'demo' . (string) ($demo['slug'] ?? ''),
            'manage_key'      => '',
            'paid'            => true,
            'paid_at'         => '',
            'email'           => '',
            'opened_at'       => '',
            'status'          => 'publish',
        ];
    }
}
