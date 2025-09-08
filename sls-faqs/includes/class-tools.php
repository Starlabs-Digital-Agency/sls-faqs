<?php
if (!defined('ABSPATH')) exit;

final class Starlabs_FAQ_Tools {
  public static function init(): void {
    add_action('admin_menu', [__CLASS__, 'add_tools_page']);
    add_action('admin_post_starlabs_faq_export', [__CLASS__, 'handle_export']);
    add_action('admin_post_starlabs_faq_import', [__CLASS__, 'handle_import']);
    add_action('admin_post_starlabs_faq_delete_all', [__CLASS__, 'handle_delete_all']);
    add_action('admin_post_starlabs_faq_update_settings', [__CLASS__, 'handle_settings']);
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('import_ok'); });
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('import_err'); });
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('export_err'); });
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('delete_ok'); });
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('delete_err'); });
    add_action('admin_notices', function(){ starlabs_faq_admin_notice('settings_ok'); });
  }

  public static function add_tools_page(): void {
    $cap = apply_filters('starlabs_faq_admin_capability', 'manage_options');

    add_submenu_page(
      'edit.php?post_type=faq',
      __('Tools', 'starlabs-faq'),
      __('Tools', 'starlabs-faq'),
      $cap,
      'starlabs-faq-tools',
      [__CLASS__, 'render_tools_page'],
      10
    );
  }

  /** Render Tools (Export, Import, Danger Zone, Uninstall behavior) */
  public static function render_tools_page(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) {
      wp_die(esc_html__('You do not have permission to access this page.', 'starlabs-faq'));
    }
    $delete_on_uninstall = get_option(STARLABS_FAQ_OPT_DELETE_ON_UNINSTALL, 'no') === 'yes';
    $tools_url = admin_url('admin-post.php');
    ?>
    <div class="wrap">
      <h1><?php echo esc_html__('FAQ Tools', 'starlabs-faq'); ?></h1>

      <h2><?php echo esc_html__('Export', 'starlabs-faq'); ?></h2>
      <p class="description"><?php echo esc_html__('Download all FAQs and categories in JSON format.', 'starlabs-faq'); ?></p>
      <form method="post" action="<?php echo esc_url($tools_url); ?>">
        <input type="hidden" name="action" value="starlabs_faq_export">
        <?php wp_nonce_field('starlabs_faq_export'); ?>
        <p><button class="button button-primary"><?php echo esc_html__('Export JSON', 'starlabs-faq'); ?></button></p>
      </form>

      <hr>

      <h2><?php echo esc_html__('Import', 'starlabs-faq'); ?></h2>
      <p class="description"><?php echo esc_html__('Import from a JSON file previously exported by this plugin.', 'starlabs-faq'); ?></p>
      <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($tools_url); ?>">
        <input type="hidden" name="action" value="starlabs_faq_import">
        <?php wp_nonce_field('starlabs_faq_import'); ?>
        <p>
          <input type="file" name="starlabs_faq_import_file" accept="application/json" required>
        </p>
        <p>
          <label><input type="checkbox" name="upsert" value="1"> <?php echo esc_html__('Update existing FAQs by slug (upsert).', 'starlabs-faq'); ?></label>
        </p>
        <p><button class="button button-primary"><?php echo esc_html__('Import', 'starlabs-faq'); ?></button></p>
      </form>

      <hr>

      <h2><?php echo esc_html__('Uninstall behavior', 'starlabs-faq'); ?></h2>
      <form method="post" action="<?php echo esc_url($tools_url); ?>">
        <input type="hidden" name="action" value="starlabs_faq_update_settings">
        <?php wp_nonce_field('starlabs_faq_update_settings'); ?>
        <label>
          <input type="checkbox" name="delete_on_uninstall" value="yes" <?php checked($delete_on_uninstall); ?>>
          <?php echo esc_html__('On uninstall, also delete all FAQs and FAQ Categories (dangerous).', 'starlabs-faq'); ?>
        </label>
        <p class="description"><?php echo esc_html__('WordPress cannot show a plugin-specific dialog on delete. Enable this if you want uninstall to purge content.', 'starlabs-faq'); ?></p>
        <p><button class="button"><?php echo esc_html__('Save Setting', 'starlabs-faq'); ?></button></p>
      </form>

      <hr>

      <h2 style="color:#b32d2e;"><?php echo esc_html__('Danger Zone', 'starlabs-faq'); ?></h2>
      <p class="description"><?php echo esc_html__('Irreversibly delete all FAQ posts and FAQ Categories right now.', 'starlabs-faq'); ?></p>
      <form method="post" action="<?php echo esc_url($tools_url); ?>" onsubmit="return confirm('This will permanently delete all FAQ posts and categories. Are you absolutely sure?');">
        <input type="hidden" name="action" value="starlabs_faq_delete_all">
        <?php wp_nonce_field('starlabs_faq_delete_all'); ?>
        <p>
          <label><input type="checkbox" name="confirm" value="yes" required> <?php echo esc_html__('I understand this cannot be undone.', 'starlabs-faq'); ?></label>
        </p>
        <p><button class="button button-secondary" style="border-color:#b32d2e;color:#b32d2e;"><?php echo esc_html__('Delete All FAQs Now', 'starlabs-faq'); ?></button></p>
      </form>
    </div>
    <?php
  }

  /** Export handler (JSON) */
  public static function handle_export(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) wp_die(__('Permission denied', 'starlabs-faq'));
    check_admin_referer('starlabs_faq_export');

    try {
      $data = self::collect_export_data();
      $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
      if ($json === false) throw new RuntimeException('JSON encode error');

      nocache_headers();
      header('Content-Type: application/json; charset=utf-8');
      header('Content-Disposition: attachment; filename="starlabs-faq-export-' . gmdate('Ymd-His') . '.json"');
      header('Content-Length: ' . strlen($json));
      echo $json;
      exit;
    } catch (Throwable $e) {
      wp_safe_redirect(add_query_arg('stb_msg', 'export_err', wp_get_referer() ?: admin_url()));
      exit;
    }
  }

  /** Import handler (JSON) */
  public static function handle_import(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) wp_die(__('Permission denied', 'starlabs-faq'));
    check_admin_referer('starlabs_faq_import');

    try {
      if (empty($_FILES['starlabs_faq_import_file']['tmp_name'])) throw new RuntimeException('No file');
      $tmp  = $_FILES['starlabs_faq_import_file']['tmp_name'];
      $type = wp_check_filetype($_FILES['starlabs_faq_import_file']['name']);
      if ($type['type'] !== 'application/json' && substr($_FILES['starlabs_faq_import_file']['name'], -5) !== '.json') {
        throw new RuntimeException('Invalid file type');
      }

      $raw = file_get_contents($tmp);
      if ($raw === false) throw new RuntimeException('Read failed');

      $payload = json_decode($raw, true);
      if (!is_array($payload) || !isset($payload['faqs']) || !is_array($payload['faqs'])) {
        throw new RuntimeException('Invalid JSON schema');
      }

      $upsert = !empty($_POST['upsert']);

      foreach ($payload['faqs'] as $item) {
        self::import_one($item, $upsert);
      }

      wp_safe_redirect(add_query_arg('stb_msg', 'import_ok', wp_get_referer() ?: admin_url()));
      exit;
    } catch (Throwable $e) {
      wp_safe_redirect(add_query_arg('stb_msg', 'import_err', wp_get_referer() ?: admin_url()));
      exit;
    }
  }

  /** Delete-all handler (Danger Zone) */
  public static function handle_delete_all(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) wp_die(__('Permission denied', 'starlabs-faq'));
    check_admin_referer('starlabs_faq_delete_all');
    if (empty($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
      wp_safe_redirect(add_query_arg('stb_msg', 'delete_err', wp_get_referer() ?: admin_url()));
      exit;
    }

    $ok = self::delete_all_content();
    wp_safe_redirect(add_query_arg('stb_msg', $ok ? 'delete_ok' : 'delete_err', wp_get_referer() ?: admin_url()));
    exit;
  }

  /** Uninstall settings handler */
  public static function handle_settings(): void {
    if (!current_user_can(apply_filters('starlabs_faq_admin_capability', 'manage_options'))) wp_die(__('Permission denied', 'starlabs-faq'));
    check_admin_referer('starlabs_faq_update_settings');

    $val = (!empty($_POST['delete_on_uninstall']) && $_POST['delete_on_uninstall'] === 'yes') ? 'yes' : 'no';
    update_option(STARLABS_FAQ_OPT_DELETE_ON_UNINSTALL, $val, false);

    wp_safe_redirect(add_query_arg('stb_msg', 'settings_ok', wp_get_referer() ?: admin_url()));
    exit;
  }

  /** Build export payload */
  private static function collect_export_data(): array {
    $faqs = [];
    $q = new WP_Query([
      'post_type'      => 'faq',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'fields'         => 'all',
      'no_found_rows'  => true,
    ]);

    while ($q->have_posts()) {
      $q->the_post();
      $terms = get_the_terms(get_the_ID(), 'faq_category') ?: [];
      $terms = array_filter($terms, fn($t) => !is_wp_error($t));
      $faqs[] = [
        'title'       => get_the_title(),
        'slug'        => get_post_field('post_name', get_the_ID()),
        'content'     => get_post_field('post_content', get_the_ID()),
        'excerpt'     => get_post_field('post_excerpt', get_the_ID()),
        'menu_order'  => (int) get_post_field('menu_order', get_the_ID()),
        'categories'  => array_map(fn($t) => ['slug' => $t->slug, 'name' => $t->name], $terms),
      ];
    }
    wp_reset_postdata();

    return [
      'version'     => '1.0',
      'exported_at' => gmdate('c'),
      'site'        => home_url('/'),
      'faqs'        => $faqs,
    ];
  }

  /** Import one record */
  private static function import_one(array $item, bool $upsert): void {
    $title      = isset($item['title']) ? wp_strip_all_tags($item['title']) : '';
    $slug       = isset($item['slug'])  ? sanitize_title($item['slug'])    : '';
    $content    = isset($item['content']) ? (string) $item['content'] : '';
    $excerpt    = isset($item['excerpt']) ? (string) $item['excerpt'] : '';
    $menu_order = isset($item['menu_order']) ? (int) $item['menu_order'] : 0;
    $cats       = isset($item['categories']) && is_array($item['categories']) ? $item['categories'] : [];

    $post_id = 0;
    if ($upsert && $slug) {
      $post = get_page_by_path($slug, OBJECT, 'faq');
      if ($post) $post_id = (int) $post->ID;
    }

    $data = [
      'post_type'   => 'faq',
      'post_status' => 'publish',
      'post_title'  => $title,
      'post_name'   => $slug ?: null,
      'post_content'=> $content,
      'post_excerpt'=> $excerpt,
      'menu_order'  => $menu_order,
    ];

    if ($post_id) {
      $data['ID'] = $post_id;
      $post_id = wp_update_post($data, true);
    } else {
      $post_id = wp_insert_post($data, true);
    }

    if (is_wp_error($post_id) || !$post_id) return;

    // Ensure category terms exist, then assign
    $term_slugs = [];
    foreach ($cats as $cat) {
      $cslug = isset($cat['slug']) ? sanitize_title($cat['slug']) : '';
      $cname = isset($cat['name']) ? sanitize_text_field($cat['name']) : $cslug;
      if (!$cslug) continue;

      $term = term_exists($cslug, 'faq_category');
      if (!$term) {
        $term = wp_insert_term($cname, 'faq_category', ['slug' => $cslug]);
      }
      if (!is_wp_error($term)) $term_slugs[] = $cslug;
    }
    if ($term_slugs) {
      wp_set_object_terms($post_id, $term_slugs, 'faq_category', false);
    }
  }

  /** Delete all FAQ posts and categories */
  public static function delete_all_content(): bool {
    global $wpdb;

    // Delete all posts (force delete)
    $ids = $wpdb->get_col($wpdb->prepare(
      "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s",
      'faq'
    ));
    if ($ids) {
      foreach ($ids as $id) {
        wp_delete_post((int) $id, true);
      }
    }

    // Delete all terms in faq_category
    $terms = get_terms([
      'taxonomy'   => 'faq_category',
      'hide_empty' => false,
    ]);
    if (!is_wp_error($terms)) {
      foreach ($terms as $t) {
        wp_delete_term($t->term_id, 'faq_category');
      }
    }

    return true;
  }
}
