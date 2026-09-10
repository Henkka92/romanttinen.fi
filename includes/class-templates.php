<?php
/**
 * Template rendering + assets.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Templates {

    public function maybe_enqueue_assets(): void {
        $route = (string) get_query_var('romant_route');
        if ($route !== '') {
            self::enqueue_assets($route === 'hallitse' ? 'hallitse' : $route);
            return;
        }

        global $post;
        if ($post instanceof WP_Post) {
            if (has_shortcode($post->post_content, 'romant_etusivu')) {
                self::enqueue_assets('home');
                return;
            }
            if (has_shortcode($post->post_content, 'romant_luo_kutsu')) {
                self::enqueue_assets('shortcode');
            }
        }
    }

    public static function enqueue_assets(string $context = ''): void {
        wp_register_style(
            'romant-kutsu-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=DM+Sans:wght@400;500;600&display=swap',
            [],
            null
        );
        wp_register_style(
            'romant-kutsu',
            ROMANT_KUTSU_URL . 'assets/css/frontend.css',
            ['romant-kutsu-fonts'],
            ROMANT_KUTSU_VERSION
        );
        wp_enqueue_style('romant-kutsu');

        wp_register_script(
            'romant-kutsu-countdown',
            ROMANT_KUTSU_URL . 'assets/js/countdown.js',
            [],
            ROMANT_KUTSU_VERSION,
            true
        );
        wp_enqueue_script('romant-kutsu-countdown');

        if (in_array($context, ['recipient', 'story', 'hallitse'], true)) {
            wp_register_script(
                'romant-kutsu-story',
                ROMANT_KUTSU_URL . 'assets/js/story.js',
                [],
                ROMANT_KUTSU_VERSION,
                true
            );
            wp_enqueue_script('romant-kutsu-story');
        }

        if (in_array($context, ['hallitse', 'uusi', 'shortcode', 'create', 'manage'], true)) {
            wp_register_script(
                'romant-kutsu-manage',
                ROMANT_KUTSU_URL . 'assets/js/manage.js',
                [],
                ROMANT_KUTSU_VERSION,
                true
            );
            wp_localize_script('romant-kutsu-manage', 'romantManage', [
                'maxSections'    => Romant_Kutsu_CPT::MAX_SECTIONS,
                'defaultLevels'  => Romant_Kutsu_CPT::DEFAULT_LEVELS,
                'softMaxLevels'  => Romant_Kutsu_CPT::SOFT_MAX_LEVELS,
                'hardMaxLevels'  => Romant_Kutsu_CPT::HARD_MAX_LEVELS,
                'defaultTitles'  => Romant_Kutsu_CPT::DEFAULT_SECTION_TITLES,
                'extraTitles'    => Romant_Kutsu_CPT::EXTRA_SECTION_TITLES,
                'curatedTitles'  => Romant_Kutsu_CPT::curated_section_titles(),
                'blurbs'         => [
                    'Elokuvahetki'    => Romant_Kutsu_CPT::DEFAULT_SECTION_BLURBS['Elokuvahetki'],
                    'Yhteinen ateria' => Romant_Kutsu_CPT::DEFAULT_SECTION_BLURBS['Yhteinen ateria'],
                    'Kotona'          => Romant_Kutsu_CPT::DEFAULT_SECTION_BLURBS['Kotona'],
                    'Kaupungilla'     => Romant_Kutsu_CPT::curated_blurb('Kaupungilla'),
                    'Pieni salaisuus' => Romant_Kutsu_CPT::curated_blurb('Pieni salaisuus'),
                    'Hellää huomiota' => Romant_Kutsu_CPT::curated_blurb('Hellää huomiota'),
                ],
                'templates'      => Romant_Kutsu_CPT::craft_templates(),
                'softKotitreffit'=> Romant_Kutsu_CPT::kotitreffit_soft_l1(),
                'placeholders'   => [
                    'empty' => Romant_Kutsu_CPT::PLACEHOLDER_EMPTY,
                    'l1'    => Romant_Kutsu_CPT::PLACEHOLDER_L1,
                    'l2'    => Romant_Kutsu_CPT::PLACEHOLDER_L2,
                ],
                'i18n'           => [
                    'sectionTitle'   => 'Osion otsikko',
                    'level'          => 'Taso',
                    'hint'           => 'Vihje',
                    'addLevel'       => 'Lisää taso',
                    'addSection'     => 'Lisää osio',
                    'removeSection'  => 'Poista',
                    'softCapWarn'    => 'Pehmeä raja (10) ylitetty — pidä tasot maltillisina.',
                    'hardCap'        => 'Enintään 20 tasoa osiossa.',
                    'maxSections'    => 'Enintään 3 osiota.',
                    'sectionPh'      => 'Elokuvahetki',
                    'levelPh'        => Romant_Kutsu_CPT::PLACEHOLDER_EMPTY,
                    'napauta'        => 'napauta muokataksesi',
                    'oletusta'       => 'oletusta',
                    'useExample'     => 'Käytä esimerkkiä',
                ],
            ]);
            wp_enqueue_script('romant-kutsu-manage');
        }

        // Copy-to-clipboard helpers live in manage.js (also used on recipient/story share buttons).
        if (in_array($context, ['recipient', 'story'], true)) {
            wp_register_script(
                'romant-kutsu-manage',
                ROMANT_KUTSU_URL . 'assets/js/manage.js',
                [],
                ROMANT_KUTSU_VERSION,
                true
            );
            wp_enqueue_script('romant-kutsu-manage');
        }

        if ($context === 'recipient') {
            wp_register_script(
                'romant-kutsu-recipient',
                ROMANT_KUTSU_URL . 'assets/js/recipient.js',
                [],
                ROMANT_KUTSU_VERSION,
                true
            );
            wp_localize_script('romant-kutsu-recipient', 'romantPeli', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('romant_track_event'),
            ]);
            wp_enqueue_script('romant-kutsu-recipient');
        }
    }

    /**
     * Full-page standalone template (no theme chrome).
     *
     * @param array<string, mixed> $vars
     */
    public static function render(string $name, array $vars = []): void {
        $file = ROMANT_KUTSU_PATH . 'templates/' . $name . '.php';
        if (!is_readable($file)) {
            wp_die(esc_html__('Mallipohjaa ei löytynyt.', 'romanttinen-kutsu'), 500);
        }

        $context_map = [
            'create'    => 'uusi',
            'manage'    => 'hallitse',
            'recipient' => 'recipient',
            'story'     => 'story',
            'home'      => 'home',
        ];
        self::enqueue_assets($context_map[$name] ?? $name);

        add_action('romant_kutsu_head', static function (): void {
            wp_print_styles();
            wp_print_head_scripts();
        });
        add_action('romant_kutsu_footer', static function (): void {
            wp_print_footer_scripts();
        });

        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract($vars, EXTR_SKIP);
        include $file;
    }

    /**
     * @param array<string, mixed> $vars
     */
    public static function render_partial(string $name, array $vars = []): void {
        $file = ROMANT_KUTSU_PATH . 'templates/' . $name . '.php';
        if (!is_readable($file)) {
            return;
        }
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract($vars, EXTR_SKIP);
        include $file;
    }

    /**
     * @return list<string>
     */
    public static function checklist_items(): array {
        return [
            'Valitse aika',
            'Kirjoita osiot',
            'Tarkista esikatselu',
            'Jaa linkki',
        ];
    }

    /**
     * Logo A PNG markup (img + text/noscript fallback).
     */
    public static function logo_markup(string $extra_class = ''): string {
        $cls = trim('romant-logo ' . $extra_class);
        $url = ROMANT_KUTSU_URL . 'assets/img/logo-a.png';
        $onerr = "this.style.display='none';var f=this.nextElementSibling;if(f){f.hidden=false;f.classList.remove('screen-reader-text');}";
        return '<span class="romant-logo-wrap" data-romant-logo>'
            . '<img class="' . esc_attr($cls) . '" src="' . esc_url($url)
            . '" alt="romanttinen" width="1048" height="169" decoding="async" onerror="' . esc_attr($onerr) . '" />'
            . '<span class="romant-logo-text screen-reader-text" hidden>romanttinen</span>'
            . '<noscript><span class="romant-logo-text">romanttinen</span></noscript>'
            . '</span>';
    }
}
