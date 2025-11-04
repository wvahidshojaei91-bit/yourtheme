<?php
/**
 * Front page template
 * Hero + two secondary sections with tabs and AJAX sorting
 */

get_header();

// Featured post (prefer tagged 'witro')
$featured_post = null;
$featured_q = new WP_Query([
  'posts_per_page' => 1,
  'post_status'    => 'publish',
  'ignore_sticky_posts' => true,
  'tag'            => 'witro',
]);
if ($featured_q->have_posts()) {
  $featured_post = $featured_q->posts[0];
}
wp_reset_postdata();

if (!$featured_post) {
  $fallback_q = new WP_Query([
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'ignore_sticky_posts' => true,
  ]);
  if ($fallback_q->have_posts()) {
    $featured_post = $fallback_q->posts[0];
  }
  wp_reset_postdata();
}

$exclude_ids = [];
if ($featured_post) { $exclude_ids[] = $featured_post->ID; }

// Next 3 posts for the side tiles
$others_q = new WP_Query([
  'posts_per_page' => 3,
  'post_status'    => 'publish',
  'ignore_sticky_posts' => true,
  'post__not_in'   => $exclude_ids,
]);
?>

<main id="primary" class="site-main">
  <section class="home-hero-grid" aria-label="شبکه کاور صفحه اول">
    <div class="hero-grid">
      <?php if ($featured_post): ?>
        <?php
          $feat_img = get_the_post_thumbnail_url($featured_post->ID, 'large');
          $feat_title = get_the_title($featured_post->ID);
          $feat_link = get_permalink($featured_post->ID);
          $feat_date = get_the_date('', $featured_post->ID);
          $feat_cats = get_the_category($featured_post->ID);
          $feat_cat  = $feat_cats ? $feat_cats[0] : null;
        ?>
        <a class="tile tile--large" href="<?php echo esc_url($feat_link); ?>" style="<?php echo $feat_img ? 'background-image:url(' . esc_url($feat_img) . ');' : ''; ?>">
          <span class="tile__overlay"></span>
          <div class="tile__content">
            <?php if ($feat_cat): ?>
              <span class="tile__cat"><?php echo esc_html($feat_cat->name); ?></span>
            <?php endif; ?>
            <h2 class="tile__title"><?php echo esc_html($feat_title); ?></h2>
            <time class="tile__meta" datetime="<?php echo esc_attr(get_the_date('c', $featured_post->ID)); ?>"><?php echo esc_html($feat_date); ?></time>
          </div>
        </a>
      <?php endif; ?>

      <div class="side-grid">
        <?php if ($others_q->have_posts()): $i=0; while ($others_q->have_posts()): $others_q->the_post(); $i++; ?>
          <?php
            $cls = $i === 1 ? 'tile--wide' : 'tile--small';
            $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
            $cats = get_the_category();
            $cat  = $cats ? $cats[0] : null;
          ?>
          <a class="tile <?php echo esc_attr($cls); ?>" href="<?php the_permalink(); ?>" style="<?php echo $img ? 'background-image:url(' . esc_url($img) . ');' : ''; ?>">
            <span class="tile__overlay"></span>
            <div class="tile__content">
              <?php if ($cat): ?><span class="tile__cat"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
              <h3 class="tile__title tile__title--sm"><?php the_title(); ?></h3>
            </div>
          </a>
        <?php endwhile; wp_reset_postdata(); endif; ?>
      </div>
    </div>
  </section>

  <?php
    // Mobile/Tablet horizontal scroll of latest 6 posts (excluding featured)
    $scroll_q = new WP_Query([
      'posts_per_page' => 6,
      'post_status'    => 'publish',
      'ignore_sticky_posts' => true,
      'post__not_in'   => $exclude_ids,
    ]);
  ?>
  <?php if ($scroll_q->have_posts()): ?>
  <section class="home-hero-grid__scroll" aria-label="پست‌های اخیر">
    <div class="hero-scroll">
      <?php while ($scroll_q->have_posts()): $scroll_q->the_post();
        $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
        $cats = get_the_category();
        $cat  = $cats ? $cats[0] : null;
      ?>
        <a class="hs-card" href="<?php the_permalink(); ?>" style="<?php echo $img ? 'background-image:url(' . esc_url($img) . ');' : ''; ?>">
          <span class="hs-card__overlay"></span>
          <div class="hs-card__content">
            <?php if ($cat): ?><span class="hs-card__cat"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
            <h3 class="hs-card__title"><?php the_title(); ?></h3>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </section>
  <?php endif; ?>

  <?php // Secondary section 1: آموزش ویترو ?>
  <?php $educ_term = get_term_by('name','آموزش ویترو','category'); if(!$educ_term){ $educ_term = get_term_by('slug','educational','category'); } $educ_id = $educ_term ? intval($educ_term->term_id) : 0; ?>
  <section class="home-secondary" data-cat="educational" data-cat-id="<?php echo $educ_id; ?>" aria-label="آموزش ویترو">
    <div class="hs-wrap">
      <div class="hs-main">
        <div class="hs-tabs" role="tablist">
          <span class="hs-tab hs-tab--primary" aria-selected="true" tabindex="0">آموزش ویترو</span>
          <button type="button" class="hs-tab is-active" data-sort="latest" role="tab" aria-selected="true">تازه‌ترین</button>
          <button type="button" class="hs-tab" data-sort="oldest" role="tab" aria-selected="false">قدیمی‌ترین</button>
          <button type="button" class="hs-tab" data-sort="popular" role="tab" aria-selected="false">محبوب‌ترین</button>
        </div>
        <?php echo yourtheme_render_hs_posts($educ_id ?: 'educational','latest'); ?>
      </div>

      <aside class="hs-aside">
        <a class="hs-ad-portrait" href="#" style="--ad-image: url('/path/to/your-vertical-ad.jpg');" aria-label="تبلیغات"></a>

        <form class="hs-newsletter" method="post" action="#" novalidate>
          <h4 class="hs-newsletter__title">عضویت در خبرنامه</h4>
          <label class="hs-field">
            <span>نام</span>
            <input type="text" name="hs_name" required>
          </label>
          <label class="hs-field">
            <span>ایمیل</span>
            <input type="email" name="hs_email" required>
          </label>
          <div class="g-recaptcha" data-sitekey="<?php echo esc_attr( defined('YOURTHEME_RECAPTCHA_SITE') ? YOURTHEME_RECAPTCHA_SITE : '' ); ?>"></div>
          <button type="submit" class="yt-cta yt-cta--block">ثبت</button>
        </form>
      </aside>
    </div>
  </section>

  <?php // Secondary section 2: ترفند فروش ?>
  <?php $sales_term = get_term_by('name','ترفند فروش','category'); if(!$sales_term){ $sales_term = get_term_by('slug','sales-tips','category'); } $sales_id = $sales_term ? intval($sales_term->term_id) : 0; ?>
  <section class="home-secondary" style="--hs-accent:#174170" data-cat="sales-tips" data-cat-id="<?php echo $sales_id; ?>" aria-label="ترفند فروش">
    <div class="hs-wrap">
      <div class="hs-main">
        <div class="hs-tabs" role="tablist">
          <span class="hs-tab hs-tab--primary" aria-selected="true" tabindex="0">ترفند فروش</span>
          <button type="button" class="hs-tab is-active" data-sort="latest" role="tab" aria-selected="true">تازه‌ترین</button>
          <button type="button" class="hs-tab" data-sort="oldest" role="tab" aria-selected="false">قدیمی‌ترین</button>
          <button type="button" class="hs-tab" data-sort="popular" role="tab" aria-selected="false">محبوب‌ترین</button>
        </div>
        <?php echo yourtheme_render_hs_posts($sales_id ?: 'sales-tips','latest'); ?>
      </div>
      <aside class="hs-aside"></aside>
    </div>
  </section>

  <section class="home-ad-banner" aria-label="بنر تبلیغاتی افقی">
    <div class="hab-wrap">
      <?php $banner_link = get_theme_mod('yourtheme_banner_link', '#'); ?>
      <a class="hab-banner" href="<?php echo esc_url($banner_link); ?>" aria-label="تبلیغ"></a>
    </div>
  </section>

  <?php // Secondary section 3: تجربه مشتری ?>
  <?php $cx_term = get_term_by('name','تجربه مشتری','category'); if(!$cx_term){ $cx_term = get_term_by('slug','customer-experience','category'); } $cx_id = $cx_term ? intval($cx_term->term_id) : 0; ?>
  <section class="home-secondary" data-cat="customer-experience" data-cat-id="<?php echo $cx_id; ?>" aria-label="تجربه مشتری">
    <div class="hs-wrap">
      <div class="hs-main">
        <div class="hs-tabs" role="tablist">
          <span class="hs-tab hs-tab--primary" aria-selected="true" tabindex="0">تجربه مشتری</span>
          <button type="button" class="hs-tab is-active" data-sort="latest" role="tab" aria-selected="true">تازه‌ترین</button>
          <button type="button" class="hs-tab" data-sort="oldest" role="tab" aria-selected="false">قدیمی‌ترین</button>
          <button type="button" class="hs-tab" data-sort="popular" role="tab" aria-selected="false">محبوب‌ترین</button>
        </div>
        <?php echo yourtheme_render_hs_posts($cx_id ?: 'customer-experience','latest'); ?>
      </div>
      <aside class="hs-aside"></aside>
    </div>
  </section>

  <?php // Secondary section 4: خبرها ?>
  <?php $news_term = get_term_by('name','خبرها','category'); if(!$news_term){ $news_term = get_term_by('slug','news','category'); } $news_id = $news_term ? intval($news_term->term_id) : 0; ?>
  <section class="home-secondary" data-cat="news" data-cat-id="<?php echo $news_id; ?>" aria-label="خبرها">
    <div class="hs-wrap">
      <div class="hs-main">
        <div class="hs-tabs" role="tablist">
          <span class="hs-tab hs-tab--primary" aria-selected="true" tabindex="0">خبرها</span>
          <button type="button" class="hs-tab is-active" data-sort="latest" role="tab" aria-selected="true">تازه‌ترین</button>
          <button type="button" class="hs-tab" data-sort="oldest" role="tab" aria-selected="false">قدیمی‌ترین</button>
          <button type="button" class="hs-tab" data-sort="popular" role="tab" aria-selected="false">محبوب‌ترین</button>
        </div>
        <?php echo yourtheme_render_hs_posts($news_id ?: 'news','latest'); ?>
      </div>
      <aside class="hs-aside"></aside>
    </div>
  </section>

  <?php // Full listing section: همه مقالات ?>
  <section class="home-listing" aria-label="همه مقالات">
    <div class="hl-wrap">
      <div class="hl-tabs" role="tablist">
        <button type="button" class="hl-tab is-active" data-sort="latest" role="tab" aria-selected="true">تازه‌ترین</button>
        <button type="button" class="hl-tab" data-sort="oldest" role="tab" aria-selected="false">قدیمی‌ترین</button>
        <button type="button" class="hl-tab" data-sort="popular" role="tab" aria-selected="false">محبوب‌ترین</button>
      </div>
      <?php echo yourtheme_render_listing('latest', get_query_var('paged')); ?>
    </div>
  </section>

</main>

<?php get_footer(); ?>
