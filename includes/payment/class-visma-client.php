<?php
/**
 * Minimal Visma Pay REST client (no Composer).
 *
 * Auth + create charge (auth_payment) + checkStatus + return AUTHCODE verify.
 * Docs: https://www.vismapay.com/docs/web_payments/
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Visma_Client {

    public const API_BASE = 'https://www.vismapay.com/pbwapi';
    public const API_VERSION = 'w3.2';
    public const CHARGE_URL_BASE = 'https://www.vismapay.com/pbwapi/token/';

    private string $api_key;
    private string $private_key;

    public function __construct(string $api_key, string $private_key) {
        $this->api_key     = $api_key;
        $this->private_key = $private_key;
    }

    /**
     * HMAC-SHA256 authcode, always UPPERCASE (Visma requirement).
     *
     * @param list<string> $parts Fields joined with '|'
     */
    public function authcode(array $parts): string {
        $message = implode('|', $parts);
        return strtoupper(hash_hmac('sha256', $message, $this->private_key));
    }

    /**
     * Create payment token (charge) — POST auth_payment.
     *
     * @param array{
     *   order_number:string,
     *   amount:int,
     *   currency?:string,
     *   email?:string,
     *   return_url:string,
     *   notify_url:string,
     *   lang?:string,
     *   customer?:array<string,string>,
     *   products?:list<array<string,mixed>>,
     *   selected?:list<string>
     * } $args
     * @return array{ok:bool,token?:string,type?:string,result?:int,errors?:list<string>,raw?:mixed,error?:string}
     */
    public function create_charge(array $args): array {
        $order_number = (string) ($args['order_number'] ?? '');
        $amount       = (int) ($args['amount'] ?? 0);
        $currency     = (string) ($args['currency'] ?? 'EUR');
        $return_url   = (string) ($args['return_url'] ?? '');
        $notify_url   = (string) ($args['notify_url'] ?? '');
        $lang         = (string) ($args['lang'] ?? 'fi');

        if ($order_number === '' || $amount < 1 || $return_url === '' || $notify_url === '') {
            return ['ok' => false, 'error' => 'invalid_args'];
        }

        $payment_method = [
            'type'       => 'e-payment',
            'return_url' => $return_url,
            'notify_url' => $notify_url,
            'lang'       => $lang,
        ];
        if (!empty($args['selected']) && is_array($args['selected'])) {
            $payment_method['selected'] = array_values($args['selected']);
        }

        $body = [
            'version'        => self::API_VERSION,
            'api_key'        => $this->api_key,
            'order_number'   => $order_number,
            'amount'         => $amount,
            'currency'       => $currency,
            'payment_method' => $payment_method,
            // authcode = HMAC-SHA256(api_key|order_number) with private_key, UPPERCASE
            'authcode'       => $this->authcode([$this->api_key, $order_number]),
        ];

        if (!empty($args['email']) && is_email((string) $args['email'])) {
            $body['email'] = (string) $args['email'];
        }
        if (!empty($args['customer']) && is_array($args['customer'])) {
            $body['customer'] = $args['customer'];
        }
        if (!empty($args['products']) && is_array($args['products'])) {
            $body['products'] = $args['products'];
        }

        $response = $this->post('auth_payment', $body);
        if (!$response['ok']) {
            return $response;
        }

        $json = $response['json'];
        $result = isset($json['result']) ? (int) $json['result'] : -1;
        if ($result !== 0 || empty($json['token'])) {
            return [
                'ok'     => false,
                'result' => $result,
                'errors' => isset($json['errors']) && is_array($json['errors']) ? $json['errors'] : [],
                'raw'    => $json,
                'error'  => 'charge_failed',
            ];
        }

        return [
            'ok'     => true,
            'token'  => (string) $json['token'],
            'type'   => (string) ($json['type'] ?? 'e-payment'),
            'result' => $result,
            'raw'    => $json,
        ];
    }

    /**
     * check_payment_status by token or order_number.
     *
     * @return array{ok:bool,paid?:bool,result?:int,settled?:int,raw?:mixed,error?:string}
     */
    public function check_status(?string $token = null, ?string $order_number = null): array {
        $body = [
            'version' => self::API_VERSION,
            'api_key' => $this->api_key,
        ];

        if ($token !== null && $token !== '') {
            $body['token']    = $token;
            $body['authcode'] = $this->authcode([$this->api_key, $token]);
        } elseif ($order_number !== null && $order_number !== '') {
            $body['order_number'] = $order_number;
            $body['authcode']     = $this->authcode([$this->api_key, $order_number]);
        } else {
            return ['ok' => false, 'error' => 'missing_token_or_order'];
        }

        $response = $this->post('check_payment_status', $body);
        if (!$response['ok']) {
            return $response;
        }

        $json   = $response['json'];
        $result = isset($json['result']) ? (int) $json['result'] : -1;

        // result 0 = success (paid/authorized)
        return [
            'ok'      => true,
            'paid'    => $result === 0,
            'result'  => $result,
            'settled' => isset($json['settled']) ? (int) $json['settled'] : null,
            'raw'     => $json,
        ];
    }

    /**
     * Verify RETURN AUTHCODE from user return / notify callback.
     *
     * MAC fields: RETURN_CODE|ORDER_NUMBER[|SETTLED][|INCIDENT_ID]
     * (CONTACT_ID also appended if present — matches official PHP lib.)
     *
     * @param array<string,string> $query
     */
    public function verify_return_authcode(array $query): bool {
        if (!isset($query['RETURN_CODE'], $query['ORDER_NUMBER'], $query['AUTHCODE'])) {
            return false;
        }

        $parts = [
            (string) $query['RETURN_CODE'],
            (string) $query['ORDER_NUMBER'],
        ];
        if (isset($query['SETTLED']) && $query['SETTLED'] !== '') {
            $parts[] = (string) $query['SETTLED'];
        }
        if (isset($query['CONTACT_ID']) && $query['CONTACT_ID'] !== '') {
            $parts[] = (string) $query['CONTACT_ID'];
        }
        if (isset($query['INCIDENT_ID']) && $query['INCIDENT_ID'] !== '') {
            $parts[] = (string) $query['INCIDENT_ID'];
        }

        $expected = $this->authcode($parts);
        $provided = (string) $query['AUTHCODE'];

        return hash_equals($expected, $provided);
    }

    public static function charge_redirect_url(string $token): string {
        return self::CHARGE_URL_BASE . rawurlencode($token);
    }

    /**
     * @param array<string,mixed> $body
     * @return array{ok:bool,json?:array<string,mixed>,error?:string,http?:int}
     */
    private function post(string $endpoint, array $body): array {
        $url  = self::API_BASE . '/' . ltrim($endpoint, '/');
        $json = wp_json_encode($body);
        if ($json === false) {
            return ['ok' => false, 'error' => 'json_encode'];
        }

        $response = wp_remote_post($url, [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => $json,
        ]);

        if (is_wp_error($response)) {
            return [
                'ok'    => false,
                'error' => 'http: ' . $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw  = (string) wp_remote_retrieve_body($response);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded) || !array_key_exists('result', $decoded)) {
            return [
                'ok'    => false,
                'error' => 'invalid_json',
                'http'  => $code,
            ];
        }

        return [
            'ok'   => true,
            'json' => $decoded,
            'http' => $code,
        ];
    }
}
