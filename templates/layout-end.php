<?php
if (!defined('ABSPATH')) {
    exit;
}
$hide_legal_footer = !empty($hide_legal_footer);
if (!$hide_legal_footer) {
    echo Romant_Kutsu_Templates::legal_footer_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
<?php do_action('romant_kutsu_footer'); ?>
</body>
</html>
