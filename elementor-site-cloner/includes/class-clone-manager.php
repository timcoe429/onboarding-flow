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
    
    /**
     * Start the cloning process
     */
    public function clone_site($source_site_id, $site_name, $site_url) {
        global $wpdb;
        
        // Start logging
        $this->log_id = $this->start_log($source_site_id);
        $this->source_site_id = $source_site_id;
        
        try {
            // Step 1: Create new site
            $this->update_log_status('creating_site');
            $new_site_id = $this->create_new_site($site_name, $site_url);
            if (is_wp_error($new_site_id)) {
                throw new Exception($new_site_id->get_error_message());
            }
            $this->destination_site_id = $new_site_id;
            
            // Step 2: Clone database
            $this->update_log_status('cloning_database');
            $db_cloner = new ESC_Database_Cloner($source_site_id, $new_site_id);
            $result = $db_cloner->clone_database();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Step 3: Update URLs
            $this->update_log_status('updating_urls');
            $url_replacer = new ESC_URL_Replacer($source_site_id, $new_site_id);
            $result = $url_replacer->replace_urls();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
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
     * Finalize the cloning process
     */
    private function finalize_clone($site_id) {
        switch_to_blog($site_id);
        
        // Clear all caches
        wp_cache_flush();
        
        // Clear Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        // Update home and siteurl if needed
        $site_url = get_site_url($site_id);
        update_option('home', $site_url);
        update_option('siteurl', $site_url);
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        restore_current_blog();
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
