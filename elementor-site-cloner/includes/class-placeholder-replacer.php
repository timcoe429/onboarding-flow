<?php
/**
 * Placeholder Replacer Class
 * Handles replacing {{placeholders}} with actual business data
 */
class ESC_Placeholder_Replacer {
    
    private $destination_site_id;
    private $replacements;
    private $destination_prefix;
    
    /**
     * Constructor
     * 
     * @param int $destination_site_id The site ID to perform replacements on
     * @param array $replacements Array of placeholder => replacement value pairs
     */
    public function __construct($destination_site_id, $replacements) {
        global $wpdb;
        
        $this->destination_site_id = $destination_site_id;
        $this->replacements = $replacements;
        $this->destination_prefix = $wpdb->get_blog_prefix($destination_site_id);
    }
    
    /**
     * Perform all placeholder replacements
     */
    public function replace_placeholders() {
        global $wpdb;
        
        try {
            // Update posts table (content, titles, excerpts)
            $this->update_posts_table();
            
            // Update postmeta table (most critical for custom fields and Elementor)
            $this->update_postmeta_table();
            
            // Update options table (site tagline, widgets, theme mods)
            $this->update_options_table();
            
            // Update comments if any exist
            $this->update_comments_table();
            
            // Update term descriptions
            $this->update_terms_table();
            
            return true;
            
        } catch (Exception $e) {
            error_log('ESC Placeholder Replacer Error: ' . $e->getMessage());
            return new WP_Error('placeholder_replace_failed', $e->getMessage());
        }
    }
    
    /**
     * Update posts table
     */
    private function update_posts_table() {
        global $wpdb;
        
        $posts_table = $this->destination_prefix . 'posts';
        
        // Build the WHERE clause to find any posts containing our placeholders
        $where_conditions = array();
        foreach ($this->replacements as $placeholder => $value) {
            $where_conditions[] = $wpdb->prepare(
                "post_content LIKE %s OR post_title LIKE %s OR post_excerpt LIKE %s",
                '%' . $wpdb->esc_like($placeholder) . '%',
                '%' . $wpdb->esc_like($placeholder) . '%',
                '%' . $wpdb->esc_like($placeholder) . '%'
            );
        }
        
        if (empty($where_conditions)) {
            return;
        }
        
        $where_clause = '(' . implode(') OR (', $where_conditions) . ')';
        
        // Get all posts that might contain placeholders
        $posts = $wpdb->get_results(
            "SELECT ID, post_content, post_title, post_excerpt 
             FROM $posts_table 
             WHERE $where_clause"
        );
        
        foreach ($posts as $post) {
            $updated = false;
            $new_content = $post->post_content;
            $new_title = $post->post_title;
            $new_excerpt = $post->post_excerpt;
            
            // Replace placeholders in each field
            foreach ($this->replacements as $placeholder => $value) {
                if (strpos($new_content, $placeholder) !== false) {
                    $new_content = str_replace($placeholder, $value, $new_content);
                    $updated = true;
                }
                if (strpos($new_title, $placeholder) !== false) {
                    $new_title = str_replace($placeholder, $value, $new_title);
                    $updated = true;
                }
                if (strpos($new_excerpt, $placeholder) !== false) {
                    $new_excerpt = str_replace($placeholder, $value, $new_excerpt);
                    $updated = true;
                }
            }
            
            if ($updated) {
                $wpdb->update(
                    $posts_table,
                    array(
                        'post_content' => $new_content,
                        'post_title' => $new_title,
                        'post_excerpt' => $new_excerpt
                    ),
                    array('ID' => $post->ID),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
            }
        }
    }
    
    /**
     * Update postmeta table
     */
    private function update_postmeta_table() {
        global $wpdb;
        
        $postmeta_table = $this->destination_prefix . 'postmeta';
        
        // Build WHERE clause for all placeholders
        $where_conditions = array();
        foreach ($this->replacements as $placeholder => $value) {
            $where_conditions[] = $wpdb->prepare(
                "meta_value LIKE %s",
                '%' . $wpdb->esc_like($placeholder) . '%'
            );
        }
        
        if (empty($where_conditions)) {
            return;
        }
        
        $where_clause = '(' . implode(') OR (', $where_conditions) . ')';
        
        // Get all postmeta that might contain placeholders
        $meta_records = $wpdb->get_results(
            "SELECT meta_id, meta_key, meta_value 
             FROM $postmeta_table 
             WHERE $where_clause"
        );
        
        foreach ($meta_records as $meta) {
            $new_value = $this->replace_in_value($meta->meta_value);
            
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
     * Update options table
     */
    private function update_options_table() {
        global $wpdb;
        
        $options_table = $this->destination_prefix . 'options';
        
        // Build WHERE clause
        $where_conditions = array();
        foreach ($this->replacements as $placeholder => $value) {
            $where_conditions[] = $wpdb->prepare(
                "option_value LIKE %s",
                '%' . $wpdb->esc_like($placeholder) . '%'
            );
        }
        
        if (empty($where_conditions)) {
            return;
        }
        
        $where_clause = '(' . implode(') OR (', $where_conditions) . ')';
        
        // Get all options that might contain placeholders
        $options = $wpdb->get_results(
            "SELECT option_id, option_name, option_value 
             FROM $options_table 
             WHERE $where_clause 
             AND option_name NOT LIKE '\_%'"  // Skip private options
        );
        
        foreach ($options as $option) {
            $new_value = $this->replace_in_value($option->option_value);
            
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
    }
    
    /**
     * Update comments table
     */
    private function update_comments_table() {
        global $wpdb;
        
        $comments_table = $this->destination_prefix . 'comments';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$comments_table'") !== $comments_table) {
            return;
        }
        
        foreach ($this->replacements as $placeholder => $value) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $comments_table 
                 SET comment_content = REPLACE(comment_content, %s, %s) 
                 WHERE comment_content LIKE %s",
                $placeholder,
                $value,
                '%' . $wpdb->esc_like($placeholder) . '%'
            ));
        }
    }
    
    /**
     * Update terms table
     */
    private function update_terms_table() {
        global $wpdb;
        
        $terms_table = $this->destination_prefix . 'term_taxonomy';
        
        foreach ($this->replacements as $placeholder => $value) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $terms_table 
                 SET description = REPLACE(description, %s, %s) 
                 WHERE description LIKE %s",
                $placeholder,
                $value,
                '%' . $wpdb->esc_like($placeholder) . '%'
            ));
        }
    }
    
    /**
     * Replace placeholders in a value, handling serialized and JSON data
     */
    private function replace_in_value($value) {
        // Check if it's serialized data
        if ($this->is_serialized($value)) {
            $unserialized = @unserialize($value);
            if ($unserialized !== false) {
                $unserialized = $this->replace_in_data($unserialized);
                return serialize($unserialized);
            }
        }
        
        // Check if it's JSON (including Elementor data)
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $decoded = $this->replace_in_data($decoded);
            return wp_json_encode($decoded);
        }
        
        // Plain text - simple replacement
        foreach ($this->replacements as $placeholder => $replacement) {
            $value = str_replace($placeholder, $replacement, $value);
        }
        
        return $value;
    }
    
    /**
     * Recursively replace placeholders in arrays/objects
     */
    private function replace_in_data($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace_in_data($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->replace_in_data($value);
            }
        } elseif (is_string($data)) {
            foreach ($this->replacements as $placeholder => $replacement) {
                $data = str_replace($placeholder, $replacement, $data);
            }
        }
        
        return $data;
    }
    
    /**
     * Check if data is serialized
     */
    private function is_serialized($data) {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
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
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$/", $data);
        }
        return false;
    }
} 