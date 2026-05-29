# PRD — Auto Rename Image SEO

## Problem
WordPress users upload images named IMG_1234.jpg. Bad for SEO and media organization.

## Buyer
Bloggers, WooCommerce owners, SEO freelancers.

## One sentence
Automatically rename uploaded image files into SEO-friendly filenames.

## Scope
- Rename image uploads only.
- Admin settings page.
- Pattern: post-title, original-name, date, site-name.
- Transliteration/sanitization via WordPress APIs.
- Lite limit: first 20 renames/month. Pro: unlimited with license key field.

## Non-goals
- Bulk rename existing library.
- CDN rewrite.
- AI alt text.
- Complex SEO suite.

## Security
- Capability: manage_options.
- Nonce for settings.
- sanitize_text_field, absint, checked, esc_html, esc_attr.
- No external calls unless license endpoint later enabled.

## Storage
wp_options only:
- ariseo_settings
- ariseo_monthly_count
- ariseo_license_key

## Release checklist
- PHP syntax pass
- README.txt valid basics
- Install/uninstall cleanup
- ZIP generated
