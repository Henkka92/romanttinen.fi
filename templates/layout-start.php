<?php
/**
 * Shared HTML head for standalone pages.
 *
 * @var string $page_title
 * @var bool   $allow_index  When true, omit noindex (marketing homepage).
 * @var string $body_class   Extra body classes.
 */
if (!defined('ABSPATH')) {
    exit;
}
$page_title  = $page_title ?? 'Romanttinen Kutsu';
$allow_index = !empty($allow_index);
$body_class  = trim('romant-kutsu-body ' . ($body_class ?? ''));
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
