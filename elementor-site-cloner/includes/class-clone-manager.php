<?php
/**
 * Clone Manager Class
 * Orchestrates the entire cloning process
 */
class ESC_Clone_Manager {
    
    private $source_site_id;
    private $destination_site_id;
    private $log_id;
    private $errors = array();
    private $placeholders = array();
    
    /**
     * Start the cloning process
     */
    public function clone_site($source_site_id, $site_name, $site_url, $placeholders = array()) {
        global $wpdb;
        
        // Start logging
        $this->log_id = $this->start_log($source_site_id);
        $this->source_site_id = $source_site_id;
        $this->placeholders = $placeholders;
        
        // Enhanced debugging for Template 4
        $is_template4 = false;
        $site = get_site($source_site_id);
        
        // Log ALL clone attempts first
        ESC_Debug_Utility::log("=== CLONE ATTEMPT START ===", [
            'source_site_id' => $source_site_id,
            'site_name' => $site_name,
            'site_url' => $site_url,
            'site_path' => $site ? $site->path : 'unknown'
        ]);
        
        if ($site && strpos($site->path, '/template4/') !== false) {
            $is_template4 = true;
            
            ESC_Debug_Utility::clear_log();
            ESC_Debug_Utility::log("=== TEMPLATE 4 CLONE ATTEMPT START ===", [
                'source_site_id' => $source_site_id,
                'site_name' => $site_name,
                'site_url' => $site_url
            ]);
            ESC_Debug_Utility::debug_template4_data($source_site_id);
        }
        
        try {
            // Step 1: Create new site
            $this->update_log_status('creating_site');
            if ($is_template4) ESC_Debug_Utility::log_clone_step('create_new_site', $source_site_id, 'started');
            
            $new_site_id = $this->create_new_site($site_name, $site_url);
            if (is_wp_error($new_site_id)) {
                if ($is_template4) ESC_Debug_Utility::log_clone_step('create_new_site', $source_site_id, 'failed', ['error' => $new_site_id->get_error_message()]);
                throw new Exception($new_site_id->get_error_message());
            }
            $this->destination_site_id = $new_site_id;
            if ($is_template4) ESC_Debug_Utility::log_clone_step('create_new_site', $source_site_id, 'completed', ['new_site_id' => $new_site_id]);
            
            // Step 2: Clone database
            $this->update_log_status('cloning_database');
            if ($is_template4) ESC_Debug_Utility::log_clone_step('clone_database', $source_site_id, 'started');
            
            $db_cloner = new ESC_Database_Cloner($source_site_id, $new_site_id);
            $result = $db_cloner->clone_database();
            if (is_wp_error($result)) {
                if ($is_template4) ESC_Debug_Utility::log_clone_step('clone_database', $source_site_id, 'failed', ['error' => $result->get_error_message()]);
                throw new Exception($result->get_error_message());
            }
            if ($is_template4) ESC_Debug_Utility::log_clone_step('clone_database', $source_site_id, 'completed');
            
            // Step 2.5: Update site name and basic settings after clone
            $this->update_site_basics($new_site_id, $site_name);
            
            // Step 3: Update URLs
            $this->update_log_status('updating_urls');
            $url_replacer = new ESC_URL_Replacer($source_site_id, $new_site_id);
            $result = $url_replacer->replace_urls();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Step 3.5: Verify critical URLs were updated
            $this->verify_url_update($new_site_id);
            
            // Step 3.6: Replace placeholders if provided
            if (!empty($this->placeholders)) {
                $this->update_log_status('replacing_placeholders');
                $placeholder_replacer = new ESC_Placeholder_Replacer($new_site_id, $this->placeholders);
                $result = $placeholder_replacer->replace_placeholders();
                if (is_wp_error($result)) {
                    throw new Exception($result->get_error_message());
                }
            }
            
            // Step 4: Clone files
            $this->update_log_status('cloning_files');
            $file_cloner = new ESC_File_Cloner($source_site_id, $new_site_id);
            $result = $file_cloner->clone_files();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Step 5: Handle Elementor-specific tasks
            $this->update_log_status('processing_elementor');
            $elementor_handler = new ESC_Elementor_Handler($new_site_id);
            $result = $elementor_handler->process_elementor_data();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Step 6: Finalize
            $this->update_log_status('finalizing');
            $this->finalize_clone($new_site_id);
            
            // Mark as complete
            $this->complete_log();
            
            return array(
                'success' => true,
                'site_id' => $new_site_id,
                'site_url' => get_site_url($new_site_id),
                'admin_url' => get_admin_url($new_site_id)
            );
            
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->fail_log($e->getMessage());
            
            // Enhanced logging for Template 4 failures
            if ($is_template4) {
                ESC_Debug_Utility::log("=== TEMPLATE 4 CLONE FAILED ===", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'ERROR');
            }
            
            // Clean up if site was created
            if (!empty($this->destination_site_id)) {
                wpmu_delete_blog($this->destination_site_id, true);
            }
            
            return new WP_Error('clone_failed', $e->getMessage());
        }
    }
    
    /**
     * Create new site in the network
     */
    private function create_new_site($site_name, $site_url) {
        // Parse the URL to get the path
        $parsed = parse_url($site_url);
        $domain = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '/';
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Create the site
        $site_id = wpmu_create_blog(
            $domain,
            $path,
            $site_name,
            $current_user->ID,
            array('public' => 1),
            get_current_network_id()
        );
        
        if (is_wp_error($site_id)) {
            return $site_id;
        }
        
        // Switch to new site to set some defaults
        switch_to_blog($site_id);
        
        // Set permalink structure
        update_option('permalink_structure', '/%postname%/');
        
        // Ensure Elementor is network activated or activate it
        if (!is_plugin_active('elementor/elementor.php')) {
            activate_plugin('elementor/elementor.php');
        }
        
        restore_current_blog();
        
        return $site_id;
    }
    
    /**
     * Update site basics after database clone
     */
    private function update_site_basics($site_id, $site_name) {
        switch_to_blog($site_id);
        
        // Update site name (blogname)
        update_option('blogname', $site_name);
        
        // Update site description if it contains template references
        $description = get_option('blogdescription');
        if (strpos(strtolower($description), 'template') !== false) {
            update_option('blogdescription', 'Another WordPress site');
        }
        
        // Ensure permalink structure is set
        update_option('permalink_structure', '/%postname%/');
        
        restore_current_blog();
    }
    
    /**
     * Finalize the cloning process
     */
    private function finalize_clone($site_id) {
        global $wpdb;
        
        // Get site details
        $site = get_site($site_id);
        if (!$site) {
            return;
        }
        
        // Construct the correct URL
        $scheme = is_ssl() ? 'https' : 'http';
        $site_url = $scheme . '://' . $site->domain . $site->path;
        $site_url_no_slash = rtrim($site_url, '/');
        
        switch_to_blog($site_id);
        
        // Force update URLs one more time
        update_option('home', $site_url_no_slash);
        update_option('siteurl', $site_url_no_slash);
        
        // Direct database update to ensure it's set
        $prefix = $wpdb->get_blog_prefix($site_id);
        $wpdb->query($wpdb->prepare(
            "UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'siteurl'",
            $site_url_no_slash
        ));
        $wpdb->query($wpdb->prepare(
            "UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'home'",
            $site_url_no_slash
        ));
        
        // Clear all caches
        wp_cache_flush();
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('siteurl', 'options');
        wp_cache_delete('home', 'options');
        
        // Clear Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        restore_current_blog();
        
        // Final cache clear
        wp_cache_flush();
    }
    
    /**
     * Verify that critical URLs were updated correctly
     */
    private function verify_url_update($site_id) {
        global $wpdb;
        
        // Get the site details
        $site = get_site($site_id);
        if (!$site) {
            return;
        }
        
        // Construct the expected URL from site details
        $scheme = is_ssl() ? 'https' : 'http';
        $expected_url = $scheme . '://' . $site->domain . $site->path;
        $expected_url_no_slash = rtrim($expected_url, '/');
        
        // Switch to the new site
        switch_to_blog($site_id);
        
        // Force update critical options
        update_option('siteurl', $expected_url_no_slash);
        update_option('home', $expected_url_no_slash);
        
        // Also update in database directly to bypass any caching
        $prefix = $wpdb->get_blog_prefix($site_id);
        
        // Update siteurl
        $wpdb->query($wpdb->prepare(
            "UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'siteurl'",
            $expected_url_no_slash
        ));
        
        // Update home
        $wpdb->query($wpdb->prepare(
            "UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'home'",
            $expected_url_no_slash
        ));
        
        restore_current_blog();
        
        // Clear any object cache that might be holding old values
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('siteurl', 'options');
        wp_cache_delete('home', 'options');
        wp_cache_flush();
    }
    
    /**
     * Logging functions
     */
    private function start_log($source_site_id) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->base_prefix . 'esc_clone_logs',
            array(
                'source_site_id' => $source_site_id,
                'destination_site_id' => 0,
                'status' => 'started',
                'user_id' => get_current_user_id()
            ),
            array('%d', '%d', '%s', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    private function update_log_status($status) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->base_prefix . 'esc_clone_logs',
            array(
                'status' => $status,
                'destination_site_id' => $this->destination_site_id ?: 0
            ),
            array('id' => $this->log_id),
            array('%s', '%d'),
            array('%d')
        );
    }
    
    private function complete_log() {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->base_prefix . 'esc_clone_logs',
            array(
                'status' => 'completed',
                'completed_at' => current_time('mysql'),
                'destination_site_id' => $this->destination_site_id
            ),
            array('id' => $this->log_id),
            array('%s', '%s', '%d'),
            array('%d')
        );
    }
    
    private function fail_log($error_message) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->base_prefix . 'esc_clone_logs',
            array(
                'status' => 'failed',
                'completed_at' => current_time('mysql'),
                'error_message' => $error_message,
                'destination_site_id' => $this->destination_site_id ?: 0
            ),
            array('id' => $this->log_id),
            array('%s', '%s', '%s', '%d'),
            array('%d')
        );
    }
    
    /**
     * Get clone status
     */
    public static function get_clone_status($log_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->base_prefix}esc_clone_logs WHERE id = %d",
            $log_id
        ));
    }
}
