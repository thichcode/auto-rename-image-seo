<?php
/**
 * Settings page view for Auto Rename Image SEO
 *
 * @package Auto Rename Image SEO
 */

if (!defined('ABSPATH')) {
    exit;
}

/** @var array $settings */
/** @var array $lite_uses */
/** @var bool $pro_active */

$current_week_uses = $lite_uses['count'] ?? 0;
$is_pro = false; // TODO: Check license
$remaining = ARISEO_Plugin::LITE_LIMIT - $current_week_uses;
?>

<div class="wrap ariseo-settings">
    <h1><?php echo esc_html__('Auto Rename Image SEO', 'auto-rename-image-seo'); ?></h1>
    
    <div class="card ariseo-card">
        <h2 class="title"><?php echo esc_html__('Settings', 'auto-rename-image-seo'); ?></h2>
        <p><?php echo esc_html__('Configure how image files are automatically renamed on upload.', 'auto-rename-image-seo'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field(ARISEO_SLUG . '_save'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ariseo_per_post"><?php echo esc_html__('Enable Automatic Renaming', 'auto-rename-image-seo'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="ariseo_per_post" name="ariseo_per_post" value="1" <?php checked($settings['per_post'] ?? 0, 1); ?>>
                            <?php echo esc_html__('Rename uploaded images based on post title', 'auto-rename-image-seo'); ?>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('When enabled, images uploaded to posts will be renamed to SEO-friendly filenames.', 'auto-rename-image-seo'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ariseo_auto_enable"><?php echo esc_html__('Auto Enable for New Sites', 'auto-rename-image-seo'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="ariseo_auto_enable" name="ariseo_auto_enable" value="1" <?php checked($settings['auto_enable'] ?? 0, 1); ?>>
                            <?php echo esc_html__('Enable by default on new installations', 'auto-rename-image-seo'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="<?php echo esc_attr(ARISEO_SLUG); ?>_save" class="button button-primary">
                    <?php echo esc_html__('Save Settings', 'auto-rename-image-seo'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <div class="card ariseo-card">
        <h2 class="title"><?php echo esc_html__('Usage Statistics', 'auto-rename-image-seo'); ?></h2>
        
        <?php if (!$is_pro): ?>
            <div class="ariseo-usage">
                <p>
                    <strong><?php echo esc_html__('This Month:', 'auto-rename-image-seo'); ?></strong>
                    <?php echo esc_html(sprintf(__('%d / %d renames used', 'auto-rename-image-seo'), $current_week_uses, ARISEO_Plugin::LITE_LIMIT)); ?>
                </p>
                <p>
                    <?php echo esc_html(sprintf(__('%d renames remaining in Lite mode', 'auto-rename-image-seo'), max(0, $remaining))); ?>
                </p>
            </div>
            
            <div class="ariseo-upsell">
                <h3><?php echo esc_html__('Upgrade to Pro', 'auto-rename-image-seo'); ?></h3>
                <p><?php echo esc_html__('Get unlimited renames, priority support, and advanced features.', 'auto-rename-image-seo'); ?></p>
                <p>
                    <strong><?php echo esc_html__('Price: $2 (one-time)', 'auto-rename-image-seo'); ?></strong>
                </p>
                <p>
                    <a href="https://gumroad.com/l/auto-rename-image-seo-pro" class="button button-primary" target="_blank">
                        <?php echo esc_html__('Get Pro License', 'auto-rename-image-seo'); ?>
                    </a>
                </p>
            </div>
        <?php else: ?>
            <div class="ariseo-usage ariseo-pro">
                <p>
                    <strong class="ariseo-pro-badge"><?php echo esc_html__('Pro Version Active', 'auto-rename-image-seo'); ?></strong>
                </p>
                <p><?php echo esc_html__('Unlimited renames enabled!', 'auto-rename-image-seo'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card ariseo-card">
        <h2 class="title"><?php echo esc_html__('How It Works', 'auto-rename-image-seo'); ?></h2>
        <ol>
            <li><?php echo esc_html__('Upload an image to a post or page', 'auto-rename-image-seo'); ?></li>
            <li><?php echo esc_html__('Plugin automatically renames the file based on your post title', 'auto-rename-image-seo'); ?></li>
            <li><?php echo esc_html__('Example: "DSC_0012.jpg" becomes "my-awesome-post-image-1.jpg"', 'auto-rename-image-seo'); ?></li>
        </ol>
        <p>
            <em><?php echo esc_html__('SEO Tip: Search engines prefer descriptive filenames with keywords!', 'auto-rename-image-seo'); ?></em>
        </p>
    </div>
    
    <div class="card ariseo-card ariseo-support">
        <h2 class="title"><?php echo esc_html__('Support & Documentation', 'auto-rename-image-seo'); ?></h2>
        <p>
            <?php echo wp_kses_post(sprintf(
                __('Need help? Check our <a href="%s" target="_blank">documentation</a> or open an issue on <a href="%s" target="_blank">GitHub</a>.', 'auto-rename-image-seo'),
                'https://github.com/thuong/auto-rename-image-seo#readme',
                'https://github.com/thuong/auto-rename-image-seo/issues'
            ));
            ?>
        </p>
    </div>
</div>

<style>
.ariseo-settings .card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin: 20px 0;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}
.ariseo-settings .card .title {
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    font-size: 1.1em;
}
.ariseo-usage {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    margin: 15px 0;
}
.ariseo-upsell {
    background: #fff3cd;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #ffc107;
    margin: 15px 0;
}
.ariseo-upsell h3 {
    margin: 0 0 10px 0;
    color: #856404;
}
.ariseo-pro-badge {
    color: #155724;
    background: #d4edda;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.9em;
}
.ariseo-support {
    background: #f8f9fa;
}
</style>
