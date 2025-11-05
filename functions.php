<?php
/**
 * Theme setup and assets.
 */

if ( ! defined( 'YOURTHEME_VER' ) ) {
  define( 'YOURTHEME_VER', '0.1.0' );
}

// reCAPTCHA keys (site, secret) provided by user
if ( ! defined('YOURTHEME_RECAPTCHA_SITE') ) {
  define('YOURTHEME_RECAPTCHA_SITE', '6LcxSPMrAAAAABwzKgF592PiNO3UX8lTZlvAoC7n');
}
if ( ! defined('YOURTHEME_RECAPTCHA_SECRET') ) {
  define('YOURTHEME_RECAPTCHA_SECRET', '6LcxSPMrAAAAADPkmIvtijley9sv_SDp2UA5uumu');
}

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('responsive-embeds');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script','navigation-widgets']);
  add_theme_support('wp-block-styles');
  add_theme_support('editor-styles');
  add_theme_support('automatic-feed-links');
  add_theme_support('custom-logo', [
    'height'      => 64,
    'width'       => 64,
    'flex-height' => true,
    'flex-width'  => true,
    'unlink-homepage-logo' => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu','yourtheme'),
    'footer'  => __('Footer Menu','yourtheme'),
  ]);
});

/**
 * Apply sorting to front-page secondary queries via ?hs_sort=latest|popular|oldest
 */
add_action('pre_get_posts', function($q){
  if (is_admin() || !is_front_page()) return;
  if (! $q instanceof WP_Query) return;
  // Only adjust our secondary loops, not the main query
  if ($q->is_main_query()) return;

  $sort = isset($_GET['hs_sort']) ? sanitize_text_field($_GET['hs_sort']) : '';
  if (! $sort) return;

  $educational_slug = 'educational';
  $sales_slug = 'sales-tips';
  $sales_term = get_term_by('name','ترفند فروش','category');
  $sales_cat  = $sales_term && !is_wp_error($sales_term) ? (int)$sales_term->term_id : 0;

  $cat_name = $q->get('category_name');
  $cat_id   = (int)$q->get('cat');

  $is_target = false;
  if ($cat_name === $educational_slug || $cat_name === $sales_slug) $is_target = true;
  if ($sales_cat && $cat_id === $sales_cat) $is_target = true;

  if (! $is_target) return;

  switch ($sort){
    case 'popular':
      $q->set('orderby','comment_count');
      $q->set('order','DESC');
      break;
    case 'oldest':
      $q->set('orderby','date');
      $q->set('order','ASC');
      break;
    default: // latest
      $q->set('orderby','date');
      $q->set('order','DESC');
  }
});


add_action('wp_enqueue_scripts', function () {
  $theme_dir = get_stylesheet_directory();
  $css = $theme_dir . '/assets/css/main.css';
  $js  = $theme_dir . '/assets/js/main.js';

  $css_ver = file_exists($css) ? filemtime($css) : YOURTHEME_VER;
  $js_ver  = file_exists($js)  ? filemtime($js)  : YOURTHEME_VER;

  wp_enqueue_style('yourtheme', get_stylesheet_directory_uri().'/assets/css/main.css', [], $css_ver);
  wp_enqueue_script('yourtheme', get_stylesheet_directory_uri().'/assets/js/main.js', [], $js_ver, true);
  wp_localize_script('yourtheme', 'yourthemeAjax', [
    'url'   => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('yourtheme_ajax')
  ]);
  // Load Google reCAPTCHA on front page (for newsletter form). Replace site key in markup.
  if (is_front_page()) {
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
  }
});

/**
 * Customizer: Home secondary ad settings.
 */
add_action('customize_register', function($wp_customize){
  $wp_customize->add_section('yourtheme_home', [
    'title'    => __('Home Settings','yourtheme'),
    'priority' => 30,
  ]);

  // Ad image
  $wp_customize->add_setting('yourtheme_ad_image', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  if (class_exists('WP_Customize_Image_Control')){
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'yourtheme_ad_image', [
      'label'   => __('Ad Image (square)','yourtheme'),
      'section' => 'yourtheme_home',
      'settings'=> 'yourtheme_ad_image',
    ]));
  }

  // Ad link
  $wp_customize->add_setting('yourtheme_ad_link', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'esc_url_raw',
    'default' => '#',
  ]);
  $wp_customize->add_control('yourtheme_ad_link', [
    'label'   => __('Ad Link URL','yourtheme'),
    'section' => 'yourtheme_home',
    'type'    => 'url',
  ]);

  // Ad text
  $wp_customize->add_setting('yourtheme_ad_text', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'wp_kses_post',
    'default' => 'همین الان سایت اختصاصی خودت رو به سادگی ساخت اکانت اینستاگرام بساز',
  ]);
  $wp_customize->add_control('yourtheme_ad_text', [
    'label'   => __('Ad Text','yourtheme'),
    'section' => 'yourtheme_home',
    'type'    => 'textarea',
  ]);

  // Ad button text
  $wp_customize->add_setting('yourtheme_ad_button_text', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'sanitize_text_field',
    'default' => 'ساخت سایت',
  ]);
  $wp_customize->add_control('yourtheme_ad_button_text', [
    'label'   => __('Ad Button Text','yourtheme'),
    'section' => 'yourtheme_home',
    'type'    => 'text',
  ]);

  // Horizontal banner (12:1) under sections
  $wp_customize->add_setting('yourtheme_banner_image', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  if (class_exists('WP_Customize_Image_Control')){
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'yourtheme_banner_image', [
      'label'   => __('Horizontal Banner Image (12:1)','yourtheme'),
      'section' => 'yourtheme_home',
      'settings'=> 'yourtheme_banner_image',
    ]));
  }

  $wp_customize->add_setting('yourtheme_banner_link', [
    'type' => 'theme_mod',
    'sanitize_callback' => 'esc_url_raw',
    'default' => '#',
  ]);
  $wp_customize->add_control('yourtheme_banner_link', [
    'label'   => __('Horizontal Banner Link URL','yourtheme'),
    'section' => 'yourtheme_home',
    'type'    => 'url',
  ]);
});

// Output inline CSS for ad (image + texts) to integrate with pseudo-elements
add_action('wp_head', function(){
  if (!is_front_page()) return;
  $img = get_theme_mod('yourtheme_ad_image', '');
  $text = get_theme_mod('yourtheme_ad_text', '');
  $btn  = get_theme_mod('yourtheme_ad_button_text', '');
  $banner = get_theme_mod('yourtheme_banner_image', '');
  echo "\n<style id=\"yourtheme-home-ad\">\n";
  if ($img){
    echo ":root{--yt-ad-image: url('".esc_url($img)."');}\n";
  }
  if ($banner){
    echo ":root{--yt-ad-banner: url('".esc_url($banner)."');}\n";
  }
  if ($text){
    echo ".hs-ad-portrait::before{content:'".esc_js($text)."';}\n";
  }
  if ($btn){
    echo ".hs-ad-portrait::after{content:'".esc_js($btn)."';}\n";
  }
  echo "</style>\n";
});

/**
 * Render helper: Home section posts list (1 featured + 3 small)
 */
function yourtheme_render_hs_posts($cat, $sort='latest'){
  $orderby = 'date'; $order = 'DESC';
  if ($sort === 'popular') { $orderby = 'comment_count'; $order = 'DESC'; }
  if ($sort === 'oldest')  { $orderby = 'date';          $order = 'ASC';  }

  $args = [
    'posts_per_page' => 4,
    'post_status'    => 'publish',
    'ignore_sticky_posts' => true,
    'orderby' => $orderby,
    'order'   => $order,
  ];
  if (is_numeric($cat)){
    $args['cat'] = (int)$cat;
  } else {
    $args['category_name'] = sanitize_title($cat);
  }

  $q = new WP_Query($args);
  ob_start();
  echo '<div class="hs-posts">';
  $i = 0;
  if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); $i++;
    $img = get_the_post_thumbnail_url(get_the_ID(), $i === 1 ? 'large' : 'medium');
    $cls = $i === 1 ? 'hs-post hs-post--featured' : 'hs-post';
    echo '<article class="'.esc_attr($cls).'">';
      echo '<a class="hs-post__thumb" href="'.esc_url(get_permalink()).'" style="'.($img ? 'background-image:url('.esc_url($img).');' : '').'" aria-label="'.esc_attr(get_the_title()).'"></a>';
      echo '<div class="hs-post__body">';
        if ($i === 1){ $cats = get_the_category(); if ($cats){ echo '<span class="hs-post__cat">'.esc_html($cats[0]->name).'</span>'; } }
        $title_tag = $i === 1 ? 'h3' : 'h4';
        echo '<'.$title_tag.' class="hs-post__title"><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></'.$title_tag.'>';
        echo '<div class="hs-post__meta"><time datetime="'.esc_attr(get_the_date('c')).'">'.esc_html(get_the_date()).'</time></div>';
        if ($i === 1){ echo '<p class="hs-post__excerpt">'.esc_html( wp_trim_words( get_the_excerpt(), 26 ) ).'</p>'; }
      echo '</div>';
    echo '</article>';
  endwhile; wp_reset_postdata(); else:
    echo '<p class="hs-empty">'.esc_html__('No posts found.','yourtheme').'</p>';
  endif;
  echo '</div>';
  return ob_get_clean();
}

/**
 * Render helper: Full listing (no sidebar), 6 per page with pagination
 */
function yourtheme_render_listing($sort='latest', $paged=1){
  $orderby = 'date'; $order = 'DESC';
  if ($sort === 'popular') { $orderby = 'comment_count'; $order = 'DESC'; }
  if ($sort === 'oldest')  { $orderby = 'date';          $order = 'ASC';  }

  $q = new WP_Query([
    'posts_per_page' => 6,
    'paged'          => max(1, intval($paged)),
    'post_status'    => 'publish',
    'ignore_sticky_posts' => true,
    'orderby' => $orderby,
    'order'   => $order,
  ]);

  ob_start();
  echo '<div class="hl-posts">';
  if ($q->have_posts()): while ($q->have_posts()): $q->the_post();
    $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
    echo '<article class="hl-post">';
      echo '<a class="hl-thumb" href="'.esc_url(get_permalink()).'" style="'.($img ? 'background-image:url('.esc_url($img).');' : '').'"></a>';
      echo '<div class="hl-body">';
        echo '<h3 class="hl-title"><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3>';
        echo '<div class="hl-meta"><time datetime="'.esc_attr(get_the_date('c')).'">'.esc_html(get_the_date()).'</time></div>';
        echo '<p class="hl-excerpt">'.esc_html( wp_trim_words( get_the_excerpt(), 32 ) ).'</p>';
      echo '</div>';
    echo '</article>';
  endwhile; wp_reset_postdata(); else:
    echo '<p class="hl-empty">'.esc_html__('No posts found.','yourtheme').'</p>';
  endif;
  echo '</div>';

  // Pagination
  $links = paginate_links([
    'total'   => max(1, $q->max_num_pages),
    'current' => max(1, intval($paged)),
    'type'    => 'array',
    'prev_text' => '«',
    'next_text' => '»',
  ]);
  if (!empty($links) && is_array($links)){
    echo '<nav class="hl-pagination" aria-label="pagination">';
    foreach ($links as $lnk){ echo '<span class="hl-page">'.$lnk.'</span>'; }
    echo '</nav>';
  }

  return ob_get_clean();
}

/**
 * AJAX: load posts for home sections/listing without reloading
 */
add_action('wp_ajax_yourtheme_load_posts', function(){
  check_ajax_referer('yourtheme_ajax');
  $req  = $_REQUEST; // accept both GET and POST
  $cat  = isset($req['cat'])  ? sanitize_text_field($req['cat'])  : '';
  $catn = isset($req['cat_name']) ? wp_unslash($req['cat_name']) : '';
  $sort = isset($req['sort']) ? sanitize_text_field($req['sort']) : 'latest';
  $layout = isset($req['layout']) ? sanitize_text_field($req['layout']) : 'hs';
  if (!$cat && $catn){
    $term = get_term_by('name', $catn, 'category');
    if ($term && !is_wp_error($term)) $cat = (string) intval($term->term_id);
  }
  if ($layout === 'listing'){
    $paged = isset($req['paged']) ? intval($req['paged']) : 1;
    $html = yourtheme_render_listing($sort, $paged);
  } else {
    if (!$cat){ wp_send_json_error(['message'=>'missing cat'], 400); }
    $html = yourtheme_render_hs_posts($cat, $sort);
  }
  wp_send_json_success(['html'=>$html]);
});
add_action('wp_ajax_nopriv_yourtheme_load_posts', function(){
  check_ajax_referer('yourtheme_ajax');
  $req  = $_REQUEST; // accept both GET and POST
  $cat  = isset($req['cat'])  ? sanitize_text_field($req['cat'])  : '';
  $catn = isset($req['cat_name']) ? wp_unslash($req['cat_name']) : '';
  $sort = isset($req['sort']) ? sanitize_text_field($req['sort']) : 'latest';
  $layout = isset($req['layout']) ? sanitize_text_field($req['layout']) : 'hs';
  if (!$cat && $catn){
    $term = get_term_by('name', $catn, 'category');
    if ($term && !is_wp_error($term)) $cat = (string) intval($term->term_id);
  }
  if ($layout === 'listing'){
    $paged = isset($req['paged']) ? intval($req['paged']) : 1;
    $html = yourtheme_render_listing($sort, $paged);
  } else {
    if (!$cat){ wp_send_json_error(['message'=>'missing cat'], 400); }
    $html = yourtheme_render_hs_posts($cat, $sort);
  }
  wp_send_json_success(['html'=>$html]);
});

/**
 * Simple JSON-LD for single posts.
 */
add_action('wp_head', function () {
  if (is_single()) {
    global $post;
    $data = [
      '@context' => 'https://schema.org',
      '@type'    => 'BlogPosting',
      'headline' => get_the_title(),
      'datePublished' => get_the_date('c'),
      'dateModified'  => get_the_modified_date('c'),
      'author' => ['@type'=>'Person','name'=> get_the_author()],
      'image'  => get_the_post_thumbnail_url($post,'full') ?: '',
      'mainEntityOfPage' => get_permalink(),
      'publisher' => [
        '@type'=>'Organization',
        'name'=> get_bloginfo('name'),
        'logo'=> ['@type'=>'ImageObject','url'=> get_site_icon_url()]
      ],
      'description' => wp_strip_all_tags(get_the_excerpt())
    ];
    echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
  }
}, 20);

/**
 * Register a JSON Feed endpoint at /feed.json (simple, optional).
 */
add_action('init', function () {
  add_rewrite_rule('feed\.json$', 'index.php?yourtheme_jsonfeed=1', 'top');
  add_rewrite_tag('%yourtheme_jsonfeed%', '1');
});

add_action('template_redirect', function () {
  if (get_query_var('yourtheme_jsonfeed')) {
    header('Content-Type: application/feed+json; charset=' . get_option('blog_charset'));
    $q = new WP_Query(['posts_per_page'=>20,'post_status'=>'publish']);
    $items = [];
    while ($q->have_posts()) { $q->the_post();
      $items[] = [
        'id' => get_the_ID(),
        'url' => get_permalink(),
        'title' => get_the_title(),
        'content_html' => apply_filters('the_content', get_the_content()),
        'date_published' => get_the_date('c'),
        'image' => get_the_post_thumbnail_url(get_the_ID(),'full') ?: null,
        'author' => ['name'=> get_the_author()],
      ];
    } wp_reset_postdata();
    $feed = [
      'version' => 'https://jsonfeed.org/version/1.1',
      'title'   => get_bloginfo('name'),
      'home_page_url' => home_url('/'),
      'feed_url' => home_url('/feed.json'),
      'items'   => $items
    ];
    echo wp_json_encode($feed, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
  }
});

/**
 * Local-only override: force header background to green with !important.
 * Applies on localhost/127.0.0.1/.local/.test or non-production WP envs.
 */
add_action('wp_head', function(){
  $env  = function_exists('wp_get_environment_type') ? wp_get_environment_type() : '';
  $host = wp_parse_url(home_url(), PHP_URL_HOST);
  $is_local_host = in_array($host, ['localhost','127.0.0.1'], true) || (is_string($host) && preg_match('/\.(local|test)$/i', $host));
  $is_dev_env    = ($env && $env !== 'production');
  if (!($is_local_host || $is_dev_env)) return;

  $green = '#16a34a';
  echo "\n<style id=\"yourtheme-local-header-override\">\n";
  // Ensure variable override and direct background fallback both win.
  echo ":root{ --yt-header-bg: {$green} !important; }\n";
  echo ".yt-header[data-variant=\"desktop\"], .yt-header--mobile, .yt-drawer{ background: {$green} !important; }\n";
  echo "</style>\n";
}, 100);
