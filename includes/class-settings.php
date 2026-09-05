<?php
/**
 * Settings → Romanttinen.
 *
 * @package Romanttinen_Kutsu
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Romant_Kutsu_Settings {

    public const OPTION_PRICE              = 'romant_kutsu_price';
    public const OPTION_STUB               = 'romant_kutsu_stub_payments';
    public const OPTION_USE_HOME           = 'romant_kutsu_use_homepage';
    public const OPTION_VISMA_API_KEY      = 'romant_kutsu_visma_api_key';
    public const OPTION_VISMA_PRIVATE_KEY  = 'romant_kutsu_visma_private_key';
    public const OPTION_COMPANY_NAME       = 'romant_kutsu_company_name';
    public const OPTION_BUSINESS_ID        = 'romant_kutsu_business_id';
    public const OPTION_COMPANY_ADDRESS    = 'romant_kutsu_company_address';

    /** @deprecated Kept so old installs do not fatally reference; unused in UI. */
    public const OPTION_VISMA_MERCHANT = 'romant_kutsu_visma_merchant_id';

    public function register_menu(): void {
        add_options_page(
            'Romanttinen Kutsu',
            'Romanttinen',
            'manage_options',
            'romant-kutsu',
            [$this, 'render_page']
        );
    }

    public function register_settings(): void {
        register_setting('romant_kutsu_settings', self::OPTION_PRICE, [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_price'],
            'default'           => '4.90',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_STUB, [
            'type'              => 'string',
            'sanitize_callback' => static function ($v): string {
                return $v ? '1' : '0';
            },
            'default'           => '1',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_USE_HOME, [
            'type'              => 'string',
            'sanitize_callback' => static function ($v): string {
                return $v ? '1' : '0';
            },
            'default'           => '1',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_VISMA_API_KEY, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_VISMA_PRIVATE_KEY, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_COMPANY_NAME, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_BUSINESS_ID, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_COMPANY_ADDRESS, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
    }

    public function sanitize_price($value): string {
        $v = is_string($value) ? str_replace(',', '.', trim($value)) : '4.90';
        if (!is_numeric($v) || (float) $v < 0) {
            return '4.90';
        }
        return number_format((float) $v, 2, '.', '');
    }

    public static function get_price(): string {
        $p = get_option(self::OPTION_PRICE, '4.90');
        return is_string($p) && $p !== '' ? $p : '4.90';
    }

    public static function get_price_display(): string {
        return str_replace('.', ',', self::get_price()) . ' €';
    }

    /**
     * Amount in fractional monetary units (1 € = 100).
     */
    public static function get_price_cents(): int {
        return (int) round((float) self::get_price() * 100);
    }

    /**
     * Stub when constant true OR settings toggle on (default ON for staging).
     */
    public static function stub_payments_enabled(): bool {
        if (defined('ROMANTTINEN_STUB_PAYMENTS') && ROMANTTINEN_STUB_PAYMENTS) {
            return true;
        }
        return get_option(self::OPTION_STUB, '1') === '1';
    }

    public static function visma_api_key(): string {
        return (string) get_option(self::OPTION_VISMA_API_KEY, '');
    }

    public static function visma_private_key(): string {
        return (string) get_option(self::OPTION_VISMA_PRIVATE_KEY, '');
    }

    public static function visma_configured(): bool {
        return self::visma_api_key() !== '' && self::visma_private_key() !== '';
    }

    public static function company_name(): string {
        return trim((string) get_option(self::OPTION_COMPANY_NAME, ''));
    }

    public static function business_id(): string {
        return trim((string) get_option(self::OPTION_BUSINESS_ID, ''));
    }

    public static function company_address(): string {
        return trim((string) get_option(self::OPTION_COMPANY_ADDRESS, ''));
    }

    /**
     * Company line for receipt footer. Defaults to public contact when unset.
     */
    public static function get_company_line(): string {
        $parts = [];
        $name = self::company_name();
        if ($name !== '') {
            $parts[] = $name;
        }
        $bid = self::business_id();
        if ($bid !== '') {
            $parts[] = 'Y-tunnus ' . $bid;
        }
        $addr = self::company_address();
        if ($addr !== '') {
            $parts[] = $addr;
        }
        if ($parts === []) {
            return 'romanttinen.fi · kutsu@romanttinen.fi';
        }
        return implode(' · ', $parts);
    }

    public function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Romanttinen Kutsu</h1>
            <form method="post" action="options.php">
                <?php settings_fields('romant_kutsu_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="romant_kutsu_price">Hinta (EUR)</label></th>
                        <td>
                            <input type="text" id="romant_kutsu_price" name="<?php echo esc_attr(self::OPTION_PRICE); ?>"
                                   value="<?php echo esc_attr(self::get_price()); ?>" class="regular-text" />
                            <p class="description">Oletus 4.90. Näytetään muodossa 4,90 €.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stub payments</th>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr(self::OPTION_STUB); ?>" value="0" />
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_STUB); ?>" value="1"
                                    <?php checked(get_option(self::OPTION_STUB, '1'), '1'); ?> />
                                Merkitse kutsu maksetuksi ilman Vismaa (demo / staging)
                            </label>
                            <p class="description">
                                Oletus: päällä. Myös vakio <code>ROMANTTINEN_STUB_PAYMENTS</code> = true wp-configissa pakottaa stub-tilan.
                                Kun stub on pois päältä, tarvitaan Visma Pay API-avain ja private key.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Käytä Romanttinen-etusivua</th>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr(self::OPTION_USE_HOME); ?>" value="0" />
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_USE_HOME); ?>" value="1"
                                    <?php checked(get_option(self::OPTION_USE_HOME, '1'), '1'); ?> />
                                Korvaa WordPress-etusivu Romanttinen-markkinointisivulla (oletus: päällä)
                            </label>
                            <p class="description">
                                Kun päällä, etusivu renderöidään pluginin itsenäisellä layoutilla (ei teeman blog-ulkoasua).
                                Vaihtoehto: shortcode <code>[romant_etusivu]</code> millä tahansa sivulla.
                                Kutsu-reitit (<code>/kutsu/…</code>) toimivat aina.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_visma_api_key">Visma Pay API Key</label></th>
                        <td>
                            <input type="password" id="romant_kutsu_visma_api_key"
                                   name="<?php echo esc_attr(self::OPTION_VISMA_API_KEY); ?>"
                                   value="<?php echo esc_attr(self::visma_api_key()); ?>" class="regular-text" autocomplete="off" />
                            <p class="description">Sub-merchant API key Merchant Portalista (Sub-merchants).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_visma_private_key">Visma Pay Private Key</label></th>
                        <td>
                            <input type="password" id="romant_kutsu_visma_private_key"
                                   name="<?php echo esc_attr(self::OPTION_VISMA_PRIVATE_KEY); ?>"
                                   value="<?php echo esc_attr(self::visma_private_key()); ?>" class="regular-text" autocomplete="off" />
                            <p class="description">Private key HMAC-allekirjoitukseen. Älä jaa julkisesti.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2"><h2 style="margin:1em 0 0;">Yritystiedot (kuitti)</h2></th>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_company_name">Yrityksen nimi</label></th>
                        <td>
                            <input type="text" id="romant_kutsu_company_name"
                                   name="<?php echo esc_attr(self::OPTION_COMPANY_NAME); ?>"
                                   value="<?php echo esc_attr(self::company_name()); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_business_id">Y-tunnus</label></th>
                        <td>
                            <input type="text" id="romant_kutsu_business_id"
                                   name="<?php echo esc_attr(self::OPTION_BUSINESS_ID); ?>"
                                   value="<?php echo esc_attr(self::business_id()); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_company_address">Osoite</label></th>
                        <td>
                            <input type="text" id="romant_kutsu_company_address"
                                   name="<?php echo esc_attr(self::OPTION_COMPANY_ADDRESS); ?>"
                                   value="<?php echo esc_attr(self::company_address()); ?>" class="regular-text" />
                            <p class="description">Tyhjät kentät → kuittiin oletus: <code>romanttinen.fi · kutsu@romanttinen.fi</code></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Tallenna asetukset'); ?>
            </form>
            <p class="description">
                Testiavaimet: Visma Pay Merchant Portal → Sub-merchants, tai pyydä testi-tili
                <a href="https://www.vismapay.com/docs/web_payments/?page=testing" target="_blank" rel="noopener">dokumentaatiosta</a>.
            </p>
        </div>
        <?php
    }
}
