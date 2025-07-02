<?php
/**
 * NS Cloner Integration for Docket Onboarding
 * Handles automatic site creation and content replacement
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DocketNSClonerIntegration {
    
    private $template_site_id;
    private $log_file;
    
    public function __construct() {
        // Set default template site ID (you can change this)
        $this->template_site_id = get_option('docket_ns_cloner_template_site', 1);
        
        // Initialize log file
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/docket-ns-cloner.log';
        
        // Hook into form submissions
        add_action('docket_after_form_submission', array($this, 'trigger_site_creation'), 10, 2);
        
        // Add admin settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Trigger site creation after form submission
     */
    public function trigger_site_creation($form_data, $form_type) {
        try {
            $this->log("Starting site creation for form type: {$form_type}");
            
            // Check if NS Cloner is available
            if (!function_exists('ns_cloner_perform_clone')) {
                $this->log("ERROR: NS Cloner not found or function unavailable");
                return false;
            }
            
            // Generate site details from form data
            $site_details = $this->generate_site_details($form_data, $form_type);
            
            // Create the site using NS Cloner
            $new_site_id = $this->create_cloned_site($site_details);
            
            if ($new_site_id) {
                $this->log("Site created successfully with ID: {$new_site_id}");
                
                // Replace placeholder content
                $this->replace_placeholder_content($new_site_id, $form_data, $form_type);
                
                // Store the new site ID for later use
                $form_data['cloned_site_id'] = $new_site_id;
                $form_data['cloned_site_url'] = get_site_url($new_site_id);
                
                // Allow other plugins to hook in
                do_action('docket_site_cloned', $new_site_id, $form_data, $form_type);
                
                return $new_site_id;
            }
            
        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage());
            return false;
        }
        
        return false;
    }
    
    /**
     * Generate site details from form data
     */
    private function generate_site_details($form_data, $form_type) {
        $business_name = sanitize_text_field($form_data['business_name'] ?? '');
        $business_slug = sanitize_title($business_name);
        
        // Generate unique site name
        $site_name = $this->generate_unique_site_name($business_slug);
        
        return array(
            'source_id' => $this->template_site_id,
            'target_name' => $site_name,
            'target_title' => $business_name . ' - ' . ucwords(str_replace('-', ' ', $form_type))
        );
    }
    
    /**
     * Generate unique site name
     */
    private function generate_unique_site_name($base_name) {
        $site_name = $base_name;
        $counter = 1;
        
        // Check if site exists and increment if needed
        while ($this->site_exists($site_name)) {
            $site_name = $base_name . '-' . $counter;
            $counter++;
        }
        
        return $site_name;
    }
    
    /**
     * Check if site exists
     */
    private function site_exists($site_name) {
        $site = get_site_by_path(network_home_url(), '/' . $site_name . '/');
        return !empty($site);
    }
    
    /**
     * Create cloned site using NS Cloner
     */
    private function create_cloned_site($site_details) {
        $this->log("Attempting to clone site: " . json_encode($site_details));
        
        try {
            // Use NS Cloner's programmatic function
            $clone_args = array(
                'clone_mode' => 'core',
                'source_id' => $site_details['source_id'],
                'target_name' => $site_details['target_name'],
                'target_title' => $site_details['target_title'],
                'do_clone_users' => false, // Set to true if you want to clone users
                'do_clone_media' => true,
                'suppress_media_replacements' => false
            );
            
            // Perform the clone
            $result = ns_cloner_perform_clone($clone_args);
            
            if (isset($result['success']) && $result['success']) {
                $new_site_id = $result['target_id'];
                $this->log("Clone successful. New site ID: {$new_site_id}");
                return $new_site_id;
            } else {
                $error_msg = isset($result['message']) ? $result['message'] : 'Unknown error';
                $this->log("Clone failed: {$error_msg}");
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("Exception during cloning: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Replace placeholder content in the cloned site
     */
    private function replace_placeholder_content($site_id, $form_data, $form_type) {
        $this->log("Starting content replacement for site ID: {$site_id}");
        
        // Switch to the new site
        switch_to_blog($site_id);
        
        try {
            // Define replacement mapping
            $replacements = $this->get_replacement_mapping($form_data, $form_type);
            
            // Replace content in posts and pages
            $this->replace_in_posts($replacements);
            
            // Replace content in options (site title, tagline, etc.)
            $this->replace_in_options($replacements);
            
            // Replace content in widgets
            $this->replace_in_widgets($replacements);
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            $this->log("Content replacement completed for site ID: {$site_id}");
            
        } catch (Exception $e) {
            $this->log("Error during content replacement: " . $e->getMessage());
        }
        
        // Switch back to main site
        restore_current_blog();
    }
    
    /**
     * Get replacement mapping from form data
     */
    private function get_replacement_mapping($form_data, $form_type) {
        $replacements = array();
        
        // Basic replacements
        if (!empty($form_data['business_name'])) {
            $replacements['{{BUSINESS_NAME}}'] = $form_data['business_name'];
            $replacements['{{COMPANY_NAME}}'] = $form_data['business_name'];
        }
        
        if (!empty($form_data['business_phone_number']) || !empty($form_data['phone_number'])) {
            $phone = $form_data['business_phone_number'] ?? $form_data['phone_number'];
            $replacements['{{PHONE}}'] = $phone;
            $replacements['{{BUSINESS_PHONE}}'] = $phone;
        }
        
        if (!empty($form_data['business_email'])) {
            $replacements['{{EMAIL}}'] = $form_data['business_email'];
            $replacements['{{BUSINESS_EMAIL}}'] = $form_data['business_email'];
        }
        
        if (!empty($form_data['business_address'])) {
            $replacements['{{ADDRESS}}'] = $form_data['business_address'];
            $replacements['{{BUSINESS_ADDRESS}}'] = $form_data['business_address'];
        }
        
        if (!empty($form_data['contact_name']) || !empty($form_data['name'])) {
            $contact_name = $form_data['contact_name'] ?? $form_data['name'];
            $replacements['{{CONTACT_NAME}}'] = $contact_name;
        }
        
        if (!empty($form_data['contact_email_address']) || !empty($form_data['email'])) {
            $contact_email = $form_data['contact_email_address'] ?? $form_data['email'];
            $replacements['{{CONTACT_EMAIL}}'] = $contact_email;
        }
        
        // Service areas
        if (!empty($form_data['service_areas'])) {
            $replacements['{{SERVICE_AREAS}}'] = $form_data['service_areas'];
        }
        
        // Add more replacements based on your template needs
        $replacements['{{FORM_TYPE}}'] = ucwords(str_replace('-', ' ', $form_type));
        $replacements['{{CITY}}'] = $this->extract_city_from_address($form_data['business_address'] ?? '');
        $replacements['{{STATE}}'] = $this->extract_state_from_address($form_data['business_address'] ?? '');
        
        // Allow filtering of replacements
        return apply_filters('docket_content_replacements', $replacements, $form_data, $form_type);
    }
    
    /**
     * Replace content in posts and pages
     */
    private function replace_in_posts($replacements) {
        global $wpdb;
        
        foreach ($replacements as $search => $replace) {
            // Update post content
            $wpdb->query($wpdb->prepare("
                UPDATE {$wpdb->posts} 
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_status = 'publish'
            ", $search, $replace));
            
            // Update post titles
            $wpdb->query($wpdb->prepare("
                UPDATE {$wpdb->posts} 
                SET post_title = REPLACE(post_title, %s, %s)
                WHERE post_status = 'publish'
            ", $search, $replace));
            
            // Update post excerpts
            $wpdb->query($wpdb->prepare("
                UPDATE {$wpdb->posts} 
                SET post_excerpt = REPLACE(post_excerpt, %s, %s)
                WHERE post_status = 'publish'
            ", $search, $replace));
        }
    }
    
    /**
     * Replace content in options
     */
    private function replace_in_options($replacements) {
        foreach ($replacements as $search => $replace) {
            // Get all options that might contain the placeholder
            $options = get_alloptions();
            
            foreach ($options as $option_name => $option_value) {
                if (is_string($option_value) && strpos($option_value, $search) !== false) {
                    $new_value = str_replace($search, $replace, $option_value);
                    update_option($option_name, $new_value);
                }
            }
        }
    }
    
    /**
     * Replace content in widgets
     */
    private function replace_in_widgets($replacements) {
        $sidebars = wp_get_sidebars_widgets();
        
        foreach ($sidebars as $sidebar_id => $widgets) {
            if (empty($widgets) || !is_array($widgets)) continue;
            
            foreach ($widgets as $widget_id) {
                // Get widget type and number
                $widget_parts = explode('-', $widget_id);
                if (count($widget_parts) < 2) continue;
                
                $widget_number = array_pop($widget_parts);
                $widget_type = implode('-', $widget_parts);
                
                // Get widget options
                $widget_options = get_option('widget_' . $widget_type, array());
                
                if (isset($widget_options[$widget_number])) {
                    $widget_data = $widget_options[$widget_number];
                    $updated = false;
                    
                    // Replace content in widget data
                    foreach ($replacements as $search => $replace) {
                        $widget_data = $this->array_replace_recursive($widget_data, $search, $replace);
                        $updated = true;
                    }
                    
                    if ($updated) {
                        $widget_options[$widget_number] = $widget_data;
                        update_option('widget_' . $widget_type, $widget_options);
                    }
                }
            }
        }
    }
    
    /**
     * Recursively replace values in arrays
     */
    private function array_replace_recursive($data, $search, $replace) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->array_replace_recursive($value, $search, $replace);
            }
        } elseif (is_string($data)) {
            $data = str_replace($search, $replace, $data);
        }
        
        return $data;
    }
    
    /**
     * Extract city from address
     */
    private function extract_city_from_address($address) {
        // Basic city extraction - you may want to improve this
        $parts = explode(',', $address);
        if (count($parts) >= 2) {
            return trim($parts[count($parts) - 2]);
        }
        return '';
    }
    
    /**
     * Extract state from address
     */
    private function extract_state_from_address($address) {
        // Basic state extraction - you may want to improve this
        $parts = explode(',', $address);
        if (count($parts) >= 1) {
            $last_part = trim($parts[count($parts) - 1]);
            $state_zip = explode(' ', $last_part);
            return isset($state_zip[0]) ? $state_zip[0] : '';
        }
        return '';
    }
    
    /**
     * Log messages
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Add admin menu for settings
     */
    public function add_admin_menu() {
        if (is_multisite() && is_network_admin()) {
            add_submenu_page(
                'settings.php',
                'NS Cloner Settings',
                'NS Cloner Settings',
                'manage_network_options',
                'docket-ns-cloner-settings',
                array($this, 'admin_page')
            );
        }
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            $template_site_id = intval($_POST['template_site_id']);
            update_option('docket_ns_cloner_template_site', $template_site_id);
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $current_template_site = get_option('docket_ns_cloner_template_site', 1);
        $sites = get_sites(array('number' => 100));
        
        ?>
        <div class="wrap">
            <h1>NS Cloner Integration Settings</h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Template Site</th>
                        <td>
                            <select name="template_site_id">
                                <?php foreach ($sites as $site): ?>
                                    <option value="<?php echo $site->blog_id; ?>" <?php selected($current_template_site, $site->blog_id); ?>>
                                        <?php echo get_blog_details($site->blog_id)->blogname . ' (' . $site->domain . $site->path . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Select the template site that will be cloned for new form submissions.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2>Available Placeholders</h2>
            <p>Use these placeholders in your template site content:</p>
            <ul>
                <li><code>{{BUSINESS_NAME}}</code> - Business name from form</li>
                <li><code>{{PHONE}}</code> - Business phone number</li>
                <li><code>{{EMAIL}}</code> - Business email</li>
                <li><code>{{ADDRESS}}</code> - Business address</li>
                <li><code>{{CONTACT_NAME}}</code> - Contact person name</li>
                <li><code>{{CONTACT_EMAIL}}</code> - Contact email</li>
                <li><code>{{SERVICE_AREAS}}</code> - Service areas</li>
                <li><code>{{FORM_TYPE}}</code> - Type of form submitted</li>
                <li><code>{{CITY}}</code> - City from address</li>
                <li><code>{{STATE}}</code> - State from address</li>
            </ul>
            
            <h2>Log File</h2>
            <p>Log file location: <code><?php echo $this->log_file; ?></code></p>
            
            <?php if (file_exists($this->log_file)): ?>
                <textarea readonly style="width: 100%; height: 200px;"><?php echo esc_textarea(file_get_contents($this->log_file)); ?></textarea>
            <?php else: ?>
                <p>No log file found yet.</p>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Initialize the integration
new DocketNSClonerIntegration(); 