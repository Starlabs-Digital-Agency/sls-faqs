<?php
if (!defined('ABSPATH')) exit;

/**
 * Cache-busting: filemtime in dev; plugin version fallback.
 *
 * @param string $relative e.g. 'assets/js/faq.js'
 * @param string $fallback_version default '1.0.0'
 * @return string
 */
function starlabs_faq_asset_version(string $relative, string $fallback_version = '1.0.0'): string {
  $path = STARLABS_FAQ_DIR . ltrim($relative, '/');
  return file_exists($path) ? (string) filemtime($path) : $fallback_version;
}

/**
 * Simple admin flash messages via query args.
 * Usage: after processing, redirect with add_query_arg('stb_msg','done', $url).
 */
function starlabs_faq_admin_notice(string $code): void {
  if (!is_admin()) return;

  $msg = isset($_GET['stb_msg']) ? sanitize_key($_GET['stb_msg']) : '';
  if ($msg !== $code) return;

  $map = [
    'import_ok'     => ['success', __('Import completed.', 'starlabs-faq')],
    'import_err'    => ['error',   __('Import failed. Check the file format.', 'starlabs-faq')],
    'export_err'    => ['error',   __('Export failed.', 'starlabs-faq')],
    'delete_ok'     => ['success', __('All FAQs deleted.', 'starlabs-faq')],
    'delete_err'    => ['error',   __('Delete failed.', 'starlabs-faq')],
    'settings_ok'   => ['success', __('Settings saved.', 'starlabs-faq')],
  ];

  if (!isset($map[$code])) return;
  [$class, $text] = $map[$code];

  echo '<div class="notice notice-' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
}
