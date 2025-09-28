<?php
/**
 * Form Content Admin Interface
 * 
 * WordPress admin interface for editing form content
 */

if (!defined('ABSPATH')) {
    exit;
}

class Docket_Form_Content_Admin {
    
    private $content_manager;
    
    public function __construct() {
        $this->content_manager = new Docket_Form_Content_Manager();
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'Form Content Manager',
            'Form Content',
            'manage_options',
            'docket-form-content',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_docket-form-content') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_editor();
        wp_enqueue_media();
        
        wp_enqueue_script(
            'docket-form-content-admin',
            plugin_dir_url(__FILE__) . '../assets/form-content-admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_enqueue_style(
            'docket-form-content-admin',
            plugin_dir_url(__FILE__) . '../assets/form-content-admin.css',
            array(),
            '1.0.0'
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('docket-form-content-admin', 'docketFormContent', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('docket_form_content_nonce'),
            'strings' => array(
                'saving' => 'Saving...',
                'saved' => 'Saved!',
                'error' => 'Error saving content',
                'confirm' => 'Are you sure you want to discard changes?'
            )
        ));
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        $form_types = $this->content_manager->get_form_types();
        $current_form = isset($_GET['form']) ? sanitize_text_field($_GET['form']) : 'fast-build';
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        
        ?>
        <div class="wrap">
            <h1>Form Content Manager</h1>
            <p>Edit the content displayed in your onboarding forms. Changes are saved automatically.</p>
            
            <div class="docket-form-content-admin">
                <!-- Form and Step Selector -->
                <div class="form-selector">
                    <div class="form-type-selector">
                        <label for="form-type">Form Type:</label>
                        <select id="form-type" name="form_type">
                            <?php foreach ($form_types as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_form, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="step-selector">
                        <label for="step-number">Step:</label>
                        <select id="step-number" name="step_number">
                            <?php 
                            $steps = $this->content_manager->get_steps_for_form($current_form);
                            foreach ($steps as $step): 
                            ?>
                                <option value="<?php echo esc_attr($step); ?>" <?php selected($current_step, $step); ?>>
                                    Step <?php echo esc_html($step); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Content Editor -->
                <div class="content-editor">
                    <?php $this->render_content_editor($current_form, $current_step); ?>
                </div>
                
                <!-- Preview -->
                <div class="content-preview">
                    <h3>Preview</h3>
                    <div class="preview-container">
                        <iframe id="preview-frame" src="about:blank"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the content editor for a specific form and step
     */
    private function render_content_editor($form_type, $step_number) {
        $content = $this->content_manager->get_step_content($form_type, $step_number);
        
        if (empty($content)) {
            echo '<p>No content found for this form and step.</p>';
            return;
        }
        
        echo '<div class="content-fields">';
        
        foreach ($content as $key => $value) {
            $field_label = $this->get_field_label($key);
            $field_type = $this->get_field_type($key);
            
            echo '<div class="content-field">';
            echo '<label for="content-' . esc_attr($key) . '">' . esc_html($field_label) . '</label>';
            
            if ($field_type === 'textarea') {
                echo '<textarea id="content-' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="4">' . esc_textarea($value) . '</textarea>';
            } elseif ($field_type === 'editor') {
                echo '<div class="wp-editor-container">';
                wp_editor($value, 'content-' . esc_attr($key), array(
                    'textarea_name' => esc_attr($key),
                    'media_buttons' => false,
                    'textarea_rows' => 6,
                    'teeny' => true,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,underline,link,unlink,undo,redo',
                        'toolbar2' => ''
                    )
                ));
                echo '</div>';
            } else {
                echo '<input type="text" id="content-' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Get human-readable field label
     */
    private function get_field_label($key) {
        $labels = array(
            'form_title' => 'Form Title',
            'form_subtitle' => 'Form Subtitle',
            'what_youre_getting' => 'What You\'re Getting',
            'timeline' => 'Timeline',
            'what_we_need' => 'What We Need From You',
            'important_notes' => 'Important Notes',
            'whats_included' => 'What\'s Included',
            'post_launch_services' => 'Post-Launch Services',
            'vip_benefits' => 'WebsiteVIP Benefits',
            'info_title' => 'Information Title',
            'intro_text' => 'Introduction Text',
            'stock_content' => 'Stock Content Info',
            'no_revisions' => 'No Revisions Info',
            'self_customization' => 'Self-Customization Info',
            'turnaround' => 'Turnaround Info',
            'customized_website' => 'Customized Website Info',
            'sections_pages' => 'Sections & Pages Info',
            'revisions' => 'Revisions Info',
            'additional_customizations' => 'Additional Customizations Info',
            'review_period' => 'Review Period Info',
            'charges' => 'Charges Info',
            'refund_policy' => 'Refund Policy Info',
            'acceptance_text' => 'Acceptance Checkbox Text'
        );
        
        return isset($labels[$key]) ? $labels[$key] : ucwords(str_replace('_', ' ', $key));
    }
    
    /**
     * Get field type based on content key
     */
    private function get_field_type($key) {
        $editor_fields = array(
            'what_youre_getting', 'timeline', 'what_we_need', 'important_notes',
            'whats_included', 'post_launch_services', 'vip_benefits', 'intro_text',
            'stock_content', 'no_revisions', 'self_customization', 'turnaround',
            'customized_website', 'sections_pages', 'revisions', 'additional_customizations',
            'review_period', 'charges', 'refund_policy'
        );
        
        $textarea_fields = array('acceptance_text');
        
        if (in_array($key, $editor_fields)) {
            return 'editor';
        } elseif (in_array($key, $textarea_fields)) {
            return 'textarea';
        } else {
            return 'text';
        }
    }
}

// Initialize the admin interface
new Docket_Form_Content_Admin();
