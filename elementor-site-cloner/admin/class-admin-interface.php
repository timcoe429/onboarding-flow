<?php
/**
 * Admin Interface Class
 * Provides the UI for site cloning functionality
 */
class ESC_Admin_Interface {
    
    public function __construct() {
        // Add admin menu
        add_action('network_admin_menu', array($this, 'add_admin_menu'));
        
        // Handle AJAX requests
        add_action('wp_ajax_esc_start_clone', array($this, 'ajax_start_clone'));
        add_action('wp_ajax_esc_check_status', array($this, 'ajax_check_status'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Elementor Site Cloner', 'elementor-site-cloner'),
            __('Site Cloner', 'elementor-site-cloner'),
            'manage_network',
            'elementor-site-cloner',
            array($this, 'render_admin_page'),
            'dashicons-admin-multisite',
            30
        );
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // Get all sites in the network
        $sites = get_sites(array(
            'number' => 1000,
            'archived' => 0,
            'deleted' => 0
        ));
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Elementor Site Cloner', 'elementor-site-cloner'); ?></h1>
            
            <div class="esc-admin-container">
                <div class="esc-clone-form">
                    <h2><?php echo esc_html__('Clone a Site', 'elementor-site-cloner'); ?></h2>
                    
                    <form id="esc-clone-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="source_site"><?php echo esc_html__('Source Site', 'elementor-site-cloner'); ?></label>
                                </th>
                                <td>
                                    <select name="source_site" id="source_site" required>
                                        <option value=""><?php echo esc_html__('Select a site to clone', 'elementor-site-cloner'); ?></option>
                                        <?php foreach ($sites as $site) : ?>
                                            <option value="<?php echo esc_attr($site->blog_id); ?>">
                                                <?php echo esc_html($site->domain . $site->path); ?> - <?php echo esc_html(get_blog_option($site->blog_id, 'blogname')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php echo esc_html__('Select the site you want to clone', 'elementor-site-cloner'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="site_name"><?php echo esc_html__('New Site Name', 'elementor-site-cloner'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="site_name" id="site_name" class="regular-text" required />
                                    <p class="description"><?php echo esc_html__('Enter the name for the new site', 'elementor-site-cloner'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="site_url"><?php echo esc_html__('New Site URL', 'elementor-site-cloner'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $network = get_network();
                                    if (is_subdomain_install()) {
                                        ?>
                                        <input type="text" name="site_subdomain" id="site_subdomain" class="regular-text" required />
                                        <span>.<?php echo esc_html($network->domain); ?></span>
                                        <?php
                                    } else {
                                        ?>
                                        <span><?php echo esc_html($network->domain . $network->path); ?></span>
                                        <input type="text" name="site_path" id="site_path" class="regular-text" required />
                                        <?php
                                    }
                                    ?>
                                    <p class="description"><?php echo esc_html__('Enter the URL path or subdomain for the new site', 'elementor-site-cloner'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary" id="start-clone">
                                <?php echo esc_html__('Start Cloning', 'elementor-site-cloner'); ?>
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="esc-progress-container" style="display: none;">
                    <h2><?php echo esc_html__('Cloning Progress', 'elementor-site-cloner'); ?></h2>
                    <div class="esc-progress-bar">
                        <div class="esc-progress-fill"></div>
                    </div>
                    <div class="esc-status-message"></div>
                    <div class="esc-error-message" style="display: none;"></div>
                </div>
                
                <div class="esc-recent-clones">
                    <h2><?php echo esc_html__('Recent Clones', 'elementor-site-cloner'); ?></h2>
                    <?php $this->display_recent_clones(); ?>
                </div>
            </div>
        </div>
        
        <style>
            .esc-admin-container {
                max-width: 800px;
                margin-top: 20px;
            }
            
            .esc-clone-form,
            .esc-progress-container,
            .esc-recent-clones {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .esc-progress-bar {
                width: 100%;
                height: 30px;
                background: #f0f0f1;
                border-radius: 3px;
                overflow: hidden;
                margin: 20px 0;
            }
            
            .esc-progress-fill {
                height: 100%;
                background: #2271b1;
                width: 0%;
                transition: width 0.3s ease;
            }
            
            .esc-status-message {
                color: #666;
                font-style: italic;
            }
            
            .esc-error-message {
                color: #d63638;
                background: #f0f0f1;
                padding: 10px;
                border-radius: 3px;
                margin-top: 10px;
            }
            
            .esc-recent-clones table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .esc-recent-clones th,
            .esc-recent-clones td {
                text-align: left;
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            
            .esc-status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 500;
            }
            
            .esc-status-badge.completed {
                background: #d4edda;
                color: #155724;
            }
            
            .esc-status-badge.failed {
                background: #f8d7da;
                color: #721c24;
            }
            
            .esc-status-badge.in-progress {
                background: #cfe2ff;
                color: #084298;
            }
        </style>
        <?php
    }
    
    /**
     * Display recent clones table
     */
    private function display_recent_clones() {
        global $wpdb;
        
        $recent_clones = $wpdb->get_results(
            "SELECT * FROM {$wpdb->base_prefix}esc_clone_logs 
             ORDER BY started_at DESC 
             LIMIT 10"
        );
        
        if (empty($recent_clones)) {
            echo '<p>' . esc_html__('No clones yet.', 'elementor-site-cloner') . '</p>';
            return;
        }
        
        ?>
        <table>
            <thead>
                <tr>
                    <th><?php echo esc_html__('Source', 'elementor-site-cloner'); ?></th>
                    <th><?php echo esc_html__('Destination', 'elementor-site-cloner'); ?></th>
                    <th><?php echo esc_html__('Status', 'elementor-site-cloner'); ?></th>
                    <th><?php echo esc_html__('Started', 'elementor-site-cloner'); ?></th>
                    <th><?php echo esc_html__('Actions', 'elementor-site-cloner'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_clones as $clone) : ?>
                    <tr>
                        <td>
                            <?php
                            $source_site = get_site($clone->source_site_id);
                            echo esc_html($source_site ? $source_site->domain . $source_site->path : 'Deleted');
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($clone->destination_site_id) {
                                $dest_site = get_site($clone->destination_site_id);
                                echo esc_html($dest_site ? $dest_site->domain . $dest_site->path : 'Deleted');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="esc-status-badge <?php echo esc_attr($clone->status); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $clone->status))); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(wp_date('Y-m-d H:i', strtotime($clone->started_at))); ?></td>
                        <td>
                            <?php if ($clone->status === 'completed' && $clone->destination_site_id) : ?>
                                <a href="<?php echo esc_url(get_admin_url($clone->destination_site_id)); ?>" class="button button-small">
                                    <?php echo esc_html__('Visit Admin', 'elementor-site-cloner'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_elementor-site-cloner') {
            return;
        }
        
        wp_enqueue_script(
            'esc-admin-script',
            ESC_PLUGIN_URL . 'admin/admin.js',
            array('jquery'),
            ESC_VERSION,
            true
        );
        
        wp_localize_script('esc-admin-script', 'esc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('esc-clone-nonce'),
            'messages' => array(
                'creating_site' => __('Creating new site...', 'elementor-site-cloner'),
                'cloning_database' => __('Cloning database...', 'elementor-site-cloner'),
                'updating_urls' => __('Updating URLs...', 'elementor-site-cloner'),
                'cloning_files' => __('Cloning files...', 'elementor-site-cloner'),
                'processing_elementor' => __('Processing Elementor data...', 'elementor-site-cloner'),
                'finalizing' => __('Finalizing clone...', 'elementor-site-cloner'),
                'completed' => __('Clone completed successfully!', 'elementor-site-cloner'),
                'failed' => __('Clone failed:', 'elementor-site-cloner')
            )
        ));
    }
    
    /**
     * AJAX handler to start cloning
     */
    public function ajax_start_clone() {
        // Verify nonce
        if (!check_ajax_referer('esc-clone-nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_network')) {
            wp_die('Insufficient permissions');
        }
        
        // Get parameters
        $source_site_id = intval($_POST['source_site']);
        $site_name = sanitize_text_field($_POST['site_name']);
        
        // Build site URL
        $network = get_network();
        if (is_subdomain_install()) {
            $subdomain = sanitize_key($_POST['site_subdomain']);
            $site_url = 'http://' . $subdomain . '.' . $network->domain;
        } else {
            $path = trim(sanitize_text_field($_POST['site_path']), '/');
            $site_url = 'http://' . $network->domain . $network->path . $path;
        }
        
        // Start cloning
        $clone_manager = new ESC_Clone_Manager();
        $result = $clone_manager->clone_site($source_site_id, $site_name, $site_url);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler to check clone status
     */
    public function ajax_check_status() {
        // Verify nonce
        if (!check_ajax_referer('esc-clone-nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        $log_id = intval($_POST['log_id']);
        $status = ESC_Clone_Manager::get_clone_status($log_id);
        
        if ($status) {
            wp_send_json_success(array(
                'status' => $status->status,
                'error_message' => $status->error_message
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Clone log not found'
            ));
        }
    }
}
