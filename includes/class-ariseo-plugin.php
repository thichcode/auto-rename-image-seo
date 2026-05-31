<?php
/**
 * Auto Rename Image SEO - Main plugin class
 *
 * @package Auto Rename Image SEO
 */

if (! defined( 'ABSPATH' )) {
    exit;
}

final class ARISEO_Plugin
{
    private static ?ARISEO_Plugin $instance = null;
    private const OPTION_PREFIX = 'ariseo_';
    private const OPTION_SETTINGS = 'ariseo_settings';
    private const OPTION_LITE_COUNT = 'ariseo_count';
    private const OPTION_LICENSE = 'ariseo_license';
    public const LITE_LIMIT = 20; // 20 renames/month in Lite version

    public static function instance(): ARISEO_Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function activate(): void
    {
        do_action('' . ARISEO_SLUG . '_activated');
    }

    public static function deactivate(): void
    {
        // Cleanup on deactivation
        set_transient('' . ARISEO_SLUG . '_notice_activation', 1, DAY_IN_SECONDS);
    }

    public static function uninstall(): void
    {
        ARISEO_Plugin::uninstall_cleanup();
    }

    /**
     * Constructor - set up hooks
     */
    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_filter('wp_handle_upload_prefilter', [$this, 'rename_upload_file']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'handle_settings_save']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        
    }

    /**
     * Load translation files
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain('auto-rename-image-seo', false, dirname(plugin_basename(ARISEO_FILE)) . '/languages');
    }

    /**
     * Main rename logic for file uploads
     * @param array $file Raw file array from WordPress
     * @return array Sanitized file array
     */
    public function rename_upload_file(array $file): array
    {
        $settings = $this->settings();

            if (
                empty($file['name']) ||
                empty($file['type']) ||
                empty($_SERVER['REQUEST_URI']) ||
                strpos((string) $_SERVER['REQUEST_URI'], '/wp-admin/') !== false
            ) {
                return $file;
            }
        // Only rename images
        if (strpos((string) $file['type'], 'image/') !== 0) {
            return $file;
        }

        // Handle Lite limit
        if (!$this->is_pro() && !$this->increment_use_count()) {
            // Show notice once per session/client
            set_transient('ariseo_lite_limit_notice', 1, HOUR_IN_SECONDS);
            return $file;
        }

        $new_name = $this->build_seo_filename($file);
        if ($new_name !== $file['name']) {
            $file['name'] = sanitize_file_name($new_name);
        }

        return $file;
    }

    /**
     * Build SEO filename from context
     */
    private function build_seo_filename(array $file): string
    {
        $post_id = (int) (isset($_POST['post_id']) ? ($_POST['post_id']) : 0);
        $post_title = '';
        if ($post_id > 0) {
            $post = get_post($post_id);
            $post_title = $post ? $post->post_title : '';
        }

        $fallback = $this->safe_filename($file['name']);
        if (empty($post_title)) {
            return $fallback;
        }

        // Convert title to slug
        $slug = sanitize_title($post_title);
        $ext = pathinfo((string) $file['name'], PATHINFO_EXTENSION);

        // Base filename: post-slug-part-type-ext
        $base = $slug;
        $counter = 0;
        $max_tries = 5;
        do {
            $counter++;
            $candidate = $base . '-image-' . $counter . '.' . $ext;
        } while ($counter < $max_tries && file_exists(wp_upload_dir()['basedir'] . '/' . $candidate));

        return $counter <= $max_tries ? $candidate : $fallback;
    }

    /**
     * Increment weekly rename usage counter (Lite)
     */
    private function increment_use_count(): bool
    {
        $current = gmdate('Y-W'); // Week-based count resets weekly
        $count = get_option(self::OPTION_LITE_COUNT, ['week' => '', 'count' => 0]);

        if ($count['week'] !== $current) {
            $count = ['week' => $current, 'count' => 0];
        }

        if ($count['count'] >= self::LITE_LIMIT) {
            return false;
        }

        $count['count']++;
        update_option(self::OPTION_LITE_COUNT, $count);
        return true;
    }

    /**
     * Get plugin settings
     */
    private function settings(): array
    {
        $defaults = [
            'per_post' => 1,
            'auto_enable' => 1,
        ];
        return (array) get_option(self::OPTION_SETTINGS, $defaults);
    }

    /**
     * Check if plugin is enabled
     */
    public function is_enabled(): bool
    {
        $s = $this->settings();
        return (int) ($s['per_post'] ?? 0) === 1;
    }

    /**
     * Sanitize filename for upload safely
     */
    private function safe_filename(string $name): string
    {
        $name = sanitize_file_name($name);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = substr($name, 0, -
            (strlen($ext) + 1)
        );
        $ext = sanitize_file_name($ext);
        return $name . '.' . $ext;
    }

    /**
     * Add admin settings page
     */
    public function add_admin_page(): void
    {
        add_options_page(
            esc_html__('Auto Rename Image SEO', 'auto-rename-image-seo'),
            esc_html__('Auto Rename Image SEO', 'auto-rename-image-seo'),
            'manage_options',
            ARISEO_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Handle settings save
     */
    public function handle_settings_save(): void
    {
        if (!isset($_POST[ARISEO_SLUG . '_save'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'auto-rename-image-seo'));
        }
        check_admin_referer(ARISEO_SLUG . '_save');

        $settings = [
            'per_post' => isset($_POST['ariseo_per_post']) ? 1 : 0,
            'auto_enable' => isset($_POST['ariseo_auto_enable']) ? 1 : 0,
        ];
        update_option(self::OPTION_SETTINGS, $settings);

        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved!', 'auto-rename-image-seo') . '</p></div>';
        });
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->settings();
        $lite_uses = get_option(self::OPTION_LITE_COUNT, ['count' => 0]);
        $pro_active = false; // placeholderimerkki
        
        require_once ARISEO_DIR . 'admin/views/settings.php';
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (get_transient( ARISEO_SLUG . '_notice_activation' )) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Thank you for activating Auto Rename Image SEO! Rename your images automatically. Check Advanced settings for Pro options.', 'auto-rename-image-seo') . '</p></div>';
            delete_transient('' . ARISEO_SLUG . '_notice_activation');
        }
        if (get_transient( 'ariseo_lite_limit_notice' )) {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('Lite version limit reached. Upgrade to Pro for unlimited automatic renames!', 'auto-rename-image-seo') . '</p></div>';
            delete_transient('ariseo_lite_limit_notice');
        }
    }

    /**
     * Remove all plugin-specific options safely
     */
    public static function uninstall_cleanup(): void
    {
        $options = [
            self::OPTION_SETTINGS,
            self::OPTION_LITE_COUNT,
            self::OPTION_LICENSE,
        ];
        foreach ($options as $opt) {
            delete_option($opt);
        }
    }

    /**
     * Quick demo check - used by unit test
     */
    public function demo_rename(string $filename, string $post_title): string
    {
        $_POST['post_id'] = 123;
        $file = ['name' => $filename, 'type' => 'image/jpeg'];
        $post = get_post(123);
        $wpdb = $GLOBALS['wpdb'];
        $wpdb->update($wpdb->posts, ['post_title' => $post_title], ['ID' => 123]);
        wp_cache_delete(123, 'posts');
        return $this->build_seo_filename($file);
    }
}
