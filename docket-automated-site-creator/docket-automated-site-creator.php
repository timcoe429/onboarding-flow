    /**
     * Clone content from template site with proper Elementor support
     */
    private function clone_from_template($new_site_id, $template_site_id, $form_data) {
        global $wpdb;
        
        // Switch to template site to get content
        switch_to_blog($template_site_id);
        
        // Get current site's table prefix
        $template_prefix = $wpdb->prefix;
        
        // Get all posts from template (including Elementor templates)
        $posts = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}posts 
             WHERE post_status IN ('publish', 'private', 'draft') 
             AND post_type IN ('page', 'post', 'elementor_library', 'nav_menu_item')"
        );
        
        // Get all post meta
        $post_meta = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}postmeta"
        );
        
        // Get Elementor-specific options
        $elementor_options = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}options 
             WHERE option_name LIKE 'elementor_%' 
             OR option_name LIKE '_elementor_%'
             OR option_name = 'stylesheet'
             OR option_name = 'template'"
        );
        
        // Get terms and term relationships for menus/categories
        $terms = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}terms"
        );
        
        $term_taxonomy = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}term_taxonomy"
        );
        
        $term_relationships = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}term_relationships"
        );
        
        // Switch to new site
        restore_current_blog();
        switch_to_blog($new_site_id);
        
        // Get new site's table prefix
        $new_prefix = $wpdb->prefix;
        
        $this->log("Starting Elementor-aware content clone from template site {$template_site_id} to new site {$new_site_id}");
        
        // Create ID mapping for posts
        $post_id_map = array();
        
        // 1. Clone all posts first
        foreach ($posts as $post) {
            $old_post_id = $post->ID;
            
            // Skip if it's the default "Hello World" post
            if ($post->post_title === 'Hello world!' || $post->post_name === 'hello-world') {
                continue;
            }
            
            // Prepare post data
            $new_post_data = array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_status' => $post->post_status,
                'post_type' => $post->post_type,
                'post_name' => $post->post_name,
                'post_date' => $post->post_date,
                'post_date_gmt' => $post->post_date_gmt,
                'post_modified' => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1),
                'post_author' => 1, // Set to admin
                'post_parent' => 0, // Will update later
                'menu_order' => $post->menu_order,
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status
            );
            
            // Insert the new post
            $new_post_id = wp_insert_post($new_post_data);
            
            if ($new_post_id && !is_wp_error($new_post_id)) {
                $post_id_map[$old_post_id] = $new_post_id;
                $this->log("Cloned post: '{$post->post_title}' (ID: {$old_post_id} -> {$new_post_id})");
            }
        }
        
        // 2. Update post parents (for nav menu items, etc.)
        foreach ($posts as $post) {
            if ($post->post_parent > 0 && isset($post_id_map[$post->ID]) && isset($post_id_map[$post->post_parent])) {
                wp_update_post(array(
                    'ID' => $post_id_map[$post->ID],
                    'post_parent' => $post_id_map[$post->post_parent]
                ));
            }
        }
        
        // 3. Clone terms (categories, menus, etc.)
        $term_id_map = array();
        foreach ($terms as $term) {
            $new_term = wp_insert_term(
                $term->name,
                'category', // Will be updated in term_taxonomy
                array('slug' => $term->slug)
            );
            
            if (!is_wp_error($new_term)) {
                $term_id_map[$term->term_id] = $new_term['term_id'];
            }
        }
        
        // 4. Clone term taxonomy
        $term_taxonomy_id_map = array();
        foreach ($term_taxonomy as $tax) {
            if (isset($term_id_map[$tax->term_id])) {
                $wpdb->insert(
                    $new_prefix . 'term_taxonomy',
                    array(
                        'term_id' => $term_id_map[$tax->term_id],
                        'taxonomy' => $tax->taxonomy,
                        'description' => $tax->description,
                        'parent' => 0, // Will update later
                        'count' => $tax->count
                    )
                );
                $term_taxonomy_id_map[$tax->term_taxonomy_id] = $wpdb->insert_id;
            }
        }
        
        // 5. Clone term relationships
        foreach ($term_relationships as $rel) {
            if (isset($post_id_map[$rel->object_id]) && isset($term_taxonomy_id_map[$rel->term_taxonomy_id])) {
                $wpdb->insert(
                    $new_prefix . 'term_relationships',
                    array(
                        'object_id' => $post_id_map[$rel->object_id],
                        'term_taxonomy_id' => $term_taxonomy_id_map[$rel->term_taxonomy_id],
                        'term_order' => $rel->term_order
                    )
                );
            }
        }
        
        // 6. Clone post meta (including Elementor data)
        foreach ($post_meta as $meta) {
            if (isset($post_id_map[$meta->post_id])) {
                $meta_value = $meta->meta_value;
                
                // Handle Elementor data that might reference other posts
                if (in_array($meta->meta_key, array('_elementor_data', '_elementor_draft'))) {
                    // Update post IDs in Elementor data
                    foreach ($post_id_map as $old_id => $new_id) {
                        $meta_value = str_replace('"' . $old_id . '"', '"' . $new_id . '"', $meta_value);
                    }
                }
                
                add_post_meta(
                    $post_id_map[$meta->post_id],
                    $meta->meta_key,
                    maybe_unserialize($meta_value),
                    false
                );
            }
        }
        
        // 7. Clone Elementor options
        foreach ($elementor_options as $option) {
            // Skip some site-specific options
            if (in_array($option->option_name, array('elementor_experiment-hello-theme-header-footer'))) {
                continue;
            }
            
            update_option($option->option_name, maybe_unserialize($option->option_value));
        }
        
        // 8. Replace placeholder content with form data
        $this->replace_placeholder_content($form_data, $post_id_map);
        
        // 9. Set homepage
        $home_page = get_page_by_path('home');
        if ($home_page) {
            update_option('page_on_front', $home_page->ID);
            update_option('show_on_front', 'page');
        }
        
        // 10. Flush Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        restore_current_blog();
        
        $this->log("Successfully cloned Elementor content with " . count($post_id_map) . " posts");
    }
    
    /**
     * Replace placeholder content with actual form data
     */
    private function replace_placeholder_content($form_data, $post_id_map) {
        // Replacement mapping - UPDATED to match actual template placeholders
        $replacements = array(
            '{{company}}' => $form_data['business_name'] ?? 'Your Business',
            '{{business_name}}' => $form_data['business_name'] ?? 'Your Business',
            '{{BUSINESS_NAME}}' => $form_data['business_name'] ?? 'Your Business',
            '{{contact_name}}' => $form_data['contact_name'] ?? $form_data['name'] ?? 'Contact',
            '{{CONTACT_NAME}}' => $form_data['contact_name'] ?? $form_data['name'] ?? 'Contact',
            '{{phone}}' => $form_data['phone'] ?? $form_data['phone_number'] ?? '(555) 123-4567',
            '{{PHONE}}' => $form_data['phone'] ?? $form_data['phone_number'] ?? '(555) 123-4567',
            '{{email}}' => $form_data['business_email'] ?? $form_data['email'] ?? 'info@yourbusiness.com',
            '{{EMAIL}}' => $form_data['business_email'] ?? $form_data['email'] ?? 'info@yourbusiness.com',
            '{{business_email}}' => $form_data['business_email'] ?? $form_data['email'] ?? 'info@yourbusiness.com',
            '{{BUSINESS_EMAIL}}' => $form_data['business_email'] ?? $form_data['email'] ?? 'info@yourbusiness.com',
            '{{business_address}}' => $form_data['business_address'] ?? 'Your Address',
            '{{BUSINESS_ADDRESS}}' => $form_data['business_address'] ?? 'Your Address',
            '{{city}}' => $this->extract_city($form_data['business_address'] ?? ''),
            '{{CITY}}' => $this->extract_city($form_data['business_address'] ?? ''),
            '{{state}}' => $this->extract_state($form_data['business_address'] ?? ''),
            '{{STATE}}' => $this->extract_state($form_data['business_address'] ?? ''),
            '{{service_areas}}' => $form_data['service_areas'] ?? 'Local Area',
            '{{SERVICE_AREAS}}' => $form_data['service_areas'] ?? 'Local Area',
            '{{services}}' => $form_data['services'] ?? 'Professional Services',
            '{{SERVICES}}' => $form_data['services'] ?? 'Professional Services'
        );
        
        $this->log("Starting placeholder replacement with data: " . json_encode($form_data));
        $this->log("Replacement mappings: " . json_encode($replacements));
        
        foreach ($post_id_map as $new_post_id) {
            $post = get_post($new_post_id);
            if (!$post) continue;
            
            $updated = false;
            
            // Replace in post content
            $new_content = $post->post_content;
            $original_content = $new_content;
            foreach ($replacements as $placeholder => $replacement) {
                if (strpos($new_content, $placeholder) !== false) {
                    $new_content = str_replace($placeholder, $replacement, $new_content);
                    $this->log("Replaced '{$placeholder}' with '{$replacement}' in post content");
                }
            }
            if ($new_content !== $original_content) {
                $updated = true;
            }
            
            // Replace in post title
            $new_title = $post->post_title;
            $original_title = $new_title;
            foreach ($replacements as $placeholder => $replacement) {
                if (strpos($new_title, $placeholder) !== false) {
                    $new_title = str_replace($placeholder, $replacement, $new_title);
                    $this->log("Replaced '{$placeholder}' with '{$replacement}' in post title");
                }
            }
            if ($new_title !== $original_title) {
                $updated = true;
            }
            
            // Replace in Elementor data - IMPROVED
            $elementor_data = get_post_meta($new_post_id, '_elementor_data', true);
            if ($elementor_data) {
                $original_data = $elementor_data;
                
                // Handle both JSON string and serialized data
                if (is_string($elementor_data)) {
                    foreach ($replacements as $placeholder => $replacement) {
                        if (strpos($elementor_data, $placeholder) !== false) {
                            $elementor_data = str_replace($placeholder, $replacement, $elementor_data);
                            $this->log("Replaced '{$placeholder}' with '{$replacement}' in Elementor data");
                        }
                    }
                }
                
                if ($elementor_data !== $original_data) {
                    update_post_meta($new_post_id, '_elementor_data', $elementor_data);
                    $updated = true;
                    
                    // Also clear Elementor cache for this post
                    delete_post_meta($new_post_id, '_elementor_css');
                }
            }
            
            // Also check and replace in ALL post meta (in case placeholders are in other meta fields)
            $all_meta = get_post_meta($new_post_id);
            foreach ($all_meta as $meta_key => $meta_values) {
                foreach ($meta_values as $meta_value) {
                    if (is_string($meta_value)) {
                        $original_meta = $meta_value;
                        foreach ($replacements as $placeholder => $replacement) {
                            if (strpos($meta_value, $placeholder) !== false) {
                                $meta_value = str_replace($placeholder, $replacement, $meta_value);
                                $this->log("Replaced '{$placeholder}' with '{$replacement}' in meta field '{$meta_key}'");
                            }
                        }
                        
                        if ($meta_value !== $original_meta) {
                            update_post_meta($new_post_id, $meta_key, $meta_value);
                            $updated = true;
                        }
                    }
                }
            }
            
            // Update post if content changed
            if ($updated) {
                wp_update_post(array(
                    'ID' => $new_post_id,
                    'post_content' => $new_content,
                    'post_title' => $new_title
                ));
                
                $this->log("Updated placeholders in post: {$post->post_title} (ID: {$new_post_id})");
            }
        }
        
        // ALSO replace in WordPress options (theme customizer, etc.)
        $options_to_check = array('blogname', 'blogdescription', 'start_of_week');
        foreach ($options_to_check as $option_name) {
            $option_value = get_option($option_name);
            if (is_string($option_value)) {
                $original_value = $option_value;
                foreach ($replacements as $placeholder => $replacement) {
                    if (strpos($option_value, $placeholder) !== false) {
                        $option_value = str_replace($placeholder, $replacement, $option_value);
                        $this->log("Replaced '{$placeholder}' with '{$replacement}' in option '{$option_name}'");
                    }
                }
                
                if ($option_value !== $original_value) {
                    update_option($option_name, $option_value);
                }
            }
        }
        
        $this->log("Placeholder replacement completed");
    }
    
    /**
     * Extract city from address
     */
    private function extract_city($address) {
        if (empty($address)) {
            return 'Your City';
        }
        
        // Remove extra whitespace and normalize
        $address = trim($address);
        
        // Try different formats:
        // Format 1: "Street, City, State ZIP"
        $parts = explode(',', $address);
        if (count($parts) >= 2) {
            $city = trim($parts[1]);
            if (!empty($city)) {
                $this->log("Extracted city from address: '{$city}'");
                return $city;
            }
        }
        
        // Format 2: "Street City State ZIP" (space separated)
        $words = explode(' ', $address);
        if (count($words) >= 3) {
            // Usually city is 2nd to last word before state/zip
            $city = $words[count($words) - 3];
            if (!empty($city) && !is_numeric($city)) {
                $this->log("Extracted city from address (space format): '{$city}'");
                return $city;
            }
        }
        
        $this->log("Could not extract city from address: '{$address}', using default");
        return 'Your City';
    }
    
    /**
     * Extract state from address  
     */
    private function extract_state($address) {
        if (empty($address)) {
            return 'Your State';
        }
        
        // Remove extra whitespace and normalize
        $address = trim($address);
        
        // Try different formats:
        // Format 1: "Street, City, State ZIP"
        $parts = explode(',', $address);
        if (count($parts) >= 3) {
            $state_zip = trim($parts[2]);
            $state_parts = explode(' ', $state_zip);
            if (!empty($state_parts[0])) {
                $state = $state_parts[0];
                $this->log("Extracted state from address: '{$state}'");
                return $state;
            }
        }
        
        // Format 2: "Street City State ZIP" (space separated)
        $words = explode(' ', $address);
        if (count($words) >= 2) {
            // Usually state is 2nd to last word before ZIP
            $state = $words[count($words) - 2];
            if (!empty($state) && !is_numeric($state) && strlen($state) <= 3) {
                $this->log("Extracted state from address (space format): '{$state}'");
                return $state;
            }
        }
        
        $this->log("Could not extract state from address: '{$address}', using default");
        return 'Your State';
    } 