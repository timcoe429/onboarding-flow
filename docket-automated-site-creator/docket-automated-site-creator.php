<?php
/**
 * Plugin Name: Docket Automated Site Creator
 * Description: Creates new WordPress sites from templates via API calls from onboarding forms
 * Version: 1.1
 * Author: Tim Coe
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DocketAutomatedSiteCreator {
    
    private $allowed_templates = array(
        'template1' => 'template1',
        'template2' => 'template2', 
        'template3' => 'template3',
        'template4' => 'template4'
    );
    
    public function __construct() {
        // Register API endpoints (including debug endpoint)
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
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
        
        // Easy web interface for viewing logs (no API key needed for convenience)
        register_rest_route('docket/v1', '/debug-interface', array(
            'methods' => 'GET',
            'callback' => array($this, 'show_debug_interface'),
            'permission_callback' => '__return_true', // Public access for easy debugging
        ));
    }
    
    /**
     * Verify API key for security
     */
    public function verify_api_key($request) {
        $api_key = $request->get_param('api_key') ?: $request->get_header('X-API-Key');
        return $api_key === 'docket_automation_key_2025';
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
        
        // ✅ IMMEDIATELY force correct theme activation (before cloning)
        $this->log("Forcing theme activation immediately after site creation");
        switch_to_blog($new_site_id);
        
        // Get available themes and force hello-theme-child-master
        $themes = wp_get_themes();
        $this->log("Available themes on new site: " . implode(', ', array_keys($themes)));
        
        if (array_key_exists('hello-theme-child-master', $themes)) {
            switch_theme('hello-theme-child-master');
            $this->log("Successfully activated hello-theme-child-master theme");
        } else {
            $this->log("ERROR: hello-theme-child-master theme not found!");
        }
        
        // Verify theme activation
        $current_theme = get_option('stylesheet');
        $this->log("Current theme after force activation: " . $current_theme);
        
        restore_current_blog();
        
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
        $elementor_data_valid = 0;
        $elementor_data_invalid = 0;
        
        foreach ($post_meta as $meta) {
            if (isset($post_id_map[$meta->post_id])) {
                $meta_value = $meta->meta_value;
                
                // Handle Elementor data that might reference other posts
                if (in_array($meta->meta_key, array('_elementor_data', '_elementor_draft'))) {
                    $elementor_meta_count++;
                    $this->log("Cloning Elementor meta: {$meta->meta_key} for post {$meta->post_id} -> {$post_id_map[$meta->post_id]}");
                    
                    // Validate Elementor JSON before processing
                    $elementor_json = json_decode($meta_value, true);
                    if ($elementor_json) {
                        $elementor_data_valid++;
                        $this->log("  Valid Elementor JSON with " . count($elementor_json) . " elements");
                        
                        // Show first few characters of the data
                        $this->log("  Sample data: " . substr($meta_value, 0, 200) . '...');
                    } else {
                        $elementor_data_invalid++;
                        $this->log("  ERROR: Invalid Elementor JSON data!");
                        $this->log("  Raw data sample: " . substr($meta_value, 0, 200) . '...');
                    }
                    
                    // DON'T modify data here - we'll fix references later in fix_elementor_data()
                }
                
                $result = add_post_meta(
                    $post_id_map[$meta->post_id],
                    $meta->meta_key,
                    maybe_unserialize($meta_value),
                    false
                );
                
                // Check if meta was added successfully
                if ($result && in_array($meta->meta_key, array('_elementor_data', '_elementor_draft'))) {
                    $this->log("  Successfully added Elementor meta for post {$post_id_map[$meta->post_id]}");
                } elseif (!$result && in_array($meta->meta_key, array('_elementor_data', '_elementor_draft'))) {
                    $this->log("  ERROR: Failed to add Elementor meta for post {$post_id_map[$meta->post_id]}");
                }
                
                $meta_count++;
            }
        }
        $this->log("Cloned {$meta_count} post meta entries, including {$elementor_meta_count} Elementor meta entries");
        $this->log("Elementor data validation: {$elementor_data_valid} valid, {$elementor_data_invalid} invalid");
        
        // 7. THEME ACTIVATION FIRST (before options)
        $this->log("=== ACTIVATING THEME ===");
        $this->activate_proper_theme();
        
        // 8. Clone Elementor options (excluding theme options)
        $this->log("=== CLONING ELEMENTOR OPTIONS ===");
        $option_count = 0;
        foreach ($elementor_options as $option) {
            // Skip theme-related options - we handle theme separately
            if (in_array($option->option_name, array(
                'stylesheet', 
                'template', 
                'elementor_experiment-hello-theme-header-footer'
            ))) {
                $this->log("SKIPPING theme-related option: {$option->option_name} = " . $option->option_value);
                continue;
            }
            
            update_option($option->option_name, maybe_unserialize($option->option_value));
            $option_count++;
            $this->log("Cloned option: {$option->option_name}");
        }
        $this->log("Cloned {$option_count} Elementor options (excluding theme options)");
        
        // 9. Fix Elementor post ID references FIRST
        $this->log("=== FIXING ELEMENTOR DATA ===");
        $this->fix_elementor_data($post_id_map);
        
        // 9.5 Clone Elementor-specific post settings
        $this->log("=== CLONING ELEMENTOR POST SETTINGS ===");
        global $wpdb;
        
        // Elementor uses these meta keys for various settings
        $elementor_meta_keys = array(
            '_elementor_edit_mode',
            '_elementor_template_type',
            '_elementor_version',
            '_elementor_pro_version',
            '_elementor_page_settings',
            '_elementor_controls_usage'
        );
        
        foreach ($post_id_map as $old_post_id => $new_post_id) {
            switch_to_blog($template_site_id);
            
            foreach ($elementor_meta_keys as $meta_key) {
                $meta_value = get_post_meta($old_post_id, $meta_key, true);
                if (!empty($meta_value)) {
                    restore_current_blog();
                    switch_to_blog($new_site_id);
                    update_post_meta($new_post_id, $meta_key, $meta_value);
                    $this->log("Cloned Elementor setting {$meta_key} for post {$new_post_id}");
                }
            }
            
            restore_current_blog();
            switch_to_blog($new_site_id);
        }
        
        // 9.6 Copy Elementor Kit settings
        $this->log("=== COPYING ELEMENTOR KIT ===");
        switch_to_blog($template_site_id);
        
        // Get the active kit ID from template site
        $template_kit_id = get_option('elementor_active_kit');
        if ($template_kit_id) {
            $this->log("Template site has active kit ID: {$template_kit_id}");
            
            restore_current_blog();
            switch_to_blog($new_site_id);
            
            // Find the corresponding kit in the new site
            $kit_posts = get_posts(array(
                'post_type' => 'elementor_library',
                'post_status' => 'publish',
                'meta_key' => '_elementor_template_type',
                'meta_value' => 'kit',
                'posts_per_page' => 1
            ));
            
            if (!empty($kit_posts)) {
                $new_kit_id = $kit_posts[0]->ID;
                update_option('elementor_active_kit', $new_kit_id);
                $this->log("Set active kit to ID: {$new_kit_id}");
            } else {
                $this->log("No kit found in new site!");
            }
        } else {
            $this->log("No active kit in template site");
        }
        
        restore_current_blog();
        switch_to_blog($new_site_id);
        
        // 10. Replace placeholder content with form data
        $this->log("=== REPLACING PLACEHOLDERS ===");
        $this->replace_placeholder_content($form_data, $post_id_map);
        
        // 11. Set homepage
        $this->log("=== SETTING HOMEPAGE ===");
        $home_page = get_page_by_path('home');
        if ($home_page) {
            update_option('page_on_front', $home_page->ID);
            update_option('show_on_front', 'page');
            $this->log("Set homepage to page ID: {$home_page->ID}");
        } else {
            $this->log("No 'home' page found to set as homepage");
        }
        
        // 12. Regenerate Elementor CSS
        $this->log("=== REGENERATING ELEMENTOR CSS ===");
        
        // Ensure Elementor is properly initialized
        if (defined('ELEMENTOR_VERSION')) {
            // Force Elementor to load if not already loaded
            if (!did_action('elementor/loaded')) {
                do_action('elementor/loaded');
            }
            
            // Clear any existing Elementor cache
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
        }
        
        $this->regenerate_elementor_css();
        
        // 13. Flush Elementor cache
        $this->log("=== FLUSHING ELEMENTOR CACHE ===");
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
            $this->log("Elementor cache cleared");
        } else {
            $this->log("Elementor plugin not found - cache not cleared");
        }
        
        // 14. FORCE FINAL THEME ACTIVATION (override any cloned theme settings)
        $this->log("=== FORCE FINAL THEME ACTIVATION ===");
        
        // Force theme activation one more time to override anything from cloning
        $themes = wp_get_themes();
        $this->log("Available themes: " . implode(', ', array_keys($themes)));
        
        if (array_key_exists('hello-theme-child-master', $themes)) {
            switch_theme('hello-theme-child-master');
            
            // Also directly update the database options to be absolutely sure
            update_option('stylesheet', 'hello-theme-child-master');
            update_option('template', 'hello-elementor');
            
            $this->log("FORCED theme to hello-theme-child-master");
        } else {
            $this->log("ERROR: hello-theme-child-master theme not available!");
        }
        
        // 15. Final theme verification
        $this->log("=== FINAL THEME VERIFICATION ===");
        $this->verify_theme_activation();
        
        restore_current_blog();
        
        $this->log("Successfully cloned Elementor content with " . count($post_id_map) . " posts");
        $this->log("=== CLONE PROCESS END ===");
        
        return true;
    }
    
    /**
     * Replace placeholder content with actual form data
     */
    private function replace_placeholder_content($form_data, $post_id_map) {
        $this->log("Starting placeholder replacement with form data");
        
        // Extract city and state from business address
        $city = $this->extract_city($form_data['business_address'] ?? '');
        $state = $this->extract_state($form_data['business_address'] ?? '');
        
        // Prepare replacement mapping - handle both uppercase and lowercase
        $replacements = array(
            // Company/Business name
            '{{company}}' => $form_data['business_name'] ?? 'Your Business',
            '{{COMPANY}}' => $form_data['business_name'] ?? 'Your Business',
            '{{BUSINESS_NAME}}' => $form_data['business_name'] ?? 'Your Business',
            
            // Location
            '{{city}}' => $city,
            '{{CITY}}' => $city,
            '{{state}}' => $state,
            '{{STATE}}' => $state,
            
            // Contact info
            '{{phone}}' => $form_data['phone_number'] ?? 'Your Phone',
            '{{PHONE}}' => $form_data['phone_number'] ?? 'Your Phone',
            '{{email}}' => $form_data['business_email'] ?? 'your@email.com',
            '{{EMAIL}}' => $form_data['business_email'] ?? 'your@email.com',
        );
        
        $this->log("Replacement mapping: " . json_encode($replacements, JSON_PRETTY_PRINT));
        
        // Get all posts in the new site
        global $wpdb;
        $posts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_status IN ('publish', 'private', 'draft')");
        $this->log("Processing " . count($posts) . " posts for placeholder replacement");
        
        foreach ($posts as $post) {
            $updated = false;
            $new_content = $post->post_content;
            $new_title = $post->post_title;
            
            // Replace in post content and title
            foreach ($replacements as $placeholder => $replacement) {
                if (strpos($new_content, $placeholder) !== false) {
                    $new_content = str_replace($placeholder, $replacement, $new_content);
                    $updated = true;
                    $this->log("Replaced '{$placeholder}' with '{$replacement}' in post content: {$post->post_title}");
                }
                
                if (strpos($new_title, $placeholder) !== false) {
                    $new_title = str_replace($placeholder, $replacement, $new_title);
                    $updated = true;
                    $this->log("Replaced '{$placeholder}' with '{$replacement}' in post title: {$post->post_title}");
                }
            }
            
            // Also check ALL post meta fields (especially important for Elementor)
            $new_post_id = isset($post_id_map[$post->ID]) ? $post_id_map[$post->ID] : $post->ID;
            $meta_fields = get_post_meta($new_post_id);
            
            foreach ($meta_fields as $meta_key => $meta_values) {
                // Skip Elementor data - we handle it separately to preserve JSON structure
                if ($meta_key === '_elementor_data') {
                    continue;
                }
                
                foreach ($meta_values as $meta_value) {
                    if (is_string($meta_value)) {
                        $original_meta = $meta_value;
                        
                        foreach ($replacements as $placeholder => $replacement) {
                            if (strpos($meta_value, $placeholder) !== false) {
                                $meta_value = str_replace($placeholder, $replacement, $meta_value);
                                $this->log("Replaced '{$placeholder}' with '{$replacement}' in meta field '{$meta_key}' for post: {$post->post_title}");
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
        
        // Handle Elementor data separately to preserve JSON structure
        $this->replace_elementor_placeholders($replacements);
        
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
    
    /**
     * Activate the proper theme (Hello Elementor Child)
     */
    private function activate_proper_theme() {
        $this->log("Checking available themes...");
        
        // List all available themes
        $themes = wp_get_themes();
        $this->log("Available themes: " . implode(', ', array_keys($themes)));
        
        // Try to activate Hello Elementor Child first
        $preferred_themes = array(
            'hello-theme-child-master',
            'hello-elementor',
            'hello-theme-child', 
            'hellochild'
        );
        
        $activated_theme = null;
        foreach ($preferred_themes as $theme_name) {
            if (array_key_exists($theme_name, $themes)) {
                $this->log("Found preferred theme: {$theme_name}");
                switch_theme($theme_name);
                $activated_theme = $theme_name;
                break;
            }
        }
        
        if (!$activated_theme) {
            $this->log("ERROR: No preferred theme found! Available themes: " . implode(', ', array_keys($themes)));
            $this->log("Attempting to use first available theme...");
            if (!empty($themes)) {
                $first_theme = array_keys($themes)[0];
                switch_theme($first_theme);
                $activated_theme = $first_theme;
                $this->log("Activated fallback theme: {$first_theme}");
            }
        }
        
        $current_theme = get_option('stylesheet');
        $this->log("Theme activation completed. Current theme: {$current_theme}");
        
        return $activated_theme;
    }
    
    /**
     * Verify theme activation worked correctly
     */
    private function verify_theme_activation() {
        $stylesheet = get_option('stylesheet');
        $template = get_option('template');
        
        $this->log("Current active theme: stylesheet={$stylesheet}, template={$template}");
        
        // Check if theme files exist
        $theme_dir = get_theme_root() . '/' . $stylesheet;
        if (is_dir($theme_dir)) {
            $this->log("Theme directory exists: {$theme_dir}");
            
            // List key theme files
            $key_files = array('style.css', 'functions.php', 'index.php');
            foreach ($key_files as $file) {
                $file_path = $theme_dir . '/' . $file;
                if (file_exists($file_path)) {
                    $this->log("Theme file exists: {$file}");
                } else {
                    $this->log("WARNING: Missing theme file: {$file}");
                }
            }
        } else {
            $this->log("ERROR: Theme directory does not exist: {$theme_dir}");
        }
    }
    
    /**
     * Regenerate Elementor CSS for all posts with Elementor data
     */
    private function regenerate_elementor_css() {
        if (!class_exists('\Elementor\Plugin')) {
            $this->log("Elementor plugin not found - skipping CSS regeneration");
            return;
        }
        
        global $wpdb;
        
        // Get all posts with Elementor data
        $posts_with_elementor = $wpdb->get_results(
            "SELECT DISTINCT post_id FROM {$wpdb->prefix}postmeta 
             WHERE meta_key = '_elementor_data' AND meta_value != ''"
        );
        
        $this->log("Found " . count($posts_with_elementor) . " posts with Elementor data");
        
        if (empty($posts_with_elementor)) {
            $this->log("No posts with Elementor data found - skipping CSS regeneration");
            return;
        }
        
        $regenerated_count = 0;
        
        foreach ($posts_with_elementor as $post_data) {
            $post_id = $post_data->post_id;
            
            try {
                // Get Elementor data
                $elementor_data = get_post_meta($post_id, '_elementor_data', true);
                
                if (empty($elementor_data)) {
                    $this->log("No Elementor data found for post ID: {$post_id}");
                    continue;
                }
                
                // Parse Elementor data
                $elements = json_decode($elementor_data, true);
                if (!is_array($elements)) {
                    $this->log("Invalid Elementor data for post ID: {$post_id}");
                    continue;
                }
                
                // Use Elementor's CSS file manager to regenerate CSS
                $css_file = \Elementor\Core\Files\CSS\Post::create($post_id);
                $css_file->delete();
                $css_file->enqueue();
                
                // Also regenerate the post meta CSS
                $css_content = $css_file->get_content();
                if (!empty($css_content)) {
                    update_post_meta($post_id, '_elementor_css', $css_content);
                    $regenerated_count++;
                    $this->log("Regenerated CSS for post ID: {$post_id}");
                } else {
                    $this->log("Warning: Empty CSS generated for post ID: {$post_id}");
                }
                
            } catch (Exception $e) {
                $this->log("Error regenerating CSS for post ID {$post_id}: " . $e->getMessage());
            }
        }
        
        $this->log("CSS regeneration complete. Regenerated CSS for {$regenerated_count} posts");
        
        // Also regenerate global CSS
        try {
            if (method_exists('\Elementor\Plugin', 'instance')) {
                $global_css = \Elementor\Core\Files\CSS\Global_CSS::create();
                $global_css->delete();
                $global_css->enqueue();
                $this->log("Regenerated global Elementor CSS");
            }
        } catch (Exception $e) {
            $this->log("Error regenerating global CSS: " . $e->getMessage());
        }
    }
    
    /**
     * Fix Elementor data by updating internal references
     */
    private function fix_elementor_data($post_id_map) {
        global $wpdb;
        
        $this->log("=== FIXING ELEMENTOR DATA REFERENCES ===");
        
        // Get all posts with Elementor data
        $posts_with_elementor = $wpdb->get_results(
            "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta 
             WHERE meta_key = '_elementor_data' AND meta_value != '' AND meta_value != '[]'"
        );
        
        $fixed_count = 0;
        
        foreach ($posts_with_elementor as $post_data) {
            $post_id = $post_data->post_id;
            $elementor_data = $post_data->meta_value;
            
            // Check if it's valid JSON first
            $data_array = json_decode($elementor_data, true);
            if (!is_array($data_array)) {
                $this->log("Skipping post {$post_id} - invalid JSON");
                continue;
            }
            
            // Update post ID references in the Elementor data
            $updated_data = $this->update_elementor_references($elementor_data, $post_id_map);
            
            if ($updated_data !== $elementor_data) {
                // Update the database
                update_post_meta($post_id, '_elementor_data', $updated_data);
                $fixed_count++;
                $this->log("Fixed Elementor data references for post ID: {$post_id}");
            }
        }
        
        $this->log("Fixed Elementor data references in {$fixed_count} posts");
    }
    
    /**
     * Update post ID references within Elementor data
     */
    private function update_elementor_references($elementor_data, $post_id_map) {
        // Convert old post IDs to new post IDs in the JSON
        foreach ($post_id_map as $old_id => $new_id) {
            // Update various reference patterns in Elementor data
            $patterns = [
                '/"post_id":' . $old_id . '(?![0-9])/',
                '/"page_id":' . $old_id . '(?![0-9])/',
                '/"post":"' . $old_id . '"/',
                '/"page":"' . $old_id . '"/',
                '/\\\\"post_id\\\\":' . $old_id . '(?![0-9])/',
                '/\\\\"page_id\\\\":' . $old_id . '(?![0-9])/',
            ];
            
            $replacements = [
                '"post_id":' . $new_id,
                '"page_id":' . $new_id,
                '"post":"' . $new_id . '"',
                '"page":"' . $new_id . '"',
                '\\"post_id\\":' . $new_id,
                '\\"page_id\\":' . $new_id,
            ];
            
            $elementor_data = preg_replace($patterns, $replacements, $elementor_data);
        }
        
        return $elementor_data;
    }
    
    /**
     * Replace placeholders in Elementor data safely
     */
    private function replace_elementor_placeholders($replacements) {
        global $wpdb;
        
        $this->log("=== REPLACING ELEMENTOR PLACEHOLDERS ===");
        
        // Get all posts with Elementor data
        $posts_with_elementor = $wpdb->get_results(
            "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta 
             WHERE meta_key = '_elementor_data' AND meta_value != '' AND meta_value != '[]'"
        );
        
        $updated_count = 0;
        
        foreach ($posts_with_elementor as $post_data) {
            $post_id = $post_data->post_id;
            $elementor_data = $post_data->meta_value;
            
            // Parse JSON
            $data_array = json_decode($elementor_data, true);
            if (!is_array($data_array)) {
                $this->log("Skipping Elementor placeholder replacement for post {$post_id} - invalid JSON");
                continue;
            }
            
            // Convert back to string for replacement
            $elementor_string = json_encode($data_array);
            $original_string = $elementor_string;
            
            // Do replacements
            foreach ($replacements as $placeholder => $replacement) {
                if (strpos($elementor_string, $placeholder) !== false) {
                    // Escape replacement for JSON
                    $json_safe_replacement = str_replace(['\\', '"', "\n", "\r", "\t"], ['\\\\', '\\"', '\\n', '\\r', '\\t'], $replacement);
                    $elementor_string = str_replace($placeholder, $json_safe_replacement, $elementor_string);
                    $this->log("Replaced '{$placeholder}' in Elementor data for post ID: {$post_id}");
                }
            }
            
            if ($elementor_string !== $original_string) {
                // Validate JSON before saving
                $test_decode = json_decode($elementor_string, true);
                if (is_array($test_decode)) {
                    update_post_meta($post_id, '_elementor_data', $elementor_string);
                    $updated_count++;
                } else {
                    $this->log("ERROR: JSON validation failed after placeholder replacement for post {$post_id}");
                }
            }
        }
        
        $this->log("Updated Elementor placeholders in {$updated_count} posts");
    }

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
        
        // List ALL posts (not just 10)
        $posts = $wpdb->get_results("SELECT ID, post_title, post_type, post_status FROM {$wpdb->prefix}posts");
        foreach ($posts as $post) {
            $this->log("New site post: ID={$post->ID}, Title='{$post->post_title}', Type={$post->post_type}, Status={$post->post_status}");
            
            // Check for placeholders in content
            $content = get_post_field('post_content', $post->ID);
            if (strpos($content, '{{') !== false) {
                $this->log("FOUND UNREPLACED PLACEHOLDERS in post {$post->ID}: " . substr($content, 0, 200) . '...');
            }
        }
        
        // Check for Elementor data in detail
        $elementor_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_elementor_data'");
        $this->log("Posts with Elementor data in new site: " . $elementor_count);
        
        // Show sample Elementor data
        $elementor_sample = $wpdb->get_row("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_elementor_data' LIMIT 1");
        if ($elementor_sample) {
            $this->log("Sample Elementor data for post {$elementor_sample->post_id}: " . substr($elementor_sample->meta_value, 0, 300) . '...');
            
            // Check if it's valid JSON
            $elementor_json = json_decode($elementor_sample->meta_value, true);
            if ($elementor_json) {
                $this->log("Elementor data is valid JSON with " . count($elementor_json) . " elements");
            } else {
                $this->log("ERROR: Elementor data is NOT valid JSON!");
            }
        }
        
        // Check theme activation
        $stylesheet = get_option('stylesheet');
        $template = get_option('template');
        $this->log("New site theme: stylesheet={$stylesheet}, template={$template}");
        
        // Check if homepage is set
        $page_on_front = get_option('page_on_front');
        $show_on_front = get_option('show_on_front');
        $this->log("Homepage settings: show_on_front={$show_on_front}, page_on_front={$page_on_front}");
        
        // Check Elementor-specific options
        $elementor_options = array(
            'elementor_version',
            'elementor_scheme_color',
            'elementor_scheme_typography',
            'elementor_page_title_selector',
            'elementor_viewport_lg',
            'elementor_viewport_md'
        );
        
        $this->log("Checking Elementor options:");
        foreach ($elementor_options as $option) {
            $value = get_option($option);
            if ($value) {
                $this->log("  {$option}: " . (is_array($value) ? json_encode($value) : $value));
            } else {
                $this->log("  {$option}: NOT SET");
            }
        }
        
        restore_current_blog();
        $this->log("=== END NEW SITE DEBUG ===");
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
    
    /**
     * Show debug interface - Easy web-based log viewer
     */
    public function show_debug_interface($request) {
        $log_file = WP_CONTENT_DIR . '/docket-automated-site-creator.log';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Docket Site Creator - Debug Interface</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            background: #0073aa; 
            color: white; 
            padding: 20px; 
            border-radius: 8px 8px 0 0; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
        }
        .controls { 
            padding: 20px; 
            border-bottom: 1px solid #eee; 
            display: flex; 
            gap: 10px; 
            align-items: center;
        }
        .btn { 
            background: #0073aa; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 14px;
        }
        .btn:hover { background: #005a87; }
        .btn.danger { background: #d63638; }
        .btn.danger:hover { background: #b32d2e; }
        .status { 
            padding: 10px 15px; 
            border-radius: 4px; 
            margin-left: auto;
            font-weight: bold;
        }
        .status.online { background: #d4edda; color: #155724; }
        .logs { 
            padding: 20px; 
            height: 600px; 
            overflow-y: auto; 
            background: #1e1e1e; 
            color: #d4d4d4; 
            font-family: "Courier New", monospace; 
            font-size: 13px; 
            line-height: 1.4;
        }
        .log-line { 
            margin-bottom: 5px; 
            padding: 3px 0;
        }
        .log-line.error { color: #f48771; }
        .log-line.success { color: #4ec9b0; }
        .log-line.debug { color: #569cd6; }
        .log-line.warning { color: #dcdcaa; }
        .timestamp { color: #808080; }
        .empty-state { 
            text-align: center; 
            color: #666; 
            padding: 40px; 
        }
        .footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Docket Site Creator - Debug Interface</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Real-time debugging for automated site creation</p>
        </div>
        
        <div class="controls">
            <button class="btn" onclick="refreshLogs()">🔄 Refresh Logs</button>
            <button class="btn danger" onclick="clearLogs()">🗑️ Clear Logs</button>
            <label>
                <input type="checkbox" id="autoRefresh" checked onchange="toggleAutoRefresh()"> 
                Auto-refresh (5s)
            </label>
            <div class="status online" id="status">● Online</div>
        </div>
        
        <div class="logs" id="logsContainer">
            <div class="empty-state">Loading logs...</div>
        </div>
        
        <div class="footer">
            <strong>How to use:</strong> 
            1) Submit a form on yourdocketonline.com 
            2) Watch the logs appear here in real-time 
            3) Look for ERROR messages or missing template sites
            <br>
            <strong>Log file:</strong> ' . $log_file . '
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        
        async function refreshLogs() {
            try {
                const response = await fetch("' . site_url() . '/wp-json/docket/v1/debug-logs?api_key=docket_automation_key_2025");
                const data = await response.json();
                
                const container = document.getElementById("logsContainer");
                
                if (data.error) {
                    container.innerHTML = `<div class="empty-state">❌ ${data.error}</div>`;
                    return;
                }
                
                if (!data.recent_logs || data.recent_logs.trim() === "") {
                    container.innerHTML = `<div class="empty-state">📝 No logs yet. Submit a form to see debug info!</div>`;
                    return;
                }
                
                // Process and display logs
                const lines = data.recent_logs.split("\\n");
                let html = "";
                
                lines.forEach(line => {
                    if (line.trim() === "") return;
                    
                    let className = "log-line";
                    if (line.includes("ERROR")) className += " error";
                    else if (line.includes("SUCCESS")) className += " success";
                    else if (line.includes("DEBUG")) className += " debug";
                    else if (line.includes("WARNING")) className += " warning";
                    
                    // Highlight timestamps
                    line = line.replace(/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/g, \'<span class="timestamp">$1</span>\');
                    
                    html += `<div class="${className}">${line}</div>`;
                });
                
                container.innerHTML = html;
                container.scrollTop = container.scrollHeight; // Auto-scroll to bottom
                
                // Update status
                document.getElementById("status").innerHTML = `● Last updated: ${new Date().toLocaleTimeString()}`;
                
            } catch (error) {
                document.getElementById("logsContainer").innerHTML = `<div class="empty-state">❌ Error loading logs: ${error.message}</div>`;
            }
        }
        
        async function clearLogs() {
            if (!confirm("Are you sure you want to clear all debug logs?")) return;
            
            try {
                const response = await fetch("' . site_url() . '/wp-json/docket/v1/clear-logs?api_key=docket_automation_key_2025", {
                    method: "DELETE"
                });
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById("logsContainer").innerHTML = `<div class="empty-state">✅ Logs cleared! Submit a form to see new debug info.</div>`;
                } else {
                    alert("Error clearing logs: " + (data.error || "Unknown error"));
                }
            } catch (error) {
                alert("Error clearing logs: " + error.message);
            }
        }
        
        function toggleAutoRefresh() {
            const checkbox = document.getElementById("autoRefresh");
            
            if (checkbox.checked) {
                autoRefreshInterval = setInterval(refreshLogs, 5000); // Refresh every 5 seconds
            } else {
                clearInterval(autoRefreshInterval);
            }
        }
        
        // Initialize
        refreshLogs();
        toggleAutoRefresh(); // Start auto-refresh
    </script>
</body>
</html>';

        // Set proper headers
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        die(); // Stop WordPress from adding any additional content
    }
    
    /**
     * Get site ID by path
     */
    private function get_site_id_by_path($path) {
        $site = get_sites(array(
            'path' => $path,
            'number' => 1
        ));
        
        return !empty($site) ? $site[0]->blog_id : false;
    }
    
    /**
     * Get next available site number
     */
    private function get_next_site_number() {
        $sites = get_sites(array(
            'path__like' => '/docketsite'
        ));
        
        $highest_number = 0;
        foreach ($sites as $site) {
            if (preg_match('/\/docketsite(\d+)\//i', $site->path, $matches)) {
                $number = intval($matches[1]);
                if ($number > $highest_number) {
                    $highest_number = $number;
                }
            }
        }
        
        return $highest_number + 1;
    }
    
    /**
     * Log debugging information
     */
    private function log($message) {
        $log_file = WP_CONTENT_DIR . '/docket-automated-site-creator.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// Initialize the plugin
new DocketAutomatedSiteCreator(); 
