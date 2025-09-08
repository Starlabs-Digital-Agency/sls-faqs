<?php
if (!defined('ABSPATH')) exit;

final class Starlabs_FAQ_Admin {
  public static function init(): void {
    add_action('admin_menu', [__CLASS__, 'add_css_page']);
    add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_code_editor']);
  }

  public static function add_css_page(): void {
    $cap = apply_filters('starlabs_faq_admin_capability', 'manage_options');

    add_submenu_page(
      'edit.php?post_type=faq',
      __('Additional CSS', 'starlabs-faq'),
      __('Additional CSS', 'starlabs-faq'),
      $cap,
      'starlabs-faq-css',
      [__CLASS__, 'render_css_page'],
      20
    );
  }

  public static function render_css_page(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) {
      wp_die(esc_html__('You do not have permission to access this page.', 'starlabs-faq'));
    }

    if (isset($_POST['starlabs_faq_css_nonce']) && wp_verify_nonce((string) $_POST['starlabs_faq_css_nonce'], 'starlabs_faq_css_save')) {
      $raw = isset($_POST['starlabs_faq_custom_css']) ? wp_unslash((string) $_POST['starlabs_faq_custom_css']) : '';
      update_option(STARLABS_FAQ_OPT_CUSTOM_CSS, $raw, false);
      echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Saved.', 'starlabs-faq') . '</p></div>';
    }

    $css = (string) get_option(STARLABS_FAQ_OPT_CUSTOM_CSS, '');
    ?>
    <div class="wrap">
      <h1><?php echo esc_html__('Additional CSS (Starlabs FAQ)', 'starlabs-faq'); ?></h1>
      <p class="description"><?php echo esc_html__('Add custom CSS that loads after the plugin’s default stylesheet.', 'starlabs-faq'); ?></p>
      <form method="post">
        <?php wp_nonce_field('starlabs_faq_css_save', 'starlabs_faq_css_nonce'); ?>
        <textarea id="starlabs_faq_custom_css" name="starlabs_faq_custom_css" rows="20" class="large-text code" style="font-family:Menlo,Consolas,monospace;"><?php echo esc_textarea($css); ?></textarea>
        <p><button type="submit" class="button button-primary"><?php echo esc_html__('Save CSS', 'starlabs-faq'); ?></button></p>
      </form>
    </div>
    <?php
  }

  public static function enqueue_code_editor(string $hook): void {
    if ($hook !== 'faq_page_starlabs-faq-css') return;
    $settings = wp_enqueue_code_editor(['type' => 'text/css']);
    if ($settings) {
      wp_add_inline_script(
        'code-editor',
        'jQuery(function(){ wp.codeEditor.initialize("starlabs_faq_custom_css",' . wp_json_encode($settings) . '); });'
      );
    }
  }
}
