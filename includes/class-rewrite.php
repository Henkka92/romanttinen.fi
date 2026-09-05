<?php
/**
 * Public routes: /kutsu/uusi/, /kutsu/hallitse/{key}/, /kutsu/{token}/, /kutsu/{token}/story/
 * Payment: /kutsu/maksu/paluu/, /kutsu/maksu/ilmoitus/
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Rewrite {

    public function register_rewrites(): void {
        add_rewrite_rule('^kutsu/uusi/?$', 'index.php?romant_route=uusi', 'top');
        add_rewrite_rule(
            '^kutsu/hallitse/([a-f0-9]+)/?$',
            'index.php?romant_route=hallitse&romant_manage_key=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^kutsu/maksu/paluu/?$',
            'index.php?romant_route=pay_return',
            'top'
        );
        add_rewrite_rule(
            '^kutsu/maksu/ilmoitus/?$',
            'index.php?romant_route=pay_notify',
            'top'
        );
        add_rewrite_rule(
            '^kutsu/([a-f0-9]+)/story/?$',
            'index.php?romant_route=story&romant_token=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^kutsu/([a-f0-9]+)/?$',
            'index.php?romant_route=recipient&romant_token=$matches[1]',
            'top'
        );

        add_rewrite_tag('%romant_route%', '([^&]+)');
        add_rewrite_tag('%romant_manage_key%', '([a-f0-9]+)');
        add_rewrite_tag('%romant_token%', '([a-f0-9]+)');
    }

    /**
     * @param list<string> $vars
     * @return list<string>
     */
    public function register_query_vars(array $vars): array {
        $vars[] = 'romant_route';
        $vars[] = 'romant_manage_key';
        $vars[] = 'romant_token';
        return $vars;
    }

    public static function create_url(): string {
        return home_url('/kutsu/uusi/');
    }

    public static function manage_url(string $manage_key): string {
        return home_url('/kutsu/hallitse/' . rawurlencode($manage_key) . '/');
    }

    public static function recipient_url(string $token): string {
        return home_url('/kutsu/' . rawurlencode($token) . '/');
    }

    public static function story_url(string $token): string {
        return home_url('/kutsu/' . rawurlencode($token) . '/story/');
    }

    public static function pay_return_url(): string {
        return home_url('/kutsu/maksu/paluu/');
    }

    public static function pay_notify_url(): string {
        return home_url('/kutsu/maksu/ilmoitus/');
    }

    public function handle_request(): void {
        $route = get_query_var('romant_route');
        if (!$route) {
            return;
        }

        // Payment callbacks: no theme chrome; payment class exits.
        if ($route === 'pay_return') {
            romant_kutsu()->payment->handle_return();
            return;
        }
        if ($route === 'pay_notify') {
            romant_kutsu()->payment->handle_notify();
            return;
        }

        status_header(200);
        nocache_headers();

        switch ($route) {
            case 'uusi':
                Romant_Kutsu_Templates::render('create');
                exit;

            case 'hallitse':
                $key  = (string) get_query_var('romant_manage_key');
                $post = Romant_Kutsu_CPT::find_by_manage_key($key);
                if (!$post) {
                    status_header(404);
                    wp_die(esc_html__('Kutsua ei löytynyt. Tarkista hallintalinkki.', 'romanttinen-kutsu'), 404);
                }
                Romant_Kutsu_Templates::render('manage', [
                    'post' => $post,
                    'data' => Romant_Kutsu_CPT::get_invite_data((int) $post->ID),
                ]);
                exit;

            case 'recipient':
                $token = (string) get_query_var('romant_token');
                $post  = Romant_Kutsu_CPT::find_by_token($token);
                if (!$post) {
                    status_header(404);
                    wp_die(esc_html__('Kutsua ei löytynyt.', 'romanttinen-kutsu'), 404);
                }
                Romant_Kutsu_Templates::render('recipient', [
                    'post' => $post,
                    'data' => Romant_Kutsu_CPT::get_invite_data((int) $post->ID),
                ]);
                exit;

            case 'story':
                $token = (string) get_query_var('romant_token');
                $post  = Romant_Kutsu_CPT::find_by_token($token);
                if (!$post) {
                    status_header(404);
                    wp_die(esc_html__('Kutsua ei löytynyt.', 'romanttinen-kutsu'), 404);
                }
                Romant_Kutsu_Templates::render('story', [
                    'post' => $post,
                    'data' => Romant_Kutsu_CPT::get_invite_data((int) $post->ID),
                ]);
                exit;

            default:
                status_header(404);
                wp_die(esc_html__('Sivua ei löytynyt.', 'romanttinen-kutsu'), 404);
        }
    }
}
