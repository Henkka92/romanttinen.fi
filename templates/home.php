<?php
/**
 * Marketing homepage (standalone) — Portti 3 / 1.4.1.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title  = 'romanttinen — treffikutsu hetkeksi';
$allow_index = true;
$body_class  = 'romant-home-body';
$hide_legal_footer = true;
$create_url  = Romant_Kutsu_Rewrite::create_url();

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
include ROMANT_KUTSU_PATH . 'templates/partials-home.php';
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
