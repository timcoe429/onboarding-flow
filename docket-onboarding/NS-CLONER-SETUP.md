# NS Cloner Integration Setup Guide

## Overview

The Docket Onboarding plugin now includes automatic NS Cloner integration that will:

1. **Automatically create new WordPress sites** when forms are submitted
2. **Replace placeholder content** in the template site with actual form data
3. **Generate unique site URLs** based on business names
4. **Log all activities** for troubleshooting

## Prerequisites

1. **WordPress Multisite** - NS Cloner requires a multisite installation
2. **NS Cloner Plugin** - Install NS Cloner (free or pro version)
3. **Template Site** - Create a template site with placeholder content

## Installation Steps

### 1. Install NS Cloner

Install NS Cloner from the WordPress.org repository or purchase the Pro version:
- Free version: Search "NS Cloner Site Copier" in WordPress admin
- Pro version: Purchase from [wpsitecloner.com](https://wpsitecloner.com)

### 2. Create Template Site

Create a template site that will be cloned for each form submission:

1. Create a new subsite in your multisite network
2. Install and configure all the plugins, themes, and content you want
3. Add placeholder content using the available placeholders (see below)

### 3. Configure Template Site Placeholders

In your template site content, use these placeholders that will be replaced with form data:

#### Available Placeholders

- `{{BUSINESS_NAME}}` - Business name from form
- `{{COMPANY_NAME}}` - Alternative for business name
- `{{PHONE}}` or `{{BUSINESS_PHONE}}` - Business phone number
- `{{EMAIL}}` or `{{BUSINESS_EMAIL}}` - Business email
- `{{ADDRESS}}` or `{{BUSINESS_ADDRESS}}` - Business address
- `{{CONTACT_NAME}}` - Contact person name
- `{{CONTACT_EMAIL}}` - Contact email address
- `{{SERVICE_AREAS}}` - Service areas
- `{{FORM_TYPE}}` - Type of form submitted (Fast Build, Standard Build, etc.)
- `{{CITY}}` - City extracted from address
- `{{STATE}}` - State extracted from address

#### Example Template Content

```html
<h1>Welcome to {{BUSINESS_NAME}}</h1>
<p>{{BUSINESS_NAME}} provides excellent junk removal services in {{CITY}}, {{STATE}}.</p>
<p>Contact us at {{PHONE}} or email us at {{EMAIL}}.</p>
<p>We serve the following areas: {{SERVICE_AREAS}}</p>
<p>Our office is located at {{ADDRESS}}.</p>
```

### 4. Configure NS Cloner Settings

1. Go to **Network Admin > Settings > NS Cloner Settings**
2. Select your template site from the dropdown
3. Save settings

### 5. Test the Integration

1. Submit a form on your site
2. Check the logs to see if the integration worked
3. Verify that a new site was created
4. Check that placeholder content was replaced

## Configuration

### Settings Page

Access the settings at **Network Admin > Settings > NS Cloner Settings**:

- **Template Site**: Select which site to use as the template
- **Available Placeholders**: See all available placeholders
- **Log File**: View integration logs

### Log File Location

Logs are stored at: `/wp-content/uploads/docket-ns-cloner.log`

### Advanced Configuration

You can customize the integration using WordPress filters:

```php
// Customize content replacements
add_filter('docket_content_replacements', function($replacements, $form_data, $form_type) {
    // Add custom replacements
    $replacements['{{CUSTOM_FIELD}}'] = $form_data['custom_field'] ?? 'Default Value';
    return $replacements;
}, 10, 3);

// Hook into site creation
add_action('docket_site_cloned', function($site_id, $form_data, $form_type) {
    // Custom actions after site is created
    error_log("New site created: $site_id for " . $form_data['business_name']);
}, 10, 3);
```

## How It Works

### Process Flow

1. **Form Submission** - User submits onboarding form
2. **Email Sent** - Confirmation email sent (existing functionality)
3. **Site Creation** - NS Cloner creates new site from template
4. **Content Replacement** - Placeholders replaced with form data
5. **Portal Creation** - Client portal created (existing functionality)
6. **User Redirect** - User redirected to portal or thank you page

### Site Naming

New sites are created with URLs based on business name:
- Business Name: "ABC Junk Removal" → Site URL: `abc-junk-removal.yoursite.com`
- If URL exists, numbers are appended: `abc-junk-removal-2.yoursite.com`

### Content Replacement

The integration replaces placeholders in:
- **Post content** (pages, posts, custom post types)
- **Post titles**
- **Post excerpts**
- **WordPress options** (site title, tagline, etc.)
- **Widget content**

## Troubleshooting

### Common Issues

1. **No site created**
   - Check that NS Cloner is installed and activated
   - Verify template site ID is correct
   - Check log file for errors

2. **Placeholders not replaced**
   - Ensure placeholders use exact format: `{{PLACEHOLDER}}`
   - Check that form fields are being submitted correctly
   - Review log file for replacement errors

3. **Site naming conflicts**
   - The system automatically handles conflicts by adding numbers
   - Check existing sites if expected name doesn't appear

### Debugging

1. **Check Log File**: View logs at the settings page or directly at `/wp-content/uploads/docket-ns-cloner.log`

2. **Test NS Cloner Manually**: Try cloning a site manually through NS Cloner admin to ensure it's working

3. **Verify Form Data**: Use browser dev tools to check form submissions include expected data

## Advanced Customization

### Custom Placeholders

Add custom placeholders by filtering the replacements:

```php
add_filter('docket_content_replacements', function($replacements, $form_data, $form_type) {
    // Add business hours from form
    if (!empty($form_data['business_hours'])) {
        $replacements['{{BUSINESS_HOURS}}'] = $form_data['business_hours'];
    }
    
    // Add custom service description
    if ($form_type === 'fast-build') {
        $replacements['{{SERVICE_DESC}}'] = 'Fast and efficient junk removal';
    }
    
    return $replacements;
}, 10, 3);
```

### Custom Site Creation Logic

Hook into the site creation process:

```php
add_action('docket_site_cloned', function($site_id, $form_data, $form_type) {
    // Switch to the new site
    switch_to_blog($site_id);
    
    // Create custom pages
    $page_data = array(
        'post_title' => 'Services - ' . $form_data['business_name'],
        'post_content' => 'Custom services content here...',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    wp_insert_post($page_data);
    
    // Restore original site
    restore_current_blog();
}, 10, 3);
```

## Support

For issues with this integration:
1. Check the log file first
2. Verify NS Cloner is working independently
3. Test with a simple template site
4. Contact support with log file contents

For NS Cloner-specific issues, refer to their documentation or support channels. 