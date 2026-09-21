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
                'name'      => 'Se torstai, josta puhuttiin',
                'card_line' => 'Tiedät sen kadunkulman. Minä olen siellä.',
                'location'  => '',
                'sections'  => [
                    [
                        'title'  => 'Kävely',
                        'levels' => [
                            'Älä tule autolla. Haluan kävellä kanssasi ensin.',
                            'Se sama reitti kuin silloin kun jäit kahville «vain hetkeksi».',
                            'Odotan bussipysäkin jälkeen, harmaa takki — suukko ennen kuin mennään eteenpäin.',
                        ],
                    ],
                    [
                        'title'  => 'Pöytä',
                        'levels' => [
                            'Varasin kahdelle. Pukeudu niin että uskallat istua lähelle.',
                            'Tilaan sen viinin, jota kehuit — et saa maksaa.',
                            'Pöytä ikkunan vieressä. Polveni koskettaa sinun, jos annat.',
                        ],
                    ],
                    [
                        'title'  => 'Lopuksi',
                        'levels' => [
                            'Jos ilta venyy, en ole sattumalta myöhässä töihin perjantaina.',
                            'Tiedän paikan jälkiruoalle jossa ei ole kiirettä ulos.',
                            'Ja jos viet minut kotiovelle — voit jäädä oven pieleen pidemmäksi aikaa.',
                        ],
                    ],
                ],
            ],
            'elias' => [
                'slug'      => 'elias',
                'strip'     => 'wine',
                'inviter'   => 'Elias',
                'kicker'    => 'Elias kutsuu',
                'name'      => 'Tule meille — älä soita ovikelloa',
                'card_line' => 'Ovi auki. Valot himmeät. Sinulle.',
                'location'  => 'Kotona',
                'sections'  => [
                    [
                        'title'  => 'Keittiö',
                        'levels' => [
                            'Älä tuo mitään. Paitsi se kauluspaita, jossa näytät siltä.',
                            'Teen risottoa — sen jota et saanut viimeksi loppuun, koska…',
                            'Maistat lusikasta. Seison takanasi. Kädet vyötärölläsi.',
                        ],
                    ],
                    [
                        'title'  => 'Hieronta',
                        'levels' => [
                            'Hartiat ensin. Tiedän missä ne jumittaa sun viikosta.',
                            'Öljy on jo lämpimänä. Paita pois — autan napeissa jos hidastelet.',
                            'Sofalla. Painan peukaloilla kunnes hengität syvään minua vasten.',
                        ],
                    ],
                    [
                        'title'  => 'Ilta',
                        'levels' => [
                            'Puhelin jää eteiseen. Tämä ilta ei ole feedissä.',
                            'Makuuhuoneessa on se peitto jonka varastat aina — tänään jaan sen.',
                            'Loppuillasta en kysy «jäätkö». Tiedän vastauksen jo.',
                        ],
                    ],
                ],
            ],
            'mari'  => [
                'slug'      => 'mari',
                'strip'     => 'bokeh',
                'inviter'   => 'Mari',
                'kicker'    => 'Mari kutsuu',
                'name'      => '20 vuotta — ja yksi asia jonka piilotin',
                'card_line' => 'Laita se musta mekko. Tiedät minkä.',
                'location'  => '',
                'sections'  => [
                    [
                        'title'  => 'Tänään',
                        'levels' => [
                            'En järjestänyt tätä «koska pitää». Järjestin koska kaipaan sinua edelleen.',
                            'Lapset on hoidossa. Kalenteri tyhjä. Vain meidän nimi illassa.',
                            'Sormus sormessa 20 vuotta — silti sydän hakkaa kuin eka treffeillä.',
                        ],
                    ],
                    [
                        'title'  => 'Minne',
                        'levels' => [
                            'En kerro osoitetta. Istu viereen — ajan minä.',
                            'Se hotelli jossa yövyimme ennen kuin meistä tuli «me».',
                            'Huone 412. Avain on jo taskussani. Sänky on petattu kahdelle.',
                        ],
                    ],
                    [
                        'title'  => 'Miksi',
                        'levels' => [
                            'Koska valitsisin sinut uudestaan — joka aamu.',
                            'Koska naurunsa ja hiljaisuutesi on yhä kotini.',
                            'Koska rakastan sinua kuumemmin kuin uskalsin sanoa 20 vuotta sitten.',
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
        // Silent roll-forward: always ~+2 days from now (no CPT / no JSON seed).
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
