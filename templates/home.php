<?php
/**
 * Marketing homepage (standalone) — Portti 3 / SEO P0.
 *
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

$page_title        = 'Treffikutsu joka paljastuu vaiheittain | Romanttinen';
$page_description  = 'Luo treffikutsu, jossa vihjeet aukeavat yksi kerrallaan. Ilmaiseksi — maksat 4,90 € sis. ALV vasta kun jaat linkin.';
$allow_index       = true;
$body_class        = 'romant-home-body';
$hide_legal_footer = true;
$create_url        = Romant_Kutsu_Rewrite::create_url();

// OG: title = H1-short · description = meta · image = teaser fabric
$og_title       = 'Kutsu mielitiettysi treffeille';
$og_description = $page_description;
$og_image       = ROMANT_KUTSU_URL . 'assets/img/hero-fabric.jpg';
$og_url         = home_url('/');

include ROMANT_KUTSU_PATH . 'templates/layout-start.php';
include ROMANT_KUTSU_PATH . 'templates/partials-home.php';
include ROMANT_KUTSU_PATH . 'templates/layout-end.php';
