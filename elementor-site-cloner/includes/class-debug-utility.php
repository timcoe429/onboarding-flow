<?php
/**
 * Debug Utility Class
 * Helps diagnose URL replacement issues
 */
class ESC_Debug_Utility {
    
    /**
     * Check for remaining source URLs in the database
     */
    public static function check_remaining_urls($site_id, $source_url) {
        global $wpdb;
        
        $prefix = $wpdb->get_blog_prefix($site_id);
        $results = array();
        $source_url_no_slash = rtrim($source_url, '/');
        
        // Check options table
        $options = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value 
             FROM {$prefix}options 
             WHERE option_value LIKE %s 
             OR option_value LIKE %s
             LIMIT 20",
            '%' . $wpdb->esc_like($source_url) . '%',
            '%' . $wpdb->esc_like($source_url_no_slash) . '%'
        ));
        
        if (!empty($options)) {
            $results['options'] = array();
            foreach ($options as $option) {
                $results['options'][] = array(
                    'name' => $option->option_name,
                    'value' => substr($option->option_value, 0, 200) . (strlen($option->option_value) > 200 ? '...' : '')
                );
            }
        }
        
        // Check posts table
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_type 
             FROM {$prefix}posts 
             WHERE post_content LIKE %s 
             OR post_content LIKE %s
             OR guid LIKE %s
             OR guid LIKE %s
             LIMIT 20",
            '%' . $wpdb->esc_like($source_url) . '%',
            '%' . $wpdb->esc_like($source_url_no_slash) . '%',
            '%' . $wpdb->esc_like($source_url) . '%',
            '%' . $wpdb->esc_like($source_url_no_slash) . '%'
        ));
        
        if (!empty($posts)) {
            $results['posts'] = array();
            foreach ($posts as $post) {
                $results['posts'][] = array(
                    'ID' => $post->ID,
                    'title' => $post->post_title,
                    'type' => $post->post_type
                );
            }
        }
        
        // Check postmeta table
        $postmeta = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_key 
             FROM {$prefix}postmeta 
             WHERE meta_value LIKE %s 
             OR meta_value LIKE %s
             LIMIT 20",
            '%' . $wpdb->esc_like($source_url) . '%',
            '%' . $wpdb->esc_like($source_url_no_slash) . '%'
        ));
        
        if (!empty($postmeta)) {
            $results['postmeta'] = array();
            foreach ($postmeta as $meta) {
                $results['postmeta'][] = array(
                    'post_id' => $meta->post_id,
                    'meta_key' => $meta->meta_key
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Get critical site URLs
     */
    public static function get_critical_urls($site_id) {
        switch_to_blog($site_id);
        
        $urls = array(
            'siteurl' => get_option('siteurl'),
            'home' => get_option('home'),
            'upload_url_path' => get_option('upload_url_path'),
            'upload_path' => get_option('upload_path'),
            'fileupload_url' => get_option('fileupload_url'),
            'wp_upload_dir' => wp_upload_dir()
        );
        
        restore_current_blog();
        
        return $urls;
    }
    
    /**
     * Force update critical URLs
     */
    public static function force_update_urls($site_id) {
        global $wpdb;
        
        // Get site details
        $site = get_site($site_id);
        if (!$site) {
            return false;
        }
        
        // Construct the correct URL from site details
        $scheme = is_ssl() ? 'https' : 'http';
        $site_url = $scheme . '://' . $site->domain . $site->path;
        $site_url_no_slash = rtrim($site_url, '/');
        
        switch_to_blog($site_id);
        
        // Force update options
        update_option('siteurl', $site_url_no_slash);
        update_option('home', $site_url_no_slash);
        
        // Clear any upload path settings that might cause issues
        delete_option('upload_url_path');
        delete_option('upload_path');
        
        // Clear caches
        wp_cache_flush();
        
        // Force update in database
        $prefix = $wpdb->get_blog_prefix($site_id);
        $wpdb->query($wpdb->prepare(
            "UPDATE {$prefix}options 
             SET option_value = %s 
             WHERE option_name IN ('siteurl', 'home')",
            $site_url_no_slash
        ));
        
        restore_current_blog();
        
        // Clear all caches
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('siteurl', 'options');
        wp_cache_delete('home', 'options');
        wp_cache_flush();
        
        return true;
    }
} 