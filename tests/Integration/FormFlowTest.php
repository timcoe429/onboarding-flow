<?php
/**
 * Form Flow Integration Tests
 * Tests the complete form flow from loading to submission
 */

namespace DocketOnboarding\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use DocketOnboarding\Tests\Helpers\TestHelper;

class FormFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Reset global state
        TestHelper::resetGlobals();
        
        // Reset test options and responses
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
        Functions\when('admin_url')->alias(function() {
            return 'http://localhost/wp-admin/admin-ajax.php';
        });
        Functions\when('wp_create_nonce')->alias(function() {
            return 'test_nonce_123';
        });
        Functions\when('esc_url')->returnArg();
        Functions\when('esc_attr')->returnArg();
        Functions\when('esc_js')->returnArg();
        Functions\when('esc_html')->returnArg();
        Functions\when('sanitize_text_field')->alias(function($str) {
            return $str;
        });
        Functions\when('wp_unslash')->alias(function($value) {
            return $value;
        });
        Functions\when('wp_nonce_field')->alias(function() {
            return '<input type="hidden" name="nonce" value="test_nonce" />';
        });
        Functions\when('error_log')->justReturn();
        Functions\when('wp_kses_post')->alias(function($data) {
            return strip_tags($data, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><div><span>');
        });
        Functions\when('apply_filters')->alias(function($filter, $value) {
            return $value;
        });
        
        // Options API
        Functions\when('update_option')->alias(function($option, $value) {
            global $wp_test_options;
            if (!isset($wp_test_options)) {
                $wp_test_options = [];
            }
            $wp_test_options[$option] = $value;
            return true;
        });
        
        // Mock API calls
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
        Functions\when('wp_remote_retrieve_body')->alias(function($response) {
            return is_array($response) && isset($response['body']) ? $response['body'] : '';
        });
        Functions\when('wp_remote_retrieve_response_code')->alias(function($response) {
            return is_array($response) && isset($response['response']['code']) ? $response['response']['code'] : 200;
        });
        Functions\when('is_wp_error')->alias(function() {
            return false;
        });
        
        Functions\when('file_exists')->alias(function($file) {
            // Return true for step files and plugin files
            if (strpos($file, 'step-') !== false || strpos($file, DOCKET_ONBOARDING_PLUGIN_DIR) === 0) {
                return true;
            }
            return false;
        });
        Functions\when('update_option')->alias(function($key, $value) {
            global $wp_test_options;
            $wp_test_options[$key] = $value;
            return true;
        });
        Functions\when('get_option')->alias(function($key, $default = false) {
            global $wp_test_options;
            return isset($wp_test_options[$key]) ? $wp_test_options[$key] : $default;
        });
        
        // Mock API calls
        Functions\when('wp_remote_post')->alias(function($url, $args) {
            return [
                'body' => json_encode([
                    'success' => true,
                    'data' => [
                        'site_id' => 123,
                        'site_url' => 'https://example.com/test-site',
                        'admin_url' => 'https://example.com/test-site/wp-admin'
                    ]
                ]),
                'response' => ['code' => 200]
            ];
        });
        
        // Mock file operations
        Functions\when('wp_upload_bits')->alias(function() {
            return [
                'file' => '/tmp/test-file.jpg',
                'url' => 'http://localhost/wp-content/uploads/test-file.jpg',
                'error' => false
            ];
        });
    }
    
    protected function tearDown(): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test complete fast-build flow: load form -> submit form
     */
    public function test_fast_build_complete_flow()
    {
        // Step 1: Load the form
        $_POST = [
            'action' => 'docket_load_fast_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'basic',
            'management' => 'self',
            'buildType' => 'fast'
        ];
        
        docket_ajax_load_fast_build_form();
        
        global $wp_test_json_response;
        $this->assertTrue($wp_test_json_response['success'], 'Form should load successfully');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data']);
        
        // Step 2: Submit the form
        $_POST = [
            'action' => 'docket_submit_fast_build_form',
            'nonce' => 'test_nonce',
            'docket_plan_type' => 'Basic',
            'docket_management_type' => 'self',
            'docket_build_type' => 'fast',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'business_name' => 'Test Business',
            'phone_number' => '555-1234',
            'business_email' => 'business@example.com',
            'business_address' => '123 Test St',
            'business_city' => 'Test City',
            'business_state' => 'CA',
            'website_template_selection' => 'template1',
            'accept_terms' => 'accepted',
            'wordpress_exp' => 'Intermediate'
        ];
        
        $wp_test_json_response = null;
        docket_handle_fast_build_submission();
        
        $this->assertNotNull($wp_test_json_response, 'Submission should return response');
        // Note: Actual success depends on API being enabled, but structure should be correct
    }
    
    /**
     * Test complete standard-build flow: load form -> submit form
     */
    public function test_standard_build_complete_flow()
    {
        // Step 1: Load the form
        $_POST = [
            'action' => 'docket_load_standard_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'managed',
            'buildType' => 'standard'
        ];
        
        docket_ajax_load_standard_build_form();
        
        global $wp_test_json_response;
        $this->assertTrue($wp_test_json_response['success'], 'Form should load successfully');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data']);
        
        // Step 2: Submit the form
        $_POST = [
            'action' => 'docket_submit_standard_build_form',
            'nonce' => 'test_nonce',
            'docket_plan_type' => 'Pro',
            'docket_management_type' => 'managed',
            'docket_build_type' => 'standard',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'business_name' => 'Test Business',
            'website_template_selection' => 'template2',
            'accept_terms' => 'accepted',
            'wordpress_exp' => 'Expert'
        ];
        
        $wp_test_json_response = null;
        docket_handle_standard_build_submission();
        
        $this->assertNotNull($wp_test_json_response, 'Submission should return response');
    }
    
    /**
     * Test complete website-vip flow: load form -> submit form
     */
    public function test_website_vip_complete_flow()
    {
        // Step 1: Load the form
        $_POST = [
            'action' => 'docket_load_website_vip_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'vip',
            'buildType' => 'standard'
        ];
        
        docket_ajax_load_website_vip_form();
        
        global $wp_test_json_response;
        $this->assertTrue($wp_test_json_response['success'], 'Form should load successfully');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data']);
        $this->assertArrayHasKey('css_url', $wp_test_json_response['data']);
        $this->assertArrayHasKey('js_url', $wp_test_json_response['data']);
        
        // Step 2: Submit the form
        $_POST = [
            'action' => 'docket_submit_website_vip_form',
            'nonce' => 'test_nonce',
            'docket_plan_type' => 'Pro',
            'docket_management_type' => 'WebsiteVIP',
            'docket_build_type' => 'standard',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'business_name' => 'Test Business',
            'website_template_selection' => 'template3',
            'accept_terms' => 'accepted',
            'wordpress_exp' => 'Beginner'
        ];
        
        $wp_test_json_response = null;
        docket_handle_website_vip_submission();
        
        $this->assertNotNull($wp_test_json_response, 'Submission should return response');
    }
    
    /**
     * Test that all three form types can be loaded in sequence
     */
    public function test_all_form_types_load_in_sequence()
    {
        $form_types = ['fast-build', 'standard-build', 'website-vip'];
        
        foreach ($form_types as $form_type) {
            $_POST = [
                'action' => 'docket_load_' . str_replace('-', '_', $form_type) . '_form',
                'nonce' => 'test_nonce',
                'plan' => 'pro',
                'management' => $form_type === 'website-vip' ? 'vip' : 'managed',
                'buildType' => 'standard'
            ];
            
            global $wp_test_json_response;
            $wp_test_json_response = null;
            
            // Call appropriate handler
            if ($form_type === 'fast-build') {
                docket_ajax_load_fast_build_form();
            } elseif ($form_type === 'standard-build') {
                docket_ajax_load_standard_build_form();
            } else {
                docket_ajax_load_website_vip_form();
            }
            
            $this->assertNotNull($wp_test_json_response, "Form type {$form_type} should return response");
            $this->assertTrue($wp_test_json_response['success'], "Form type {$form_type} should load successfully");
            $this->assertArrayHasKey('form_html', $wp_test_json_response['data'], "Form type {$form_type} should have form_html");
        }
    }
}

