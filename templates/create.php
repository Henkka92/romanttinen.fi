<?php
/**
 * Full page: /kutsu/uusi/
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title       = 'Rakenna treffikutsu | Romanttinen';
$page_description = 'Valitse tunnelma, kirjoita vihjeet ja esikatsele. Saaja avaa kutsun omaan tahtiinsa.';
// Craft stays noindex (soft-launch); tab/share still get title + description via OG.
$og_title         = 'Rakenna treffikutsu | Romanttinen';
$og_description   = $page_description;
$og_url           = Romant_Kutsu_Rewrite::create_url();
$body_class       = 'romant-craft-page';

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
Romant_Kutsu_Templates::render_partial('create-form');
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
