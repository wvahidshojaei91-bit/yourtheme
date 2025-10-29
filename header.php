<?php
/**
 * Theme header wrapper for hybrid/block theme.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Render block header template part if available.
if (function_exists('do_blocks')) {
    echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
}
?>

