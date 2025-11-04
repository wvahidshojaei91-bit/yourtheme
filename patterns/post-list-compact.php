<?php
/**
 * Title: Post List (Compact)
 * Slug: yourtheme/post-list-compact
 * Categories: yourtheme, query
 */
?>
<!-- wp:query {"queryId":5,"query":{"perPage":6,"postType":"post"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template -->
      <!-- wp:group {"layout":{"type":"constrained"}} -->
      <article>
        <!-- wp:post-title {"isLink":true} /-->
        <!-- wp:post-excerpt /-->
      </article>
      <!-- /wp:group -->
    <!-- /wp:post-template -->
  </div>
<!-- /wp:query -->
