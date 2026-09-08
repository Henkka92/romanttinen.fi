<?php
/**
 * Shared HTML head for standalone pages.
 *
 * @var string $page_title
 * @var bool   $allow_index  When true, omit noindex (marketing homepage).
 * @var string $body_class   Extra body classes.
 * @var string $og_title
 * @var string $og_description
 * @var string $og_image
 * @var string $og_url
 */
if (!defined('ABSPATH')) {
    exit;
}
$page_title      = $page_title ?? 'Romanttinen Kutsu';
$allow_index     = !empty($allow_index);
$body_class      = trim('romant-kutsu-body ' . ($body_class ?? ''));
$og_title        = isset($og_title) ? (string) $og_title : '';
$og_description  = isset($og_description) ? (string) $og_description : '';
$og_image        = isset($og_image) ? (string) $og_image : '';
$og_url          = isset($og_url) ? (string) $og_url : '';
?><!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php if ($allow_index) : ?>
    <meta name="description" content="Kutsu mielitiettysi treffeille — unohtumattomalla tavalla. Luonnos ilmaiseksi. 4,90 € vasta kun jaat linkin." />
    <?php else : ?>
    <meta name="robots" content="noindex,nofollow" />
    <?php endif; ?>
    <title><?php echo esc_html($page_title); ?></title>
    <?php if ($og_title !== '') : ?>
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="romanttinen.fi" />
    <meta property="og:locale" content="fi_FI" />
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>" />
    <?php if ($og_description !== '') : ?>
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>" />
    <meta name="description" content="<?php echo esc_attr($og_description); ?>" />
    <meta name="twitter:description" content="<?php echo esc_attr($og_description); ?>" />
    <?php endif; ?>
    <?php if ($og_url !== '') : ?>
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>" />
    <?php endif; ?>
    <?php if ($og_image !== '') : ?>
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>" />
    <?php endif; ?>
    <meta name="twitter:title" content="<?php echo esc_attr($og_title); ?>" />
    <?php endif; ?>
    <?php
    if (function_exists('wp_site_icon')) {
        wp_site_icon();
    }
    if (!get_option('site_icon')) :
        ?>
    <link rel="icon" href="<?php echo esc_url(ROMANT_KUTSU_URL . 'assets/img/favicon.png'); ?>" sizes="any" />
        <?php
    endif;
    do_action('romant_kutsu_head');
    ?>
</head>
<body class="<?php echo esc_attr($body_class); ?>">
