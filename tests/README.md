# Form Flow Testing Guide

## Overview

This test suite verifies that all form flows work correctly after the refactoring. It includes tests for:
- Form loading (AJAX handlers)
- Form rendering
- Complete form flows (load → submit)
- Form configuration

## Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/FormLoadingTest.php
vendor/bin/phpunit tests/Integration/FormFlowTest.php

# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test method
vendor/bin/phpunit --filter test_fast_build_form_loads_successfully
```

## Test Files

### Unit Tests

**`tests/Unit/FormLoadingTest.php`**
- Tests AJAX form loading for all three form types
- Tests unified handler functionality
- Tests form configuration loading
- Tests error handling (invalid form types, nonce failures)
- Tests form renderer HTML generation

**Key Tests:**
- `test_fast_build_form_loads_successfully()` - Verifies fast-build form loads
- `test_standard_build_form_loads_successfully()` - Verifies standard-build form loads
- `test_website_vip_form_loads_successfully()` - Verifies website-vip form loads
- `test_unified_handler_*()` - Tests unified handler for each form type
- `test_form_config_loads_for_all_types()` - Verifies config is correct
- `test_form_renderer_generates_correct_structure()` - Verifies HTML output

### Integration Tests

**`tests/Integration/FormFlowTest.php`**
- Tests complete flows: load form → submit form
- Tests all form types can be loaded in sequence
- Verifies end-to-end functionality

**Key Tests:**
- `test_fast_build_complete_flow()` - Full fast-build flow
- `test_standard_build_complete_flow()` - Full standard-build flow
- `test_website_vip_complete_flow()` - Full website-vip flow
- `test_all_form_types_load_in_sequence()` - Verifies all forms work

## What Gets Tested

### Form Loading
✅ All three form types load via AJAX  
✅ Unified handler works for all types  
✅ Form HTML is generated correctly  
✅ WebsiteVIP returns CSS/JS URLs  
✅ Invalid form types are rejected  
✅ Nonce verification works  

### Form Configuration
✅ Config loads for all form types  
✅ Form IDs are correct  
✅ Step counts are correct  
✅ Management type formatting works  

### Form Rendering
✅ HTML structure is correct  
✅ Progress bar is generated  
✅ Success messages are correct  
✅ Form fields are included  

### Complete Flows
✅ Load → Submit works for all types  
✅ All forms can be loaded sequentially  

## Adding New Tests

When adding a new form type or modifying form behavior:

1. **Add config test** in `FormLoadingTest.php`:
```php
public function test_new_form_type_config()
{
    $config = docket_get_form_config_by_type('new-form-type');
    $this->assertIsArray($config);
    // ... assertions
}
```

2. **Add loading test**:
```php
public function test_new_form_type_loads_successfully()
{
    $_POST = [
        'action' => 'docket_load_new_form_type_form',
        'nonce' => 'test_nonce',
        // ... form data
    ];
    
    docket_ajax_load_new_form_type_form();
    
    global $wp_test_json_response;
    $this->assertTrue($wp_test_json_response['success']);
}
```

3. **Add integration test**:
```php
public function test_new_form_type_complete_flow()
{
    // Test load → submit flow
}
```

## Pre-Commit Checklist

Before committing changes, run:

```bash
# Run all tests
vendor/bin/phpunit

# Verify all tests pass
# Fix any failing tests
# Add tests for new functionality
```

## Troubleshooting

**Tests fail with "function not found"**
- Make sure `tests/bootstrap.php` loads all required files
- Check that function names match exactly

**Tests fail with "config not found"**
- Verify `form-config.php` includes the form type
- Check form type spelling (use hyphens, not underscores)

**Output buffer errors**
- Tests clean up output buffers in `tearDown()`
- If issues persist, check for unclosed output buffers

## Continuous Integration

These tests can be run in CI/CD pipelines:

```yaml
# Example GitHub Actions
- name: Run Tests
  run: |
    composer install
    vendor/bin/phpunit
```

