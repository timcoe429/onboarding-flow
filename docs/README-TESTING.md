# Local Testing Guide

This guide explains how to set up and run tests locally for the Docket Onboarding plugin **without needing a WordPress installation**.

## Quick Start

```bash
# 1. Install dependencies
composer install

# 2. Run all tests
composer test

# 3. Run with detailed output
composer test:watch
```

## How It Works

The test environment uses **mocks** to simulate WordPress functions, allowing you to:
- ✅ Test plugin code without WordPress installed
- ✅ Run tests quickly (no database setup needed)
- ✅ Test in isolation (no side effects)
- ✅ Catch bugs before committing code

### Architecture

```
┌─────────────────────────────────────┐
│  Your Test Code                     │
│  (tests/Unit/, tests/Integration/)  │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Brain Monkey                       │
│  (WordPress function mocking)       │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  WordPress Mocks                    │
│  (tests/mocks/wordpress-functions.php)│
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Plugin Code                        │
│  (docket-onboarding/)               │
└─────────────────────────────────────┘
```

## Test Structure

```
tests/
├── bootstrap.php                    # Initializes test environment
├── mocks/
│   └── wordpress-functions.php      # All WordPress function mocks
├── Helpers/
│   └── TestHelper.php              # Helper utilities
├── Unit/                            # Unit tests (fast, isolated)
│   ├── FormHandlerTest.php         # Form submission logic
│   └── FormDataSanitizationTest.php
└── Integration/                     # Integration tests (full flow)
    └── FormSubmissionTest.php      # End-to-end submission flow
```

## Running Tests

### Run All Tests
```bash
composer test
```

### Run Specific Test Suite
```bash
# Unit tests only
vendor/bin/phpunit tests/Unit

# Integration tests only
vendor/bin/phpunit tests/Integration

# Single test file
vendor/bin/phpunit tests/Unit/FormHandlerTest.php
```

### Run with Verbose Output
```bash
composer test:watch
# or
vendor/bin/phpunit --testdox
```

### Generate Coverage Report
```bash
composer test:coverage
# Opens coverage/index.html in browser
```

## Writing Tests

### Unit Test Example

```php
<?php
namespace DocketOnboarding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use DocketOnboarding\Tests\Helpers\TestHelper;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        TestHelper::resetGlobals();
    }
    
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    
    public function test_something()
    {
        // Arrange
        $_POST = TestHelper::createFormData('fast_build');
        
        // Act
        $result = some_function();
        
        // Assert
        $this->assertTrue($result);
    }
}
```

### Testing Form Submission

```php
public function test_form_submission()
{
    // Prepare form data
    $_POST = TestHelper::createFormData('fast_build', [
        'business_name' => 'Test Business',
        'email' => 'test@example.com'
    ]);
    
    // Set API options
    global $wp_test_options;
    $wp_test_options['docket_disable_api_calls'] = true;
    
    // Call handler
    ob_start();
    docket_handle_any_form_submission('fast_build');
    ob_end_clean();
    
    // Check response
    global $wp_test_json_response;
    $this->assertTrue($wp_test_json_response['success']);
}
```

### Testing API Calls

```php
public function test_api_error_handling()
{
    // Mock API error
    Functions\when('wp_remote_post')->alias(function() {
        return new \WP_Error('connection_failed', 'Could not connect');
    });
    
    // Test error handling
    // ...
}
```

## Pre-Commit Checklist

**Always run tests before committing:**

```bash
# 1. Run all tests
composer test

# 2. If tests pass, you're good to commit!
git add .
git commit -m "Your changes"
```

### What Tests Verify

✅ **Form submission works** - All three form types can be submitted  
✅ **Data sanitization** - XSS and malicious input is removed  
✅ **Security** - Nonce verification works correctly  
✅ **API integration** - API calls are made correctly  
✅ **Error handling** - Errors are handled gracefully  

## Debugging Failed Tests

### 1. Read the Error Message
PHPUnit shows exactly what failed:
```
FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

1) DocketOnboarding\Tests\Unit\FormHandlerTest::test_form_submission
Failed asserting that false is true.
```

### 2. Run with Verbose Output
```bash
vendor/bin/phpunit --testdox --verbose
```

### 3. Check Test Data
Make sure your test data matches real form submissions:
```php
// Use TestHelper for consistent data
$_POST = TestHelper::createFormData('fast_build');
```

### 4. Check Mocks
If a WordPress function isn't working, check `tests/mocks/wordpress-functions.php`:
```php
// Add or update mock
Functions\when('wp_new_function')->alias(function($arg) {
    return 'expected_result';
});
```

### 5. Common Issues

**Issue**: "Function not found"  
**Fix**: Function needs to be mocked in `wordpress-functions.php`

**Issue**: "Nonce verification failed"  
**Fix**: Use `'nonce' => 'test_nonce'` in your test data

**Issue**: "API call not mocked"  
**Fix**: Mock `wp_remote_post` in your test's `setUp()` method

## Adding New Tests

### When to Add Tests

Add tests when:
- ✅ Adding new form fields
- ✅ Changing form submission logic
- ✅ Modifying API integration
- ✅ Fixing bugs (add test to prevent regression)

### Test Naming Convention

- Test methods: `test_what_is_being_tested()`
- Test files: `WhatIsBeingTestedTest.php`

### Example: Testing New Feature

```php
public function test_new_feature_works()
{
    // Arrange - set up test data
    $_POST = ['new_field' => 'test_value'];
    
    // Act - call the function
    $result = handle_new_feature();
    
    // Assert - verify expected behavior
    $this->assertEquals('expected', $result);
}
```

## Mock Reference

### Common WordPress Functions

All WordPress functions are mocked. Common ones:

- `wp_verify_nonce()` - Security verification
- `wp_send_json_success()` - AJAX success response
- `wp_send_json_error()` - AJAX error response
- `get_option()` - Get WordPress option
- `update_option()` - Update WordPress option
- `wp_remote_post()` - HTTP POST request
- `sanitize_text_field()` - Sanitize input

### Customizing Mocks in Tests

```php
// Override default mock for this test
Functions\when('wp_remote_post')->alias(function($url, $args) {
    // Custom behavior for this test
    return ['body' => json_encode(['success' => false])];
});
```

## Best Practices

1. **Always reset globals** in `setUp()`:
   ```php
   TestHelper::resetGlobals();
   ```

2. **Use TestHelper** for consistent test data:
   ```php
   $_POST = TestHelper::createFormData('fast_build');
   ```

3. **Test one thing per test** - Keep tests focused

4. **Use descriptive names** - `test_form_rejects_invalid_email()` not `test_form()`

5. **Clean up in tearDown()**:
   ```php
   $_POST = [];
   $_FILES = [];
   ```

## Troubleshooting

### "Composer dependencies not installed"
```bash
composer install
```

### "Tests can only be run from the command line"
Tests must be run via CLI, not in a browser.

### "Class not found"
Make sure you're using the correct namespace:
```php
namespace DocketOnboarding\Tests\Unit;
```

### Plugin code not loading
Check `tests/bootstrap.php` - it should load all required plugin files.

## Next Steps

1. ✅ **Run tests before every commit**
2. ✅ **Add tests for new features**
3. ✅ **Fix failing tests before committing**
4. ✅ **Use tests to verify bug fixes**

## Questions?

- Check existing tests for examples
- Review `tests/mocks/wordpress-functions.php` for available mocks
- See `tests/Helpers/TestHelper.php` for utility functions

