<?php
/**
 * Form Loading Tests
 * Tests the AJAX form loading functionality for all form types
 */

namespace DocketOnboarding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use DocketOnboarding\Tests\Helpers\TestHelper;

class FormLoadingTest extends TestCase
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
        
        // Mock file operations
        Functions\when('file_exists')->alias(function($file) {
            // Return true for step files and plugin files
            if (strpos($file, 'step-') !== false || strpos($file, DOCKET_ONBOARDING_PLUGIN_DIR) === 0) {
                return true;
            }
            return false;
        });
        
        // Capture output buffer
        ob_start();
    }
    
    protected function tearDown(): void
    {
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test that fast-build form loads successfully
     */
    public function test_fast_build_form_loads_successfully()
    {
        // Set up POST data
        $_POST = [
            'action' => 'docket_load_fast_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'basic',
            'management' => 'self',
            'buildType' => 'fast'
        ];
        
        // Call the handler
        docket_ajax_load_fast_build_form();
        
        // Check response
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response, 'Response should not be null');
        $this->assertTrue($wp_test_json_response['success'], 'Response should be successful');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data'], 'Response should contain form_html');
        $this->assertNotEmpty($wp_test_json_response['data']['form_html'], 'Form HTML should not be empty');
    }
    
    /**
     * Test that standard-build form loads successfully
     */
    public function test_standard_build_form_loads_successfully()
    {
        // Set up POST data
        $_POST = [
            'action' => 'docket_load_standard_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'managed',
            'buildType' => 'standard'
        ];
        
        // Call the handler
        docket_ajax_load_standard_build_form();
        
        // Check response
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response, 'Response should not be null');
        $this->assertTrue($wp_test_json_response['success'], 'Response should be successful');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data'], 'Response should contain form_html');
        $this->assertNotEmpty($wp_test_json_response['data']['form_html'], 'Form HTML should not be empty');
    }
    
    /**
     * Test that website-vip form loads successfully
     */
    public function test_website_vip_form_loads_successfully()
    {
        // Set up POST data
        $_POST = [
            'action' => 'docket_load_website_vip_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'vip',
            'buildType' => 'standard'
        ];
        
        // Call the handler
        docket_ajax_load_website_vip_form();
        
        // Check response
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response, 'Response should not be null');
        $this->assertTrue($wp_test_json_response['success'], 'Response should be successful');
        $this->assertArrayHasKey('form_html', $wp_test_json_response['data'], 'Response should contain form_html');
        $this->assertNotEmpty($wp_test_json_response['data']['form_html'], 'Form HTML should not be empty');
        $this->assertArrayHasKey('css_url', $wp_test_json_response['data'], 'WebsiteVIP should include css_url');
        $this->assertArrayHasKey('js_url', $wp_test_json_response['data'], 'WebsiteVIP should include js_url');
    }
    
    /**
     * Test that unified handler works for fast-build
     */
    public function test_unified_handler_fast_build()
    {
        $_POST = [
            'action' => 'docket_load_fast_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'basic',
            'management' => 'self',
            'buildType' => 'fast'
        ];
        
        docket_ajax_load_form('fast-build');
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertTrue($wp_test_json_response['success']);
    }
    
    /**
     * Test that unified handler works for standard-build
     */
    public function test_unified_handler_standard_build()
    {
        $_POST = [
            'action' => 'docket_load_standard_build_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'managed',
            'buildType' => 'standard'
        ];
        
        docket_ajax_load_form('standard-build');
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertTrue($wp_test_json_response['success']);
    }
    
    /**
     * Test that unified handler works for website-vip
     */
    public function test_unified_handler_website_vip()
    {
        $_POST = [
            'action' => 'docket_load_website_vip_form',
            'nonce' => 'test_nonce',
            'plan' => 'pro',
            'management' => 'vip',
            'buildType' => 'standard'
        ];
        
        docket_ajax_load_form('website-vip');
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertTrue($wp_test_json_response['success']);
        $this->assertArrayHasKey('css_url', $wp_test_json_response['data']);
    }
    
    /**
     * Test that invalid form type returns error
     */
    public function test_invalid_form_type_returns_error()
    {
        $_POST = [
            'action' => 'docket_load_invalid_form',
            'nonce' => 'test_nonce'
        ];
        
        docket_ajax_load_form('invalid-form-type');
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertFalse($wp_test_json_response['success']);
        $this->assertStringContainsString('Invalid form type', $wp_test_json_response['data']['message']);
    }
    
    /**
     * Test that nonce verification fails correctly
     */
    public function test_nonce_verification_fails()
    {
        // Override the nonce verification to return false for this test
        // Use when() again to override the setUp mock
        Functions\when('wp_verify_nonce')->alias(function() {
            return false;
        });
        
        $_POST = [
            'action' => 'docket_load_fast_build_form',
            'nonce' => 'invalid_nonce',
            'plan' => 'basic',
            'management' => 'self',
            'buildType' => 'fast'
        ];
        
        docket_ajax_load_fast_build_form();
        
        global $wp_test_json_response;
        $this->assertNotNull($wp_test_json_response);
        $this->assertFalse($wp_test_json_response['success']);
        $this->assertStringContainsString('Security check failed', $wp_test_json_response['data']['message']);
    }
    
    /**
     * Test that form config is loaded correctly for all types
     */
    public function test_form_config_loads_for_all_types()
    {
        $config = docket_get_form_config();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('fast-build', $config);
        $this->assertArrayHasKey('standard-build', $config);
        $this->assertArrayHasKey('website-vip', $config);
        
        // Check fast-build config
        $fastConfig = docket_get_form_config_by_type('fast-build');
        $this->assertIsArray($fastConfig);
        $this->assertEquals('fastBuildForm', $fastConfig['form_id']);
        $this->assertEquals(8, count($fastConfig['steps']));
        
        // Check standard-build config
        $standardConfig = docket_get_form_config_by_type('standard-build');
        $this->assertIsArray($standardConfig);
        $this->assertEquals('standardBuildForm', $standardConfig['form_id']);
        $this->assertEquals(8, count($standardConfig['steps']));
        
        // Check website-vip config
        $vipConfig = docket_get_form_config_by_type('website-vip');
        $this->assertIsArray($vipConfig);
        $this->assertEquals('websiteVipForm', $vipConfig['form_id']);
        $this->assertEquals(8, count($vipConfig['steps']));
        $this->assertEquals('WebsiteVIP', $vipConfig['management_type_value']);
    }
    
    /**
     * Test that form renderer generates correct HTML structure
     */
    public function test_form_renderer_generates_correct_structure()
    {
        // Add additional mocks needed for rendering
        Functions\when('wp_kses_post')->alias(function($data) {
            return strip_tags($data, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><div><span>');
        });
        Functions\when('apply_filters')->alias(function($filter, $value) {
            return $value;
        });
        
        $form_data = [
            'plan' => 'pro',
            'management' => 'vip',
            'buildType' => 'standard'
        ];
        
        ob_start();
        docket_render_form('website-vip', $form_data);
        $html = ob_get_clean();
        
        // Check for key elements
        $this->assertStringContainsString('docket-vip-form', $html);
        $this->assertStringContainsString('websiteVipForm', $html);
        $this->assertStringContainsString('docket-form-progress', $html);
        $this->assertStringContainsString('docket-progress-dots', $html);
        $this->assertStringContainsString('form-success', $html);
        $this->assertStringContainsString('Website VIP Order Submitted!', $html);
    }
}

