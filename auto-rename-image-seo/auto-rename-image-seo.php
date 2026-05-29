<?php
/**
 * Plugin Name: Auto Rename Image SEO
 * Plugin URI: https://example.com/auto-rename-image-seo
 * Description: Automatically renames uploaded image files into SEO-friendly filenames.
 * Version: 1.0.0
 * Author: Micro Plugin Factory
 * Text Domain: auto-rename-image-seo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ARISEO_VERSION', '1.0.0');
define('ARISEO_PLUGIN_FILE', __FILE__);
define('ARISEO_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once ARISEO_PLUGIN_DIR . 'includes/class-ariseo-plugin.php';

register_activation_hook(__FILE__, ['ARISEO_Plugin', 'activate']);
register_uninstall_hook(__FILE__, ['ARISEO_Plugin', 'uninstall']);

add_action('plugins_loaded', static function (): void {
    ARISEO_Plugin::instance();
});
