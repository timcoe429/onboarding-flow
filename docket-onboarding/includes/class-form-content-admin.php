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
                    <p>Please select both a form type and step to edit content.</p>
                    <button type="button" id="manual-save-btn" class="button button-primary" style="display:none;">Save Changes</button>
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
    
}

// Initialize the admin interface
new Docket_Form_Content_Admin();
