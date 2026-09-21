<?php
/**
 * Soft-launch SEO P0: robots.txt + lightweight sitemap.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_SEO {

    public function register(): void {
        add_filter('robots_txt', [$this, 'robots_txt'], 10, 2);
        add_action('init', [$this, 'register_sitemap_rewrite']);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'maybe_render_sitemap'], 0);
    }

    /**
     * @param string $output
     * @param bool   $public
     */
    public function robots_txt(string $output, $public): string {
        if (!$public) {
            return $output;
        }
        $lines = [
            '',
            '# Romanttinen Kutsu SEO P0',
            'Disallow: /kutsu/demo/',
            'Disallow: /kutsu/hallitse/',
            'Disallow: /wp-admin/',
            'Allow: /wp-admin/admin-ajax.php',
            'Sitemap: ' . home_url('/romant-sitemap.xml'),
            '',
        ];
        return rtrim($output) . "\n" . implode("\n", $lines);
    }

    public function register_sitemap_rewrite(): void {
        add_rewrite_rule('^romant-sitemap\.xml$', 'index.php?romant_sitemap=1', 'top');
    }

    /**
     * @param list<string> $vars
     * @return list<string>
     */
    public function query_vars(array $vars): array {
        $vars[] = 'romant_sitemap';
        return $vars;
    }

    public function maybe_render_sitemap(): void {
        if ((string) get_query_var('romant_sitemap') !== '1') {
            return;
        }
        $urls = [
            home_url('/'),
            Romant_Kutsu_Rewrite::create_url(),
            Romant_Kutsu_Rewrite::terms_url(),
            Romant_Kutsu_Rewrite::privacy_url(),
        ];
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex, follow');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            echo '  <url><loc>' . esc_url($url) . '</loc></url>' . "\n";
        }
        echo '</urlset>';
        exit;
    }
}
