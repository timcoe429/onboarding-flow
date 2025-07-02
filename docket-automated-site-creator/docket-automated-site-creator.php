    /**
     * Clone content from template site with proper Elementor support
     */
    private function clone_from_template($new_site_id, $template_site_id, $form_data) {
        global $wpdb;
        
        $this->log("=== CLONE PROCESS START ===");
        $this->log("Cloning from template site {$template_site_id} to new site {$new_site_id}");
        
        // Switch to template site to get content
        $this->log("Switching to template site {$template_site_id}");
        switch_to_blog($template_site_id);
        
        // Get current site's table prefix
        $template_prefix = $wpdb->prefix;
        $this->log("Template site table prefix: {$template_prefix}");
        
        // Get all posts from template (including Elementor templates)
        $this->log("Fetching posts from template site...");
        $posts = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}posts 
             WHERE post_status IN ('publish', 'private', 'draft') 
             AND post_type IN ('page', 'post', 'elementor_library', 'nav_menu_item')"
        );
        $this->log("Found " . count($posts) . " posts in template site");
        
        // Get all post meta
        $this->log("Fetching post meta from template site...");
        $post_meta = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}postmeta"
        );
        $this->log("Found " . count($post_meta) . " post meta entries");
        
        // Get Elementor-specific options
        $this->log("Fetching Elementor options from template site...");
        $elementor_options = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}options 
             WHERE option_name LIKE 'elementor_%' 
             OR option_name LIKE '_elementor_%'
             OR option_name = 'stylesheet'
             OR option_name = 'template'"
        );
        $this->log("Found " . count($elementor_options) . " Elementor options");
        
        // Get terms and term relationships for menus/categories
        $this->log("Fetching terms and relationships...");
        $terms = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}terms"
        );
        
        $term_taxonomy = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}term_taxonomy"
        );
        
        $term_relationships = $wpdb->get_results(
            "SELECT * FROM {$template_prefix}term_relationships"
        );
        $this->log("Found " . count($terms) . " terms, " . count($term_taxonomy) . " taxonomies, " . count($term_relationships) . " relationships");
        
        // Switch to new site
        $this->log("Switching to new site {$new_site_id}");
        restore_current_blog();
        switch_to_blog($new_site_id);
        
        // Get new site's table prefix
        $new_prefix = $wpdb->prefix;
        $this->log("New site table prefix: {$new_prefix}");
        
        $this->log("Starting Elementor-aware content clone from template site {$template_site_id} to new site {$new_site_id}");
        
        // Create ID mapping for posts
        $post_id_map = array();
        
        // 1. Clone all posts first
        $this->log("=== CLONING POSTS ===");
        foreach ($posts as $post) {
            $old_post_id = $post->ID;
            
            // Skip if it's the default "Hello World" post
            if ($post->post_title === 'Hello world!' || $post->post_name === 'hello-world') {
                $this->log("Skipping default post: {$post->post_title}");
                continue;
            }
            
            $this->log("Cloning post: ID={$old_post_id}, Title='{$post->post_title}', Type={$post->post_type}");
            
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
                $this->log("Successfully cloned post: '{$post->post_title}' (ID: {$old_post_id} -> {$new_post_id})");
            } else {
                $error_msg = is_wp_error($new_post_id) ? $new_post_id->get_error_message() : 'Unknown error';
                $this->log("ERROR cloning post '{$post->post_title}': {$error_msg}");
            }
        }
        $this->log("Post cloning complete. Cloned " . count($post_id_map) . " posts");
        
        // 2. Update post parents (for nav menu items, etc.)
        $this->log("=== UPDATING POST PARENTS ===");
        $parent_updates = 0;
        foreach ($posts as $post) {
            if ($post->post_parent > 0 && isset($post_id_map[$post->ID]) && isset($post_id_map[$post->post_parent])) {
                wp_update_post(array(
                    'ID' => $post_id_map[$post->ID],
                    'post_parent' => $post_id_map[$post->post_parent]
                ));
                $parent_updates++;
            }
        }
        $this->log("Updated {$parent_updates} post parent relationships");
        
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
        
        // 6. Clone post meta (including Elementor data) - Moving this earlier for better debugging
        $this->log("=== CLONING POST META ===");
        $meta_count = 0;
        $elementor_meta_count = 0;
        foreach ($post_meta as $meta) {
            if (isset($post_id_map[$meta->post_id])) {
                $meta_value = $meta->meta_value;
                
                // Handle Elementor data that might reference other posts
                if (in_array($meta->meta_key, array('_elementor_data', '_elementor_draft'))) {
                    $elementor_meta_count++;
                    $this->log("Cloning Elementor meta: {$meta->meta_key} for post {$meta->post_id} -> {$post_id_map[$meta->post_id]}");
                    
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
                $meta_count++;
            }
        }
        $this->log("Cloned {$meta_count} post meta entries, including {$elementor_meta_count} Elementor meta entries");
        
        // 7. Clone Elementor options
        $this->log("=== CLONING ELEMENTOR OPTIONS ===");
        $option_count = 0;
        foreach ($elementor_options as $option) {
            // Skip some site-specific options
            if (in_array($option->option_name, array('elementor_experiment-hello-theme-header-footer'))) {
                continue;
            }
            
            update_option($option->option_name, maybe_unserialize($option->option_value));
            $option_count++;
            $this->log("Cloned option: {$option->option_name}");
        }
        $this->log("Cloned {$option_count} Elementor options");
        
        // 8. Replace placeholder content with form data
        $this->log("=== REPLACING PLACEHOLDERS ===");
        $this->replace_placeholder_content($form_data, $post_id_map);
        
        // 9. Set homepage
        $this->log("=== SETTING HOMEPAGE ===");
        $home_page = get_page_by_path('home');
        if ($home_page) {
            update_option('page_on_front', $home_page->ID);
            update_option('show_on_front', 'page');
            $this->log("Set homepage to page ID: {$home_page->ID}");
        } else {
            $this->log("No 'home' page found to set as homepage");
        }
        
        // 10. Flush Elementor cache
        $this->log("=== FLUSHING ELEMENTOR CACHE ===");
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
            $this->log("Elementor cache cleared");
        } else {
            $this->log("Elementor plugin not found - cache not cleared");
        }
        
        restore_current_blog();
        
        $this->log("Successfully cloned Elementor content with " . count($post_id_map) . " posts");
        $this->log("=== CLONE PROCESS END ===");
        
        return true;
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
    
    // Duplicate function removed - using the one below with debugging
    
    /**
     * Debug: List all sites in the network
     */
    private function debug_list_all_sites() {
        $this->log("=== DEBUG: ALL SITES IN NETWORK ===");
        $sites = get_sites();
        foreach ($sites as $site) {
            $this->log("Site ID: {$site->blog_id}, Domain: {$site->domain}, Path: {$site->path}");
        }
        $this->log("=== END ALL SITES DEBUG ===");
    }
    
    /**
     * Debug: Show template site content
     */
    private function debug_template_site_content($template_site_id) {
        $this->log("=== DEBUG: TEMPLATE SITE CONTENT (ID: {$template_site_id}) ===");
        
        switch_to_blog($template_site_id);
        global $wpdb;
        
        // Count posts
        $post_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_status = 'publish'");
        $this->log("Published posts in template: " . $post_count);
        
        // List some posts
        $posts = $wpdb->get_results("SELECT ID, post_title, post_type, post_status FROM {$wpdb->prefix}posts LIMIT 10");
        foreach ($posts as $post) {
            $this->log("Post: ID={$post->ID}, Title='{$post->post_title}', Type={$post->post_type}, Status={$post->post_status}");
        }
        
        // Check for Elementor data
        $elementor_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_elementor_data'");
        $this->log("Posts with Elementor data: " . $elementor_count);
        
        // Check theme
        $theme = get_option('stylesheet');
        $this->log("Template site theme: " . $theme);
        
        restore_current_blog();
        $this->log("=== END TEMPLATE SITE DEBUG ===");
    }
    
    /**
     * Debug: Show new site content after cloning
     */
    private function debug_new_site_content($new_site_id) {
        $this->log("=== DEBUG: NEW SITE CONTENT (ID: {$new_site_id}) ===");
        
        switch_to_blog($new_site_id);
        global $wpdb;
        
        // Count posts
        $post_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_status = 'publish'");
        $this->log("Published posts in new site: " . $post_count);
        
        // List some posts
        $posts = $wpdb->get_results("SELECT ID, post_title, post_type, post_status FROM {$wpdb->prefix}posts LIMIT 10");
        foreach ($posts as $post) {
            $this->log("New site post: ID={$post->ID}, Title='{$post->post_title}', Type={$post->post_type}, Status={$post->post_status}");
            
            // Check for placeholders in content
            $content = get_post_field('post_content', $post->ID);
            if (strpos($content, '{{') !== false) {
                $this->log("FOUND UNREPLACED PLACEHOLDERS in post {$post->ID}: " . substr($content, 0, 200) . '...');
            }
        }
        
        // Check for Elementor data
        $elementor_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_elementor_data'");
        $this->log("Posts with Elementor data in new site: " . $elementor_count);
        
        // Check if homepage is set
        $page_on_front = get_option('page_on_front');
        $show_on_front = get_option('show_on_front');
        $this->log("Homepage settings: show_on_front={$show_on_front}, page_on_front={$page_on_front}");
        
        restore_current_blog();
        $this->log("=== END NEW SITE DEBUG ===");
    }
    
    /**
     * Create a new site from template based on form submission
     */
    public function create_site_from_form($request) {
        $form_data = $request->get_json_params();
        
        $this->log("=== STARTING SITE CREATION DEBUG ===");
        $this->log("Received form data: " . json_encode($form_data, JSON_PRETTY_PRINT));
        
        // ✅ Validate template selection
        $selected_template = $form_data['selected_template'] ?? 'template1';
        $this->log("Selected template: " . $selected_template);
        
        if (!isset($this->allowed_templates[$selected_template])) {
            $this->log("ERROR: Invalid template selection: " . $selected_template);
            return new WP_Error('invalid_template', 'Invalid template selection. Only template1-4 allowed.');
        }
        
        // Get the template site to clone from
        $template_site_path = '/' . $this->allowed_templates[$selected_template] . '/';
        $this->log("Looking for template site with path: " . $template_site_path);
        
        $template_site_id = $this->get_site_id_by_path($template_site_path);
        $this->log("Template site ID found: " . ($template_site_id ? $template_site_id : 'NOT FOUND'));
        
        if (!$template_site_id) {
            $this->log("ERROR: Template site not found for path: " . $template_site_path);
            // Let's also check what sites actually exist
            $this->debug_list_all_sites();
            return new WP_Error('template_not_found', "Template site {$selected_template} not found at path {$template_site_path}.");
        }
        
        // Debug template site content
        $this->debug_template_site_content($template_site_id);
        
        // Get next available site number
        $site_number = $this->get_next_site_number();
        $this->log("Next available site number: " . $site_number);
        
        // Create new site
        $site_url = "docketsite{$site_number}";
        $site_title = $form_data['business_name'] ?? "Docket Site {$site_number}";
        $this->log("Creating new site: URL={$site_url}, Title={$site_title}");
        
        $new_site_id = wpmu_create_blog(
            get_current_site()->domain,
            "/{$site_url}/", 
            $site_title,
            1, // Admin user ID
            array('public' => 1),
            get_current_network_id()
        );
        
        if (is_wp_error($new_site_id)) {
            $this->log("ERROR creating site: " . $new_site_id->get_error_message());
            return new WP_Error('site_creation_failed', $new_site_id->get_error_message());
        }
        
        $this->log("New site created successfully with ID: " . $new_site_id);
        
        // ✅ Clone from selected template ONLY
        $this->log("Starting clone process from template {$template_site_id} to new site {$new_site_id}");
        $clone_result = $this->clone_from_template($new_site_id, $template_site_id, $form_data);
        $this->log("Clone process completed. Result: " . ($clone_result ? 'SUCCESS' : 'FAILED'));
        
        // Debug new site content after cloning
        $this->debug_new_site_content($new_site_id);
        
        $site_url_full = "https://" . get_current_site()->domain . "/{$site_url}/";
        $this->log("Site creation complete. Full URL: " . $site_url_full);
        $this->log("=== SITE CREATION DEBUG COMPLETE ===");
        
        return array(
            'success' => true,
            'site_id' => $new_site_id,
            'site_url' => $site_url_full,
            'site_path' => "/{$site_url}/",
            'template_used' => $selected_template,
            'template_site_id' => $template_site_id,
            'debug_log' => "Check docket-automated-site-creator.log for detailed debugging info"
        );
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_api_endpoints() {
        // Site creation endpoint
        register_rest_route('docket/v1', '/create-site', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_site_from_form'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
        
        // Debug endpoint to view logs
        register_rest_route('docket/v1', '/debug-logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_debug_logs'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
        
        // Clear logs endpoint for fresh debugging
        register_rest_route('docket/v1', '/clear-logs', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'clear_debug_logs'),
            'permission_callback' => array($this, 'verify_api_key'),
        ));
    }
    
    /**
     * Get debug logs for troubleshooting
     */
    public function get_debug_logs($request) {
        $log_file = WP_CONTENT_DIR . '/docket-automated-site-creator.log';
        
        if (!file_exists($log_file)) {
            return array(
                'error' => 'Log file not found',
                'log_path' => $log_file
            );
        }
        
        // Get last 100 lines of log file for recent debugging
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -100);
        
        return array(
            'success' => true,
            'log_path' => $log_file,
            'log_size' => filesize($log_file),
            'recent_logs' => implode('', $recent_lines),
            'total_lines' => count($lines),
            'showing_last' => count($recent_lines)
        );
    }
    
    /**
     * Clear debug logs for fresh debugging
     */
    public function clear_debug_logs($request) {
        $log_file = WP_CONTENT_DIR . '/docket-automated-site-creator.log';
        
        if (file_exists($log_file)) {
            $cleared = file_put_contents($log_file, '');
            if ($cleared !== false) {
                $this->log("=== LOG CLEARED FOR FRESH DEBUGGING ===");
                return array(
                    'success' => true,
                    'message' => 'Debug log cleared successfully',
                    'log_path' => $log_file
                );
            } else {
                return array(
                    'error' => 'Failed to clear log file',
                    'log_path' => $log_file
                );
            }
        } else {
            return array(
                'message' => 'Log file does not exist',
                'log_path' => $log_file
            );
        }
    }
} 