<?php
/**
 * Recipient open / reveal events + optional organizer emails.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Events {

    public function register(): void {
        add_action('wp_ajax_romant_track_event', [$this, 'handle_ajax']);
        add_action('wp_ajax_nopriv_romant_track_event', [$this, 'handle_ajax']);
    }

    public function handle_ajax(): void {
        $nonce = isset($_POST['nonce'])
            ? sanitize_text_field(wp_unslash((string) $_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'romant_track_event')) {
            wp_send_json_error(['message' => 'nonce'], 403);
        }

        $token = isset($_POST['token'])
            ? sanitize_text_field(wp_unslash((string) $_POST['token'])) : '';
        $type  = isset($_POST['type'])
            ? sanitize_text_field(wp_unslash((string) $_POST['type'])) : '';
        $section_title = isset($_POST['section'])
            ? sanitize_text_field(wp_unslash((string) $_POST['section'])) : '';
        $section_index = isset($_POST['section_index'])
            ? (int) $_POST['section_index'] : -1;

        if ($token === '' || !in_array($type, ['open', 'reveal'], true)) {
            wp_send_json_error(['message' => 'bad_request'], 400);
        }

        $post = Romant_Kutsu_CPT::find_by_token($token);
        if (!$post) {
            wp_send_json_error(['message' => 'not_found'], 404);
        }

        $post_id = (int) $post->ID;
        $data    = Romant_Kutsu_CPT::get_invite_data($post_id);

        if ($type === 'open') {
            $already = (string) get_post_meta($post_id, Romant_Kutsu_CPT::META_OPENED_AT, true);
            Romant_Kutsu_CPT::record_event($post_id, [
                'type' => 'open',
            ]);
            if ($already === '') {
                update_post_meta($post_id, Romant_Kutsu_CPT::META_OPENED_AT, gmdate('c'));
                $this->maybe_mail_opened($post_id, $data);
            }
            wp_send_json_success(['ok' => true, 'first' => $already === '']);
        }

        // reveal — first deepen per section gets mail (digest-friendly).
        if ($section_title === '' && $section_index >= 0) {
            $secs = $data['sections'] ?? [];
            if (isset($secs[$section_index]['title'])) {
                $section_title = (string) $secs[$section_index]['title'];
            }
        }

        Romant_Kutsu_CPT::record_event($post_id, [
            'type'          => 'reveal',
            'section'       => $section_title,
            'section_index' => $section_index,
        ]);

        $mail_key = 'romant_reveal_mailed_' . $post_id . '_' . md5((string) $section_index . '|' . $section_title);
        if (!get_transient($mail_key)) {
            $this->maybe_mail_reveal($post_id, $data, $section_title);
            set_transient($mail_key, 1, WEEK_IN_SECONDS);
        }

        wp_send_json_success(['ok' => true]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function maybe_mail_opened(int $post_id, array $data): void {
        $email = sanitize_email((string) ($data['email'] ?? ''));
        if ($email === '' || !is_email($email)) {
            return;
        }
        $when    = Romant_Kutsu_CPT::format_datetime_fi((string) ($data['datetime'] ?? ''));
        $subject = 'Kutsu avattiin — ' . ($when !== '' ? $when : 'romanttinen');
        $body    = "Hei,\n\n"
            . "Kutsusi avattiin.\n"
            . ($when !== '' ? "Treffiaika: {$when}\n" : '')
            . "\n— romanttinen\n";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
        wp_mail($email, $subject, $body, $headers);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function maybe_mail_reveal(int $post_id, array $data, string $section_title): void {
        $email = sanitize_email((string) ($data['email'] ?? ''));
        if ($email === '' || !is_email($email)) {
            return;
        }
        $osio    = $section_title !== '' ? $section_title : 'osio';
        $subject = 'Pyysi kuulla lisää: ' . $osio;
        $body    = "Hei,\n\n"
            . "Vastaanottaja pyysi kuulla lisää osiosta: {$osio}.\n\n"
            . "— romanttinen\n";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
        wp_mail($email, $subject, $body, $headers);
    }
}
