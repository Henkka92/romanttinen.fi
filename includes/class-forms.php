<?php
/**
 * Create / update forms + shortcode.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Forms {

    public function register_shortcodes(): void {
        add_shortcode('romant_luo_kutsu', [$this, 'shortcode_create']);
    }

    public function shortcode_create(): string {
        ob_start();
        Romant_Kutsu_Templates::render_partial('create-form');
        return (string) ob_get_clean();
    }

    /**
     * Parse sections from POST: romant_sections[i][title], romant_sections[i][levels][j]
     *
     * @return list<array{id: string, title: string, levels: list<string>}>
     */
    private function parse_sections_from_post(array $src): array {
        $raw = $src['romant_sections'] ?? null;
        if (!is_array($raw)) {
            return [];
        }
        $built = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = isset($item['id'])
                ? sanitize_text_field(wp_unslash((string) $item['id']))
                : '';
            $title = isset($item['title'])
                ? sanitize_text_field(wp_unslash((string) $item['title']))
                : '';
            $levels_raw = $item['levels'] ?? [];
            $levels     = [];
            if (is_array($levels_raw)) {
                foreach ($levels_raw as $lv) {
                    $levels[] = sanitize_textarea_field(wp_unslash((string) $lv));
                }
            }
            $built[] = [
                'id'     => $id,
                'title'  => $title,
                'levels' => $levels,
            ];
        }
        return Romant_Kutsu_CPT::normalize_sections($built);
    }

    /**
     * @return array{datetime: string, location: string, inviter_name: string, saate: string, dress: string, sections: list<array{id: string, title: string, levels: list<string>}>}
     */
    private function sanitize_invite_fields(array $src): array {
        $datetime = isset($src['romant_datetime'])
            ? sanitize_text_field(wp_unslash((string) $src['romant_datetime']))
            : '';

        if ($datetime !== '' && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $datetime)) {
            try {
                $tz = new DateTimeZone('Europe/Helsinki');
                $dt = new DateTimeImmutable($datetime, $tz);
                $datetime = $dt->format('c');
            } catch (Exception $e) {
                $datetime = '';
            }
        }

        $inviter = isset($src['romant_inviter_name'])
            ? sanitize_text_field(wp_unslash((string) $src['romant_inviter_name']))
            : '';
        if (function_exists('mb_substr')) {
            $inviter = mb_substr($inviter, 0, 80);
        } else {
            $inviter = substr($inviter, 0, 80);
        }

        $saate = isset($src['romant_saate'])
            ? sanitize_textarea_field(wp_unslash((string) $src['romant_saate']))
            : '';
        if (function_exists('mb_substr')) {
            $saate = mb_substr($saate, 0, 400);
        } else {
            $saate = substr($saate, 0, 400);
        }

        $location = isset($src['romant_location'])
            ? sanitize_text_field(wp_unslash((string) $src['romant_location']))
            : '';
        if (function_exists('mb_substr')) {
            $location = mb_substr($location, 0, 120);
        } else {
            $location = substr($location, 0, 120);
        }

        return [
            'datetime'     => $datetime,
            'location'     => $location,
            'inviter_name' => $inviter,
            'saate'        => $saate,
            'dress'        => isset($src['romant_dress'])
                ? sanitize_textarea_field(wp_unslash((string) $src['romant_dress'])) : '',
            'sections'     => $this->parse_sections_from_post($src),
        ];
    }

    /**
     * Validate that at least one section has a title and one non-empty level.
     *
     * @param list<array{id: string, title: string, levels: list<string>}> $sections
     */
    private function sections_are_valid(array $sections): bool {
        foreach ($sections as $sec) {
            $title = (string) ($sec['title'] ?? '');
            if ($title === '') {
                continue;
            }
            foreach ($sec['levels'] ?? [] as $lv) {
                if ((string) $lv !== '') {
                    return true;
                }
            }
        }
        return false;
    }

    private function save_invite_meta(int $post_id, array $fields): void {
        update_post_meta($post_id, Romant_Kutsu_CPT::META_DATETIME, $fields['datetime']);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_LOCATION, $fields['location'] ?? '');
        update_post_meta($post_id, Romant_Kutsu_CPT::META_INVITER_NAME, $fields['inviter_name']);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_SAATE, $fields['saate']);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_DRESS, $fields['dress']);
        update_post_meta(
            $post_id,
            Romant_Kutsu_CPT::META_SECTIONS,
            wp_json_encode($fields['sections'], JSON_UNESCAPED_UNICODE)
        );
    }

    public function handle_create(): void {
        if (!isset($_POST['romant_create_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['romant_create_nonce'])), 'romant_create_kutsu')) {
            wp_die(esc_html__('Turvatarkistus epäonnistui. Yritä uudelleen.', 'romanttinen-kutsu'), 403);
        }

        $fields = $this->sanitize_invite_fields($_POST);

        if ($fields['datetime'] === '') {
            wp_die(esc_html__('Valitse aika.', 'romanttinen-kutsu'), 400);
        }
        if ($fields['inviter_name'] === '') {
            wp_die(esc_html__('Kirjoita kutsujan nimi.', 'romanttinen-kutsu'), 400);
        }
        if (!$this->sections_are_valid($fields['sections'])) {
            wp_die(esc_html__('Lisää vähintään yksi osio otsikolla ja ensimmäisellä tasolla.', 'romanttinen-kutsu'), 400);
        }

        $manage_key = Romant_Kutsu_CPT::generate_secret(24);

        $post_id = wp_insert_post([
            'post_type'   => Romant_Kutsu_CPT::POST_TYPE,
            'post_status' => 'draft',
            'post_title'  => 'Kutsu ' . gmdate('Y-m-d H:i'),
        ], true);

        if (is_wp_error($post_id)) {
            wp_die(esc_html__('Kutsun luonti epäonnistui.', 'romanttinen-kutsu'), 500);
        }

        update_post_meta($post_id, Romant_Kutsu_CPT::META_MANAGE_KEY, $manage_key);
        $this->save_invite_meta((int) $post_id, $fields);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_PAID, false);

        wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key));
        exit;
    }

    public function handle_update(): void {
        if (!isset($_POST['romant_update_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['romant_update_nonce'])), 'romant_update_kutsu')) {
            wp_die(esc_html__('Turvatarkistus epäonnistui. Yritä uudelleen.', 'romanttinen-kutsu'), 403);
        }

        $manage_key = isset($_POST['manage_key'])
            ? sanitize_text_field(wp_unslash((string) $_POST['manage_key'])) : '';
        $post = Romant_Kutsu_CPT::find_by_manage_key($manage_key);

        if (!$post) {
            wp_die(esc_html__('Kutsua ei löytynyt.', 'romanttinen-kutsu'), 404);
        }

        $fields = $this->sanitize_invite_fields($_POST);

        if ($fields['datetime'] === '') {
            wp_die(esc_html__('Valitse aika.', 'romanttinen-kutsu'), 400);
        }
        if ($fields['inviter_name'] === '') {
            wp_die(esc_html__('Kirjoita kutsujan nimi.', 'romanttinen-kutsu'), 400);
        }
        if (!$this->sections_are_valid($fields['sections'])) {
            wp_die(esc_html__('Lisää vähintään yksi osio otsikolla ja ensimmäisellä tasolla.', 'romanttinen-kutsu'), 400);
        }

        $this->save_invite_meta((int) $post->ID, $fields);

        wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?saved=1');
        exit;
    }

    /**
     * On-demand resend after payment — same Nea receipt (includes manage link).
     *
     * @return true|\WP_Error
     */
    public static function send_manage_link_email(int $post_id, string $email, string $manage_key) {
        return self::send_receipt_email($post_id, $email, $manage_key);
    }

    /**
     * Payment receipt + manage URL. LOCKED COPY (Nea). Plain text; no social icons.
     * Kutsuja line = Nimi kuittiin (receipt_name), fallback kutsujan nimi.
     *
     * @return true|\WP_Error
     */
    public static function send_receipt_email(int $post_id, string $email, string $manage_key) {
        $email = sanitize_email($email);
        if ($email === '' || !is_email($email)) {
            return new \WP_Error('invalid_email', __('Anna kelvollinen sähköpostiosoite.', 'romanttinen-kutsu'));
        }

        $rate_key = 'romant_mail_' . md5($manage_key);
        if (get_transient($rate_key)) {
            return new \WP_Error(
                'rate_limited',
                __('Odota hetki ennen kuin lähetät uudelleen.', 'romanttinen-kutsu')
            );
        }

        $data         = Romant_Kutsu_CPT::get_invite_data($post_id);
        $receipt_name = trim((string) ($data['receipt_name'] ?? ''));
        if ($receipt_name === '') {
            $receipt_name = trim((string) ($data['inviter_name'] ?? ''));
        }
        if ($receipt_name === '') {
            $receipt_name = '—';
        }
        $price_disp   = Romant_Kutsu_Settings::get_price_display(); // "4,90 €"
        $manage_url   = Romant_Kutsu_Rewrite::manage_url($manage_key);
        $company_line = Romant_Kutsu_Settings::get_company_line();

        // LOCKED COPY (Nea) — exact wording; placeholders filled.
        $subject = 'Treffikutsu valmis — ' . $price_disp;
        $body    = "Hei,\n\n"
            . "Treffikutsusi on maksettu ja valmis jaettavaksi.\n\n"
            . "Summa: {$price_disp}\n"
            . "Kutsuja: {$receipt_name}\n\n"
            . "Hallitse kutsua tästä:\n"
            . $manage_url . "\n\n"
            . "—\n"
            . "Romanttinen\n"
            . $company_line . "\n\n"
            . "Tallenna hallintalinkki — se on avaimesi kutsuun.\n";

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $sent    = wp_mail($email, $subject, $body, $headers);

        if (!$sent) {
            return new \WP_Error(
                'mail_failed',
                __('Sähköpostin lähetys epäonnistui. Tarkista osoite tai yritä myöhemmin.', 'romanttinen-kutsu')
            );
        }

        update_post_meta($post_id, Romant_Kutsu_CPT::META_EMAIL, $email);
        set_transient($rate_key, 1, 90);

        return true;
    }

    /**
     * Post-payment: send manage link to email on demand.
     */
    public function handle_send_manage_email(): void {
        if (!isset($_POST['romant_email_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['romant_email_nonce'])), 'romant_send_manage_email')) {
            wp_die(esc_html__('Turvatarkistus epäonnistui. Yritä uudelleen.', 'romanttinen-kutsu'), 403);
        }

        $manage_key = isset($_POST['manage_key'])
            ? sanitize_text_field(wp_unslash((string) $_POST['manage_key'])) : '';
        $post = Romant_Kutsu_CPT::find_by_manage_key($manage_key);

        if (!$post) {
            wp_die(esc_html__('Kutsua ei löytynyt.', 'romanttinen-kutsu'), 404);
        }

        $data = Romant_Kutsu_CPT::get_invite_data((int) $post->ID);
        if (empty($data['paid']) || $data['token'] === '') {
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?email_error=not_paid');
            exit;
        }

        $email = isset($_POST['romant_email'])
            ? sanitize_email(wp_unslash((string) $_POST['romant_email'])) : '';

        $result = self::send_manage_link_email((int) $post->ID, $email, $manage_key);

        if (is_wp_error($result)) {
            $code = $result->get_error_code();
            $map  = [
                'invalid_email' => 'invalid',
                'rate_limited'  => 'rate',
                'mail_failed'   => 'fail',
            ];
            $q = $map[$code] ?? 'fail';
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?email_error=' . rawurlencode($q));
            exit;
        }

        wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?email_sent=1');
        exit;
    }

    public static function datetime_local_value(string $iso): string {
        if ($iso === '') {
            return '';
        }
        try {
            $dt = new DateTimeImmutable($iso);
            $dt = $dt->setTimezone(new DateTimeZone('Europe/Helsinki'));
            return $dt->format('Y-m-d\TH:i');
        } catch (Exception $e) {
            return '';
        }
    }
}
