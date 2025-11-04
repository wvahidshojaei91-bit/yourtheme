<?php
/**
 * Theme footer wrapper for hybrid/block theme.
 */
?>

<?php
// Render block footer template part if available.
if (function_exists('do_blocks')) {
    echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
}
?>

<?php wp_footer(); ?>
</body>
</html>

