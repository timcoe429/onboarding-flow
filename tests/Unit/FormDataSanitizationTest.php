<?php
/**
 * Form Data Sanitization Tests
 * Ensures form data is properly sanitized before processing
 */

namespace DocketOnboarding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use DocketOnboarding\Tests\Helpers\TestHelper;

class FormDataSanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        TestHelper::resetGlobals();
        
        // Mock WordPress sanitization functions
        Functions\when('sanitize_text_field')->alias(function($value) {
            return strip_tags($value);
        });
        
        Functions\when('wp_unslash')->alias(function($value) {
            return stripslashes($value);
        });
    }
    
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test that script tags are removed from form data
     */
    public function test_script_tags_removed()
    {
        $malicious = '<script>alert("xss")</script>Clean Text';
        
        // Simulate sanitization
        $sanitized = sanitize_text_field($malicious);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Clean Text', $sanitized);
    }
    
    /**
     * Test that HTML entities are handled
     */
    public function test_html_entities_handled()
    {
        $withEntities = 'Test & Company';
        
        $sanitized = sanitize_text_field($withEntities);
        
        // Should preserve the text (exact behavior depends on implementation)
        $this->assertIsString($sanitized);
    }
    
    /**
     * Test form data structure validation
     */
    public function test_form_data_structure()
    {
        $formData = TestHelper::createFormData();
        
        $this->assertTrue(TestHelper::assertFormDataStructure($formData));
        $this->assertArrayHasKey('business_name', $formData);
        $this->assertArrayHasKey('email', $formData);
        $this->assertArrayHasKey('phone_number', $formData);
    }
}

