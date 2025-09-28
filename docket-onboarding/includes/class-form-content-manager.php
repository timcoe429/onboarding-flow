<?php
/**
 * Form Content Manager
 * 
 * Manages editable content for all onboarding forms
 */

if (!defined('ABSPATH')) {
    exit;
}

class Docket_Form_Content_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'docket_form_content';
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'maybe_create_table'));
        add_action('wp_ajax_save_form_content', array($this, 'ajax_save_content'));
        add_action('wp_ajax_get_form_content', array($this, 'ajax_get_content'));
    }
    
    /**
     * Create the form content table if it doesn't exist
     */
    public function maybe_create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            form_type varchar(50) NOT NULL,
            step_number int(3) NOT NULL,
            content_key varchar(100) NOT NULL,
            content_value longtext,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_content (form_type, step_number, content_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Initialize with default content if table is empty
        $this->initialize_default_content();
    }
    
    /**
     * Initialize default content for all forms
     */
    private function initialize_default_content() {
        global $wpdb;
        
        // Check if content already exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        if ($existing > 0) {
            return;
        }
        
        $default_content = $this->get_default_content();
        
        foreach ($default_content as $content) {
            $wpdb->insert(
                $this->table_name,
                array(
                    'form_type' => $content['form_type'],
                    'step_number' => $content['step_number'],
                    'content_key' => $content['content_key'],
                    'content_value' => $content['content_value']
                ),
                array('%s', '%d', '%s', '%s')
            );
        }
    }
    
    /**
     * Get default content for all forms
     */
    private function get_default_content() {
        return array(
            // Fast Build - Step 1
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'form_title',
                'content_value' => 'Fast Build Website'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'form_subtitle',
                'content_value' => 'Let\'s start by reviewing the terms and checking your WordPress experience'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'what_youre_getting',
                'content_value' => 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, delivered in just 3 business days with zero revisions before launch.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'timeline',
                'content_value' => 'Your website will be ready in 3 days. Stock content and images will be used to meet this expedited timeline.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'what_we_need',
                'content_value' => '<li>WordPress/Elementor experience level</li><li>Basic business information and branding</li><li>Service area details</li><li>Immediate payment to begin work</li>'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 1,
                'content_key' => 'important_notes',
                'content_value' => '<li>Zero revisions before launch - customization is your responsibility</li><li>You\'ll need WordPress/Elementor knowledge to customize</li><li>Changes after launch are charged at $175/hour</li><li>Rank Math SEO plugin included</li><li>Additional plugin installation not permitted</li>'
            ),
            
            // Standard Build - Step 1
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'form_title',
                'content_value' => 'Terms & Conditions'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'form_subtitle',
                'content_value' => 'Please review and accept our terms'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'what_youre_getting',
                'content_value' => 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, including SEO optimization and mobile responsiveness.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'timeline',
                'content_value' => 'Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'what_we_need',
                'content_value' => '<li>Business information and branding materials</li><li>Service area details</li><li>Photos and content for your website</li><li>Dumpster rental/junk removal services information</li>'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'whats_included',
                'content_value' => '<li>Rank Math SEO plugin included</li><li>Additional plugin installation not permitted</li>'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 1,
                'content_key' => 'post_launch_services',
                'content_value' => 'After launch, website access for editing and Rank Math SEO plugin configuration is shared. If you\'d like to have the Docket Team work on your website, you\'ll need to upgrade to the WebsiteVIP plan.'
            ),
            
            // Website VIP - Step 1
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'form_title',
                'content_value' => 'Website with WebsiteVIP Terms & Conditions'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'form_subtitle',
                'content_value' => 'Please review and accept our terms for your WordPress experience with WebsiteVIP.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'what_youre_getting',
                'content_value' => 'A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, with ongoing management by the Docket team for $299/month. You will not receive edit access to your website once it launches.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'timeline',
                'content_value' => 'Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'what_we_need',
                'content_value' => '<li>Business information and branding materials</li><li>Service area details and pricing</li><li>Photos and content for your website</li><li>Timely feedback during the review process</li>'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 1,
                'content_key' => 'vip_benefits',
                'content_value' => '<li>Completely managed by the Docket Team</li><li>Unlimited edits</li><li>AI Chat Bot, On-Page SEO, Location Pages, Analytics, and more</li><li>You\'ll be contacted to discuss the WebsiteVIP plan upgrade after you submit this form</li>'
            ),
            
            // Step 3 content for all forms
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'form_title',
                'content_value' => 'Fast Build Template Information'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'form_subtitle',
                'content_value' => 'Important information about your Fast Build template'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'info_title',
                'content_value' => 'What\'s Included in Fast Build'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'stock_content',
                'content_value' => 'Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'no_revisions',
                'content_value' => 'Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'self_customization',
                'content_value' => 'You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'turnaround',
                'content_value' => 'Your website will be ready to launch within 3 business days of payment and domain setup.'
            ),
            array(
                'form_type' => 'fast-build',
                'step_number' => 3,
                'content_key' => 'acceptance_text',
                'content_value' => 'I understand the Fast Build limitations and am ready to proceed'
            ),
            
            // Standard Build Step 3
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'form_title',
                'content_value' => 'Website Template Information'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'form_subtitle',
                'content_value' => 'You will now get to select your website template!'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'info_title',
                'content_value' => 'Important Information About Your Template'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'customized_website',
                'content_value' => 'You will have the ability to customize your website beyond the template design once the website is launched and self-managed.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'sections_pages',
                'content_value' => 'The template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'revisions',
                'content_value' => 'We limit revisions to 1 round. You have 3 full days to review and request changes within scope. Your website is self-managed post-launch unless you upgrade to WebsiteVIP.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'additional_customizations',
                'content_value' => 'Out-of-scope customizations are charged at $175/hour.'
            ),
            array(
                'form_type' => 'standard-build',
                'step_number' => 3,
                'content_key' => 'acceptance_text',
                'content_value' => 'I understand the above clarifications on the process'
            ),
            
            // Website VIP Step 3
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'form_title',
                'content_value' => 'Website Template Information'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'form_subtitle',
                'content_value' => 'You will now get to select your website template!'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'intro_text',
                'content_value' => 'We will customize your website based on the template you choose, and the information you provide.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'customized_website',
                'content_value' => 'Please note that this is not a full custom website, it is a pre-built website theme that we add your content and images to. We do not do any additional design work or custom requests that are not already built into the website theme. This includes but isn\'t limited to logo design, product image design, adding new pages, adding new sections, and more.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'sections_pages',
                'content_value' => 'Once you choose a template, you\'ll be able to preview the pages and sections included. Please note: the template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'revisions',
                'content_value' => 'Since the website is considered a small website by industry standards, (less than 10 pages) we limit our revision round to 1. You will be notified once your website is ready to be reviewed.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'review_period',
                'content_value' => 'Once your revision round is done (You have 3 full days to fully review the site and add in any changes you see within the scope and theme that you selected) we will make the changes you request that are within the scope we can provide, and then your website will be ready to be pushed live. <strong>Once your website is live, you\'ll be on our WebsiteVIP plan where our team manages edits to your website.</strong>'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'charges',
                'content_value' => 'If you want our team to provide any customizations outside of scope and not included in the theme you chose, such as adding new pages, or if you would like our team to implement edits that you do not include in your review period, will be charged $175/hr for our team to execute these changes during the website build.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'refund_policy',
                'content_value' => 'The amount paid is only refundable if we have not fulfilled our obligations to deliver the work required under the agreement. The total paid is not refundable if the development work has been started and you terminate the contract or work through no fault of ours, or if you accept ownership of the project transferred to you. Once you upgrade to WebsiteVIP, our team can make edits to your website for you as included in the plan.'
            ),
            array(
                'form_type' => 'website-vip',
                'step_number' => 3,
                'content_key' => 'acceptance_text',
                'content_value' => 'I understand the above clarifications on the process'
            )
        );
    }
    
    /**
     * Get content for a specific form, step, and key
     */
    public function get_content($form_type, $step_number, $content_key, $default = '') {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT content_value FROM {$this->table_name} 
             WHERE form_type = %s AND step_number = %d AND content_key = %s",
            $form_type, $step_number, $content_key
        ));
        
        return $result !== null ? $result : $default;
    }
    
    /**
     * Save content for a specific form, step, and key
     */
    public function save_content($form_type, $step_number, $content_key, $content_value) {
        global $wpdb;
        
        return $wpdb->replace(
            $this->table_name,
            array(
                'form_type' => $form_type,
                'step_number' => $step_number,
                'content_key' => $content_key,
                'content_value' => $content_value
            ),
            array('%s', '%d', '%s', '%s')
        );
    }
    
    /**
     * Get all content for a specific form and step
     */
    public function get_step_content($form_type, $step_number) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT content_key, content_value FROM {$this->table_name} 
             WHERE form_type = %s AND step_number = %d",
            $form_type, $step_number
        ), ARRAY_A);
        
        $content = array();
        foreach ($results as $result) {
            $content[$result['content_key']] = $result['content_value'];
        }
        
        return $content;
    }
    
    /**
     * AJAX handler for saving content
     */
    public function ajax_save_content() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'docket_form_content_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $form_type = sanitize_text_field($_POST['form_type']);
        $step_number = intval($_POST['step_number']);
        $content_key = sanitize_text_field($_POST['content_key']);
        $content_value = stripslashes($_POST['content_value']);
        
        $result = $this->save_content($form_type, $step_number, $content_key, $content_value);
        
        wp_send_json_success(array(
            'saved' => $result !== false,
            'message' => $result !== false ? 'Content saved successfully' : 'Failed to save content'
        ));
    }
    
    /**
     * AJAX handler for getting content
     */
    public function ajax_get_content() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'docket_form_content_nonce')) {
            wp_die('Security check failed');
        }
        
        $form_type = sanitize_text_field($_POST['form_type']);
        $step_number = intval($_POST['step_number']);
        
        $content = $this->get_step_content($form_type, $step_number);
        
        wp_send_json_success($content);
    }
    
    /**
     * Get all available form types
     */
    public function get_form_types() {
        return array(
            'fast-build' => 'Fast Build',
            'standard-build' => 'Standard Build', 
            'website-vip' => 'Website VIP'
        );
    }
    
    /**
     * Get all available steps for a form type
     */
    public function get_steps_for_form($form_type) {
        global $wpdb;
        
        $steps = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT step_number FROM {$this->table_name} 
             WHERE form_type = %s ORDER BY step_number",
            $form_type
        ));
        
        return $steps;
    }
}

// Initialize the content manager
new Docket_Form_Content_Manager();
