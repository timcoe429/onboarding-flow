<?php
/**
 * Elementor Handler Class
 * Handles Elementor-specific processing and regeneration
 */
class ESC_Elementor_Handler {
    
    private $site_id;
    
    public function __construct($site_id) {
        $this->site_id = $site_id;
    }
    
    /**
     * Process all Elementor-specific data after cloning
     */
    public function process_elementor_data() {
        // Switch to the destination site
        switch_to_blog($this->site_id);
        
        // Clear caches to ensure WordPress uses correct site context
        // This prevents slug validation from checking against wrong site
        wp_cache_flush();
        wp_cache_delete('alloptions', 'options');
        if (function_exists('wp_cache_switch_to_blog')) {
            wp_cache_switch_to_blog($this->site_id);
        }
        clean_blog_cache($this->site_id);
        
        try {
            // Ensure Elementor is active
            if (!did_action('elementor/loaded')) {
                restore_current_blog();
                return new WP_Error('elementor_not_active', 'Elementor is not active on the destination site');
            }
            
            // Clear Elementor cache
            $this->clear_elementor_cache();
            
            // Regenerate Elementor CSS files
            $this->regenerate_css_files();
            
            // Update Elementor settings
            $this->update_elementor_settings();
            
            // Fix kit settings
            $this->fix_kit_settings();
            
            // Update global colors and fonts
            $this->update_global_styles();
            
            // Regenerate assets
            $this->regenerate_assets();
            
            restore_current_blog();
            return true;
            
        } catch (Exception $e) {
            restore_current_blog();
            return new WP_Error('elementor_processing_failed', $e->getMessage());
        }
    }
    
    /**
     * Clear all Elementor caches
     */
    private function clear_elementor_cache() {
        // Clear Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        // Delete cache meta
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE '%_elementor_css%' 
             OR meta_key = '_elementor_inline_svg'"
        );
        
        // Clear uploads/elementor/css directory
        $upload_dir = wp_upload_dir();
        $elementor_css_dir = $upload_dir['basedir'] . '/elementor/css';
        
        if (is_dir($elementor_css_dir)) {
            $this->delete_directory_contents($elementor_css_dir);
        }
    }
    
    /**
     * Regenerate all Elementor CSS files
     */
    private function regenerate_css_files() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        // Get all posts/pages with Elementor data
        global $wpdb;
        $elementor_posts = $wpdb->get_results(
            "SELECT ID FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = '_elementor_data'
             AND pm.meta_value != ''
             AND pm.meta_value != '[]'
             AND p.post_status = 'publish'"
        );
        
        foreach ($elementor_posts as $post) {
            // Regenerate CSS for each post
            if (class_exists('\Elementor\Core\Files\CSS\Post')) {
                $css_file = new \Elementor\Core\Files\CSS\Post($post->ID);
                $css_file->update();
            }
        }
        
        // Regenerate global CSS
        if (class_exists('\Elementor\Core\Files\CSS\Global_CSS')) {
            $global_css = new \Elementor\Core\Files\CSS\Global_CSS();
            $global_css->update();
        }
    }
    
    /**
     * Update Elementor settings
     */
    private function update_elementor_settings() {
        // Ensure default settings are set
        $default_settings = array(
            'elementor_cpt_support' => array('post', 'page'),
            'elementor_disable_color_schemes' => 'yes',
            'elementor_disable_typography_schemes' => 'yes',
            'elementor_container_width' => '1140',
            'elementor_space_between_widgets' => '20',
            'elementor_stretched_section_container' => '',
            'elementor_page_title_selector' => 'h1.entry-title',
            'elementor_viewport_lg' => '1025',
            'elementor_viewport_md' => '768'
        );
        
        foreach ($default_settings as $option_name => $default_value) {
            $current_value = get_option($option_name);
            if ($current_value === false) {
                update_option($option_name, $default_value);
            }
        }
        
        // Update scheme colors if they don't exist
        $scheme_colors = get_option('elementor_scheme_color');
        if (!$scheme_colors) {
            update_option('elementor_scheme_color', array(
                '1' => '#6EC1E4',
                '2' => '#54595F',
                '3' => '#7A7A7A',
                '4' => '#61CE70'
            ));
        }
        
        // Update scheme typography if it doesn't exist
        $scheme_typography = get_option('elementor_scheme_typography');
        if (!$scheme_typography) {
            update_option('elementor_scheme_typography', array(
                '1' => array('font_family' => 'Roboto', 'font_weight' => '600'),
                '2' => array('font_family' => 'Roboto Slab', 'font_weight' => '400'),
                '3' => array('font_family' => 'Roboto', 'font_weight' => '400'),
                '4' => array('font_family' => 'Roboto', 'font_weight' => '500')
            ));
        }
    }
    
    /**
     * Fix Elementor Kit settings
     */
    private function fix_kit_settings() {
        // Get the active kit
        $kit_id = get_option('elementor_active_kit');
        
        if (!$kit_id) {
            // Find or create a kit
            $kit_id = $this->find_or_create_kit();
            update_option('elementor_active_kit', $kit_id);
        }
        
        // Ensure kit post exists and is published
        $kit_post = get_post($kit_id);
        if (!$kit_post || $kit_post->post_status !== 'publish') {
            $kit_id = $this->find_or_create_kit();
            update_option('elementor_active_kit', $kit_id);
        }
    }
    
    /**
     * Find or create an Elementor kit
     */
    private function find_or_create_kit() {
        // Look for existing kit
        $existing_kit = get_posts(array(
            'post_type' => 'elementor_library',
            'meta_key' => '_elementor_template_type',
            'meta_value' => 'kit',
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ));
        
        if (!empty($existing_kit)) {
            return $existing_kit[0]->ID;
        }
        
        // Create new kit
        $kit_id = wp_insert_post(array(
            'post_title' => 'Default Kit',
            'post_type' => 'elementor_library',
            'post_status' => 'publish',
            'meta_input' => array(
                '_elementor_template_type' => 'kit',
                '_elementor_edit_mode' => 'builder'
            )
        ));
        
        // Set default kit settings
        if ($kit_id && !is_wp_error($kit_id)) {
            $default_kit_settings = array(
                'system_colors' => array(
                    array(
                        '_id' => 'primary',
                        'title' => 'Primary',
                        'color' => '#6EC1E4'
                    ),
                    array(
                        '_id' => 'secondary',
                        'title' => 'Secondary',
                        'color' => '#54595F'
                    ),
                    array(
                        '_id' => 'text',
                        'title' => 'Text',
                        'color' => '#7A7A7A'
                    ),
                    array(
                        '_id' => 'accent',
                        'title' => 'Accent',
                        'color' => '#61CE70'
                    )
                ),
                'system_typography' => array(
                    array(
                        '_id' => 'primary',
                        'title' => 'Primary',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => '600'
                    ),
                    array(
                        '_id' => 'secondary',
                        'title' => 'Secondary',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto Slab',
                        'typography_font_weight' => '400'
                    ),
                    array(
                        '_id' => 'text',
                        'title' => 'Text',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => '400'
                    ),
                    array(
                        '_id' => 'accent',
                        'title' => 'Accent',
                        'typography_typography' => 'custom',
                        'typography_font_family' => 'Roboto',
                        'typography_font_weight' => '500'
                    )
                )
            );
            
            update_post_meta($kit_id, '_elementor_page_settings', $default_kit_settings);
        }
        
        return $kit_id;
    }
    
    /**
     * Update global colors and fonts
     */
    private function update_global_styles() {
        // This ensures any global styles are properly set
        $kit_id = get_option('elementor_active_kit');
        
        if ($kit_id) {
            // Trigger Elementor to regenerate kit CSS
            if (class_exists('\Elementor\Core\Kits\Documents\Kit')) {
                $kit = \Elementor\Plugin::$instance->documents->get($kit_id);
                if ($kit) {
                    $kit->save(array());
                }
            }
        }
    }
    
    /**
     * Regenerate all Elementor assets
     */
    private function regenerate_assets() {
        // Clear any transients
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_elementor_%' 
             OR option_name LIKE '_transient_timeout_elementor_%'"
        );
        
        // Regenerate fonts cache
        if (class_exists('\Elementor\Core\Files\Manager')) {
            $files_manager = new \Elementor\Core\Files\Manager();
            $files_manager->clear_cache();
        }
    }
    
    /**
     * Delete directory contents
     */
    private function delete_directory_contents($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->delete_directory_contents($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}
