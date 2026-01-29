<?php
/**
 * Database Cloner Class
 * Handles all database cloning operations
 */
class ESC_Database_Cloner {
    
    private $source_site_id;
    private $destination_site_id;
    private $source_prefix;
    private $destination_prefix;
    private $tables_to_skip = array();
    
    public function __construct($source_site_id, $destination_site_id) {
        global $wpdb;
        
        $this->source_site_id = $source_site_id;
        $this->destination_site_id = $destination_site_id;
        
        // Get table prefixes
        $this->source_prefix = $wpdb->get_blog_prefix($source_site_id);
        $this->destination_prefix = $wpdb->get_blog_prefix($destination_site_id);
        
        // Tables that should not be cloned
        $this->tables_to_skip = array(
            'users',
            'usermeta',
            'blogs',
            'blog_versions',
            'registration_log',
            'signups',
            'site',
            'sitemeta',
            'sitecategories'
        );
    }
    
    /**
     * Clone all database tables from source to destination
     */
    public function clone_database() {
        global $wpdb;
        
        try {
            // Get all tables for the source site
            $tables = $this->get_site_tables();
            
            // Start transaction
            $wpdb->query('START TRANSACTION');
            
            foreach ($tables as $table) {
                $result = $this->clone_table($table);
                if (is_wp_error($result)) {
                    throw new Exception($result->get_error_message());
                }
            }
            
            // Update prefixed options (e.g., user_roles) to use destination prefix
            $this->update_prefixed_options();
            
            // Clone user relationships
            $this->clone_user_relationships();
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_clone_failed', $e->getMessage());
        }
    }
    
    /**
     * Get all tables for the source site
     */
    private function get_site_tables() {
        global $wpdb;
        
        $tables = array();
        
        // Get all tables with the source prefix
        $all_tables = $wpdb->get_col("SHOW TABLES LIKE '{$this->source_prefix}%'");
        
        foreach ($all_tables as $table) {
            // Extract table name without prefix
            $table_name = str_replace($this->source_prefix, '', $table);
            
            // Skip network-wide tables
            if (in_array($table_name, $this->tables_to_skip)) {
                continue;
            }
            
            $tables[] = $table_name;
        }
        
        return $tables;
    }
    
    /**
     * Clone a single table
     */
    private function clone_table($table_name) {
        global $wpdb;
        
        $source_table = $this->source_prefix . $table_name;
        $destination_table = $this->destination_prefix . $table_name;
        
        try {
            // Drop destination table if it exists
            $wpdb->query("DROP TABLE IF EXISTS `$destination_table`");
            
            // Create table structure
            $create_table = $wpdb->get_var("SHOW CREATE TABLE `$source_table`", 1);
            $create_table = str_replace($source_table, $destination_table, $create_table);
            $wpdb->query($create_table);
            
            // Copy data in batches to handle large tables
            $offset = 0;
            $batch_size = 1000;
            
            do {
                $rows = $wpdb->get_results(
                    "SELECT * FROM `$source_table` LIMIT $offset, $batch_size",
                    ARRAY_A
                );
                
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $wpdb->insert($destination_table, $row);
                    }
                }
                
                $offset += $batch_size;
                
            } while (count($rows) === $batch_size);
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('table_clone_failed', 
                sprintf('Failed to clone table %s: %s', $table_name, $e->getMessage())
            );
        }
    }
    
    /**
     * Update prefixed option names in the options table
     * Replaces source prefix with destination prefix in option_name values
     */
    private function update_prefixed_options() {
        global $wpdb;
        
        // Skip if prefixes are the same or source prefix is empty
        if (empty($this->source_prefix) || $this->source_prefix === $this->destination_prefix) {
            return;
        }
        
        $destination_options_table = $this->destination_prefix . 'options';
        
        // Query for all options that contain the source prefix in their option_name
        // Use LIKE with escaped source prefix to find matching options
        $like_pattern = $wpdb->esc_like($this->source_prefix) . '%';
        
        $prefixed_options = $wpdb->get_results($wpdb->prepare(
            "SELECT option_id, option_name FROM `{$destination_options_table}` 
             WHERE option_name LIKE %s",
            $like_pattern
        ));
        
        if (empty($prefixed_options)) {
            return;
        }
        
        // Update each option_name to replace source prefix with destination prefix
        foreach ($prefixed_options as $option) {
            // Only update if the option_name actually starts with the source prefix
            // This prevents partial matches in the middle of option names
            if (strpos($option->option_name, $this->source_prefix) === 0) {
                $new_option_name = str_replace($this->source_prefix, $this->destination_prefix, $option->option_name);
                
                // Update the option_name
                $wpdb->update(
                    $destination_options_table,
                    array('option_name' => $new_option_name),
                    array('option_id' => $option->option_id),
                    array('%s'),
                    array('%d')
                );
            }
        }
    }
    
    /**
     * Clone user relationships (capabilities, roles)
     */
    private function clone_user_relationships() {
        global $wpdb;
        
        // Get all users from source site
        $source_cap_key = $this->source_prefix . 'capabilities';
        $source_level_key = $this->source_prefix . 'user_level';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, meta_value FROM {$wpdb->usermeta} 
             WHERE meta_key = %s",
            $source_cap_key
        ));
        
        // Copy capabilities to destination site
        $dest_cap_key = $this->destination_prefix . 'capabilities';
        $dest_level_key = $this->destination_prefix . 'user_level';
        
        foreach ($users as $user) {
            // Check if user already has capabilities for destination site
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->usermeta} 
                 WHERE user_id = %d AND meta_key = %s",
                $user->user_id,
                $dest_cap_key
            ));
            
            if (!$existing) {
                // Copy capabilities
                $wpdb->insert(
                    $wpdb->usermeta,
                    array(
                        'user_id' => $user->user_id,
                        'meta_key' => $dest_cap_key,
                        'meta_value' => $user->meta_value
                    ),
                    array('%d', '%s', '%s')
                );
                
                // Copy user level
                $user_level = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$wpdb->usermeta} 
                     WHERE user_id = %d AND meta_key = %s",
                    $user->user_id,
                    $source_level_key
                ));
                
                if ($user_level) {
                    $wpdb->insert(
                        $wpdb->usermeta,
                        array(
                            'user_id' => $user->user_id,
                            'meta_key' => $dest_level_key,
                            'meta_value' => $user_level
                        ),
                        array('%d', '%s', '%s')
                    );
                }
            }
        }
    }
    
    /**
     * Get database size for a site
     */
    public static function get_site_database_size($site_id) {
        global $wpdb;
        
        $prefix = $wpdb->get_blog_prefix($site_id);
        $size = 0;
        
        $tables = $wpdb->get_results(
            "SELECT table_name, data_length + index_length AS size 
             FROM information_schema.TABLES 
             WHERE table_schema = DATABASE() 
             AND table_name LIKE '{$prefix}%'"
        );
        
        foreach ($tables as $table) {
            $size += $table->size;
        }
        
        return $size;
    }
}
