<?php
/**
 * Category archive template matching the home hero/listing layout.
 *
 * @package YourTheme
 */

get_header();

$term = get_queried_object();
if (! $term || ! isset($term->term_id)) {
  $term = get_term_by('slug', get_query_var('category_name'), 'category');
}

$cat_id   = $term ? (int) $term->term_id : 0;
$cat_name = $term ? $term->name : '';
$cat_desc = $term ? term_description($term) : '';

$hero_query = new WP_Query([
  'posts_per_page'      => 4,
  'post_status'         => 'publish',
  'ignore_sticky_posts' => true,
  'cat'                 => $cat_id,
]);

$hero_posts = [];
if ($hero_query->have_posts()) {
  while ($hero_query->have_posts()) {
    $hero_query->the_post();
    $hero_posts[] = get_post();
  }
  wp_reset_postdata();
}

$featured_post = $hero_posts[0] ?? null;
$side_posts    = array_slice($hero_posts, 1);
$paged         = max(1, get_query_var('paged'));
?>

<main id="primary" class="site-main category-layout">
  <section class="home-hero-grid category-hero" aria-label="<?php echo esc_attr(sprintf(__('آخرین نوشته‌های %s', 'yourtheme'), $cat_name)); ?>">

    <?php if ($featured_post) : ?>
      <div class="hero-grid">
        <?php
          $feat_img  = get_the_post_thumbnail_url($featured_post->ID, 'large');
          $feat_link = get_permalink($featured_post->ID);
          $feat_date = get_the_date('', $featured_post->ID);
          $feat_title = get_the_title($featured_post->ID);
          $feat_cats = get_the_category($featured_post->ID);
          $feat_cat  = $feat_cats ? $feat_cats[0] : null;
        ?>
        <a class="tile tile--large" href="<?php echo esc_url($feat_link); ?>" style="<?php echo $feat_img ? 'background-image:url(' . esc_url($feat_img) . ');' : ''; ?>">
          <span class="tile__overlay"></span>
          <div class="tile__content">
            <?php if ($feat_cat) : ?>
              <span class="tile__cat"><?php echo esc_html($feat_cat->name); ?></span>
            <?php endif; ?>
            <h2 class="tile__title"><?php echo esc_html($feat_title); ?></h2>
            <time class="tile__meta" datetime="<?php echo esc_attr(get_the_date('c', $featured_post->ID)); ?>"><?php echo esc_html($feat_date); ?></time>
          </div>
        </a>

        <div class="side-grid">
          <?php if (! empty($side_posts)) : ?>
            <?php foreach ($side_posts as $index => $post_obj) :
              $img = get_the_post_thumbnail_url($post_obj->ID, 'large');
              $cats = get_the_category($post_obj->ID);
              $cat  = $cats ? $cats[0] : null;
              $cls  = ($index === 0) ? 'tile--wide' : 'tile--small';
            ?>
              <a class="tile <?php echo esc_attr($cls); ?>" href="<?php echo esc_url(get_permalink($post_obj->ID)); ?>" style="<?php echo $img ? 'background-image:url(' . esc_url($img) . ');' : ''; ?>">
                <span class="tile__overlay"></span>
                <div class="tile__content">
                  <?php if ($cat) : ?><span class="tile__cat"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
                  <h3 class="tile__title tile__title--sm"><?php echo esc_html(get_the_title($post_obj->ID)); ?></h3>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else : ?>
            <p class="hero-grid__empty"><?php esc_html_e('هنوز نوشته‌ای برای این دسته پیدا نشد.', 'yourtheme'); ?></p>
          <?php endif; ?>
        </div>
      </div>
    <?php else : ?>
      <p class="hero-grid__empty"><?php esc_html_e('هنوز نوشته‌ای برای این دسته پیدا نشد.', 'yourtheme'); ?></p>
    <?php endif; ?>
  </section>

  <section class="home-listing" data-cat-id="<?php echo esc_attr($cat_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('تمام نوشته‌های %s', 'yourtheme'), $cat_name)); ?>">
    <div class="hl-wrap">
      <div class="hl-tabs" role="tablist">
        <button type="button" class="hl-tab is-active" data-sort="latest" role="tab" aria-selected="true"><?php esc_html_e('جدیدترین', 'yourtheme'); ?></button>
        <button type="button" class="hl-tab" data-sort="oldest" role="tab" aria-selected="false"><?php esc_html_e('قدیمی‌ترین', 'yourtheme'); ?></button>
        <button type="button" class="hl-tab" data-sort="popular" role="tab" aria-selected="false"><?php esc_html_e('پربازدید', 'yourtheme'); ?></button>
      </div>
      <?php echo yourtheme_render_listing('latest', $paged, $cat_id); ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
