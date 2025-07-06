<?php
/**
 * URL Replacer Class
 * Handles smart URL replacements while respecting data formats
 */
class ESC_URL_Replacer {
    
    private $source_site_id;
    private $destination_site_id;
    private $source_url;
    private $destination_url;
    private $source_upload_url;
    private $destination_upload_url;
    private $source_prefix;
    private $destination_prefix;
    
    public function __construct($source_site_id, $destination_site_id) {
        global $wpdb;
        
        $this->source_site_id = $source_site_id;
        $this->destination_site_id = $destination_site_id;
        
        // Get URLs
        switch_to_blog($source_site_id);
        $this->source_url = trailingslashit(get_site_url());
        $upload_dir = wp_upload_dir();
        $this->source_upload_url = trailingslashit($upload_dir['baseurl']);
        restore_current_blog();
        
        switch_to_blog($destination_site_id);
        $this->destination_url = trailingslashit(get_site_url());
        $upload_dir = wp_upload_dir();
        $this->destination_upload_url = trailingslashit($upload_dir['baseurl']);
        restore_current_blog();
        
        // Get table prefixes
        $this->source_prefix = $wpdb->get_blog_prefix($source_site_id);
        $this->destination_prefix = $wpdb->get_blog_prefix($destination_site_id);
    }
    
    /**
     * Replace all URLs in the destination site
     */
    public function replace_urls() {
        global $wpdb;
        
        try {
            // Update options table
            $this->update_options_table();
            
            // Update posts table
            $this->update_posts_table();
            
            // Update postmeta table (most critical for Elementor)
            $this->update_postmeta_table();
            
            // Update other tables that might contain URLs
            $this->update_other_tables();
            
            // Update Elementor-specific data
            $this->update_elementor_urls();
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('url_replace_failed', $e->getMessage());
        }
    }
    
    /**
     * Update options table
     */
    private function update_options_table() {
        global $wpdb;
        
        $options_table = $this->destination_prefix . 'options';
        
        // CRITICAL: First update the most important options explicitly
        // These are the ones that cause redirect issues if not updated
        $critical_options = array('siteurl', 'home');
        
        foreach ($critical_options as $option_name) {
            // Get the current value
            $current_value = $wpdb->get_var($wpdb->prepare(
                "SELECT option_value FROM $options_table WHERE option_name = %s",
                $option_name
            ));
            
            // If it contains the source URL (with or without trailing slash), replace it
            if ($current_value) {
                $source_url_no_slash = rtrim($this->source_url, '/');
                $dest_url_no_slash = rtrim($this->destination_url, '/');
                
                // Replace both with and without trailing slashes
                $new_value = str_replace(
                    array($this->source_url, $source_url_no_slash),
                    array($this->destination_url, $dest_url_no_slash),
                    $current_value
                );
                
                // If the value still contains the old URL or doesn't match the new site URL, force update
                if (strpos($new_value, $source_url_no_slash) !== false || $new_value !== $dest_url_no_slash) {
                    $new_value = $dest_url_no_slash;
                }
                
                $wpdb->update(
                    $options_table,
                    array('option_value' => $new_value),
                    array('option_name' => $option_name),
                    array('%s'),
                    array('%s')
                );
            }
        }
        
        // Handle any redirect plugins that might be installed
        $redirect_options = array(
            'redirection_options',
            'wpseo_redirects',
            'redirect_canonical',
            'wp_redirect_*',
            '_redirection_*'
        );
        
        foreach ($redirect_options as $option_pattern) {
            if (strpos($option_pattern, '*') !== false) {
                // Handle wildcard patterns
                $pattern = str_replace('*', '%', $option_pattern);
                $redirect_opts = $wpdb->get_results($wpdb->prepare(
                    "SELECT option_name, option_value FROM $options_table WHERE option_name LIKE %s",
                    $pattern
                ));
                
                foreach ($redirect_opts as $opt) {
                    $new_value = $this->replace_value($opt->option_value);
                    if ($new_value !== $opt->option_value) {
                        $wpdb->update(
                            $options_table,
                            array('option_value' => $new_value),
                            array('option_name' => $opt->option_name),
                            array('%s'),
                            array('%s')
                        );
                    }
                }
            } else {
                // Handle exact option names
                $option_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_value FROM $options_table WHERE option_name = %s",
                    $option_pattern
                ));
                
                if ($option_value) {
                    $new_value = $this->replace_value($option_value);
                    if ($new_value !== $option_value) {
                        $wpdb->update(
                            $options_table,
                            array('option_value' => $new_value),
                            array('option_name' => $option_pattern),
                            array('%s'),
                            array('%s')
                        );
                    }
                }
            }
        }
        
        // Now handle all other options that might contain URLs
        $source_url_no_slash = rtrim($this->source_url, '/');
        $options = $wpdb->get_results(
            "SELECT option_id, option_name, option_value 
             FROM $options_table 
             WHERE option_value LIKE '%{$this->source_url}%' 
             OR option_value LIKE '%{$source_url_no_slash}%'
             OR option_value LIKE '%{$this->source_upload_url}%'"
        );
        
        foreach ($options as $option) {
            $new_value = $this->replace_value($option->option_value);
            
            if ($new_value !== $option->option_value) {
                $wpdb->update(
                    $options_table,
                    array('option_value' => $new_value),
                    array('option_id' => $option->option_id),
                    array('%s'),
                    array('%d')
                );
            }
        }
        
        // Additional WordPress options that might contain URLs
        $url_options = array(
            'upload_url_path',
            'upload_path',
            'fileupload_url'
        );
        
        foreach ($url_options as $option_name) {
            $option_value = $wpdb->get_var($wpdb->prepare(
                "SELECT option_value FROM $options_table WHERE option_name = %s",
                $option_name
            ));
            
            if ($option_value) {
                $new_value = $this->replace_value($option_value);
                if ($new_value !== $option_value) {
                    $wpdb->update(
                        $options_table,
                        array('option_value' => $new_value),
                        array('option_name' => $option_name),
                        array('%s'),
                        array('%s')
                    );
                }
            }
        }
    }
    
    /**
     * Update posts table
     */
    private function update_posts_table() {
        global $wpdb;
        
        $posts_table = $this->destination_prefix . 'posts';
        
        // Update post content
        $wpdb->query($wpdb->prepare(
            "UPDATE $posts_table 
             SET post_content = REPLACE(post_content, %s, %s) 
             WHERE post_content LIKE %s",
            $this->source_url,
            $this->destination_url,
            '%' . $wpdb->esc_like($this->source_url) . '%'
        ));
        
        // Update GUIDs
        $wpdb->query($wpdb->prepare(
            "UPDATE $posts_table 
             SET guid = REPLACE(guid, %s, %s) 
             WHERE guid LIKE %s",
            $this->source_url,
            $this->destination_url,
            '%' . $wpdb->esc_like($this->source_url) . '%'
        ));
    }
    
    /**
     * Update postmeta table - most critical for Elementor
     */
    private function update_postmeta_table() {
        global $wpdb;
        
        $postmeta_table = $this->destination_prefix . 'postmeta';
        
        // Get all postmeta that might contain URLs
        $meta_records = $wpdb->get_results(
            "SELECT meta_id, meta_key, meta_value 
             FROM $postmeta_table 
             WHERE meta_value LIKE '%{$this->source_url}%' 
             OR meta_value LIKE '%{$this->source_upload_url}%'"
        );
        
        foreach ($meta_records as $meta) {
            // Special handling for Elementor data
            if ($meta->meta_key === '_elementor_data') {
                $new_value = $this->replace_elementor_data($meta->meta_value);
            } else {
                $new_value = $this->replace_value($meta->meta_value);
            }
            
            if ($new_value !== $meta->meta_value) {
                $wpdb->update(
                    $postmeta_table,
                    array('meta_value' => $new_value),
                    array('meta_id' => $meta->meta_id),
                    array('%s'),
                    array('%d')
                );
            }
        }
    }
    
    /**
     * Replace value handling serialized data and JSON
     */
    private function replace_value($value) {
        // Check if it's serialized data
        if ($this->is_serialized($value)) {
            $unserialized = @unserialize($value);
            if ($unserialized !== false) {
                $unserialized = $this->replace_in_array($unserialized);
                return serialize($unserialized);
            }
        }
        
        // Check if it's JSON
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $decoded = $this->replace_in_array($decoded);
            return json_encode($decoded);
        }
        
        // Plain text replacement - handle both with and without trailing slashes
        $source_url_no_slash = rtrim($this->source_url, '/');
        $dest_url_no_slash = rtrim($this->destination_url, '/');
        $source_upload_no_slash = rtrim($this->source_upload_url, '/');
        $dest_upload_no_slash = rtrim($this->destination_upload_url, '/');
        
        // Replace all variations
        $value = str_replace(
            array(
                $this->source_url,
                $source_url_no_slash,
                $this->source_upload_url,
                $source_upload_no_slash
            ),
            array(
                $this->destination_url,
                $dest_url_no_slash,
                $this->destination_upload_url,
                $dest_upload_no_slash
            ),
            $value
        );
        
        return $value;
    }
    
    /**
     * Replace Elementor data specifically
     */
    private function replace_elementor_data($data) {
        // Elementor data is JSON encoded
        $decoded = json_decode($data, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $decoded = $this->replace_in_elementor_array($decoded);
            return wp_json_encode($decoded);
        }
        
        // Fallback to string replacement if JSON decode fails
        return str_replace(
            array($this->source_url, $this->source_upload_url),
            array($this->destination_url, $this->destination_upload_url),
            $data
        );
    }
    
    /**
     * Recursively replace URLs in arrays
     */
    private function replace_in_array($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace_in_array($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->replace_in_array($value);
            }
        } elseif (is_string($data)) {
            // Handle both with and without trailing slashes
            $source_url_no_slash = rtrim($this->source_url, '/');
            $dest_url_no_slash = rtrim($this->destination_url, '/');
            $source_upload_no_slash = rtrim($this->source_upload_url, '/');
            $dest_upload_no_slash = rtrim($this->destination_upload_url, '/');
            
            $data = str_replace(
                array(
                    $this->source_url,
                    $source_url_no_slash,
                    $this->source_upload_url,
                    $source_upload_no_slash
                ),
                array(
                    $this->destination_url,
                    $dest_url_no_slash,
                    $this->destination_upload_url,
                    $dest_upload_no_slash
                ),
                $data
            );
        }
        
        return $data;
    }
    
    /**
     * Recursively replace URLs in Elementor arrays (special handling)
     */
    private function replace_in_elementor_array($data) {
        if (is_array($data)) {
            // Check for URL fields commonly used in Elementor
            $url_fields = array('url', 'src', 'background_image', 'background_overlay_image', 'link', 'video_link');
            
            // Get URL variations
            $source_url_no_slash = rtrim($this->source_url, '/');
            $dest_url_no_slash = rtrim($this->destination_url, '/');
            $source_upload_no_slash = rtrim($this->source_upload_url, '/');
            $dest_upload_no_slash = rtrim($this->destination_upload_url, '/');
            
            foreach ($data as $key => $value) {
                if (in_array($key, $url_fields) && is_array($value) && isset($value['url'])) {
                    // Handle Elementor's URL structure
                    $data[$key]['url'] = str_replace(
                        array(
                            $this->source_url,
                            $source_url_no_slash,
                            $this->source_upload_url,
                            $source_upload_no_slash
                        ),
                        array(
                            $this->destination_url,
                            $dest_url_no_slash,
                            $this->destination_upload_url,
                            $dest_upload_no_slash
                        ),
                        $value['url']
                    );
                } else {
                    $data[$key] = $this->replace_in_elementor_array($value);
                }
            }
        } elseif (is_string($data)) {
            $source_url_no_slash = rtrim($this->source_url, '/');
            $dest_url_no_slash = rtrim($this->destination_url, '/');
            $source_upload_no_slash = rtrim($this->source_upload_url, '/');
            $dest_upload_no_slash = rtrim($this->destination_upload_url, '/');
            
            $data = str_replace(
                array(
                    $this->source_url,
                    $source_url_no_slash,
                    $this->source_upload_url,
                    $source_upload_no_slash
                ),
                array(
                    $this->destination_url,
                    $dest_url_no_slash,
                    $this->destination_upload_url,
                    $dest_upload_no_slash
                ),
                $data
            );
        }
        
        return $data;
    }
    
    /**
     * Update other tables that might contain URLs
     */
    private function update_other_tables() {
        global $wpdb;
        
        // Update comments
        $comments_table = $this->destination_prefix . 'comments';
        if ($wpdb->get_var("SHOW TABLES LIKE '$comments_table'") === $comments_table) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $comments_table 
                 SET comment_content = REPLACE(comment_content, %s, %s) 
                 WHERE comment_content LIKE %s",
                $this->source_url,
                $this->destination_url,
                '%' . $wpdb->esc_like($this->source_url) . '%'
            ));
        }
        
        // Update term meta if it exists
        $termmeta_table = $this->destination_prefix . 'termmeta';
        if ($wpdb->get_var("SHOW TABLES LIKE '$termmeta_table'") === $termmeta_table) {
            $meta_records = $wpdb->get_results(
                "SELECT meta_id, meta_value 
                 FROM $termmeta_table 
                 WHERE meta_value LIKE '%{$this->source_url}%'"
            );
            
            foreach ($meta_records as $meta) {
                $new_value = $this->replace_value($meta->meta_value);
                if ($new_value !== $meta->meta_value) {
                    $wpdb->update(
                        $termmeta_table,
                        array('meta_value' => $new_value),
                        array('meta_id' => $meta->meta_id),
                        array('%s'),
                        array('%d')
                    );
                }
            }
        }
    }
    
    /**
     * Update Elementor-specific URLs
     */
    private function update_elementor_urls() {
        global $wpdb;
        
        $options_table = $this->destination_prefix . 'options';
        
        // Update Elementor global settings
        $elementor_options = array(
            'elementor_global_image_lightbox',
            'elementor_css_print_method',
            'elementor_editor_break_lines',
            'elementor_container_width',
            '_elementor_global_css',
            'elementor_pro_theme_builder_conditions'
        );
        
        foreach ($elementor_options as $option_name) {
            $option_value = $wpdb->get_var($wpdb->prepare(
                "SELECT option_value FROM $options_table WHERE option_name = %s",
                $option_name
            ));
            
            if ($option_value) {
                $new_value = $this->replace_value($option_value);
                if ($new_value !== $option_value) {
                    $wpdb->update(
                        $options_table,
                        array('option_value' => $new_value),
                        array('option_name' => $option_name),
                        array('%s'),
                        array('%s')
                    );
                }
            }
        }
    }
    
    /**
     * Check if a string is serialized
     */
    private function is_serialized($data) {
        if (!is_string($data)) {
            return false;
        }
        
        $data = trim($data);
        
        if ('N;' === $data) {
            return true;
        }
        
        if (strlen($data) < 4) {
            return false;
        }
        
        if (':' !== $data[1]) {
            return false;
        }
        
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        
        $token = $data[0];
        switch ($token) {
            case 's':
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$/", $data);
        }
        
        return false;
    }
}
