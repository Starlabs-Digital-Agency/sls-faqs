<?php
/**
 * Plugin Name: Starlabs FAQ Filter
 * Description: FAQ CPT + Category taxonomy, accessible tabbed filtering UI, shortcode [starlabs_faqs], Additional CSS, Import/Export, and safe uninstall options.
 * Version: 1.3.2
 * Author: Starlabs
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: starlabs-faq
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('STARLABS_FAQ_FILE', __FILE__);
define('STARLABS_FAQ_DIR', plugin_dir_path(__FILE__));
define('STARLABS_FAQ_URL', plugin_dir_url(__FILE__));

/** Option keys used across modules (duplicated in uninstall.php for safety) */
define('STARLABS_FAQ_OPT_CUSTOM_CSS',         'starlabs_faq_custom_css');
define('STARLABS_FAQ_OPT_DELETE_ON_UNINSTALL','starlabs_faq_delete_on_uninstall');

require_once STARLABS_FAQ_DIR . 'includes/helpers.php';
require_once STARLABS_FAQ_DIR . 'includes/class-plugin.php';
require_once STARLABS_FAQ_DIR . 'includes/class-frontend.php';
require_once STARLABS_FAQ_DIR . 'includes/class-admin.php';
require_once STARLABS_FAQ_DIR . 'includes/class-help.php';
require_once STARLABS_FAQ_DIR . 'includes/class-tools.php';

// Bootstrap modules
Starlabs_FAQ_Plugin::init();
Starlabs_FAQ_Frontend::init();
Starlabs_FAQ_Admin::init();
Starlabs_FAQ_Help::init();
Starlabs_FAQ_Tools::init();
