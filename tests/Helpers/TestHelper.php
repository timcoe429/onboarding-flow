<?php
/**
 * Test Helper Functions
 * Utilities for making tests easier to write
 */

namespace DocketOnboarding\Tests\Helpers;

class TestHelper
{
    /**
     * Create mock form submission data
     */
    public static function createFormData($formType = 'fast_build', $overrides = [])
    {
        $defaults = [
            'nonce' => 'test_nonce',
            'action' => 'docket_submit_' . $formType . '_form',
            'business_name' => 'Test Business',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '555-123-4567',
            'business_email' => 'info@testbusiness.com',
            'business_address' => '123 Main St',
            'business_city' => 'Test City',
            'business_state' => 'CA',
            'website_template_selection' => 'template1',
            'select_your_docket_plan' => 'Grow',
            'docket_plan_type' => 'Grow',
            'docket_management_type' => 'self',
            'docket_build_type' => 'fast',
            'form_type' => $formType
        ];
        
        return array_merge($defaults, $overrides);
    }
    
    /**
     * Reset WordPress globals
     */
    public static function resetGlobals()
    {
        global $wp_test_options;
        $wp_test_options = [];
        
        $_POST = [];
        $_GET = [];
        $_FILES = [];
    }
    
    /**
     * Assert form data structure
     */
    public static function assertFormDataStructure($data)
    {
        $required = ['business_name', 'email', 'phone_number'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
        
        return true;
    }
}

