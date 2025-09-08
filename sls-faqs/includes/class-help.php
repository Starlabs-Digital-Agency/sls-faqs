<?php
if (!defined('ABSPATH')) exit;

final class Starlabs_FAQ_Help {
  public static function init(): void {
    add_action('admin_menu', [__CLASS__, 'add_help_page']);
  }

  public static function add_help_page(): void {
    $cap = apply_filters('starlabs_faq_admin_capability', 'manage_options');
    add_submenu_page(
      'edit.php?post_type=faq',
      __('Help', 'starlabs-faq'),
      __('Help', 'starlabs-faq'),
      $cap,
      'starlabs-faq-help',
      [__CLASS__, 'render_help_page']
    );
  }

  public static function render_help_page(): void { ?>
    <div class="wrap">
      <h1><?php echo esc_html__('Starlabs FAQ — Help & Usage', 'starlabs-faq'); ?></h1>
      <p class="description"><?php echo esc_html__('Quick guide to set up and use the SLS FAQs plugin.', 'starlabs-faq'); ?></p>
      <hr />
      <h2><?php echo esc_html__('1) Create Categories', 'starlabs-faq'); ?></h2>
      <ol>
        <li><?php echo esc_html__('Go to', 'starlabs-faq'); ?> <strong><?php echo esc_html__('SLS FAQs → FAQ Categories', 'starlabs-faq'); ?></strong>.</li>
        <li><?php echo esc_html__('Add categories like “General”, “Billing”, “Tech Support”, etc.', 'starlabs-faq'); ?></li>
      </ol>
      <h2><?php echo esc_html__('2) Add FAQs', 'starlabs-faq'); ?></h2>
      <ol>
        <li><?php echo esc_html__('Go to', 'starlabs-faq'); ?> <strong><?php echo esc_html__('SLS FAQs → Add New', 'starlabs-faq'); ?></strong>.</li>
        <li><?php echo esc_html__('Enter the question as the Title and the answer in the Editor.', 'starlabs-faq'); ?></li>
        <li><?php echo esc_html__('Assign one or more FAQ Categories in the sidebar.', 'starlabs-faq'); ?></li>
      </ol>
      <h2><?php echo esc_html__('3) Display on a Page (Shortcode)', 'starlabs-faq'); ?></h2>
      <pre><code>[starlabs_faqs]</code></pre>
      <h3><?php echo esc_html__('Shortcode Options', 'starlabs-faq'); ?></h3>
      <ul>
        <li><code>categories="slug-a,slug-b"</code> — <?php echo esc_html__('Show only selected categories (tabs auto-hide if one category).', 'starlabs-faq'); ?></li>
        <li><code>limit="10"</code> — <?php echo esc_html__('Limit FAQs per category.', 'starlabs-faq'); ?></li>
        <li><code>order="ASC|DESC"</code> — <?php echo esc_html__('Sort by menu order.', 'starlabs-faq'); ?></li>
      </ul>
      <h2><?php echo esc_html__('4) Styling (Optional)', 'starlabs-faq'); ?></h2>
      <p><?php echo esc_html__('Use', 'starlabs-faq'); ?> <strong><?php echo esc_html__('SLS FAQs → Additional CSS', 'starlabs-faq'); ?></strong> <?php echo esc_html__('to add custom CSS that loads after the plugin stylesheet.', 'starlabs-faq'); ?></p>
      <h2><?php echo esc_html__('5) Import / Export / Delete', 'starlabs-faq'); ?></h2>
      <p><?php echo esc_html__('Use', 'starlabs-faq'); ?> <strong><?php echo esc_html__('SLS FAQs → Tools', 'starlabs-faq'); ?></strong> <?php echo esc_html__('to export to JSON, import from JSON, or delete all FAQs.', 'starlabs-faq'); ?></p>
    </div><?php
  }
}
