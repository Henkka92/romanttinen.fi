<?php
/**
 * Payment gateway contract.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

interface Romant_Kutsu_Payment_Gateway {

    /**
     * Start payment for an invite. Stub marks paid immediately; Visma redirects.
     *
     * @param array{post_id:int,manage_key:string,amount_cents:int,email:string,return_url:string,notify_url:string} $ctx
     * @return array{ok:bool,redirect?:string,error?:string,marked_paid?:bool}
     */
    public function start_payment(array $ctx): array;

    /**
     * Verify Visma return/notify query params (AUTHCODE) and confirm paid via checkStatus.
     *
     * @param array<string,string> $query GET params from Visma
     * @return array{ok:bool,post_id?:int,manage_key?:string,error?:string}
     */
    public function handle_callback(array $query): array;
}
