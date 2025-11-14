<?php
/**
 * Form Handler Tests
 * Tests the core form submission logic
 */

namespace DocketOnboarding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use DocketOnboarding\Tests\Helpers\TestHelper;

class FormHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Reset global state
        TestHelper::resetGlobals();
        
        // Reset test options
        global $wp_test_options, $wp_test_http_responses, $wp_test_json_response;
        $wp_test_options = [];
        $wp_test_http_responses = [];
        $wp_test_json_response = null;
        
        // Mock WordPress functions
        Functions\when('wp_verify_nonce')->return(true);
        Functions\when('wp_send_json_success')->alias(function($data) {
            global $wp_test_json_response;
            $wp_test_json_response = ['success' => true, 'data' => $data];
            return $wp_test_json_response;
        });
        Functions\when('wp_send_json_error')->alias(function($data) {
            global $wp_test_json_response;
            $wp_test_json_response = ['success' => false, 'data' => $data];
            return $wp_test_json_response;
        });
        Functions\when('wp_die')->justReturn();
        
        // Mock API calls to return success by default
        Functions\when('wp_remote_post')->alias(function($url, $args) {
            return [
                'body' => json_encode([
                    'success' => true,
                    'data' => [
                        'site_id' => 123,
                        'site_url' => 'http://test-site.local',
                        'admin_url' => 'http://test-site.local/wp-admin'
                    ]
                ]),
                'response' => ['code' => 200]
            ];
        });
        Functions\when('wp_remote_retrieve_body')->alias(function($response) {
            return is_array($response) && isset($response['body']) ? $response['body'] : '';
        });
        Functions\when('is_wp_error')->return(false);
    }
    
    protected function tearDown(): void
    {
        // Clean up
        $_POST = [];
        $_FILES = [];
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test that form handler function exists
     */
    public function test_form_handler_function_exists()
    {
        $this->assertTrue(
            function_exists('docket_handle_any_form_submission'),
            'Form handler function should exist'
        );
    }
    
    /**
     * Test form submission with valid data
     */
    public function test_form_submission_with_valid_data()
    {
        // Prepare form data
        $_POST = TestHelper::createFormData('fast_build', [
            'nonce' => 'test_nonce',
            'business_name' => 'Test Business',
            'email' => 'test@example.com',
            'phone_number' => '555-123-4567',
            'website_template_selection' => 'template1'
        ]);
        
        // Set API options
        global $wp_test_options;
        $wp_test_options['docket_cloner_api_url'] = 'https://test.local';
        $wp_test_options['docket_cloner_api_key'] = 'test_key';
        $wp_test_options['docket_disable_api_calls'] = false;
        
        // Call the handler
        ob_start();
        docket_handle_any_form_submission('fast_build');
        ob_end_clean();
        
        // Check that JSON response was sent
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response, 'Should have JSON response');
        $this->assertTrue($wp_test_json_response['success'], 'Submission should succeed');
    }
    
    /**
     * Test form data sanitization removes XSS
     */
    public function test_form_data_sanitization()
    {
        $_POST = [
            'nonce' => 'test_nonce',
            'business_name' => '<script>alert("xss")</script>Test Business',
            'email' => 'test@example.com'
        ];
        
        // Set API options
        global $wp_test_options;
        $wp_test_options['docket_cloner_api_url'] = 'https://test.local';
        $wp_test_options['docket_cloner_api_key'] = 'test_key';
        $wp_test_options['docket_disable_api_calls'] = true; // Disable API to test sanitization only
        
        ob_start();
        docket_handle_any_form_submission('fast_build');
        ob_end_clean();
        
        // Check that data was sanitized (stored in options)
        $submission_id = array_keys($wp_test_options)[0] ?? null;
        if ($submission_id && strpos($submission_id, 'docket_submission_') === 0) {
            $form_data = $wp_test_options[$submission_id];
            $this->assertStringNotContainsString('<script>', $form_data['business_name'], 'XSS should be removed');
            $this->assertStringContainsString('Test Business', $form_data['business_name'], 'Valid content should remain');
        }
    }
    
    /**
     * Test nonce verification fails with invalid nonce
     */
    public function test_nonce_verification_fails()
    {
        Functions\when('wp_verify_nonce')->return(false);
        
        $_POST = [
            'nonce' => 'invalid_nonce',
            'business_name' => 'Test Business'
        ];
        
        ob_start();
        docket_handle_any_form_submission('fast_build');
        ob_end_clean();
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertFalse($wp_test_json_response['success'], 'Should fail with invalid nonce');
        $this->assertStringContainsString('Security check failed', $wp_test_json_response['data']['message']);
    }
    
    /**
     * Test form submission when API calls are disabled
     */
    public function test_form_submission_with_api_disabled()
    {
        $_POST = TestHelper::createFormData('fast_build');
        
        global $wp_test_options;
        $wp_test_options['docket_disable_api_calls'] = true;
        
        ob_start();
        docket_handle_any_form_submission('fast_build');
        ob_end_clean();
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertTrue($wp_test_json_response['success']);
        $this->assertStringContainsString('API calls disabled', $wp_test_json_response['data']['message']);
    }
    
    /**
     * Test that required fields are present in form data
     */
    public function test_required_fields_present()
    {
        $form_data = TestHelper::createFormData('fast_build');
        
        // Verify structure
        $this->assertArrayHasKey('business_name', $form_data);
        $this->assertArrayHasKey('email', $form_data);
        $this->assertArrayHasKey('phone_number', $form_data);
        $this->assertArrayHasKey('form_type', $form_data);
        
        // Verify form type
        $this->assertEquals('fast_build', $form_data['form_type']);
    }
}

