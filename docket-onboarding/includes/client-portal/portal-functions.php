<?php
/**
 * Client Portal Functions
 * Main functionality for client project tracking
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize Client Portal
 */
class DocketClientPortal {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(plugin_dir_path(__FILE__) . '../docket-onboarding.php', array($this, 'create_tables'));
    }
    
    public function init() {
        // Add rewrite rules for client URLs
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_portal_display'));
        
        // Hook into form submissions
        add_action('wp_ajax_docket_submit_website_vip_form', array($this, 'hook_form_submission'), 999);
        add_action('wp_ajax_nopriv_docket_submit_website_vip_form', array($this, 'hook_form_submission'), 999);
        add_action('wp_ajax_docket_submit_fast_build_form', array($this, 'hook_form_submission'), 999);
        add_action('wp_ajax_nopriv_docket_submit_fast_build_form', array($this, 'hook_form_submission'), 999);
        add_action('wp_ajax_docket_submit_standard_build_form', array($this, 'hook_form_submission'), 999);
        add_action('wp_ajax_nopriv_docket_submit_standard_build_form', array($this, 'hook_form_submission'), 999);
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Projects table
        $table_name = $wpdb->prefix . 'docket_client_projects';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_uuid varchar(36) NOT NULL,
            business_name varchar(255) NOT NULL,
            business_email varchar(255) NOT NULL,
            form_type varchar(50) NOT NULL,
            current_step varchar(50) DEFAULT 'submitted',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            estimated_completion date NULL,
            notes text NULL,
            PRIMARY KEY (id),
            UNIQUE KEY client_uuid (client_uuid)
        ) $charset_collate;";
        
        // Timeline table
        $table_timeline = $wpdb->prefix . 'docket_project_timeline';
        $sql_timeline = "CREATE TABLE $table_timeline (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            project_id mediumint(9) NOT NULL,
            step_name varchar(50) NOT NULL,
            status enum('pending','in_progress','completed') DEFAULT 'pending',
            completed_date datetime NULL,
            notes text NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql_timeline);
    }
    
    /**
     * Add rewrite rules for client portal URLs
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^project-status/([a-f0-9-]{36})/?$',
            'index.php?docket_project_uuid=$matches[1]',
            'top'
        );
        
        // Flush rewrite rules if needed
        if (get_option('docket_portal_rewrite_flushed') !== '1') {
            flush_rewrite_rules();
            update_option('docket_portal_rewrite_flushed', '1');
        }
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'docket_project_uuid';
        return $vars;
    }
    
    /**
     * Handle portal display
     */
    public function handle_portal_display() {
        $uuid = get_query_var('docket_project_uuid');
        if ($uuid) {
            $this->display_portal($uuid);
            exit;
        }
    }
    
    /**
     * Hook into form submissions to create portal entries
     */
    public function hook_form_submission() {
        // Only run after successful form submission
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Hook into wp_mail to detect successful email send
            add_filter('wp_mail', array($this, 'capture_successful_submission'), 10, 5);
        }
    }
    
    /**
     * Capture successful form submission and create portal
     */
    public function capture_successful_submission($args) {
        // If we get here, the main email was sent successfully
        if (isset($_POST['business_name']) && isset($_POST['business_email'])) {
            
            // Determine form type
            $form_type = 'unknown';
            if (strpos(current_filter(), 'website_vip') !== false) {
                $form_type = 'website-vip';
            } elseif (strpos(current_filter(), 'fast_build') !== false) {
                $form_type = 'fast-build';
            } elseif (strpos(current_filter(), 'standard_build') !== false) {
                $form_type = 'standard-build';
            }
            
            $this->create_client_project($_POST, $form_type);
        }
        
        return $args; // Don't interfere with original email
    }
    
    /**
     * Create client project and send portal URL
     */
    public function create_client_project($form_data, $form_type) {
        global $wpdb;
        
        $client_uuid = wp_generate_uuid4();
        $project_url = home_url("/project-status/{$client_uuid}/");
        
        // Insert project
        $project_id = $wpdb->insert(
            $wpdb->prefix . 'docket_client_projects',
            array(
                'client_uuid' => $client_uuid,
                'business_name' => sanitize_text_field($form_data['business_name']),
                'business_email' => sanitize_email($form_data['business_email']),
                'form_type' => $form_type,
                'current_step' => 'submitted',
                'created_at' => current_time('mysql')
            )
        );
        
        if ($project_id) {
            // Initialize timeline steps
            $steps = array(
                'submitted' => 'completed',
                'building' => 'pending', 
                'review' => 'pending',
                'final_touches' => 'pending',
                'launched' => 'pending'
            );
            
            foreach ($steps as $step => $status) {
                $wpdb->insert(
                    $wpdb->prefix . 'docket_project_timeline',
                    array(
                        'project_id' => $wpdb->insert_id,
                        'step_name' => $step,
                        'status' => $status,
                        'completed_date' => $status === 'completed' ? current_time('mysql') : null
                    )
                );
            }
            
            // Send client portal email
            $this->send_portal_email($form_data['business_email'], $form_data['business_name'], $project_url);
        }
    }
    
    /**
     * Send portal email to client
     */
    private function send_portal_email($email, $business_name, $portal_url) {
        $subject = "Track Your Website Progress - {$business_name}";
        
        $message = '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #185fb0;">🎉 Your Website Project Has Started!</h2>
                
                <p>Hi ' . esc_html($business_name) . ',</p>
                
                <p>Thanks for choosing us for your website! We\'ve received your order and are excited to get started.</p>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #185fb0;">📊 Track Your Progress</h3>
                    <p>We\'ve created a personal dashboard where you can track your website\'s progress in real-time:</p>
                    
                    <a href="' . esc_url($portal_url) . '" style="display: inline-block; background: #185fb0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 10px 0;">
                        🔗 View Your Project Status
                    </a>
                    
                    <p style="font-size: 14px; color: #666; margin-top: 15px;">
                        Bookmark this link! We\'ll update it as your project moves through each stage.
                    </p>
                </div>
                
                <p><strong>What happens next?</strong></p>
                <ol>
                    <li>Our team will review your requirements</li>
                    <li>We\'ll start building your website</li>
                    <li>You\'ll get a chance to review and request changes</li>
                    <li>We\'ll make final touches</li>
                    <li>Your website goes live! 🚀</li>
                </ol>
                
                <p>Questions? Just reply to this email or check your project dashboard.</p>
                
                <p>Thanks!<br>
                <strong>The Docket Team</strong></p>
            </div>
        </body>
        </html>';
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Display the client portal
     */
    private function display_portal($uuid) {
        global $wpdb;
        
        // Get project data
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}docket_client_projects WHERE client_uuid = %s",
            $uuid
        ));
        
        if (!$project) {
            wp_die('Project not found', 'Project Not Found', array('response' => 404));
        }
        
        // Get timeline data
        $timeline = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}docket_project_timeline 
             WHERE project_id = %d ORDER BY id ASC",
            $project->id
        ));
        
        // Include the portal template
        include DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/client-portal/templates/client-portal.php';
    }
}

// Initialize the portal
new DocketClientPortal();
?>
