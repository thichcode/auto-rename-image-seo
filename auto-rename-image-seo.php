<?php
/**
 * Plugin Name: Auto Rename Image SEO
 * Plugin URI: https://github.com/thuong/auto-rename-image-seo
 * Description: Automatically renames uploaded images to SEO-friendly filenames based on post title. Improves search rankings instantly.
 * Version: 1.0.0
 * Author: Thuong
 * Text Domain: auto-rename-image-seo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Auto Rename Image SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ARISEO_VERSION', '1.0.0');
define('ARISEO_FILE', __FILE__);
define('ARISEO_DIR', plugin_dir_path(__FILE__));
define('ARISEO_URL', plugin_dir_url(__FILE__));
define('ARISEO_SLUG', 'auto-rename-image-seo');

require_once ARISEO_DIR . 'includes/class-ariseo-plugin.php';

register_activation_hook(__FILE__, ['ARISEO_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['ARISEO_Plugin', 'deactivate']);
register_uninstall_hook(__FILE__, ['ARISEO_Plugin', 'uninstall']);

add_action('plugins_loaded', static function (): void {
    ARISEO_Plugin::instance();
});
