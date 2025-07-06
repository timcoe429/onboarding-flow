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
        add_action('wp_ajax_esc_debug_check_urls', array($this, 'ajax_debug_check_urls'));
        add_action('wp_ajax_esc_force_fix_urls', array($this, 'ajax_force_fix_urls'));
        
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
        
        add_submenu_page(
            'elementor-site-cloner',
            __('Debug Tools', 'elementor-site-cloner'),
            __('Debug', 'elementor-site-cloner'),
            'manage_network',
            'elementor-site-cloner-debug',
            array($this, 'render_debug_page')
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
     * Render the debug page
     */
    public function render_debug_page() {
        // Get all sites
        $sites = get_sites(array(
            'number' => 1000,
            'archived' => 0,
            'deleted' => 0
        ));
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Elementor Site Cloner - Debug Tools', 'elementor-site-cloner'); ?></h1>
            
            <div class="esc-debug-container">
                <div class="esc-debug-form">
                    <h2><?php echo esc_html__('Check Site URLs', 'elementor-site-cloner'); ?></h2>
                    <p><?php echo esc_html__('Select a site to check its URL configuration and find any remaining source URLs.', 'elementor-site-cloner'); ?></p>
                    
                    <form id="esc-debug-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="debug_site"><?php echo esc_html__('Site to Debug', 'elementor-site-cloner'); ?></label>
                                </th>
                                <td>
                                    <select name="debug_site" id="debug_site" required>
                                        <option value=""><?php echo esc_html__('Select a site', 'elementor-site-cloner'); ?></option>
                                        <?php foreach ($sites as $site) : ?>
                                            <option value="<?php echo esc_attr($site->blog_id); ?>">
                                                <?php echo esc_html($site->domain . $site->path); ?> - <?php echo esc_html(get_blog_option($site->blog_id, 'blogname')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="source_url"><?php echo esc_html__('Source URL to Search', 'elementor-site-cloner'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="source_url" id="source_url" class="regular-text" placeholder="https://example.com/template1" />
                                    <p class="description"><?php echo esc_html__('Optional: Enter the source URL to search for in the database', 'elementor-site-cloner'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary" id="check-urls">
                                <?php echo esc_html__('Check URLs', 'elementor-site-cloner'); ?>
                            </button>
                            <button type="button" class="button" id="force-fix-urls">
                                <?php echo esc_html__('Force Fix URLs', 'elementor-site-cloner'); ?>
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="esc-debug-results" style="display: none;">
                    <h2><?php echo esc_html__('Debug Results', 'elementor-site-cloner'); ?></h2>
                    <div class="esc-debug-output"></div>
                </div>
            </div>
        </div>
        
        <style>
            .esc-debug-container {
                max-width: 1000px;
                margin-top: 20px;
            }
            
            .esc-debug-form,
            .esc-debug-results {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .esc-debug-output {
                font-family: monospace;
                background: #f0f0f1;
                padding: 15px;
                border-radius: 3px;
                overflow-x: auto;
            }
            
            .esc-debug-output h3 {
                margin-top: 20px;
                margin-bottom: 10px;
                color: #1d2327;
            }
            
            .esc-debug-output pre {
                margin: 0;
                white-space: pre-wrap;
            }
            
            .esc-url-issue {
                background: #fcf0f1;
                border-left: 4px solid #d63638;
                padding: 10px;
                margin: 10px 0;
            }
            
            .esc-url-ok {
                background: #edfaef;
                border-left: 4px solid #00a32a;
                padding: 10px;
                margin: 10px 0;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#esc-debug-form').on('submit', function(e) {
                e.preventDefault();
                
                var site_id = $('#debug_site').val();
                var source_url = $('#source_url').val();
                
                if (!site_id) return;
                
                $('#check-urls').prop('disabled', true).text('Checking...');
                $('.esc-debug-results').show();
                $('.esc-debug-output').html('<p>Loading...</p>');
                
                $.post(ajaxurl, {
                    action: 'esc_debug_check_urls',
                    nonce: '<?php echo wp_create_nonce('esc-debug-nonce'); ?>',
                    site_id: site_id,
                    source_url: source_url
                }, function(response) {
                    if (response.success) {
                        var html = '<h3>Critical URLs:</h3><pre>' + JSON.stringify(response.data.critical_urls, null, 2) + '</pre>';
                        
                        if (response.data.remaining_urls && Object.keys(response.data.remaining_urls).length > 0) {
                            html += '<div class="esc-url-issue"><h3>⚠️ Found Source URLs:</h3><pre>' + JSON.stringify(response.data.remaining_urls, null, 2) + '</pre></div>';
                        } else if (source_url) {
                            html += '<div class="esc-url-ok"><h3>✅ No source URLs found!</h3></div>';
                        }
                        
                        $('.esc-debug-output').html(html);
                    } else {
                        $('.esc-debug-output').html('<p class="error">' + response.data.message + '</p>');
                    }
                    
                    $('#check-urls').prop('disabled', false).text('Check URLs');
                });
            });
            
            $('#force-fix-urls').on('click', function() {
                var site_id = $('#debug_site').val();
                if (!site_id) {
                    alert('Please select a site first');
                    return;
                }
                
                if (!confirm('This will force update the siteurl and home options. Continue?')) {
                    return;
                }
                
                $(this).prop('disabled', true).text('Fixing...');
                
                $.post(ajaxurl, {
                    action: 'esc_force_fix_urls',
                    nonce: '<?php echo wp_create_nonce('esc-debug-nonce'); ?>',
                    site_id: site_id
                }, function(response) {
                    if (response.success) {
                        alert('URLs have been force updated. Please check the site again.');
                        $('#esc-debug-form').submit();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                    
                    $('#force-fix-urls').prop('disabled', false).text('Force Fix URLs');
                });
            });
        });
        </script>
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
    
    /**
     * AJAX handler to check URLs in debug
     */
    public function ajax_debug_check_urls() {
        // Verify nonce
        if (!check_ajax_referer('esc-debug-nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_network')) {
            wp_die('Insufficient permissions');
        }
        
        $site_id = intval($_POST['site_id']);
        $source_url = sanitize_text_field($_POST['source_url']);
        
        // Include debug utility
        require_once ESC_PLUGIN_DIR . 'includes/class-debug-utility.php';
        
        // Get critical URLs
        $critical_urls = ESC_Debug_Utility::get_critical_urls($site_id);
        
        // Check for remaining source URLs if provided
        $remaining_urls = array();
        if (!empty($source_url)) {
            $remaining_urls = ESC_Debug_Utility::check_remaining_urls($site_id, $source_url);
        }
        
        wp_send_json_success(array(
            'critical_urls' => $critical_urls,
            'remaining_urls' => $remaining_urls
        ));
    }
    
    /**
     * AJAX handler to force fix URLs
     */
    public function ajax_force_fix_urls() {
        // Verify nonce
        if (!check_ajax_referer('esc-debug-nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_network')) {
            wp_die('Insufficient permissions');
        }
        
        $site_id = intval($_POST['site_id']);
        
        // Include debug utility
        require_once ESC_PLUGIN_DIR . 'includes/class-debug-utility.php';
        
        // Force update URLs
        $result = ESC_Debug_Utility::force_update_urls($site_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'URLs updated successfully'
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to update URLs'
            ));
        }
    }
}
