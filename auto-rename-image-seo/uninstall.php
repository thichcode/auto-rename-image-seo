<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('ariseo_settings');
delete_option('ariseo_monthly_count');
delete_option('ariseo_license_key');
