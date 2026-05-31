<?php
if (!defined('ABSPATH')) {
    exit;
}

final class ARISEO_Plugin
{
    private static ?self $instance = null;
    private const OPTION_PREFIX = 'ariseo_';
    private array $settings = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function activate(): void
    {
        $settings = [
            'enabled' => true,
            'auto_start' => 'file,title',
            'separator' => '-',
            'max_length' => 100,
            'lowercase' => true,
            'remove_accents' => true,
            'pro_upsell' => false
        ];
        update_option(self::OPTION_PREFIX . 'settings', $settings);
        set_transient(self::OPTION_PREFIX . 'activation_redirect', true, DAY_IN_SECONDS);
    }

    public static function deactivate(): void
    {
    }

    public static function uninstall(): void
    {
        delete_option(self::OPTION_PREFIX . 'settings');
        delete_option(self::OPTION_PREFIX . 'count');
    }

    private function __construct()
    {
        $this->settings = (array) get_option(self::OPTION_PREFIX . 'settings', []);
        
        // Hooks
        add_filter('wp_handle_upload_prefilter', [$this, 'rename_upload'], 10, 1);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'handle_settings_save']);
        add_action('admin_notices', [$this, 'show_activation_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_admin_page(): void
    {
        add_options_page(
            __('Auto Rename Image SEO', ARISEO_SLUG),
            __('Auto Rename Image SEO', ARISEO_SLUG),
            'manage_options',
            ARISEO_SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function renames()
    {
        $methods = explode(',', (string) ($this->settings['auto_start'] ?? ''));
        return $methods;
    }

    public function separator()
    {
        return (string) ($this->settings['separator'] ?? '-');
    }

    public function remove_accents()
    {
        return isset($this->settings['remove_accents']) && (bool) $this->settings['remove_accents'];
    }

    public function lowercase()
    {
        return isset($this->settings['lowercase']) && (bool) $this->settings['lowercase'];
    }

    public function max_length()
    {
        return (int) ($this->settings['max_length'] ?? 100);
    }

    public function encode_filename(string $name): string
    {
        $name = sanitize_file_name($name);
        $name = pathinfo($name, PATHINFO_FILENAME);
        $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        $original = $name;
        
        if ($this->remove_accents()) {
            $name = remove_accents($name);
        }
        
        $name = preg_replace('/[\s_]+/', ' ', $name);
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);
        
        $methods = $this->renames();
        $post_id = (int) ($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        
        $name_parts = [];
        foreach ($methods as $method) {
            switch ($method) {
                case 'title':
                    if ($post) {
                        $title = sanitize_text_field($post->post_title);
                        if ($title) {
                            $name_parts[] = $title;
                        }
                    }
                    break;
                case 'random':
                    $name_parts[] = 'image';
                    break;
                case 'file':
                    if ($original) {
                        $name_parts[] = $original;
                    }
                    break;
            }
        }
        
        if ($name_parts) {
            $name = implode($this->separator(), $name_parts);
        }
        
        if ($this->lowercase()) {
            $name = strtolower($name);
        }
        
        if ($this->max_length() > 0) {
            $name = substr($name, 0, $this->max_length());
        }
        
        $name = trim($name, '-._ ');
        
        return $name ? $name . '.' . $ext : $original . '.' . $ext;
    }

    public function rename_upload(array $file): array
    {
        if (!isset($file['name']) || pathinfo($file['name'], PATHINFO_EXTENSION) === '') {
            return $file;
        }

        $ext = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
        $current_name = pathinfo($file['name'], PATHINFO_FILENAME);

        if (empty($current_name)) {
            return $file;
        }

        if (strpos((string) $file['type'], 'image/' !== 0)) {
            return $file;
        }

        if (empty($this->settings['enabled'] ?? false)) {
            return $file;
        }

        $new_name = $this->encode_filename($current_name);
        if ($new_name === $current_name) {
            return $file;
        }

        $file['name'] = $new_name;
        return $file;
    }

    public function handle_settings_save(): void
    {
        if (!isset($_POST[ARISEO_SLUG . '_save'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', ARISEO_SLUG));
        }
        check_admin_referer(ARISEO_SLUG . '_save');

        $settings = [
            'enabled' => isset($_POST['enabled']) && (bool) $_POST['enabled'],
            'auto_start' => sanitize_text_field($_POST['auto_start'] ?? ''),
            'separator' => sanitize_text_field($_POST['separator'] ?? '-'),
            'max_length' => max(10, (int) ($_POST['max_length'] ?? 100)),
            'lowercase' => isset($_POST['lowercase']),
            'remove_accents' => isset($_POST['remove_accents']),
        ];
        update_option(self::OPTION_PREFIX . 'settings', $settings);
        
        
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', ARISEO_SLUG) . '</p></div>';
        });
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', ARISEO_SLUG));
        }
        $this->settings = (array) get_option(self::OPTION_PREFIX . 'settings', []);
        
        $separator = (string) ($this->settings['separator'] ?? '-');
        $styles = [
            implode(' ', ['max-width: 600px;', 'padding: 20px;', 'background: #fff;', 'border-radius: 5px;'])
        ];
        
        echo '<div class="wrap"><h1>' . esc_html__('Auto Rename Image SEO', ARISEO_SLUG) . '</h1>';
        echo '<form method="post" action=""><table class="form-table"><tbody>';
        wp_nonce_field(ARISEO_SLUG . '_save');
        
        echo '<tr><th scope="row"><label for="enabled">' . esc_html__('Enable Plugin', ARISEO_SLUG) . '</label></th>';
        echo '<td><input type="checkbox" name="enabled" id="enabled" ' . checked($this->settings['enabled'] ?? false, true, false) . '></td></tr>';
        
        echo '<tr><th scope="row"><label>' . esc_html__('Rename Method', ARISEO_SLUG) . '</label></th><td>';
        echo '<fieldset>';
        echo '<legend class="screen-reader-text"><span>' . esc_html__('Choose rename method', ARISEO_SLUG) . '</span></legend>';
        echo '<ul style="list-style: none; padding-left: 0;">';
        $choices = [
            'title' => __('Post Title', ARISEO_SLUG),
            'file' => __('Original Filename', ARISEO_SLUG),
        ];
        foreach ($choices as $value => $label) {
            $checked = in_array($value, $this->renames(), true);
            echo '<li style="margin-bottom:8px"><label><input type="checkbox" name="auto_start[]" value="' . esc_attr($value) . '" ' . checked($checked, true, false) . '> ' . esc_html($label) . '</label></li>';
        }
        echo '</ul>';
        echo '<p class="description">' . esc_html__('Sử dụng dấu phân cách:', ARISEO_SLUG) . ' <code>' . esc_html($separator) . '</code></p>';
        echo '</fieldset></td></tr>';
        
        echo '<tr><th scope="row"><label for="separator">' . esc_html__('Separator', ARISEO_SLUG) . '</label></th><td><input type="text" name="separator" id="separator" value="' . esc_attr($separator) . '" class="regular-text"></td></tr>';
        
        echo '<tr><th scope="row"><label for="max_length">' . esc_html__('Max Length', ARISEO_SLUG) . '</label></th><td><input type="number" name="max_length" id="max_length" min="10" max="200" value="' . esc_attr($this->max_length()) . '" class="small-text"></td></tr>';
        
        echo '<tr><th scope="row"><label for="remove_accents"><input type="checkbox" name="remove_accents" id="remove_accents" ' . checked($this->remove_accents(), true, false) . '> ' . esc_html__('Remove accents & special chars', ARISEO_SLUG) . '</label></th><td></td></tr>';
        echo '<tr><th scope="row"><label for="lowercase"><input type="checkbox" name="lowercase" id="lowercase" ' . checked($this->lowercase(), true, false) . '> ' . esc_html__('Convert to lowercase', ARISEO_SLUG) . '</label></th><td></td></tr>';
        
        echo '</tbody></table>';
        echo '<p><input type="submit" name="' . ARISEO_SLUG . '_save" class="button button-primary" value="' . esc_attr__('Save Settings', ARISEO_SLUG) . '"></p></form></div>';
    }

    public function show_activation_notice(): void
    {
        if (!get_transient(self::OPTION_PREFIX . 'activation_redirect')) {
            return;
        }
        delete_transient(self::OPTION_PREFIX . 'activation_redirect');
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Cảm ơn! Plugin đã sẵn sàng. Truy cập <a href="' . admin_url('options-general.php?page=' . ARISEO_SLUG) . '">Cài đặt Auto Rename Image SEO</a> để cấu hình.', ARISEO_SLUG) . '</p></div>';
    }

    public function enqueue_admin_assets(string $hook): void
    {
        if ($hook !== 'settings_page_' . ARISEO_SLUG) {
            return;
        }
    }
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name(string $filename): string
    {
        $filename = remove_accents($filename);
        $filename = html_entity_decode((string) $filename, ENT_COMPAT, 'UTF-8');
        $filename = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $filename);
        $filename = preg_replace('/[\s#\?\%\&\*\:\<\>\|\"\']/', '-', $filename);
        $filename = preg_replace('/[\.\-]+/', '.', $filename);
        $filename = preg_replace('|\.(?=[^/]*$)|', '-', $filename);
        $filename = trim($filename, '-');
        return $filename;
    }
}

if (!function_exists('remove_accents')) {
    function remove_accents(string $string): string
    {
        if (!preg_match('/\p{M}/u', $string)) {
            return $string;
        }
        $string = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE//NO-CONVERSION", $string);
        return $string ?: $string;
    }
}