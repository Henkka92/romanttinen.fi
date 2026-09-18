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
    public const OPTION_COMPANY_EMAIL      = 'romant_kutsu_company_email';
    public const OPTION_VAT_RATE           = 'romant_kutsu_vat_rate';

    public const DEFAULT_COMPANY_NAME    = 'Kelaus Finland Oy';
    public const DEFAULT_BUSINESS_ID     = '2806633-5';
    public const DEFAULT_COMPANY_ADDRESS = 'Heiniläntie 37, 08500 Lohja';
    public const DEFAULT_COMPANY_EMAIL   = 'henry@kelaus.fi';
    public const DEFAULT_VAT_RATE        = '25.5';

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
            'default'           => '0',
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
            'default'           => self::DEFAULT_COMPANY_NAME,
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_BUSINESS_ID, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => self::DEFAULT_BUSINESS_ID,
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_COMPANY_ADDRESS, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => self::DEFAULT_COMPANY_ADDRESS,
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_COMPANY_EMAIL, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => self::DEFAULT_COMPANY_EMAIL,
        ]);
        register_setting('romant_kutsu_settings', self::OPTION_VAT_RATE, [
            'type'              => 'string',
            'sanitize_callback' => static function ($v): string {
                $n = is_string($v) ? str_replace(',', '.', trim($v)) : self::DEFAULT_VAT_RATE;
                if (!is_numeric($n) || (float) $n < 0) {
                    return self::DEFAULT_VAT_RATE;
                }
                return number_format((float) $n, 1, '.', '');
            },
            'default'           => self::DEFAULT_VAT_RATE,
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
     * LOCKED pay/receipt price line (Portti 4). One line, VAT included.
     * Default: 4,90 € sis. ALV 25,5 %
     */
    public static function get_price_vat_line(): string {
        return self::get_price_display() . ' sis. ALV ' . self::vat_rate_display() . ' %';
    }

    /**
     * Amount in fractional monetary units (1 € = 100).
     */
    public static function get_price_cents(): int {
        return (int) round((float) self::get_price() * 100);
    }

    /**
     * Stub only when constant true OR settings toggle on.
     * Production default: OFF.
     */
    public static function stub_payments_enabled(): bool {
        if (defined('ROMANTTINEN_STUB_PAYMENTS') && ROMANTTINEN_STUB_PAYMENTS) {
            return true;
        }
        return get_option(self::OPTION_STUB, '0') === '1';
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
        $v = trim((string) get_option(self::OPTION_COMPANY_NAME, self::DEFAULT_COMPANY_NAME));
        return $v !== '' ? $v : self::DEFAULT_COMPANY_NAME;
    }

    public static function business_id(): string {
        $v = trim((string) get_option(self::OPTION_BUSINESS_ID, self::DEFAULT_BUSINESS_ID));
        return $v !== '' ? $v : self::DEFAULT_BUSINESS_ID;
    }

    public static function company_address(): string {
        $v = trim((string) get_option(self::OPTION_COMPANY_ADDRESS, self::DEFAULT_COMPANY_ADDRESS));
        return $v !== '' ? $v : self::DEFAULT_COMPANY_ADDRESS;
    }

    public static function company_email(): string {
        $v = trim((string) get_option(self::OPTION_COMPANY_EMAIL, self::DEFAULT_COMPANY_EMAIL));
        return $v !== '' ? $v : self::DEFAULT_COMPANY_EMAIL;
    }

    public static function vat_rate(): string {
        $v = get_option(self::OPTION_VAT_RATE, self::DEFAULT_VAT_RATE);
        $n = is_string($v) ? str_replace(',', '.', trim($v)) : self::DEFAULT_VAT_RATE;
        if (!is_numeric($n) || (float) $n < 0) {
            return self::DEFAULT_VAT_RATE;
        }
        return number_format((float) $n, 1, '.', '');
    }

    public static function vat_rate_display(): string {
        return str_replace('.', ',', self::vat_rate());
    }

    /**
     * Company line for receipt footer (Kelaus seed).
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
        $email = self::company_email();
        if ($email !== '') {
            $parts[] = $email;
        }
        if ($parts === []) {
            return self::DEFAULT_COMPANY_NAME . ' · Y-tunnus ' . self::DEFAULT_BUSINESS_ID;
        }
        return implode(' · ', $parts);
    }

    /**
     * Multiline seller block for kuitti (LOCKED Portti 4).
     */
    public static function get_company_block(): string {
        return self::company_name() . "\n"
            . 'Y-tunnus ' . self::business_id() . "\n"
            . self::company_address() . "\n"
            . self::company_email();
    }

    /**
     * Seed Kelaus seller + ALV + stub OFF (production). Safe to call on every init.
     * Empty company fields fill with defaults; stub is forced OFF once per install.
     */
    public static function maybe_seed(): void {
        $map = [
            self::OPTION_COMPANY_NAME    => self::DEFAULT_COMPANY_NAME,
            self::OPTION_BUSINESS_ID     => self::DEFAULT_BUSINESS_ID,
            self::OPTION_COMPANY_ADDRESS => self::DEFAULT_COMPANY_ADDRESS,
            self::OPTION_COMPANY_EMAIL   => self::DEFAULT_COMPANY_EMAIL,
            self::OPTION_VAT_RATE        => self::DEFAULT_VAT_RATE,
        ];
        foreach ($map as $opt => $default) {
            $cur = get_option($opt, false);
            if ($cur === false || (is_string($cur) && trim($cur) === '')) {
                update_option($opt, $default);
            }
        }
        if (get_option(self::OPTION_PRICE, false) === false) {
            add_option(self::OPTION_PRICE, '4.90');
        }
        if (get_option(self::OPTION_STUB, false) === false) {
            add_option(self::OPTION_STUB, '0');
        }
        if (get_option('romant_kutsu_trust_seeded', '') !== '1') {
            update_option(self::OPTION_STUB, '0');
            update_option('romant_kutsu_trust_seeded', '1');
        }
    }


    /**
     * WP-admin: create a paid multi-level peel QA invite (stub-friendly).
     */
    public function handle_create_peel_qa(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Ei oikeuksia.', 'romanttinen-kutsu'), 403);
        }
        if (!isset($_POST['romant_peel_qa_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['romant_peel_qa_nonce'])), 'romant_create_peel_qa')) {
            wp_die(esc_html__('Turvatarkistus epäonnistui. Yritä uudelleen.', 'romanttinen-kutsu'), 403);
        }

        $result = Romant_Kutsu_CPT::create_peel_qa_invite();
        if (is_wp_error($result)) {
            wp_die(esc_html__('Peel-testikutsun luonti epäonnistui.', 'romanttinen-kutsu'), 500);
        }

        $redirect = add_query_arg(
            [
                'page'       => 'romant-kutsu',
                'peel_qa'    => '1',
                'token'      => $result['token'],
                'manage_key' => $result['manage_key'],
            ],
            admin_url('options-general.php')
        );
        wp_safe_redirect($redirect);
        exit;
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
                            <p class="description">Oletus 4.90. Maksussa ja kuitissa: <code>4,90 € sis. ALV 25,5 %</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stub payments</th>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr(self::OPTION_STUB); ?>" value="0" />
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_STUB); ?>" value="1"
                                    <?php checked(get_option(self::OPTION_STUB, '0'), '1'); ?> />
                                Merkitse kutsu maksetuksi ilman Vismaa (demo / staging)
                            </label>
                            <p class="description">
                                Oletus: <strong>pois päältä</strong> (tuotanto). Staging: vakio
                                <code>ROMANTTINEN_STUB_PAYMENTS</code> = true wp-configissa pakottaa stub-tilan.
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
                        <th scope="row" colspan="2"><h2 style="margin:1em 0 0;">Yritystiedot (kuitti / Portti 4)</h2></th>
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
                            <p class="description">Oletus: Kelaus Finland Oy / 2806633-5 / Heiniläntie 37, 08500 Lohja.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_company_email">Sähköposti</label></th>
                        <td>
                            <input type="email" id="romant_kutsu_company_email"
                                   name="<?php echo esc_attr(self::OPTION_COMPANY_EMAIL); ?>"
                                   value="<?php echo esc_attr(self::company_email()); ?>" class="regular-text" />
                            <p class="description">Oletus: henry@kelaus.fi</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="romant_kutsu_vat_rate">ALV %</label></th>
                        <td>
                            <input type="text" id="romant_kutsu_vat_rate"
                                   name="<?php echo esc_attr(self::OPTION_VAT_RATE); ?>"
                                   value="<?php echo esc_attr(self::vat_rate()); ?>" class="small-text" />
                            <p class="description">Oletus 25.5 → näytetään <code>25,5 %</code>.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Tallenna asetukset'); ?>
            </form>

            <hr />
            <h2>Peel-testikutsu (Portti 1 QA)</h2>
            <p class="description">
                Luo valmis, maksettu kutsu jossa jokaisessa osiossa on <strong>3 tasoa</strong>.
                Avaa vastaanottajalinkki → <em>Avaa kutsu</em> → <em>Tapaaminen</em> + L1
                (osion otsikko kortissa) + <em>Haluatko kuulla lisää?</em>
                → modal (<em>Kerro lisää</em> / <em>Pidän jännityksen</em>) → L2 jää L1:n alle.
            </p>
            <?php
            $peel_qa = isset($_GET['peel_qa']);
            $qa_token = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';
            $qa_manage = isset($_GET['manage_key']) ? sanitize_text_field(wp_unslash((string) $_GET['manage_key'])) : '';
            if ($peel_qa && $qa_token !== '') :
                $recv = Romant_Kutsu_Rewrite::recipient_url($qa_token);
                $mgmt = $qa_manage !== '' ? Romant_Kutsu_Rewrite::manage_url($qa_manage) : '';
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Peel-testikutsu luotu.</strong></p>
                    <p>Vastaanottaja: <a href="<?php echo esc_url($recv); ?>" target="_blank" rel="noopener"><?php echo esc_html($recv); ?></a></p>
                    <?php if ($mgmt !== '') : ?>
                        <p>Hallinta: <a href="<?php echo esc_url($mgmt); ?>" target="_blank" rel="noopener"><?php echo esc_html($mgmt); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="romant_create_peel_qa" />
                <?php wp_nonce_field('romant_create_peel_qa', 'romant_peel_qa_nonce'); ?>
                <?php submit_button('Luo peel-testikutsu', 'secondary', 'submit', false); ?>
            </form>

            <p class="description">
                Testiavaimet: Visma Pay Merchant Portal → Sub-merchants, tai pyydä testi-tili
                <a href="https://www.vismapay.com/docs/web_payments/?page=testing" target="_blank" rel="noopener">dokumentaatiosta</a>.
            </p>
        </div>
        <?php
    }
}
