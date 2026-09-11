<?php
/**
 * Custom post type romant_kutsu.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_CPT {

    public const POST_TYPE = 'romant_kutsu';

    public const META_DATETIME        = 'romant_datetime';
    public const META_SPOILER_PIENI   = 'spoiler_pieni';
    public const META_SPOILER_MELKEIN = 'spoiler_melkein';
    public const META_SPOILER_KOKO    = 'spoiler_koko';
    public const META_SPOILERITASO    = 'spoileritaso';
    public const META_DRESS           = 'romant_dress';
    public const META_LOCATION        = 'romant_location';
    public const META_INVITER_NAME    = 'romant_inviter_name';
    public const META_RECEIPT_NAME    = 'romant_receipt_name';
    public const META_SAATE           = 'romant_saate';
    public const META_SECTIONS        = 'romant_sections';
    public const META_EVENTS          = 'romant_events';
    public const META_OPENED_AT       = 'romant_opened_at';
    public const META_TOKEN           = 'romant_token';
    public const META_MANAGE_KEY      = 'romant_manage_key';
    public const META_PAID            = 'romant_paid';
    public const META_PAID_AT         = 'romant_paid_at';
    public const META_EMAIL             = 'romant_email';
    public const META_ORDER_NUMBER      = 'romant_order_number';
    public const META_VISMA_TOKEN       = 'romant_visma_token';
    public const META_PAY_EMAIL_PENDING = 'romant_pay_email_pending';

    public const MAX_SECTIONS      = 3;
    public const DEFAULT_LEVELS    = 3;
    public const SOFT_MAX_LEVELS   = 10;
    public const HARD_MAX_LEVELS   = 20;

    /** Nea-locked default section titles (craft OLETUS badge). */
    public const DEFAULT_SECTION_TITLES = ['Elokuvahetki', 'Yhteinen ateria', 'Kotona'];

    /** Extra curated chips only — not a free planner. */
    public const EXTRA_SECTION_TITLES = ['Kaupungilla', 'Pieni salaisuus', 'Hellää huomiota'];

    public const CRAFT_INVITE_TITLE = 'Ilta kahdelle';

    public const PLACEHOLDER_EMPTY = 'Kirjoita vihje saajalle…';
    public const PLACEHOLDER_L1    = 'Pieni vihje — älä paljasta kaikkea';
    public const PLACEHOLDER_L2    = 'Seuraava kerros…';

    /** Nea-locked L1 examples (Käytä esimerkkiä — not initial input values). */
    public const DEFAULT_SECTION_BLURBS = [
        'Elokuvahetki'    => 'Valitaan leffa, tehdään popcornit — ja katsotaan yhdessä.',
        'Yhteinen ateria' => 'Kokataan jotain hyvää tai varataan pöytä.',
        'Kotona'          => 'Rauhallista aikaa yhdessä, ilman kiirettä.',
    ];

    public static function is_default_section_title(string $title): bool {
        return in_array($title, self::DEFAULT_SECTION_TITLES, true);
    }

    public static function is_curated_section_title(string $title): bool {
        return in_array($title, self::curated_section_titles(), true);
    }

    /**
     * @return list<string>
     */
    public static function curated_section_titles(): array {
        return array_values(array_unique(array_merge(self::DEFAULT_SECTION_TITLES, self::EXTRA_SECTION_TITLES)));
    }

    /**
     * Soft L1 when a curated chip is added.
     */
    public static function curated_blurb(string $title): string {
        $all = self::DEFAULT_SECTION_BLURBS + [
            'Kaupungilla'     => 'Kävelylle — suunta selviää myöhemmin.',
            'Pieni salaisuus' => 'Ota mukaan jotain lämmintä.',
            'Hellää huomiota' => '',
        ];
        return $all[$title] ?? '';
    }

    /**
     * Nea example L1 for Käytä esimerkkiä (pohja-aware).
     */
    public static function example_l1(string $title, string $pohja = 'kotitreffit', bool $soft = false): string {
        if ($pohja === 'kotitreffit' && $soft) {
            $soft_map = self::kotitreffit_soft_l1();
            if (isset($soft_map[$title]) && $soft_map[$title] !== '') {
                return $soft_map[$title];
            }
        }
        $templates = self::craft_templates();
        if (isset($templates[$pohja]['sections']) && is_array($templates[$pohja]['sections'])) {
            foreach ($templates[$pohja]['sections'] as $sec) {
                if (($sec['title'] ?? '') === $title && ($sec['l1'] ?? '') !== '') {
                    return (string) $sec['l1'];
                }
            }
        }
        return self::curated_blurb($title);
    }

    /**
     * Portti 2 pohjat (max 2). Kotitreffit lands as the default three; Nea L1s via Käytä esimerkkiä.
     *
     * @return array<string, array{label: string, location: string, sections: list<array{title: string, l1: string}>}>
     */
    public static function craft_templates(): array {
        return [
            'kotitreffit' => [
                'label'    => 'Kotitreffit',
                'location' => 'Kotona',
                'sections' => [
                    [
                        'title' => 'Elokuvahetki',
                        'l1'    => self::DEFAULT_SECTION_BLURBS['Elokuvahetki'],
                    ],
                    [
                        'title' => 'Yhteinen ateria',
                        'l1'    => self::DEFAULT_SECTION_BLURBS['Yhteinen ateria'],
                    ],
                    [
                        'title' => 'Kotona',
                        'l1'    => self::DEFAULT_SECTION_BLURBS['Kotona'],
                    ],
                ],
            ],
            // Mari: Kaupungilla may rename its 3 sections (curated only — not a free planner).
            'kaupungilla' => [
                'label'    => 'Kaupungilla',
                'location' => '',
                'sections' => [
                    [
                        'title' => 'Kaupungilla',
                        'l1'    => 'Kävelylle — suunta selviää myöhemmin.',
                    ],
                    [
                        'title' => 'Yhteinen ateria',
                        'l1'    => 'Pöytä odottaa, mutta missä…',
                    ],
                    [
                        'title' => 'Pieni salaisuus',
                        'l1'    => 'Ota mukaan jotain lämmintä.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Soft Kotitreffit L1s (Nea) — applied when re-selecting the home pohja after a switch.
     *
     * @return array<string, string>
     */
    public static function kotitreffit_soft_l1(): array {
        return [
            'Elokuvahetki'    => 'Valitaan jotain kevyttä — ei se ensimmäinen arvaus.',
            'Yhteinen ateria' => 'Syödään hyvin kotona, rauhassa.',
            'Kotona'          => 'Illan lopuksi ei kiirettä mihinkään.',
        ];
    }

    /**
     * Next Saturday 18:00 Europe/Helsinki — craft summary line.
     */
    public static function default_craft_datetime(): DateTimeImmutable {
        $tz = new DateTimeZone('Europe/Helsinki');
        $now = new DateTimeImmutable('now', $tz);
        $saturday = new DateTimeImmutable('saturday this week 18:00:00', $tz);
        if ($saturday <= $now) {
            $saturday = $saturday->modify('+7 days');
        }
        return $saturday;
    }

    public function register(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'Kutsut',
                'singular_name' => 'Kutsu',
                'add_new'       => 'Lisää kutsu',
                'add_new_item'  => 'Lisää uusi kutsu',
                'edit_item'     => 'Muokkaa kutsua',
                'view_item'     => 'Näytä kutsu',
                'search_items'  => 'Etsi kutsuja',
                'not_found'     => 'Kutsuja ei löytynyt',
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-heart',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => ['title'],
            'has_archive'        => false,
            'rewrite'            => false,
            'query_var'          => false,
            'show_in_rest'       => false,
        ]);

        $text_keys = [
            self::META_DATETIME,
            self::META_INVITER_NAME,
            self::META_RECEIPT_NAME,
            self::META_SPOILERITASO,
            self::META_TOKEN,
            self::META_MANAGE_KEY,
            self::META_PAID_AT,
            self::META_ORDER_NUMBER,
            self::META_VISMA_TOKEN,
            self::META_PAY_EMAIL_PENDING,
            self::META_OPENED_AT,
            self::META_LOCATION,
        ];
        $textarea_keys = [
            self::META_SPOILER_PIENI,
            self::META_SPOILER_MELKEIN,
            self::META_SPOILER_KOKO,
            self::META_DRESS,
            self::META_SAATE,
            self::META_SECTIONS,
            self::META_EVENTS,
        ];

        $auth = static function (): bool {
            return current_user_can('edit_posts');
        };

        foreach ($text_keys as $key) {
            register_post_meta(self::POST_TYPE, $key, [
                'type'              => 'string',
                'single'            => true,
                'sanitize_callback' => 'sanitize_text_field',
                'show_in_rest'      => false,
                'auth_callback'     => $auth,
            ]);
        }
        foreach ($textarea_keys as $key) {
            register_post_meta(self::POST_TYPE, $key, [
                'type'              => 'string',
                'single'            => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'show_in_rest'      => false,
                'auth_callback'     => $auth,
            ]);
        }

        register_post_meta(self::POST_TYPE, self::META_EMAIL, [
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_email',
            'show_in_rest'      => false,
            'auth_callback'     => $auth,
        ]);

        register_post_meta(self::POST_TYPE, self::META_PAID, [
            'type'              => 'boolean',
            'single'            => true,
            'sanitize_callback' => static function ($v): bool {
                return (bool) $v;
            },
            'show_in_rest'      => false,
            'auth_callback'     => static function (): bool {
                return current_user_can('edit_posts');
            },
        ]);
    }

    public static function generate_secret(int $bytes = 24): string {
        return bin2hex(random_bytes($bytes));
    }

    public static function find_by_manage_key(string $key): ?WP_Post {
        if ($key === '') {
            return null;
        }
        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => ['draft', 'publish', 'private'],
            'posts_per_page' => 1,
            'meta_key'       => self::META_MANAGE_KEY,
            'meta_value'     => $key,
            'no_found_rows'  => true,
        ]);
        return $q->have_posts() ? $q->posts[0] : null;
    }

    public static function find_by_token(string $token): ?WP_Post {
        if ($token === '') {
            return null;
        }
        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => self::META_TOKEN,
            'meta_value'     => $token,
            'no_found_rows'  => true,
        ]);
        return $q->have_posts() ? $q->posts[0] : null;
    }

    public static function find_by_order_number(string $order_number): ?WP_Post {
        if ($order_number === '') {
            return null;
        }
        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => ['draft', 'publish', 'private'],
            'posts_per_page' => 1,
            'meta_key'       => self::META_ORDER_NUMBER,
            'meta_value'     => $order_number,
            'no_found_rows'  => true,
        ]);
        return $q->have_posts() ? $q->posts[0] : null;
    }

    /**
     * Empty section scaffold for forms.
     *
     * @return array{id: string, title: string, levels: list<string>}
     */
    public static function empty_section(int $level_count = self::DEFAULT_LEVELS): array {
        $levels = [];
        for ($i = 0; $i < max(1, $level_count); $i++) {
            $levels[] = '';
        }
        return [
            'id'     => self::generate_secret(8),
            'title'  => '',
            'levels' => $levels,
        ];
    }

    /**
     * Portti 2 craft open state — three default titles, empty L1 (Nea via Käytä esimerkkiä).
     *
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    public static function default_craft_sections(): array {
        $out = [];
        foreach (self::DEFAULT_SECTION_TITLES as $title) {
            $out[] = [
                'id'     => self::generate_secret(8),
                'title'  => $title,
                'levels' => ['', '', ''],
            ];
        }
        return $out;
    }

    /**
     * Multi-level sample sections for peel QA (Portti 1).
     * Peel CTA ("Haluatko kuulla lisää?") only appears when a section has 2+ levels.
     *
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    public static function sample_sections(): array {
        return [
            [
                'id'     => self::generate_secret(8),
                'title'  => 'Elokuvahetki',
                'levels' => [
                    'Elokuva — mutta ei se, jota arvaat ensimmäisenä.',
                    'Jotain kevyttä ja yhteistä. Popcornia saa olla.',
                    'Paikat on varattu — loppu selviää perillä.',
                ],
            ],
            [
                'id'     => self::generate_secret(8),
                'title'  => 'Yhteinen ateria',
                'levels' => [
                    'Syödään hyvin — ei pikaruokaa.',
                    'Pöytä on katettu kahdelle. Tunnelma ratkaisee.',
                    'Jälkiruoka odottaa — tai jätetään se huomiseen.',
                ],
            ],
            [
                'id'     => self::generate_secret(8),
                'title'  => 'Kotona',
                'levels' => [
                    'Ilta jatkuu — ei vielä kotiin nukkumaan.',
                    'Valot himmeinä. Ei kiirettä.',
                    'Viimeinen vihje odottaa ovella.',
                ],
            ],
        ];
    }

    /**
     * True when any section has at least two non-empty levels (peel path possible).
     *
     * @param list<array{id?: string, title?: string, levels?: list<string>}> $sections
     */
    public static function sections_offer_peel(array $sections): bool {
        foreach ($sections as $sec) {
            $filled = 0;
            foreach ($sec['levels'] ?? [] as $lv) {
                if ((string) $lv !== '') {
                    $filled++;
                    if ($filled >= 2) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Create a paid stub invite with multi-level sample content for Portti 1 QA.
     *
     * @return array{post_id: int, token: string, manage_key: string}|\WP_Error
     */
    public static function create_peel_qa_invite() {
        $manage_key = self::generate_secret(24);
        $token      = self::generate_secret(24);
        $tz         = new DateTimeZone('Europe/Helsinki');
        $dt         = (new DateTimeImmutable('+3 days 19:00', $tz))->format('c');
        $sections   = self::sample_sections();

        $post_id = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title'  => 'Peel QA ' . gmdate('Y-m-d H:i'),
        ], true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $post_id = (int) $post_id;
        update_post_meta($post_id, self::META_MANAGE_KEY, $manage_key);
        update_post_meta($post_id, self::META_TOKEN, $token);
        update_post_meta($post_id, self::META_DATETIME, $dt);
        update_post_meta($post_id, self::META_INVITER_NAME, 'Alex');
        update_post_meta($post_id, self::META_RECEIPT_NAME, 'Alex');
        update_post_meta($post_id, self::META_SAATE, 'Pieni kutsu — avaa kun olet valmis.');
        update_post_meta($post_id, self::META_DRESS, 'Jotain pehmeää ja kaunista.');
        update_post_meta($post_id, self::META_LOCATION, 'Keskusta');
        update_post_meta($post_id, self::META_SECTIONS, wp_json_encode($sections, JSON_UNESCAPED_UNICODE));
        update_post_meta($post_id, self::META_PAID, true);
        update_post_meta($post_id, self::META_PAID_AT, gmdate('c'));

        return [
            'post_id'    => $post_id,
            'token'      => $token,
            'manage_key' => $manage_key,
        ];
    }

    /**
     * Normalize / sanitize section list from storage or form.
     *
     * @param mixed $raw
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    public static function normalize_sections($raw): array {
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            $raw     = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (count($out) >= self::MAX_SECTIONS) {
                break;
            }
            $title = isset($item['title']) ? sanitize_text_field((string) $item['title']) : '';
            if (function_exists('mb_substr')) {
                $title = mb_substr($title, 0, 80);
            } else {
                $title = substr($title, 0, 80);
            }

            $levels_in = $item['levels'] ?? [];
            if (!is_array($levels_in)) {
                $levels_in = [];
            }
            $levels = [];
            foreach ($levels_in as $lv) {
                if (count($levels) >= self::HARD_MAX_LEVELS) {
                    break;
                }
                $text = sanitize_textarea_field((string) $lv);
                if (function_exists('mb_substr')) {
                    $text = mb_substr($text, 0, 800);
                } else {
                    $text = substr($text, 0, 800);
                }
                $levels[] = $text;
            }
            // Drop trailing empty levels but keep at least one slot when title set.
            while (count($levels) > 1 && end($levels) === '') {
                array_pop($levels);
            }
            if ($levels === []) {
                $levels = [''];
            }

            $id = isset($item['id']) ? sanitize_text_field((string) $item['id']) : '';
            if ($id === '' || !preg_match('/^[a-f0-9]{8,64}$/', $id)) {
                $id = self::generate_secret(8);
            }

            // Skip completely empty sections (no title, all levels blank).
            $has_text = $title !== '';
            foreach ($levels as $lv) {
                if ($lv !== '') {
                    $has_text = true;
                    break;
                }
            }
            if (!$has_text) {
                continue;
            }

            $out[] = [
                'id'     => $id,
                'title'  => $title,
                'levels' => $levels,
            ];
        }

        return $out;
    }

    /**
     * Migrate V1 spoileritaso fields into one section when sections empty.
     *
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    public static function migrate_legacy_spoilers(int $post_id): array {
        $pieni   = (string) get_post_meta($post_id, self::META_SPOILER_PIENI, true);
        $melkein = (string) get_post_meta($post_id, self::META_SPOILER_MELKEIN, true);
        $koko    = (string) get_post_meta($post_id, self::META_SPOILER_KOKO, true);
        $level   = (string) get_post_meta($post_id, self::META_SPOILERITASO, true);
        if (!in_array($level, ['pieni', 'melkein', 'koko'], true)) {
            $level = 'pieni';
        }

        $levels = [];
        if ($pieni !== '') {
            $levels[] = $pieni;
        }
        if (in_array($level, ['melkein', 'koko'], true) && $melkein !== '') {
            $levels[] = $melkein;
        } elseif ($melkein !== '' && $pieni === '' && $koko === '') {
            $levels[] = $melkein;
        }
        if ($level === 'koko' && $koko !== '') {
            $levels[] = $koko;
        } elseif ($koko !== '' && $levels === []) {
            $levels[] = $koko;
        }

        // Include any non-empty leftovers even if level was "pieni".
        if ($levels === []) {
            foreach ([$pieni, $melkein, $koko] as $t) {
                if ($t !== '') {
                    $levels[] = $t;
                }
            }
        }

        if ($levels === []) {
            return [];
        }

        return [
            [
                'id'     => self::generate_secret(8),
                'title'  => 'Vihjeet',
                'levels' => $levels,
            ],
        ];
    }

    /**
     * Load sections for invite; migrates legacy spoilers if needed.
     *
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    public static function get_sections(int $post_id): array {
        $raw = get_post_meta($post_id, self::META_SECTIONS, true);
        $sections = self::normalize_sections($raw);
        if ($sections !== []) {
            return $sections;
        }
        $migrated = self::migrate_legacy_spoilers($post_id);
        if ($migrated !== []) {
            // Persist so manage form sees sections next time.
            update_post_meta($post_id, self::META_SECTIONS, wp_json_encode($migrated, JSON_UNESCAPED_UNICODE));
        }
        return $migrated;
    }

    /**
     * @return array<string, mixed>
     */
    public static function get_invite_data(int $post_id): array {
        $level = (string) get_post_meta($post_id, self::META_SPOILERITASO, true);
        if (!in_array($level, ['pieni', 'melkein', 'koko'], true)) {
            $level = 'pieni';
        }

        $sections = self::get_sections($post_id);

        return [
            'id'              => $post_id,
            'datetime'        => (string) get_post_meta($post_id, self::META_DATETIME, true),
            'spoiler_pieni'   => (string) get_post_meta($post_id, self::META_SPOILER_PIENI, true),
            'spoiler_melkein' => (string) get_post_meta($post_id, self::META_SPOILER_MELKEIN, true),
            'spoiler_koko'    => (string) get_post_meta($post_id, self::META_SPOILER_KOKO, true),
            'spoileritaso'    => $level,
            'dress'           => (string) get_post_meta($post_id, self::META_DRESS, true),
            'location'        => (string) get_post_meta($post_id, self::META_LOCATION, true),
            'inviter_name'    => (string) get_post_meta($post_id, self::META_INVITER_NAME, true),
            'receipt_name'    => (string) get_post_meta($post_id, self::META_RECEIPT_NAME, true),
            'saate'           => (string) get_post_meta($post_id, self::META_SAATE, true),
            'sections'        => $sections,
            'token'           => (string) get_post_meta($post_id, self::META_TOKEN, true),
            'manage_key'      => (string) get_post_meta($post_id, self::META_MANAGE_KEY, true),
            'paid'            => (bool) get_post_meta($post_id, self::META_PAID, true),
            'paid_at'         => (string) get_post_meta($post_id, self::META_PAID_AT, true),
            'email'           => (string) get_post_meta($post_id, self::META_EMAIL, true),
            'opened_at'       => (string) get_post_meta($post_id, self::META_OPENED_AT, true),
            'status'          => get_post_status($post_id),
        ];
    }

    /**
     * Legacy helper — maps old spoiler stack for any leftover callers.
     *
     * @return list<array{label: string, text: string}>
     */
    public static function visible_spoilers(array $data): array {
        $sections = $data['sections'] ?? [];
        if (is_array($sections) && $sections !== []) {
            $out = [];
            foreach ($sections as $sec) {
                $title  = (string) ($sec['title'] ?? '');
                $levels = $sec['levels'] ?? [];
                if (!is_array($levels) || $levels === []) {
                    continue;
                }
                // First level only (preview / legacy).
                $text = (string) ($levels[0] ?? '');
                if ($text === '') {
                    continue;
                }
                $out[] = [
                    'label' => $title !== '' ? $title : 'Osio',
                    'text'  => $text,
                ];
            }
            return $out;
        }

        $out   = [];
        $level = (string) ($data['spoileritaso'] ?? 'pieni');
        if (!in_array($level, ['pieni', 'melkein', 'koko'], true)) {
            $level = 'pieni';
        }

        if (($data['spoiler_pieni'] ?? '') !== '') {
            $out[] = ['label' => 'Pieni vihje', 'text' => (string) $data['spoiler_pieni']];
        }
        if (in_array($level, ['melkein', 'koko'], true) && ($data['spoiler_melkein'] ?? '') !== '') {
            $out[] = ['label' => 'Melkein tiedät', 'text' => (string) $data['spoiler_melkein']];
        }
        if ($level === 'koko' && ($data['spoiler_koko'] ?? '') !== '') {
            $out[] = ['label' => 'Koko juttu', 'text' => (string) $data['spoiler_koko']];
        }

        return $out;
    }

    /**
     * Append a recipient/organizer event and optionally notify.
     *
     * @param array<string, mixed> $event
     */
    public static function record_event(int $post_id, array $event): void {
        $raw = get_post_meta($post_id, self::META_EVENTS, true);
        $list = [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = $decoded;
            }
        }
        $event['at'] = gmdate('c');
        $list[]      = $event;
        // Cap log size.
        if (count($list) > 200) {
            $list = array_slice($list, -200);
        }
        update_post_meta($post_id, self::META_EVENTS, wp_json_encode($list, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Format treffiaika for email subjects (Europe/Helsinki).
     */
    public static function format_datetime_fi(string $iso): string {
        if ($iso === '') {
            return '';
        }
        try {
            $dt = new DateTimeImmutable($iso);
            $dt = $dt->setTimezone(new DateTimeZone('Europe/Helsinki'));
            return $dt->format('j.n.Y H:i');
        } catch (Exception $e) {
            return $iso;
        }
    }

    /**
     * Tapaaminen card line (Europe/Helsinki). Locked: `La 14.6. · 18:00`.
     *
     * @return array{date: string, time: string, line: string}
     */
    public static function format_tapaaminen(string $iso): array {
        $empty = ['date' => '', 'time' => '', 'line' => ''];
        if ($iso === '') {
            return $empty;
        }
        try {
            $dt   = new DateTimeImmutable($iso);
            $dt   = $dt->setTimezone(new DateTimeZone('Europe/Helsinki'));
            $days = ['Su', 'Ma', 'Ti', 'Ke', 'To', 'Pe', 'La'];
            $wd   = $days[(int) $dt->format('w')];
            $date = $dt->format('j.n.');
            $time = $dt->format('H:i');
            return [
                'date' => $wd . ' ' . $date,
                'time' => $time,
                'line' => $wd . ' ' . $date . ' · ' . $time,
            ];
        } catch (Exception $e) {
            return $empty;
        }
    }
}
