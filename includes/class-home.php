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
     * Locked Nea demos (1.4.4 hivelee): card = name + title + 1 line;
     * opened demo = 3 osiot × 3 escalating peels (Vihje 1→2→3).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function demos(): array {
        return [
            'aino'  => [
                'slug'      => 'aino',
                'strip'     => 'fabric',
                'inviter'   => 'Aino',
                'kicker'    => 'Aino kutsuu',
                'name'      => 'Torstai vain meille',
                'card_line' => 'Kävelylle — ja sitten pöytä, jota et vielä arvaa.',
                'location'  => 'Kaupungilla',
                'sections'  => [
                    [
                        'title'  => 'Kävely',
                        'levels' => [
                            'Aloitetaan ilman kiirettä. Suunta selviää matkalla.',
                            'Ei karttaa. Tunnistat kadun kun se tulee.',
                            'Pysähdytään siihen, missä valo on lämmin.',
                        ],
                    ],
                    [
                        'title'  => 'Pöytä',
                        'levels' => [
                            'Varasin meille paikan. Pukeudu niin että uskallat.',
                            'Ei se ensimmäinen arvaus. Odota ovea.',
                            'Pöytä ikkunan vieressä — nimesi on listalla.',
                        ],
                    ],
                    [
                        'title'  => 'Lopuksi',
                        'levels' => [
                            'Jos ilta venyy, se on tarkoitus.',
                            'Jälkeenpäin ei ole kiirettä mihinkään.',
                            'Kävely kotiin saa kestää niin kauan kuin haluat.',
                        ],
                    ],
                ],
            ],
            'elias' => [
                'slug'      => 'elias',
                'strip'     => 'wine',
                'inviter'   => 'Elias',
                'kicker'    => 'Elias kutsuu',
                'name'      => 'Tule kotiin — illalla',
                'card_line' => 'Kokkaan. Sitten hartiat. Loppu on meidän.',
                'location'  => 'Kotona',
                'sections'  => [
                    [
                        'title'  => 'Keittiö',
                        'levels' => [
                            'Sinun ei tarvitse tuoda mitään — paitsi itsesi.',
                            'Ruoka valmistuu hitaasti. Saat vain tulla.',
                            'Pöytä on katettu kahdelle. Minä hoidan loput.',
                        ],
                    ],
                    [
                        'title'  => 'Hieronta',
                        'levels' => [
                            'Hartiat ensin. Kiire jää oven taakse.',
                            'Lämpö ensin. Sitten hiljaisuus.',
                            'Saat sulkea silmät. Minä tiedän missä jännittää.',
                        ],
                    ],
                    [
                        'title'  => 'Ilta',
                        'levels' => [
                            'Kynttilät. Hidas musiikki. Ei kelloa.',
                            'Sohva on valmiina. Ei aikataulua.',
                            'Loppu on meidän. Aamu voi odottaa.',
                        ],
                    ],
                ],
            ],
            'mari'  => [
                'slug'      => 'mari',
                'strip'     => 'bokeh',
                'inviter'   => 'Mari',
                'kicker'    => 'Mari kutsuu',
                'name'      => 'Kaksikymmentä — ja vielä yksi juttu',
                'card_line' => 'Älä kysy minne. Laita se mekko, josta pidän.',
                'location'  => '',
                'sections'  => [
                    [
                        'title'  => 'Tänään',
                        'levels' => [
                            '20 vuotta sinua. Tänä iltana en kerro kaikkea etukäteen.',
                            'Sama mekko. Tiedät minkä.',
                            'Juhlimme — mutta ei niin kuin arvaat.',
                        ],
                    ],
                    [
                        'title'  => 'Minne',
                        'levels' => [
                            'Auto odottaa. Loppu on yllätys.',
                            'Älä kysy osoitetta. Katso vain ulos.',
                            'Perillä odottaa pöytä — ja yksi asia lisää.',
                        ],
                    ],
                    [
                        'title'  => 'Miksi',
                        'levels' => [
                            'Koska valitsisin sinut uudestaan.',
                            'Yhä. Joka kerta.',
                            'Siksi tämä yksi juttu on vain sinulle.',
                        ],
                    ],
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

        $slug     = (string) ($demo['slug'] ?? '');
        $sections = [];
        foreach ($demo['sections'] ?? [] as $i => $sec) {
            if (!is_array($sec) || count($sections) >= 3) {
                continue;
            }
            $title  = (string) ($sec['title'] ?? '');
            $levels = array_values($sec['levels'] ?? []);
            if ($title === '' && $levels === []) {
                continue;
            }
            $sections[] = [
                'id'     => substr(hash('sha256', 'romant-demo-' . $slug . '-' . (string) $i), 0, 16),
                'title'  => $title,
                'levels' => $levels,
            ];
        }

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
            'sections'        => $sections,
            'token'           => 'demo' . $slug,
            'manage_key'      => '',
            'paid'            => true,
            'paid_at'         => '',
            'email'           => '',
            'opened_at'       => '',
            'status'          => 'publish',
        ];
    }
}
