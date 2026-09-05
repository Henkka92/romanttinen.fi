<?php
/**
 * Visma Pay e-payment gateway.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Visma_Gateway implements Romant_Kutsu_Payment_Gateway {

    private Romant_Kutsu_Visma_Client $client;

    public function __construct(?Romant_Kutsu_Visma_Client $client = null) {
        $this->client = $client ?? new Romant_Kutsu_Visma_Client(
            Romant_Kutsu_Settings::visma_api_key(),
            Romant_Kutsu_Settings::visma_private_key()
        );
    }

    public function start_payment(array $ctx): array {
        $post_id     = (int) ($ctx['post_id'] ?? 0);
        $amount      = (int) ($ctx['amount_cents'] ?? 0);
        $email       = (string) ($ctx['email'] ?? '');
        $return_url  = (string) ($ctx['return_url'] ?? '');
        $notify_url  = (string) ($ctx['notify_url'] ?? '');

        if ($post_id < 1 || $amount < 1) {
            return ['ok' => false, 'error' => 'invalid_ctx'];
        }

        // Unique order number: letters, numbers, dashes, underscores only.
        $order_number = sprintf('romant-%d-%s', $post_id, bin2hex(random_bytes(6)));

        $product_title = 'Romanttinen kutsu';
        $charge = $this->client->create_charge([
            'order_number' => $order_number,
            'amount'       => $amount,
            'currency'     => 'EUR',
            'email'        => $email,
            'return_url'   => $return_url,
            'notify_url'   => $notify_url,
            'lang'         => 'fi',
            'customer'     => array_filter([
                'email' => $email !== '' && is_email($email) ? $email : null,
            ]),
            'products'     => [
                [
                    'id'           => 'romant-kutsu',
                    'title'        => $product_title,
                    'count'        => 1,
                    'pretax_price' => $amount,
                    'tax'          => 0,
                    'price'        => $amount,
                    'type'         => 1,
                ],
            ],
        ]);

        if (!$charge['ok'] || empty($charge['token'])) {
            return [
                'ok'    => false,
                'error' => 'visma_charge',
            ];
        }

        $token = (string) $charge['token'];
        update_post_meta($post_id, Romant_Kutsu_CPT::META_ORDER_NUMBER, $order_number);
        update_post_meta($post_id, Romant_Kutsu_CPT::META_VISMA_TOKEN, $token);

        return [
            'ok'       => true,
            'redirect' => Romant_Kutsu_Visma_Client::charge_redirect_url($token),
        ];
    }

    public function handle_callback(array $query): array {
        if (!$this->client->verify_return_authcode($query)) {
            return ['ok' => false, 'error' => 'bad_authcode'];
        }

        $order_number = (string) ($query['ORDER_NUMBER'] ?? '');
        $return_code  = (string) ($query['RETURN_CODE'] ?? '');

        $post = Romant_Kutsu_CPT::find_by_order_number($order_number);
        if (!$post) {
            return ['ok' => false, 'error' => 'order_not_found'];
        }

        $post_id    = (int) $post->ID;
        $manage_key = (string) get_post_meta($post_id, Romant_Kutsu_CPT::META_MANAGE_KEY, true);
        $visma_tok  = (string) get_post_meta($post_id, Romant_Kutsu_CPT::META_VISMA_TOKEN, true);

        // Always confirm server-side with checkStatus (never trust RETURN_CODE alone).
        $status = $visma_tok !== ''
            ? $this->client->check_status($visma_tok, null)
            : $this->client->check_status(null, $order_number);

        if (!$status['ok']) {
            return [
                'ok'         => false,
                'error'      => 'status_check_failed',
                'post_id'    => $post_id,
                'manage_key' => $manage_key,
            ];
        }

        if (!empty($status['paid'])) {
            return [
                'ok'         => true,
                'post_id'    => $post_id,
                'manage_key' => $manage_key,
            ];
        }

        // Payment not successful (failed / pending / etc.)
        return [
            'ok'         => false,
            'error'      => $return_code === '0' ? 'not_settled' : 'payment_failed',
            'post_id'    => $post_id,
            'manage_key' => $manage_key,
        ];
    }
}
