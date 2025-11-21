<?php
/**
 * Integration Tests
 * Tests form submission end-to-end flow
 * 
 * These tests verify the complete flow from form submission
 * through API calls to final response handling.
 */

namespace DocketOnboarding\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use DocketOnboarding\Tests\Helpers\TestHelper;

class FormSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Reset global state
        TestHelper::resetGlobals();
        
        global $wp_test_options, $wp_test_http_responses, $wp_test_json_response;
        $wp_test_options = [];
        $wp_test_http_responses = [];
        $wp_test_json_response = null;
        
        // Mock WordPress functions
        Functions\when('wp_verify_nonce')->alias(function() {
            return true;
        });
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
        
        // Sanitization functions
        Functions\when('sanitize_text_field')->alias(function($str) {
            return is_string($str) ? strip_tags($str) : $str;
        });
        Functions\when('wp_unslash')->alias(function($value) {
            return is_string($value) ? stripslashes($value) : $value;
        });
        
        // Options API
        Functions\when('get_option')->alias(function($option, $default = false) {
            global $wp_test_options;
            if (!isset($wp_test_options)) {
                $wp_test_options = [];
            }
            return isset($wp_test_options[$option]) ? $wp_test_options[$option] : $default;
        });
        Functions\when('update_option')->alias(function($option, $value) {
            global $wp_test_options;
            if (!isset($wp_test_options)) {
                $wp_test_options = [];
            }
            $wp_test_options[$option] = $value;
            return true;
        });
        
        // Mock API calls
        Functions\when('wp_remote_get')->alias(function($url, $args = []) {
            // Mock Trello API calls - return array of lists for board lists endpoint
            if (strpos($url, '/lists') !== false) {
                return [
                    'body' => json_encode([
                        ['id' => 'list1', 'name' => '1. Docket Team'],
                        ['id' => 'list2', 'name' => '2. In Progress']
                    ]),
                    'response' => ['code' => 200]
                ];
            }
            // Default response for other Trello endpoints
            return [
                'body' => json_encode(['id' => 'test_card_id', 'name' => 'Test Card']),
                'response' => ['code' => 200]
            ];
        });
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
        Functions\when('wp_remote_retrieve_response_code')->alias(function($response) {
            return is_array($response) && isset($response['response']['code']) ? $response['response']['code'] : 200;
        });
        Functions\when('is_wp_error')->alias(function($thing) {
            return $thing instanceof \WP_Error;
        });
    }
    
    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test complete form submission flow for Fast Build
     */
    public function test_fast_build_submission_flow()
    {
        // Prepare complete form data
        $_POST = TestHelper::createFormData('fast_build', [
            'nonce' => 'test_nonce',
            'business_name' => 'Test Dumpster Co',
            'email' => 'owner@testdumpster.com',
            'phone_number' => '555-123-4567',
            'business_email' => 'info@testdumpster.com',
            'business_address' => '123 Main St',
            'business_city' => 'Test City',
            'business_state' => 'CA',
            'website_template_selection' => 'template1',
            'select_your_docket_plan' => 'Grow'
        ]);
        
        // Configure API settings
        global $wp_test_options;
        $wp_test_options['docket_cloner_api_url'] = 'https://dockethosting5.com';
        $wp_test_options['docket_cloner_api_key'] = 'test_api_key';
        $wp_test_options['docket_disable_api_calls'] = false;
        
        // Mock successful API response
        Functions\when('wp_remote_post')->alias(function($url, $args) {
            return [
                'body' => json_encode([
                    'success' => true,
                    'data' => [
                        'site_id' => 456,
                        'site_url' => 'http://testdumpster.local',
                        'admin_url' => 'http://testdumpster.local/wp-admin'
                    ]
                ]),
                'response' => ['code' => 200]
            ];
        });
        Functions\when('wp_remote_retrieve_body')->alias(function($response) {
            return is_array($response) && isset($response['body']) ? $response['body'] : '';
        });
        Functions\when('is_wp_error')->alias(function() {
            return false;
        });
        
        // Execute submission
        ob_start();
        docket_handle_any_form_submission('fast_build');
        ob_end_clean();
        
        // Verify success response
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response, 'Should have response');
        $this->assertTrue($wp_test_json_response['success'], 'Submission should succeed');
        $this->assertArrayHasKey('site_id', $wp_test_json_response['data'], 'Should have site_id');
        $this->assertArrayHasKey('site_url', $wp_test_json_response['data'], 'Should have site_url');
    }
    
    /**
     * Test API error handling
     */
    public function test_api_error_handling()
    {
        $_POST = TestHelper::createFormData('standard_build');
        
        global $wp_test_options;
        $wp_test_options['docket_cloner_api_url'] = 'https://dockethosting5.com';
        $wp_test_options['docket_cloner_api_key'] = 'test_api_key';
        $wp_test_options['docket_disable_api_calls'] = false;
        
        // Mock API error
        Functions\when('wp_remote_post')->alias(function($url, $args) {
            return new \WP_Error('connection_failed', 'Could not connect to server');
        });
        Functions\when('is_wp_error')->alias(function($thing) {
            return $thing instanceof \WP_Error;
        });
        
        ob_start();
        docket_handle_any_form_submission('standard_build');
        ob_end_clean();
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertFalse($wp_test_json_response['success'], 'Should fail on API error');
        $this->assertStringContainsString('Failed to connect', $wp_test_json_response['data']['message']);
    }
    
    /**
     * Test all three form types can be submitted
     */
    public function test_all_form_types_submittable()
    {
        $form_types = ['fast_build', 'standard_build', 'website_vip'];
        
        global $wp_test_options;
        $wp_test_options['docket_cloner_api_url'] = 'https://test.local';
        $wp_test_options['docket_cloner_api_key'] = 'test_key';
        $wp_test_options['docket_disable_api_calls'] = true; // Disable API for this test
        
        foreach ($form_types as $form_type) {
            $_POST = TestHelper::createFormData($form_type);
            
            ob_start();
            docket_handle_any_form_submission($form_type);
            ob_end_clean();
            
            global $wp_test_json_response;
            $this->assertNotNull($wp_test_json_response, "Form type {$form_type} should have response");
            $this->assertTrue($wp_test_json_response['success'], "Form type {$form_type} should succeed");
            
            // Reset for next iteration
            $wp_test_json_response = null;
        }
    }
}

