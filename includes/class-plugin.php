<?php
/**
 * Main plugin orchestrator.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Plugin {

    private static ?self $instance = null;

    public Romant_Kutsu_CPT $cpt;
    public Romant_Kutsu_Settings $settings;
    public Romant_Kutsu_Payment $payment;
    public Romant_Kutsu_Rewrite $rewrite;
    public Romant_Kutsu_Forms $forms;
    public Romant_Kutsu_Events $events;
    public Romant_Kutsu_Templates $templates;
    public Romant_Kutsu_Home $home;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->cpt       = new Romant_Kutsu_CPT();
        $this->settings  = new Romant_Kutsu_Settings();
        $this->payment   = new Romant_Kutsu_Payment();
        $this->rewrite   = new Romant_Kutsu_Rewrite();
        $this->forms     = new Romant_Kutsu_Forms();
        $this->events    = new Romant_Kutsu_Events();
        $this->templates = new Romant_Kutsu_Templates();
        $this->home      = new Romant_Kutsu_Home();

        add_action('init', [$this->cpt, 'register']);
        add_action('init', [$this->rewrite, 'register_rewrites']);
        add_action('init', [$this, 'maybe_flush_rewrites'], 20);
        add_filter('query_vars', [$this->rewrite, 'register_query_vars']);
        add_action('init', [$this->forms, 'register_shortcodes']);
        add_action('init', [$this->home, 'register']);
        add_action('init', [$this->events, 'register']);
        add_action('admin_menu', [$this->settings, 'register_menu']);
        add_action('admin_init', [$this->settings, 'register_settings']);
        add_action('template_redirect', [$this->rewrite, 'handle_request']);
        add_action('wp_enqueue_scripts', [$this->templates, 'maybe_enqueue_assets']);
        add_action('admin_post_romant_create_kutsu', [$this->forms, 'handle_create']);
        add_action('admin_post_nopriv_romant_create_kutsu', [$this->forms, 'handle_create']);
        add_action('admin_post_romant_update_kutsu', [$this->forms, 'handle_update']);
        add_action('admin_post_nopriv_romant_update_kutsu', [$this->forms, 'handle_update']);
        add_action('admin_post_romant_pay_kutsu', [$this->payment, 'handle_pay']);
        add_action('admin_post_nopriv_romant_pay_kutsu', [$this->payment, 'handle_pay']);
        add_action('admin_post_romant_send_manage_email', [$this->forms, 'handle_send_manage_email']);
        add_action('admin_post_nopriv_romant_send_manage_email', [$this->forms, 'handle_send_manage_email']);
        add_action('admin_post_romant_create_peel_qa', [$this->settings, 'handle_create_peel_qa']);
    }

    /**
     * Flush rewrite rules once after version bump (new pay return/notify routes).
     */
    public function maybe_flush_rewrites(): void {
        $stored = (string) get_option('romant_kutsu_version', '');
        if ($stored === ROMANT_KUTSU_VERSION) {
            return;
        }
        $this->rewrite->register_rewrites();
        flush_rewrite_rules(false);
        update_option('romant_kutsu_version', ROMANT_KUTSU_VERSION);
    }

    public static function activate(): void {
        $cpt = new Romant_Kutsu_CPT();
        $cpt->register();
        $rewrite = new Romant_Kutsu_Rewrite();
        $rewrite->register_rewrites();
        flush_rewrite_rules();

        if (get_option('romant_kutsu_price') === false) {
            add_option('romant_kutsu_price', '4.90');
        }
        if (get_option('romant_kutsu_stub_payments') === false) {
            add_option('romant_kutsu_stub_payments', '1');
        }
        if (get_option('romant_kutsu_use_homepage') === false) {
            add_option('romant_kutsu_use_homepage', '1');
        }
        update_option('romant_kutsu_version', ROMANT_KUTSU_VERSION);
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}
