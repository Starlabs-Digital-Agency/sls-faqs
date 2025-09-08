<?php
/**
 * Front-end: shortcode, asset loading, and markup rendering.
 *
 * - Renders the [starlabs_faqs] shortcode
 * - Enqueues CSS/JS (robust to page builders)
 * - Hides tabs automatically when exactly one category is requested
 * - Shows a category badge on each FAQ item
 */
if (!defined('ABSPATH')) exit;

final class Starlabs_FAQ_Frontend {

  /**
   * Bootstrap front-end hooks.
   */
  public static function init(): void {
    add_shortcode('starlabs_faqs', [__CLASS__, 'shortcode_render']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'conditionally_enqueue_assets']);
    add_filter('plugin_action_links_' . plugin_basename(STARLABS_FAQ_FILE), [__CLASS__, 'plugin_action_links']);
  }

  /**
   * Add a quick "Tools" link on the Plugins list row.
   */
  public static function plugin_action_links(array $links): array {
    $url = admin_url('edit.php?post_type=faq&page=starlabs-faq-tools');
    $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Tools', 'starlabs-faq') . '</a>';
    return $links;
  }

  /**
   * Conditional enqueue to keep things lean on pages without the shortcode.
   * (The shortcode itself also enqueues to be bulletproof with builders.)
   */
  public static function conditionally_enqueue_assets(): void {
    if (!is_singular()) return;

    $force = (bool) apply_filters('starlabs_faq_force_assets', false);
    $contains = false;

    global $post;
    if ($post instanceof WP_Post) {
      $contains = has_shortcode($post->post_content ?? '', 'starlabs_faqs');
    }
    if (!$force && !$contains) return;

    self::enqueue_assets();
  }

  /**
   * Register + enqueue CSS/JS, append Additional CSS, respect opt-out.
   */
  private static function enqueue_assets(): void {
    $ver = Starlabs_FAQ_Plugin::VERSION;

    if (!wp_style_is(Starlabs_FAQ_Plugin::SLUG, 'registered')) {
      wp_register_style(
        Starlabs_FAQ_Plugin::SLUG,
        STARLABS_FAQ_URL . 'assets/css/faq.css',
        [],
        starlabs_faq_asset_version('assets/css/faq.css', $ver)
      );
    }
    if (!wp_script_is(Starlabs_FAQ_Plugin::SLUG, 'registered')) {
      wp_register_script(
        Starlabs_FAQ_Plugin::SLUG,
        STARLABS_FAQ_URL . 'assets/js/faq.js',
        [],
        starlabs_faq_asset_version('assets/js/faq.js', $ver),
        true
      );
    }

    wp_enqueue_style(Starlabs_FAQ_Plugin::SLUG);
    wp_enqueue_script(Starlabs_FAQ_Plugin::SLUG);

    // Append Additional CSS after base CSS so overrides win
    $user_css = (string) get_option(STARLABS_FAQ_OPT_CUSTOM_CSS, '');
    if (trim($user_css) !== '') {
      wp_add_inline_style(Starlabs_FAQ_Plugin::SLUG, $user_css);
    }

    // Allow disabling base CSS entirely (site takes full control)
    if (apply_filters('starlabs_faq_disable_default_css', defined('STARLABS_FAQ_DISABLE_DEFAULT_CSS') && STARLABS_FAQ_DISABLE_DEFAULT_CSS)) {
      wp_dequeue_style(Starlabs_FAQ_Plugin::SLUG);
    }
  }

  /**
   * Shortcode renderer with category badge and smart tabs behavior.
   *
   * Attributes:
   * - categories   (csv slugs)  Limit tabs + query to these categories
   * - per_page     (int)        Number of FAQs (-1 = all)
   * - orderby      (string)     menu_order|title|date
   * - order        (string)     ASC|DESC
   * - show_all_tab (bool)       Whether to show the "All" tab when tabs are rendered
   * - update_hash  (bool)       Whether to update location.hash on filter
   */
  public static function shortcode_render($atts = []): string {
    $atts = shortcode_atts([
      'categories'   => '',
      'per_page'     => -1,
      'orderby'      => 'menu_order',
      'order'        => 'ASC',
      'show_all_tab' => 'true',
      'update_hash'  => 'true',
    ], $atts, 'starlabs_faqs');

    // Ensure assets even in builder contexts
    self::enqueue_assets();

    $show_all   = strtolower((string) $atts['show_all_tab']) !== 'false';
    $updateHash = strtolower((string) $atts['update_hash']) !== 'false';

    // Normalize category slugs from attribute
    $slugs = array_filter(array_map('sanitize_title', array_map('trim', explode(',', (string) $atts['categories']))));

    // Fetch terms for tabs (if we render them)
    $term_args = [
      'taxonomy'   => 'faq_category',
      'hide_empty' => true,
      'orderby'    => 'name',
      'order'      => 'ASC',
    ];
    if (!empty($slugs)) {
      $term_args['slug'] = $slugs;
    }
    $terms = get_terms($term_args);
    if (is_wp_error($terms)) {
      $terms = [];
    }

    /**
     * Decide whether to render tabs.
     * Rule: hide tabs if exactly one category is requested in the shortcode.
     * Developers can override via the 'starlabs_faq_render_tabs' filter.
     */
    $single_category_selected = !empty($slugs) && count($slugs) === 1;
    $render_tabs = apply_filters('starlabs_faq_render_tabs', !$single_category_selected, $slugs, $terms, $atts);

    // If we won't render tabs, the JS isn't needed—dequeue for a tiny perf win.
    if (!$render_tabs && wp_script_is(Starlabs_FAQ_Plugin::SLUG, 'enqueued')) {
      wp_dequeue_script(Starlabs_FAQ_Plugin::SLUG);
    }

    // Build the query
    $q_args = [
      'post_type'              => 'faq',
      'post_status'            => 'publish',
      'posts_per_page'         => (int) $atts['per_page'],
      'orderby'                => sanitize_key($atts['orderby']),
      'order'                  => strtoupper((string) $atts['order']) === 'DESC' ? 'DESC' : 'ASC',
      'no_found_rows'          => true,
      'update_post_term_cache' => true,
      'ignore_sticky_posts'    => true,
    ];
    if (!empty($slugs)) {
      $q_args['tax_query'] = [[
        'taxonomy' => 'faq_category',
        'field'    => 'slug',
        'terms'    => $slugs,
      ]];
    }
    /** Allow devs to customize the query */
    $q_args = apply_filters('starlabs_faq_query_args', $q_args, $atts);

    $loop = new WP_Query($q_args);

    // Start output
    ob_start();

    // Section attributes
    $attrs = [
      'class'            => 'starlabs-faqs',
      'aria-label'       => esc_attr__('FAQs', 'starlabs-faq'),
      'data-update-hash' => $updateHash ? 'true' : 'false',
    ];
    $attr_html = '';
    foreach ($attrs as $k => $v) {
      $attr_html .= sprintf(' %s="%s"', esc_attr($k), esc_attr($v));
    }
    echo '<section' . $attr_html . '>';

    // Tabs (only if $render_tabs is true)
    if ($render_tabs) {
      echo '<div class="faq-tabs" role="tablist" aria-label="' . esc_attr__('FAQ categories', 'starlabs-faq') . '">';
      if ($show_all) {
        $all_label = apply_filters('starlabs_faq_all_label', __('All', 'starlabs-faq'));
        echo '<button class="faq-tab is-active" role="tab" aria-selected="true" data-filter="all" tabindex="0" aria-controls="starlabs-faq-list">'
           . esc_html($all_label)
           . '</button>';
      }
      foreach ($terms as $term) {
        printf(
          '<button class="faq-tab" role="tab" aria-selected="false" data-filter="%1$s" tabindex="-1" aria-controls="starlabs-faq-list">%2$s</button>',
          esc_attr($term->slug),
          esc_html($term->name)
        );
      }
      echo '</div>';
    }

    // List region
    echo '<div id="starlabs-faq-list" class="faq-list" role="region" aria-live="polite">';

    if ($loop->have_posts()) {
      while ($loop->have_posts()) {
        $loop->the_post();

        $term_list   = get_the_terms(get_the_ID(), 'faq_category');
        $term_classes = '';
        $badge_label  = __('None', 'starlabs-faq');

        if ($term_list && !is_wp_error($term_list)) {
          // Choose a primary term (default: alphabetical by name)
          usort($term_list, static function($a, $b) {
            return strcasecmp($a->name, $b->name);
          });
          $primary = apply_filters('starlabs_faq_primary_term', $term_list[0], $term_list);
          if ($primary && isset($primary->name)) {
            $badge_label = $primary->name;
          }
          $term_classes = ' ' . implode(' ', array_map(static function ($t) {
            return sanitize_html_class($t->slug);
          }, $term_list));
        }

        // FAQ item with category badge (top-right)
        $item_html = sprintf(
          '<details class="faq-item%1$s"><summary>%2$s<span class="faq-badge" aria-label="%4$s">%3$s</span></summary><div class="faq-content">%5$s</div></details>',
          esc_attr($term_classes),
          esc_html(get_the_title()),
          esc_html($badge_label),
          esc_attr__('Category', 'starlabs-faq'),
          apply_filters('the_content', get_the_content())
        );

        echo apply_filters('starlabs_faq_render_item', $item_html, get_the_ID());
      }
      wp_reset_postdata();
    } else {
      echo '<p class="faq-empty">' . esc_html__('No FAQs found.', 'starlabs-faq') . '</p>';
    }

    // Placeholder used by JS when filtering yields zero results (tabs mode only)
    echo '<p class="faq-empty-category" hidden>' . esc_html__('No FAQs in this category.', 'starlabs-faq') . '</p>';

    echo '</div></section>';

    return (string) ob_get_clean();
  }
}
