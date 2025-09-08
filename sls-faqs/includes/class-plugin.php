<?php
if (!defined('ABSPATH')) exit;

final class Starlabs_FAQ_Plugin {
  const VERSION = '1.3.2';
  const SLUG    = 'starlabs-faq';

  public static function init(): void {
    add_action('plugins_loaded', [__CLASS__, 'load_textdomain']);
    add_action('init',           [__CLASS__, 'register_cpt_and_tax']);
    register_activation_hook(STARLABS_FAQ_FILE, [__CLASS__, 'activate']);
    register_deactivation_hook(STARLABS_FAQ_FILE, [__CLASS__, 'deactivate']);
  }

  public static function load_textdomain(): void {
    load_plugin_textdomain('starlabs-faq', false, dirname(plugin_basename(STARLABS_FAQ_FILE)) . '/languages/');
  }

  public static function register_cpt_and_tax(): void {
    register_post_type('faq', [
      'labels' => [
        'name'               => __('SLS FAQs', 'starlabs-faq'),
        
        'menu_name'        => __('SLS FAQs', 'starlabs-faq'),
        'singular_name'      => __('FAQ', 'starlabs-faq'),
        'add_new_item'       => __('Add New FAQ', 'starlabs-faq'),
        'edit_item'          => __('Edit FAQ', 'starlabs-faq'),
        'view_item'          => __('View FAQ', 'starlabs-faq'),
        'search_items'       => __('Search FAQs', 'starlabs-faq'),
        'not_found'          => __('No FAQs found', 'starlabs-faq'),
      ],
      'public'       => true,
      'show_in_rest' => true,
      'menu_icon'    => 'dashicons-editor-help',
      'supports'     => ['title', 'editor', 'excerpt', 'page-attributes'],
      'rewrite'      => ['slug' => 'faqs'],
      'has_archive'  => false,
    ]);

    register_taxonomy('faq_category', 'faq', [
      'labels' => [
        'name'          => __('FAQ Categories', 'starlabs-faq'),
        'singular_name' => __('FAQ Category', 'starlabs-faq'),
      ],
      'hierarchical' => true,
      'show_in_rest' => true,
      'rewrite'      => ['slug' => 'faq-category'],
      'public'       => true,
    ]);
  }

  public static function activate(): void {
    self::register_cpt_and_tax();
    flush_rewrite_rules();
  }

  public static function deactivate(): void {
    flush_rewrite_rules();
  }
}
