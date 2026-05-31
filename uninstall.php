<?php
/**
 * Uninstall script for Auto Rename Image SEO
 * Removes all plugin data on uninstall
 *
 * @package Auto Rename Image SEO
 */

if (! defined( 'ABSPATH' )) {
    exit;
}

// Remove all plugin options
ARISEO_Plugin::uninstall_cleanup();

// Clean up any transients
delete_transient('ariseo_lite_limit_notice');