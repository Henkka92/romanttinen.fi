<?php
/**
 * Payment orchestration: stub or Visma Pay.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Payment {

    public function gateway(): Romant_Kutsu_Payment_Gateway {
        if (Romant_Kutsu_Settings::stub_payments_enabled()) {
            return new Romant_Kutsu_Stub_Gateway();
        }
        return new Romant_Kutsu_Visma_Gateway();
    }

    public function handle_pay(): void {
        if (!isset($_POST['romant_pay_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['romant_pay_nonce'])), 'romant_pay_kutsu')) {
            wp_die(esc_html__('Turvatarkistus epäonnistui. Yritä uudelleen.', 'romanttinen-kutsu'), 403);
        }

        $manage_key = isset($_POST['manage_key']) ? sanitize_text_field(wp_unslash((string) $_POST['manage_key'])) : '';
        $post       = Romant_Kutsu_CPT::find_by_manage_key($manage_key);

        if (!$post) {
            wp_die(esc_html__('Kutsua ei löytynyt.', 'romanttinen-kutsu'), 404);
        }

        $post_id = (int) $post->ID;
        $data    = Romant_Kutsu_CPT::get_invite_data($post_id);

        // Allow saving kutsujan nimi on the pay CTA (required for share link / anti-spam).
        $posted_inviter = isset($_POST['romant_inviter_name'])
            ? sanitize_text_field(wp_unslash((string) $_POST['romant_inviter_name'])) : '';
        if (function_exists('mb_substr')) {
            $posted_inviter = mb_substr($posted_inviter, 0, 80);
        } else {
            $posted_inviter = substr($posted_inviter, 0, 80);
        }
        if ($posted_inviter !== '') {
            update_post_meta($post_id, Romant_Kutsu_CPT::META_INVITER_NAME, $posted_inviter);
            $data['inviter_name'] = $posted_inviter;
        }
        if (($data['inviter_name'] ?? '') === '') {
            wp_die(esc_html__('Kirjoita kutsujan nimi ennen jakolinkin hankkimista.', 'romanttinen-kutsu'), 400);
        }

        // Receipt name (Nimi kuittiin): optional override; empty → kutsujan nimi.
        $posted_receipt = isset($_POST['romant_receipt_name'])
            ? sanitize_text_field(wp_unslash((string) $_POST['romant_receipt_name'])) : '';
        if (function_exists('mb_substr')) {
            $posted_receipt = mb_substr($posted_receipt, 0, 80);
        } else {
            $posted_receipt = substr($posted_receipt, 0, 80);
        }
        $posted_receipt = trim($posted_receipt);
        if ($posted_receipt === '') {
            $posted_receipt = (string) ($data['inviter_name'] ?? '');
        }
        update_post_meta($post_id, Romant_Kutsu_CPT::META_RECEIPT_NAME, $posted_receipt);
        $data['receipt_name'] = $posted_receipt;

        // Buyer email REQUIRED before charging (receipt + manage link).
        $posted_email = isset($_POST['romant_email'])
            ? sanitize_email(wp_unslash((string) $_POST['romant_email'])) : '';
        if ($posted_email === '' || !is_email($posted_email)) {
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?pay_error=email');
            exit;
        }
        update_post_meta($post_id, Romant_Kutsu_CPT::META_EMAIL, $posted_email);
        $email_for_send = $posted_email;

        if ($data['paid'] && $data['token'] !== '') {
            $suffix = $this->maybe_auto_send_email($post_id, $email_for_send, $manage_key);
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?paid=1' . $suffix);
            exit;
        }

        // --- STUB MODE (default for staging) ---
        if (Romant_Kutsu_Settings::stub_payments_enabled()) {
            $this->mark_paid($post_id);
            $suffix = $this->maybe_auto_send_email($post_id, $email_for_send, $manage_key);
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?paid=1' . $suffix);
            exit;
        }

        // Stub off + no Visma keys → Finnish error.
        if (!Romant_Kutsu_Settings::visma_configured()) {
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?pay_error=no_visma');
            exit;
        }

        // Remember email for Visma return/notify receipt auto-send.
        update_post_meta($post_id, Romant_Kutsu_CPT::META_PAY_EMAIL_PENDING, $email_for_send);

        $result = $this->gateway()->start_payment([
            'post_id'      => $post_id,
            'manage_key'   => $manage_key,
            'amount_cents' => Romant_Kutsu_Settings::get_price_cents(),
            'email'        => $email_for_send,
            'return_url'   => Romant_Kutsu_Rewrite::pay_return_url(),
            'notify_url'   => Romant_Kutsu_Rewrite::pay_notify_url(),
        ]);

        if (!empty($result['ok']) && !empty($result['redirect'])) {
            // External Visma hosted pay page — wp_redirect intentionally (not wp_safe_redirect).
            wp_redirect($result['redirect']); // phpcs:ignore WordPress.Security.SafeRedirect
            exit;
        }

        wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage_key) . '?pay_error=visma_charge');
        exit;
    }

    /**
     * User return from Visma Pay (/kutsu/maksu/paluu/).
     */
    public function handle_return(): void {
        $query = $this->sanitize_visma_query($_GET);

        if (!Romant_Kutsu_Settings::visma_configured()) {
            wp_die(esc_html__('Visma Pay -tunnuksia ei ole asetettu.', 'romanttinen-kutsu'), 400);
        }

        $result = (new Romant_Kutsu_Visma_Gateway())->handle_callback($query);
        $manage = (string) ($result['manage_key'] ?? '');

        if (!empty($result['ok']) && !empty($result['post_id'])) {
            $this->mark_paid((int) $result['post_id']);
            $suffix = $this->finish_pending_email((int) $result['post_id'], $manage);
            $url = $manage !== ''
                ? Romant_Kutsu_Rewrite::manage_url($manage) . '?paid=1' . $suffix
                : home_url('/');
            wp_safe_redirect($url);
            exit;
        }

        $err = (string) ($result['error'] ?? 'payment_failed');
        if ($manage !== '') {
            wp_safe_redirect(Romant_Kutsu_Rewrite::manage_url($manage) . '?pay_error=' . rawurlencode($err));
            exit;
        }

        wp_die(esc_html__('Maksun vahvistus epäonnistui.', 'romanttinen-kutsu'), 400);
    }

    /**
     * Server-to-server notify from Visma Pay (/kutsu/maksu/ilmoitus/).
     * Must respond HTTP 200 when handled.
     */
    public function handle_notify(): void {
        $query = $this->sanitize_visma_query($_GET);

        if (!Romant_Kutsu_Settings::visma_configured()) {
            status_header(400);
            echo 'no_keys';
            exit;
        }

        $result = (new Romant_Kutsu_Visma_Gateway())->handle_callback($query);

        if (!empty($result['ok']) && !empty($result['post_id'])) {
            $post_id = (int) $result['post_id'];
            $already = (bool) get_post_meta($post_id, Romant_Kutsu_CPT::META_PAID, true);
            if (!$already) {
                $this->mark_paid($post_id);
                $manage = (string) ($result['manage_key'] ?? '');
                $this->finish_pending_email($post_id, $manage);
            }
        }

        status_header(200);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'OK';
        exit;
    }

    /**
     * @param array<string,mixed> $src
     * @return array<string,string>
     */
    private function sanitize_visma_query(array $src): array {
        $keys = ['RETURN_CODE', 'ORDER_NUMBER', 'SETTLED', 'INCIDENT_ID', 'CONTACT_ID', 'AUTHCODE'];
        $out  = [];
        foreach ($keys as $k) {
            if (isset($src[$k])) {
                $out[$k] = sanitize_text_field(wp_unslash((string) $src[$k]));
            }
        }
        return $out;
    }

    /**
     * Auto-send tilausvahvistus / kuitti (includes manage link) after successful payment.
     *
     * @return string Query suffix (&email_sent=1 or &email_error=…) or empty.
     */
    private function maybe_auto_send_email(int $post_id, string $email, string $manage_key): string {
        if ($email === '' || !is_email($email)) {
            return '';
        }
        $result = Romant_Kutsu_Forms::send_receipt_email($post_id, $email, $manage_key);
        if (is_wp_error($result)) {
            $code = $result->get_error_code();
            $map  = [
                'invalid_email' => 'invalid',
                'rate_limited'  => 'rate',
                'mail_failed'   => 'fail',
            ];
            $q = $map[$code] ?? 'fail';
            return '&email_error=' . rawurlencode($q);
        }
        return '&email_sent=1';
    }

    /**
     * Send pending pay-form receipt after Visma return/notify, then clear flag.
     */
    private function finish_pending_email(int $post_id, string $manage_key): string {
        $pending = (string) get_post_meta($post_id, Romant_Kutsu_CPT::META_PAY_EMAIL_PENDING, true);
        delete_post_meta($post_id, Romant_Kutsu_CPT::META_PAY_EMAIL_PENDING);
        if ($pending === '' || $manage_key === '') {
            return '';
        }
        return $this->maybe_auto_send_email($post_id, $pending, $manage_key);
    }

    /**
     * Mark invite paid: publish + generate public share token.
     */
    public function mark_paid(int $post_id): void {
        $existing = (string) get_post_meta($post_id, Romant_Kutsu_CPT::META_TOKEN, true);
        if ($existing === '') {
            update_post_meta($post_id, Romant_Kutsu_CPT::META_TOKEN, Romant_Kutsu_CPT::generate_secret(24));
        }
        update_post_meta($post_id, Romant_Kutsu_CPT::META_PAID, true);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_PAID_AT, gmdate('c'));

        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'publish',
        ]);
    }
}
