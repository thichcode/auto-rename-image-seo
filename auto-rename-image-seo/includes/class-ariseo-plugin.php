<?php
if (!defined('ABSPATH')) {
    exit;
}

final class ARISEO_Plugin
{
    private const OPTION_SETTINGS = 'ariseo_settings';
    private const OPTION_COUNT = 'ariseo_monthly_count';
    private const OPTION_LICENSE = 'ariseo_license_key';
    private const LITE_MONTHLY_LIMIT = 20;

    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function activate(): void
    {
        if (!get_option(self::OPTION_SETTINGS)) {
            add_option(self::OPTION_SETTINGS, [
                'enabled' => 1,
                'include_site_name' => 0,
                'include_date' => 0,
                'max_words' => 8,
            ]);
        }
        if (!get_option(self::OPTION_COUNT)) {
            add_option(self::OPTION_COUNT, ['month' => gmdate('Y-m'), 'count' => 0]);
        }
    }

    public static function uninstall(): void
    {
        delete_option(self::OPTION_SETTINGS);
        delete_option(self::OPTION_COUNT);
        delete_option(self::OPTION_LICENSE);
    }

    private function __construct()
    {
        add_filter('wp_handle_upload_prefilter', [$this, 'rename_upload']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'handle_settings_save']);
        add_filter('plugin_action_links_' . plugin_basename(ARISEO_PLUGIN_FILE), [$this, 'settings_link']);
    }

    public function settings_link(array $links): array
    {
        $url = admin_url('options-general.php?page=auto-rename-image-seo');
        $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'auto-rename-image-seo') . '</a>';
        return $links;
    }

    public function add_admin_page(): void
    {
        add_options_page(
            __('Auto Rename Image SEO', 'auto-rename-image-seo'),
            __('Auto Rename Image SEO', 'auto-rename-image-seo'),
            'manage_options',
            'auto-rename-image-seo',
            [$this, 'render_settings_page']
        );
    }

    public function handle_settings_save(): void
    {
        if (!isset($_POST['ariseo_save_settings'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to change these settings.', 'auto-rename-image-seo'));
        }
        check_admin_referer('ariseo_save_settings');

        $settings = [
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'include_site_name' => isset($_POST['include_site_name']) ? 1 : 0,
            'include_date' => isset($_POST['include_date']) ? 1 : 0,
            'max_words' => max(3, min(16, absint($_POST['max_words'] ?? 8))),
        ];
        update_option(self::OPTION_SETTINGS, $settings);
        update_option(self::OPTION_LICENSE, sanitize_text_field(wp_unslash($_POST['license_key'] ?? '')));
        add_settings_error('ariseo_messages', 'ariseo_saved', __('Settings saved.', 'auto-rename-image-seo'), 'updated');
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->settings();
        $license = (string) get_option(self::OPTION_LICENSE, '');
        $count = $this->monthly_count();
        settings_errors('ariseo_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Auto Rename Image SEO', 'auto-rename-image-seo'); ?></h1>
            <p><?php echo esc_html__('Rename uploaded image files into clean SEO filenames before WordPress saves them.', 'auto-rename-image-seo'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('ariseo_save_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable rename', 'auto-rename-image-seo'); ?></th>
                        <td><label><input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], 1); ?>> <?php echo esc_html__('Rename new image uploads', 'auto-rename-image-seo'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Filename parts', 'auto-rename-image-seo'); ?></th>
                        <td>
                            <label><input type="checkbox" name="include_site_name" value="1" <?php checked($settings['include_site_name'], 1); ?>> <?php echo esc_html__('Append site name', 'auto-rename-image-seo'); ?></label><br>
                            <label><input type="checkbox" name="include_date" value="1" <?php checked($settings['include_date'], 1); ?>> <?php echo esc_html__('Append current date', 'auto-rename-image-seo'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="max_words"><?php echo esc_html__('Max words', 'auto-rename-image-seo'); ?></label></th>
                        <td><input id="max_words" name="max_words" type="number" min="3" max="16" value="<?php echo esc_attr((string) $settings['max_words']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="license_key"><?php echo esc_html__('Pro license key', 'auto-rename-image-seo'); ?></label></th>
                        <td>
                            <input id="license_key" name="license_key" type="text" class="regular-text" value="<?php echo esc_attr($license); ?>">
                            <p class="description"><?php echo esc_html__('Lite includes 20 renames/month. Any non-empty license key unlocks unlimited mode in this micro-plugin version.', 'auto-rename-image-seo'); ?></p>
                        </td>
                    </tr>
                </table>
                <p><?php echo esc_html(sprintf(__('Lite usage this month: %1$d / %2$d renames.', 'auto-rename-image-seo'), $count['count'], self::LITE_MONTHLY_LIMIT)); ?></p>
                <p><button type="submit" name="ariseo_save_settings" class="button button-primary" value="1"><?php echo esc_html__('Save Settings', 'auto-rename-image-seo'); ?></button></p>
            </form>
        </div>
        <?php
    }

    public function rename_upload(array $file): array
    {
        $settings = $this->settings();
        if ((int) $settings['enabled'] !== 1 || empty($file['name']) || empty($file['type'])) {
            return $file;
        }
        if (strpos((string) $file['type'], 'image/') !== 0) {
            return $file;
        }
        if (!$this->is_pro() && !$this->increment_lite_count()) {
            return $file;
        }

        $path = pathinfo((string) $file['name']);
        $extension = isset($path['extension']) ? strtolower(sanitize_file_name($path['extension'])) : '';
        $original = isset($path['filename']) ? (string) $path['filename'] : 'image';
        $base = $this->build_base_name($original, $settings);
        $file['name'] = $extension ? $base . '.' . $extension : $base;
        return $file;
    }

    private function build_base_name(string $original, array $settings): string
    {
        $parts = [$original];
        if ((int) $settings['include_site_name'] === 1) {
            $parts[] = get_bloginfo('name');
        }
        if ((int) $settings['include_date'] === 1) {
            $parts[] = gmdate('Y-m-d');
        }
        $base = sanitize_title(implode(' ', array_filter($parts)));
        $words = array_values(array_filter(explode('-', $base)));
        $max_words = max(3, min(16, (int) $settings['max_words']));
        $base = implode('-', array_slice($words, 0, $max_words));
        return $base !== '' ? $base : 'seo-image';
    }

    private function settings(): array
    {
        $defaults = ['enabled' => 1, 'include_site_name' => 0, 'include_date' => 0, 'max_words' => 8];
        $settings = get_option(self::OPTION_SETTINGS, []);
        return array_merge($defaults, is_array($settings) ? $settings : []);
    }

    private function is_pro(): bool
    {
        return trim((string) get_option(self::OPTION_LICENSE, '')) !== '';
    }

    private function monthly_count(): array
    {
        $current = gmdate('Y-m');
        $count = get_option(self::OPTION_COUNT, ['month' => $current, 'count' => 0]);
        if (!is_array($count) || ($count['month'] ?? '') !== $current) {
            $count = ['month' => $current, 'count' => 0];
            update_option(self::OPTION_COUNT, $count);
        }
        return ['month' => (string) $count['month'], 'count' => (int) $count['count']];
    }

    private function increment_lite_count(): bool
    {
        $count = $this->monthly_count();
        if ($count['count'] >= self::LITE_MONTHLY_LIMIT) {
            return false;
        }
        $count['count']++;
        update_option(self::OPTION_COUNT, $count);
        return true;
    }
}
