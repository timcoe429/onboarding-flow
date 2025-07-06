# Docket Onboarding + Elementor Site Cloner Integration Guide

This guide explains how to integrate the Docket Onboarding forms with the Elementor Site Cloner to automatically create new sites when forms are submitted.

## Prerequisites

1. WordPress Multisite installation
2. Both plugins installed and network activated:
   - `docket-onboarding`
   - `elementor-site-cloner`
3. Template sites created (template1, template2, etc.)

## How It Works

When a user submits an onboarding form:

1. The form data is collected and sanitized
2. The selected template (from `website_template_selection` field) is identified
3. The Elementor Site Cloner creates a new site from the template
4. The user is redirected to the new site's admin area

## Integration Points

### Form Fields

The integration looks for these form fields:

- `business_name` - Used as the new site's name
- `website_template_selection` - Determines which template to clone (template1, template2, etc.)

### Form Actions

The following AJAX actions trigger site creation:

- `docket_submit_fast_build_form`
- `docket_submit_standard_build_form`
- `docket_submit_website_vip_form`
- `docket_submit_onboarding` (generic/backward compatibility)

## Testing the Integration

1. **Verify Plugins Are Active**
   - Go to Network Admin → Plugins
   - Ensure both plugins are network activated

2. **Check Template Sites Exist**
   - Visit Network Admin → Sites
   - Verify template1, template2, etc. exist at paths like `/template1/`

3. **Test Form Submission**
   - Navigate to a page with the onboarding shortcode
   - Fill out the form selecting a template
   - Submit and verify:
     - New site is created
     - Site uses the selected template
     - Business name is set correctly
     - You're redirected to the new site admin

## Troubleshooting

### Check Error Logs

Look for messages starting with "Docket Onboarding:" in your PHP error log:

```
tail -f /path/to/error_log | grep "Docket Onboarding:"
```

### Common Issues

1. **"Template site not found"**
   - Ensure template sites exist at the expected paths
   - Check that paths match exactly (e.g., `/template1/`)

2. **"Elementor Site Cloner not found"**
   - Verify the plugin is network activated
   - Check that the plugin files are in the correct location

3. **Redirect Issues**
   - Use the Debug Tools tab in Site Cloner to check URLs
   - Force fix URLs if needed

## Customization

### Change Site URL Pattern

By default, new sites are created at `/docketsiteN/`. To change this, modify the `docket_get_next_site_number()` function in `form-handler.php`.

### Add Custom Processing

Use these hooks to add custom processing:

```php
// Before cloning
add_filter('esc_before_clone', function($params) {
    // Modify clone parameters
    return $params;
});

// After cloning
add_action('esc_after_clone', function($new_site_id, $template_site_id, $result) {
    // Perform additional tasks
    // e.g., send emails, update databases, etc.
}, 10, 3);
```

### Disable Site Creation

To disable automatic site creation (e.g., for testing), comment out the Elementor Site Cloner check in `form-handler.php`:

```php
// if (class_exists('ESC_Clone_Manager')) {
```

## Next Steps

Once the basic cloning is working, you can:

1. Add placeholder content replacement
2. Integrate with external APIs
3. Set up automated emails
4. Configure user access and permissions

For placeholder replacement, the infrastructure is already in place - you'll just need to implement the content replacement logic based on your specific requirements. 