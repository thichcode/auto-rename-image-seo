<?php
/**
 * Auto Rename Image SEO - Uninstall
 * 
 * @package Auto Rename Image SEO
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

define('ARISEO_OPTION_PREFIX', 'ariseo_');

// Remove all plugin options
delete_option(ARISEO_OPTION_PREFIX . 'settings');
delete_option(ARISEO_OPTION_PREFIX . 'count');

// Remove any transients
$transients = [
    ARISEO_OPTION_PREFIX . 'activation_redirect',
];

foreach ($transients as $transient) {
    delete_transient($transient);
}

// Remove all site transients
$site_transients = [
    ARISEO_OPTION_PREFIX . 'activation_redirect',
];

foreach ($site_transients as $site_transient) {
    delete_site_transient($site_transient);
}
