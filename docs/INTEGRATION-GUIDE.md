# Integration Guide

This guide explains how to integrate the Docket Onboarding forms with the Elementor Site Cloner for cross-site site creation.

## Architecture Overview

**Cross-Site Setup** (Production):
- **Forms Site**: `yourdocketonline.com` - Where the onboarding forms are hosted
- **Cloning Site**: `dockethosting5.com` - Where WordPress Multisite and site cloning happens

**Single-Site Setup** (Development):
- Both plugins can run on the same WordPress installation for testing

## Prerequisites

1. WordPress Multisite installation (for production cross-site setup)
2. Both plugins installed:
   - `docket-onboarding` - On the forms site
   - `elementor-site-cloner` - On the cloning site (network activated)
3. Template sites created (template1, template2, template3, template4, template5)

## Setup Instructions

### For Cross-Site Setup (Production)

#### 1. On the Cloning Site (dockethosting5.com)

1. Install and activate the **Elementor Site Cloner** plugin (network activated)
2. Go to **Network Admin → Elementor Site Cloner → API Settings**
3. Set your API key (default: `esc_docket_2025_secure_key`)
4. Select which sites can be used as templates (by default: template1-5)
5. Note the API endpoints shown on the settings page

#### 2. On the Forms Site (yourdocketonline.com)

1. Install and activate the **Docket Onboarding** plugin
2. Go to **Settings → Docket Cloner**
3. Configure:
   - **API URL**: `https://dockethosting5.com`
   - **API Key**: Same key you set on the cloning site
4. Click "Test Connection" to verify the setup

## How It Works

When a user submits an onboarding form:

1. The form data is collected and sanitized
2. The selected template (from `website_template_selection` field) is identified
3. Form submission triggers API call to the cloning site
4. The Elementor Site Cloner creates a new site from the template
5. The user is redirected to the new site's admin area

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

## API Endpoints

### Clone Site
```
POST https://dockethosting5.com/wp-admin/admin-ajax.php
Body:
  action: esc_clone_site
  api_key: YOUR_API_KEY
  template: template1
  site_name: Business Name
  form_data: {...}
```

### Check Status
```
POST https://dockethosting5.com/wp-admin/admin-ajax.php
Body:
  action: esc_check_clone_status
  api_key: YOUR_API_KEY
  job_id: JOB_ID
```

## Template Mapping

The forms use these template selections:
- `template1` - Template 1
- `template2` - Template 2
- `template3` - Template 3
- `template4` - Template 4
- `template5` - Template 5

### Template Restrictions

You can control which sites are allowed to be cloned via the API:
1. Go to **Network Admin → Elementor Site Cloner → API Settings**
2. Check/uncheck sites in the "Allowed Templates" section
3. Only checked sites can be cloned through the API

## Troubleshooting

### Connection Test Fails
- Verify the API URL is correct (including https://)
- Check that the Elementor Site Cloner is network activated
- Ensure the API key matches on both sites

### Site Creation Fails
- Check that template sites exist (e.g., `/template1/`)
- Verify WordPress Multisite is properly configured
- Check PHP error logs on the cloning site

### Check Error Logs

Look for messages starting with "Docket Onboarding:" in your PHP error log:

```bash
tail -f /path/to/error_log | grep "Docket Onboarding:"
```

Or check Docker logs:
```bash
docker-compose logs wordpress | grep "Docket"
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

4. **API Connection Issues**
   - Verify API URL and key in Settings → Docket Cloner
   - Test connection using the "Test Connection" button
   - Check that SSL certificates are valid

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