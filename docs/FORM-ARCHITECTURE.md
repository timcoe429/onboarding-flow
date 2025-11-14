# Form Architecture Documentation

## Overview

The Docket Onboarding plugin uses a unified form system that eliminates code duplication across three form types: `fast-build`, `standard-build`, and `website-vip`. All forms share the same rendering logic, JavaScript functionality, and AJAX handlers, with differences controlled by a centralized configuration file.

## Architecture Components

### 1. Form Configuration (`includes/forms/form-config.php`)

**Purpose**: Single source of truth for all form differences.

**Key Functions**:
- `docket_get_form_config()` - Returns configuration for all form types
- `docket_get_form_config_by_type($form_type)` - Returns config for a specific form type
- `docket_get_form_step_count($form_type)` - Returns number of steps for a form
- `docket_get_form_steps_path($form_type)` - Returns path to step files directory

**Configuration Structure**:
Each form type has:
- `form_id` - Form HTML ID (e.g., 'fastBuildForm')
- `form_class` - CSS class for form container
- `container_id` - ID for outer container div
- `steps` - Array of step numbers (e.g., [1, 2, 3, 4, 5, 6, 7, 8])
- `step_files` - Mapping of step numbers to file names
- `success_title` - Success message title
- `success_message` - Success message body
- `success_note` - Additional success note
- `action_name` - AJAX action name for form submission
- `js_file` - Legacy JS file name (deprecated, now using unified JS)
- `plan_type_format` - How to format plan type ('raw' or 'ucfirst')
- `management_type_format` - How to format management type ('raw', 'ucfirst', or 'fixed')
- `management_type_value` - Fixed value if format is 'fixed'

### 2. Unified Form Renderer (`includes/forms/unified-form-renderer.php`)

**Purpose**: Single function to render any form type based on configuration.

**Key Function**:
- `docket_render_form($form_type, $form_data)` - Renders a form based on type and data

**What it does**:
1. Loads configuration for the specified form type
2. Formats plan and management types according to config
3. Generates progress bar HTML based on step count
4. Includes step files dynamically based on config
5. Outputs form-specific JavaScript configuration object
6. Generates success screen HTML

**Form Data Structure**:
```php
$form_data = array(
    'plan' => 'basic', // or 'premium', etc.
    'management' => 'self', // or 'managed', 'vip'
    'buildType' => 'fast' // or 'standard'
);
```

### 3. Unified JavaScript (`assets/docket-form-unified.js`)

**Purpose**: Single JavaScript file that handles all form types.

**Key Function**:
- `initDocketForm(config)` - Initializes form with configuration object

**Configuration Object** (passed from PHP):
```javascript
window.docketFormConfig = {
    formId: '#fastBuildForm',
    formType: 'fast-build',
    actionName: 'docket_submit_fast_build_form',
    stepCount: 8
};
```

**Features**:
- Navigation (next/prev buttons)
- Step validation
- Form submission via AJAX
- Progress bar updates
- Session storage for step persistence
- Dynamic field logic (e.g., logo upload toggle)

**Auto-detection**: If config is not provided, the script attempts to auto-detect the form type from the DOM.

### 4. Unified AJAX Handler (`includes/form-handler.php`)

**Purpose**: Single AJAX handler for loading all form types.

**Key Function**:
- `docket_ajax_load_form($form_type)` - Unified handler for loading any form

**What it does**:
1. Verifies nonce for security
2. Determines form type (from parameter or POST data)
3. Validates form type against config
4. Loads CSS and JavaScript files
5. Renders form using unified renderer
6. Returns JSON response with form HTML

**Legacy Wrappers** (for backward compatibility):
- `docket_ajax_load_fast_build_form()` - Wrapper calling unified handler
- `docket_ajax_load_standard_build_form()` - Wrapper calling unified handler
- `docket_ajax_load_website_vip_form()` - Wrapper calling unified handler

## Form Types

### Fast Build (`fast-build`)
- **Steps**: 8 steps (step 5 is service-areas)
- **Timeline**: 3 business days
- **Plan Format**: Raw (no capitalization)
- **Management Format**: Raw

### Standard Build (`standard-build`)
- **Steps**: 8 steps (step 5 is content)
- **Timeline**: Standard timeline
- **Plan Format**: ucfirst (capitalized)
- **Management Format**: Raw

### Website VIP (`website-vip`)
- **Steps**: 8 steps (step 5 is content)
- **Timeline**: 21-30 business days
- **Plan Format**: ucfirst (capitalized)
- **Management Format**: Fixed to "WebsiteVIP"

## Step Files Structure

Step files are located in:
- `includes/forms/{form-type}/steps/step-{number}-{name}.php`

Each step file contains:
- HTML structure for that step
- Form fields specific to that step
- Content pulled from database via `docket_get_form_content()`

## Adding a New Form Type

1. **Add configuration** to `form-config.php`:
```php
'new-form-type' => array(
    'form_id' => 'newFormForm',
    'form_class' => 'docket-new-form',
    // ... other config values
),
```

2. **Create step files** in `includes/forms/new-form-type/steps/`

3. **No changes needed** to renderer, JavaScript, or AJAX handler - they work with any form type!

## Backward Compatibility

The old form files are still included for backward compatibility:
- `includes/forms/fast-build/fast-build-form.php`
- `includes/forms/standard-build/standard-build-form.php`
- `includes/forms/website-vip/website-vip-form.php`

These can be removed once the unified system is fully verified and tested.

## Benefits of Unified System

1. **DRY Principle**: No code duplication - changes made once apply to all forms
2. **Maintainability**: Single source of truth for form differences
3. **Consistency**: All forms behave identically
4. **Extensibility**: Easy to add new form types
5. **AI-Friendly**: Clear structure makes it easier for Cursor/AI agents to understand and modify

## Testing Checklist

When modifying forms, verify:
- [ ] All three form types load correctly via AJAX
- [ ] Progress bar shows correct step count
- [ ] Navigation (next/prev) works on all steps
- [ ] Validation works on each step
- [ ] Form submission works correctly
- [ ] Redirect after submission works
- [ ] Session storage persists step correctly
- [ ] Success messages display correctly

## File Locations

- Configuration: `includes/forms/form-config.php`
- Renderer: `includes/forms/unified-form-renderer.php`
- JavaScript: `assets/docket-form-unified.js`
- AJAX Handler: `includes/form-handler.php` (function `docket_ajax_load_form()`)
- Step Files: `includes/forms/{form-type}/steps/step-{number}-{name}.php`

