<?php
/**
 * Plugin Name: Romanttinen Kutsu
 * Plugin URI:  https://romanttinen.fi
 * Description: Romanttinen.fi V1.1 – kutsu-peli: osiot, progressiivinen paljastus, Visma Pay (tai stub), jaettava linkki.
 * Version:     1.3.7
 * Author:      Romanttinen
 * Author URI:  https://romanttinen.fi
 * Text Domain: romanttinen-kutsu
 * Requires PHP: 8.0
 * License:     GPL-2.0-or-later
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('ROMANT_KUTSU_VERSION', '1.3.7');
define('ROMANT_KUTSU_FILE', __FILE__);
define('ROMANT_KUTSU_PATH', plugin_dir_path(__FILE__));
define('ROMANT_KUTSU_URL', plugin_dir_url(__FILE__));
define('ROMANT_KUTSU_SLUG', 'romanttinen-kutsu');

// Optional env override for stub payments (demo without Visma keys).
if (!defined('ROMANTTINEN_STUB_PAYMENTS')) {
    define('ROMANTTINEN_STUB_PAYMENTS', false);
}

require_once ROMANT_KUTSU_PATH . 'includes/class-cpt.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-settings.php';
require_once ROMANT_KUTSU_PATH . 'includes/payment/interface-gateway.php';
require_once ROMANT_KUTSU_PATH . 'includes/payment/class-stub-gateway.php';
require_once ROMANT_KUTSU_PATH . 'includes/payment/class-visma-client.php';
require_once ROMANT_KUTSU_PATH . 'includes/payment/class-visma-gateway.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-payment.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-rewrite.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-forms.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-events.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-templates.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-home.php';
require_once ROMANT_KUTSU_PATH . 'includes/class-plugin.php';

/**
 * Bootstrap.
 */
function romant_kutsu(): Romant_Kutsu_Plugin {
    return Romant_Kutsu_Plugin::instance();
}

romant_kutsu();

register_activation_hook(__FILE__, ['Romant_Kutsu_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Romant_Kutsu_Plugin', 'deactivate']);
