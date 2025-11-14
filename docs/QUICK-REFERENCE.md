# Quick Reference - Onboarding Flow

## 🎯 Most Common Tasks & Where to Go

### "I need to add a new form field"
1. **HTML**: `/includes/forms/{form-type}/steps/step-X-{name}.php`
2. **Add to field list**: `/includes/form-handler.php` (search for `$fields = array`)
3. **Style it**: `/assets/docket-forms-unified.css`
4. **JS validation**: `/assets/onboarding.js` or form-specific JS

### "I need to debug a form submission"
```sql
-- Check latest submissions
SELECT * FROM wp_docket_form_submissions ORDER BY id DESC LIMIT 5;
```
- Error logs: Check `/includes/error-logger.php` output
- Browser console for JS errors

### "I need to modify what happens after form submit"
- **Form handler**: `/includes/form-handler.php` → `handle_{form_type}_submission()`
- **API call**: Look for `wp_remote_post` in form handler
- **Site creation**: `/docket-automated-site-creator/docket-automated-site-creator.php`

## 📍 File Quick Finder

| What I'm Looking For | Go To |
|---------------------|--------|
| Form steps HTML | `/includes/forms/{type}/steps/` |
| Form processing | `/includes/form-handler.php` |
| API endpoints | `/docket-automated-site-creator/` → search `register_rest_route` |
| Site cloning logic | `/elementor-site-cloner/includes/class-clone-manager.php` |
| Trello integration | `/includes/trello-sync.php` |
| Client portal | `/includes/client-portal/` |
| Form styles | `/assets/docket-forms-unified.css` |
| Form JavaScript | `/assets/docket-form-unified.js` (unified) |
| Form configuration | `/includes/forms/form-config.php` |
| Form renderer | `/includes/forms/unified-form-renderer.php` |

## 🔌 API Reference

### Create Site Endpoint
```
POST /wp-json/docket/v1/create-site
Headers: X-API-Key: {key from cloner-settings.php}
Body: {
    "template": "template1|2|3|4",
    "form_data": { ...all form fields... }
}
```

## 📝 Form Configuration System

### Unified Form Architecture
All forms now use a unified system. See `docs/FORM-ARCHITECTURE.md` for full details.

**Key Files**:
- **Form Config**: `/includes/forms/form-config.php` - Single source of truth for form differences
- **Unified Renderer**: `/includes/forms/unified-form-renderer.php` - Renders any form type
- **Unified JavaScript**: `/assets/docket-form-unified.js` - Handles all form types
- **Unified AJAX Handler**: `/includes/form-handler.php` → `docket_ajax_load_form()`

**To modify form behavior**:
1. Edit `/includes/forms/form-config.php` for form-specific settings
2. Edit `/includes/forms/unified-form-renderer.php` for rendering logic
3. Edit `/assets/docket-form-unified.js` for JavaScript behavior
4. Edit step files in `/includes/forms/{form-type}/steps/` for step content

**To add a new form type**:
1. Add configuration to `form-config.php`
2. Create step files in `includes/forms/{new-type}/steps/`
3. No changes needed to renderer or JavaScript!

## 📝 Form Types & Their Files

### Fast Build
- Handler: `docket_handle_fast_build()`
- Steps: 8 files in `/includes/forms/fast-build/steps/`
- JS: `fast-build-form.js`

### Standard Build  
- Handler: `docket_handle_standard_build()`
- Steps: 8 files in `/includes/forms/standard-build/steps/`
- JS: `standard-build-form.js`

### Website VIP
- Handler: `docket_handle_website_vip()`
- Steps: 8 files in `/includes/forms/website-vip/steps/`
- JS: `website-vip-form.js`

## 🚀 Common Commands

```bash
# Watch error log (if configured)
tail -f wp-content/debug.log

# Quick database check
wp db query "SELECT COUNT(*) FROM wp_docket_form_submissions"

# Export recent submissions
wp db export --tables=wp_docket_form_submissions --where="created_at > '2025-01-01'"
```

## ⚡ Key Functions to Remember

```php
// Get form data from DB
$submission = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}docket_form_submissions WHERE id = %d", 
    $id
));

// Trigger site creation
$response = wp_remote_post($api_url . '/wp-json/docket/v1/create-site', array(
    'headers' => array('X-API-Key' => $api_key),
    'body' => json_encode($data)
));

// Add to client portal
$wpdb->insert($wpdb->prefix . 'docket_client_sites', array(
    'client_email' => $email,
    'site_url' => $site_url,
    'status' => 'active'
));
```

## 🐛 Debug Mode Quick Toggle

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('DOCKET_DEBUG', true); // Custom debug flag
```
