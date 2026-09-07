<?php
/**
 * Full page: /kutsu/uusi/
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}
$page_title = 'Luo kutsu · romanttinen';
$body_class = 'romant-craft-page';
include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
Romant_Kutsu_Templates::render_partial('create-form');
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
