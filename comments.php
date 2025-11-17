<?php
/**
 * Custom comments template.
 *
 * @package YourTheme
 */

if (post_password_required()) {
  return;
}
?>

<div class="comment-thread">
  <?php if (have_comments()) : ?>
    <ol class="comment-list">
      <?php
        wp_list_comments([
          'style'       => 'ol',
          'short_ping'  => false,
          'avatar_size' => 64,
          'per_page'    => 10,
          'callback'    => 'yourtheme_render_comment',
          'max_depth'   => 1,
        ]);
      ?>
    </ol>

    <?php
      $comment_links = paginate_comments_links([
        'type'      => 'array',
        'prev_text' => '‹',
        'next_text' => '›',
      ]);
      if (! empty($comment_links) && is_array($comment_links)) :
    ?>
      <nav class="comment-pagination" aria-label="<?php esc_attr_e('صفحه‌بندی دیدگاه‌ها', 'yourtheme'); ?>">
        <?php foreach ($comment_links as $c_link) : ?>
          <span class="comment-page"><?php echo wp_kses_post($c_link); ?></span>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
  <?php else : ?>
    <p class="comment-empty"><?php esc_html_e('هنوز دیدگاهی ثبت نشده است.', 'yourtheme'); ?></p>
  <?php endif; ?>

  <?php
    if (comments_open()) :
      $commenter = wp_get_current_commenter();
      $req       = get_option('require_name_email');
      $aria_req  = $req ? " aria-required='true' required" : '';
      $html_req  = $req ? ' required' : '';
      echo '<div class="single-comments__form-title">';
      echo '<h3>' . esc_html__('نظرات', 'yourtheme') . '</h3>';
      echo '<span class="single-comments__divider" aria-hidden="true"></span>';
      echo '</div>';
      comment_form([
        'title_reply'        => '',
        'title_reply_before' => '',
        'title_reply_after'  => '',
        'comment_notes_before' => '<p class="comment-notes">' . esc_html__('نظر شما', 'yourtheme') . '</p>',
        'comment_notes_after'  => '',
        'label_submit'         => __('ارسال دیدگاه', 'yourtheme'),
        'fields'               => [
          'author' =>
            '<p class="comment-form-author">' .
            '<input id="author" name="author" type="text" placeholder="' . esc_attr__('نام', 'yourtheme') . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . $html_req  . ' />' .
            '</p>',
          'email'  =>
            '<p class="comment-form-email">' .
            '<input id="email" name="email" type="email" placeholder="' . esc_attr__('ایمیل', 'yourtheme') . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" ' . $aria_req . $html_req  . ' />' .
            '</p>',
          'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /> <label for="wp-comment-cookies-consent">' . esc_html__('ذخیره نام و ایمیل  در مرورگر برای زمانی که دوباره دیدگاهی می‌نویسم.', 'yourtheme') . '</label></p>',
        ],
        'comment_field' =>
          '<p class="comment-form-comment">' .
          '<textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" placeholder="' . esc_attr__('دیدگاه شما', 'yourtheme') . '" required="required" aria-required="true"></textarea>' .
          '</p>',
          '</p>',
      ]);
    endif;
  ?>
</div>
