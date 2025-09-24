# Development Notes

## Code Patterns & Conventions

### Form Field Naming
- Use underscores for field names: `business_name`, `contact_email`
- Radio buttons: `name="field_name" value="Option"`
- File uploads: `name="field_name[]"` for multiple

### JavaScript Patterns
```javascript
// Form navigation pattern
jQuery('.form-navigation .next-button').on('click', function() {
    // Validation then navigation
});

// AJAX submission pattern
jQuery.ajax({
    url: docket_ajax.ajax_url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false
});
```

### PHP Patterns
```php
// Plugin constant pattern
define('PLUGIN_NAME_VERSION', '1.0.0');
define('PLUGIN_NAME_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_NAME_URL', plugin_dir_url(__FILE__));

// Form data sanitization
$field_value = sanitize_text_field($_POST['field_name'] ?? '');
```

## Common Issues & Solutions

### Form Not Submitting
1. Check browser console for JS errors
2. Verify nonce in form handler
3. Check if required fields are filled

### Site Creation Failing
1. Verify API key matches between plugins
2. Check if template site exists
3. Look for PHP errors in debug.log

### Styles Not Loading
- All forms now use `docket-forms-unified.css`
- Check if shortcode is present on page
- Verify CSS enqueue in form handler

## Testing Checklist

### Before Deployment
- [ ] Test all 3 form types
- [ ] Verify site creation works
- [ ] Check Trello integration
- [ ] Test on mobile devices
- [ ] Validate form submissions save to database

## Useful SQL Queries

```sql
-- Recent form submissions
SELECT * FROM wp_docket_form_submissions 
ORDER BY created_at DESC LIMIT 10;

-- Sites created today
SELECT * FROM wp_docket_client_sites 
WHERE DATE(created_at) = CURDATE();

-- Form submissions by type
SELECT form_type, COUNT(*) as count 
FROM wp_docket_form_submissions 
GROUP BY form_type;
```

## Environment Variables
- No .env file used
- API keys stored in database or PHP files
- Trello credentials in `trello-sync.php`

## Quick Debugging

### Enable WordPress Debug
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Check Plugin Conflicts
- Deactivate all plugins except ours
- Switch to default theme
- Check if issue persists
