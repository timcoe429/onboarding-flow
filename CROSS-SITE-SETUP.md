# Cross-Site Integration Setup

This guide explains how to set up the cross-site integration between the Docket Onboarding forms and the Elementor Site Cloner.

## Architecture Overview

- **Forms Site**: `yourdocketonline.com` - Where the onboarding forms are hosted
- **Cloning Site**: `dockethosting5.com` - Where WordPress Multisite and site cloning happens

## Setup Instructions

### 1. On the Cloning Site (dockethosting5.com)

1. Install and activate the **Elementor Site Cloner** plugin (network activated)
2. Go to **Network Admin → Elementor Site Cloner → API Settings**
3. Set your API key (default: `esc_docket_2025_secure_key`)
4. Note the API endpoints shown on the settings page

### 2. On the Forms Site (yourdocketonline.com)

1. Install and activate the **Docket Onboarding** plugin
2. Go to **Settings → Docket Cloner**
3. Configure:
   - **API URL**: `https://dockethosting5.com`
   - **API Key**: Same key you set on the cloning site
4. Click "Test Connection" to verify the setup

## How It Works

1. User fills out onboarding form on `yourdocketonline.com`
2. Form submission triggers API call to `dockethosting5.com`
3. Elementor Site Cloner creates the new site based on selected template
4. User is redirected to the new site's admin dashboard

## API Endpoints

### Clone Site
```
POST https://dockethosting5.com/wp-json/elementor-site-cloner/v1/clone
Headers:
  Content-Type: application/json
  X-API-Key: YOUR_API_KEY

Body:
{
  "template": "template1",
  "site_name": "Business Name",
  "site_path": "custompath", // optional
  "form_data": {} // optional form data
}
```

### Check Status
```
GET https://dockethosting5.com/wp-json/elementor-site-cloner/v1/status
Headers:
  X-API-Key: YOUR_API_KEY
```

## Template Mapping

The forms use these template selections:
- `template1` - Template 1
- `template2` - Template 2
- `template3` - Template 3
- `template4` - Template 4
- `template5` - Template 5

## Security Notes

- Always use HTTPS for API communication
- Keep your API key secure and change it from the default
- The API key can be passed either as a header (`X-API-Key`) or query parameter (`api_key`)

## Troubleshooting

### Connection Test Fails
- Verify the API URL is correct (including https://)
- Check that the Elementor Site Cloner is network activated
- Ensure the API key matches on both sites

### Site Creation Fails
- Check that template sites exist (e.g., `/template1/`)
- Verify WordPress Multisite is properly configured
- Check PHP error logs on the cloning site

### URL Redirect Issues
If cloned sites redirect to the template site:
1. Use the Debug Tools in Elementor Site Cloner
2. Run "Check URLs" to identify issues
3. Use "Force Fix URLs" if needed 