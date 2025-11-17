<?php
/**
 * Custom single post template inspired by the provided mockup.
 *
 * @package YourTheme
 */

get_header();

if (! have_posts()) {
  echo '<main class="single-screen"><div class="single-shell__empty">';
  esc_html_e('هیچ نوشته‌ای یافت نشد.', 'yourtheme');
  echo '</div></main>';
  get_footer();
  return;
}

while (have_posts()) :
  the_post();
  $post_id     = get_the_ID();
  $categories  = get_the_category();
  $primary_cat = $categories ? $categories[0] : null;
  $reading_txt = yourtheme_get_reading_time_text($post_id);
  $permalink   = get_permalink();
  $encoded_url = rawurlencode($permalink);
  $encoded_title = rawurlencode(get_the_title());

  $sidebar_query = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'post__not_in'   => [$post_id],
    'ignore_sticky_posts' => true,
  ]);

  $related_args = [
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'post__not_in'   => [$post_id],
    'ignore_sticky_posts' => true,
  ];
  if ($primary_cat) {
    $related_args['category__in'] = [$primary_cat->term_id];
  }
  $related_query = new WP_Query($related_args);

  $source_link = get_post_meta($post_id, 'yourtheme_source_link', true);
  $source_label = get_post_meta($post_id, 'yourtheme_source_label', true);
  $toc_headings = yourtheme_get_single_headings($post_id);
  ?>

  <main class="single-screen" dir="rtl">
    <div class="single-plane">
      <div class="single-shell">
        <article <?php post_class('single-article'); ?>>
        <nav class="single-breadcrumb" aria-label="<?php esc_attr_e('مسیر ناوبری', 'yourtheme'); ?>">
          <ol>
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('خانه', 'yourtheme'); ?></a></li>
            <?php if ($primary_cat) : ?>
              <li><a href="<?php echo esc_url(get_category_link($primary_cat)); ?>"><?php echo esc_html($primary_cat->name); ?></a></li>
            <?php endif; ?>
            <li aria-current="page"><?php the_title(); ?></li>
          </ol>
        </nav>

          <?php if (has_post_thumbnail()) : ?>
            <figure class="single-hero__figure">
              <?php the_post_thumbnail('large'); ?>
            </figure>
          <?php endif; ?>

          <header class="single-hero">
            <?php if ($primary_cat) : ?>
              <a class="single-hero__badge" href="<?php echo esc_url(get_category_link($primary_cat)); ?>">
                <?php echo esc_html($primary_cat->name); ?>
              </a>
            <?php endif; ?>
            <h1 class="single-hero__title"><?php the_title(); ?></h1>
            <?php if (has_excerpt()) : ?>
              <p class="single-hero__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
            <?php endif; ?>
            <div class="single-hero__meta">
              <div class="single-hero__meta-group">
                <span class="single-hero__meta-item">
                  <span class="single-hero__meta-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-3.31 0-6 1.79-6 4v1h12v-1c0-2.21-2.69-4-6-4Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span><?php echo esc_html(get_the_author()); ?></span>
                </span>
                <span class="single-hero__meta-item">
                  <span class="single-hero__meta-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M7 4v3M17 4v3M4 9h16M6 7h12a2 2 0 0 1 2 2v9H4V9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M8 13h2m4 0h2m-8 4h2m4 0h2" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    </svg>
                  </span>
                  <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></time>
                </span>
              </div>
              <div class="single-hero__meta-group single-hero__meta--secondary">
                <span class="single-hero__meta-item">
                  <span class="single-hero__meta-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.5" fill="none"/>
                      <path d="M12 9v4l2 2" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span><?php echo esc_html($reading_txt); ?></span>
                </span>
                <?php if (comments_open() || get_comments_number()) : ?>
                  <span class="single-hero__meta-item">
                    <span class="single-hero__meta-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-6l-4 4v-4H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </span>
                    <span><?php printf(esc_html__('%d نظر', 'yourtheme'), get_comments_number()); ?></span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </header>

          <div class="single-content entry-content">
            <?php the_content(); ?>
          </div>

          <?php if ($source_link) : ?>
            <div class="single-source">
              <div class="single-source__icon" aria-hidden="true"></div>
              <div>
                <?php if ($source_label) : ?>
                  <p class="single-source__label"><?php echo esc_html($source_label); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url($source_link); ?>" target="_blank" rel="noopener">
                  <?php echo esc_html($source_link); ?>
                </a>
              </div>
            </div>
          <?php endif; ?>

          <div class="single-tags-share">
            <div class="single-tags">
              <?php the_tags('<span>' . esc_html__('برچسب‌ها:', 'yourtheme') . '</span>', '', ''); ?>
            </div>
            <div class="single-share" aria-label="<?php esc_attr_e('اشتراک‌گذاری مطلب', 'yourtheme'); ?>">
              <span><?php esc_html_e('اشتراک‌گذاری:', 'yourtheme'); ?></span>
              <a class="single-share__btn" href="https://t.me/share/url?url=<?php echo $encoded_url; ?>&text=<?php echo $encoded_title; ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('اشتراک در تلگرام', 'yourtheme'); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 3L3 11l6 2 2 6 3-4 4 3 3-15z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/></svg>
              </a>
              <a class="single-share__btn" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encoded_url; ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('اشتراک در لینکدین', 'yourtheme'); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9v9M6 6v.01M11 18v-6a3 3 0 0 1 6 0v6" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
              </a>
              <button type="button" class="single-share__btn" data-share-copy data-share-url="<?php echo esc_attr($permalink); ?>" aria-label="<?php esc_attr_e('کپی لینک', 'yourtheme'); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 7h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9m-3-4H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
              </button>
            </div>
          </div>

          <?php if ($related_query->have_posts()) : ?>
            <section class="single-related">
              <div class="single-related__head">

                <?php if ($primary_cat) : ?>
                  <a href="<?php echo esc_url(get_category_link($primary_cat)); ?>"><?php esc_html_e('مشاهده دسته', 'yourtheme'); ?></a>
                <?php endif; ?>
              </div>
              <div class="single-related__grid">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                  <?php $rel_thumb = get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>
                  <article class="single-related__item">
                    <a class="single-related__thumb" href="<?php the_permalink(); ?>" style="<?php echo $rel_thumb ? 'background-image:url(' . esc_url($rel_thumb) . ');' : ''; ?>"></a>
                    <div class="single-related__body">
                      <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                      <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    </div>
                  </article>


                <?php endwhile; ?>
              </div>
            </section>
            <?php wp_reset_postdata(); ?>
          <?php endif; ?>

          <section class="single-comments" id="comments">

            <?php comments_template(); ?>
          </section>
        </article>

        <aside class="single-sidebar">
          <section class="single-card single-card--toc">

            <div class="single-toc__head">

              <span class="single-toc__icon" aria-hidden="true">

                <svg viewBox="0 0 20 20" aria-hidden="true">

                  <path d="M3 5h14M7 10h10M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>

                </svg>

              </span>
              <span class="single-toc__title"><?php esc_html_e('سرفصل‌ها', 'yourtheme'); ?></span>


            </div>

            <span class="single-toc__divider" aria-hidden="true"></span>

            <?php if (! empty($toc_headings)) : ?>

              <ul class="single-toc">

                <?php foreach ($toc_headings as $heading) : ?>

                  <li>

                    <a href="#<?php echo esc_attr($heading['id']); ?>" data-single-toc-link>

                      <?php echo esc_html($heading['text']); ?>

                    </a>

                  </li>

                <?php endforeach; ?>

              </ul>

            <?php else : ?>

              <p class="single-toc__empty"><?php esc_html_e('???? ??? ????? ?????? ??? ???? ???.', 'yourtheme'); ?></p>

            <?php endif; ?>

          </section>


          <?php if ($sidebar_query->have_posts()) : ?>
          <section class="single-card single-card--mini-feed">
            <div class="single-mini-feed">
              <?php while ($sidebar_query->have_posts()) : $sidebar_query->the_post(); ?>
                <?php
                  $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
                  $mini_reading = yourtheme_get_reading_time_text(get_the_ID());
                  $mini_date    = get_the_date(get_option('date_format'));
                  $mini_excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
                ?>
                <article class="single-mini-feed__item">
                  <a class="single-mini-feed__thumb" href="<?php the_permalink(); ?>" style="<?php echo $thumb ? 'background-image:url(' . esc_url($thumb) . ');' : ''; ?>">
                    <div class="single-mini-feed__meta-bar">
                      <span class="single-mini-feed__meta"><?php echo esc_html($mini_reading); ?></span>
                      <span class="single-mini-feed__meta"><?php echo esc_html($mini_date); ?></span>
                    </div>
                  </a>
                  <div class="single-mini-feed__body">
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php if ($mini_excerpt) : ?>
                      <p class="single-mini-feed__excerpt"><?php echo esc_html($mini_excerpt); ?></p>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endwhile; ?>
            </div>
          </section>
          <?php wp_reset_postdata(); endif; ?>

          <section class="single-card single-card--cta">
            <div>
              <p><?php esc_html_e('در خبرنامه ویترو عضو شوید تا جدیدترین مقالات و پیشنهادها را دریافت کنید.', 'yourtheme'); ?></p>
              <a class="single-chip single-chip--dark" href="https://witro.shop/" target="_blank" rel="noopener">
                <?php esc_html_e('عضویت در خبرنامه', 'yourtheme'); ?>
              </a>
            </div>
          </section>
        </aside>
      </div>
    </div>
  </main>

  <?php
endwhile;

get_footer();
