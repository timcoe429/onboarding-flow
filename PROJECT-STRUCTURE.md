# Onboarding Flow Project Structure

## Quick Navigation Guide

### Core Plugins

1. **docket-onboarding/** - Main onboarding forms plugin
   - Entry: `docket-onboarding.php`
   - Handles: Fast Build, Standard Build, Website VIP forms
   - API Key: Stored in `includes/cloner-settings.php`

2. **docket-automated-site-creator/** - Receives form submissions & creates sites
   - Entry: `docket-automated-site-creator.php`
   - API Endpoints: `/wp-json/docket/v1/create-site`
   - Templates: template1-4 in allowed_templates array

3. **elementor-site-cloner/** - Handles the actual site cloning
   - Entry: `elementor-site-cloner.php`
   - Key Classes: Clone_Manager, Database_Cloner, URL_Replacer

## Key Integration Points

### Form Submission Flow
1. User fills form → `form-handler.php` processes
2. Data sent to → Site Creator API (`/create-site`)
3. Site Creator calls → Elementor Cloner to duplicate template
4. Trello card created → via `trello-sync.php`

### Important Files

**Forms Logic:**
- `includes/form-handler.php` - Main form processing
- `includes/forms/*/steps/*.php` - Individual form steps
- `assets/onboarding.js` - Frontend form navigation

**API & Integration:**
- `includes/cloner-settings.php` - API configuration
- `includes/trello-sync.php` - Trello integration
- `includes/client-portal/*` - Client portal system

**Styling:**
- `assets/docket-forms-unified.css` - All form styles
- `assets/onboarding.css` - General onboarding styles

## Database Tables

- `{prefix}_docket_form_submissions` - Stores all form data
- `{prefix}_docket_client_sites` - Tracks created sites

## Hooks & Filters

**Key Actions:**
- `docket_after_site_creation` - Fired after site is created
- `docket_form_submission_complete` - After form submitted

**Shortcodes:**
- `[docket_onboarding]` - Displays onboarding forms
- `[docket_client_portal]` - Shows client portal

## Common Tasks

### Add New Form Field
1. Add field HTML in appropriate step file
2. Update field name in form handler's field list
3. Add validation if needed in form JS

### Debug Form Submissions
1. Check `wp_docket_form_submissions` table
2. Look for errors in `includes/error-logger.php` logs

### Modify Templates
1. Template sites are: template1, template2, template3, template4
2. Changes to templates require updating allowed_templates array

## API Authentication
- Uses custom API key validation
- Key must match between onboarding & site creator plugins
