<?php
/**
 * Stub payment gateway — marks invite paid without Visma.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Stub_Gateway implements Romant_Kutsu_Payment_Gateway {

    public function start_payment(array $ctx): array {
        return [
            'ok'          => true,
            'marked_paid' => true,
        ];
    }

    public function handle_callback(array $query): array {
        return [
            'ok'    => false,
            'error' => 'stub_no_callback',
        ];
    }
}
