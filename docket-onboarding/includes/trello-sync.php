<?php
/**
 * Trello API Integration for Project Status Sync
 * Syncs project status based on card positions in Trello board
 */

class DocketTrelloSync {
    
    private $api_key = 'bb036ac8c5b6301f2547de0bae0e551d';
    private $token = 'ATTAdc6d631ead317c421f6a29fda75409bd50dbbfddbd7a789327860f8eb31fce23A6577396';
    private $board_id = 'XzUXs3SH';
    private $api_base = 'https://api.trello.com/1';
    
    // Map Trello list names to project status
    private $status_mapping = array(
        'Docket Team' => 'docket_team',
        'QA' => 'qa',
        'New Builds Ready to Send' => 'ready_to_send',
        'Waiting on Review Scheduling' => 'waiting_review_scheduling',
        'Client Reviewing' => 'client_reviewing',
        'Edits to Complete' => 'edits_to_complete',
        'Review Edits Completed' => 'review_edits_completed',
        'Pre Launch' => 'pre_launch',
        'Ready for Launch' => 'ready_for_launch',
        'Web Complete: Grow/Legacy' => 'web_complete_grow',
        'Web Complete: Pro/ServiceCore' => 'web_complete_pro'
    );
    
    public function __construct() {
        // Hook into WordPress admin for manual sync
        add_action('wp_ajax_sync_trello', array($this, 'manual_sync'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Schedule automatic sync (every 30 minutes)
        add_action('wp', array($this, 'schedule_sync'));
        add_action('docket_trello_sync_hook', array($this, 'sync_all_projects'));
        
        // Migrate old project statuses
        add_action('init', array($this, 'migrate_old_statuses'));
    }
    
    /**
     * Migrate old project statuses to new system
     */
    public function migrate_old_statuses() {
        global $wpdb;
        
        $projects_table = $wpdb->prefix . 'docket_client_projects';
        
        // Check if we have projects with old statuses
        $old_projects = $wpdb->get_results("
            SELECT id, current_step FROM {$projects_table} 
            WHERE current_step IN ('submitted', 'building', 'review', 'final_touches', 'launched')
        ");
        
        if (!$old_projects) {
            return; // No migration needed
        }
        
        // Map old statuses to new ones
        $migration_map = array(
            'submitted' => 'docket_team',
            'building' => 'qa',
            'review' => 'client_reviewing',
            'final_touches' => 'pre_launch',
            'launched' => 'ready_for_launch'
        );
        
        foreach ($old_projects as $project) {
            $old_status = $project->current_step;
            $new_status = $migration_map[$old_status] ?? 'docket_team';
            
            // Update project status
            $wpdb->update(
                $projects_table,
                array('current_step' => $new_status),
                array('id' => $project->id),
                array('%s'),
                array('%d')
            );
            
            error_log("Migrated project {$project->id} from {$old_status} to {$new_status}");
        }
    }
    
    /**
     * Schedule automatic sync
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('docket_trello_sync_hook')) {
            wp_schedule_event(time(), 'every_30_minutes', 'docket_trello_sync_hook');
        }
    }
    
    /**
     * Add custom cron interval
     */
    public function add_cron_intervals($schedules) {
        $schedules['every_30_minutes'] = array(
            'interval' => 1800, // 30 minutes
            'display' => __('Every 30 Minutes')
        );
        return $schedules;
    }
    
    /**
     * Add admin menu for manual sync
     */
    public function add_admin_menu() {
        add_management_page(
            'Trello Sync',
            'Trello Sync', 
            'manage_options',
            'trello-sync',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page for manual sync
     */
    public function admin_page() {
        if (isset($_POST['sync_now'])) {
            $results = $this->sync_all_projects();
            echo '<div class="notice notice-success"><p>Sync completed! Updated ' . count($results) . ' projects.</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>Trello Project Sync</h1>
            <p>Sync project statuses with Trello board positions.</p>
            
            <form method="post">
                <?php wp_nonce_field('trello_sync'); ?>
                <button type="submit" name="sync_now" class="button button-primary">Sync Now</button>
            </form>
            
            <h2>Board Info</h2>
            <p><strong>Board ID:</strong> <?php echo $this->board_id; ?></p>
            <p><strong>Board URL:</strong> <a href="https://trello.com/b/<?php echo $this->board_id; ?>" target="_blank">View Board</a></p>
            
            <h2>Status Mapping</h2>
            <table class="widefat">
                <thead>
                    <tr><th>Trello Column</th><th>Project Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($this->status_mapping as $trello => $status): ?>
                    <tr>
                        <td><?php echo esc_html($trello); ?></td>
                        <td><?php echo esc_html($status); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Manual sync via AJAX
     */
    public function manual_sync() {
        check_ajax_referer('trello_sync');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $results = $this->sync_all_projects();
        wp_send_json_success($results);
    }
    
    /**
     * Get all lists from the Trello board
     */
    private function get_board_lists() {
        $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $url = "{$this->api_base}/boards/{$this->board_id}/lists";
        $url .= "?key={$this->api_key}&token={$this->token}";
        
        file_put_contents($trello_debug_log, "[$timestamp] API URL: $url\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Board ID: {$this->board_id}\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] API Key: " . substr($this->api_key, 0, 8) . "...\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Token: " . substr($this->token, 0, 8) . "...\n", FILE_APPEND);
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: API request failed - " . $response->get_error_message() . "\n", FILE_APPEND);
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        file_put_contents($trello_debug_log, "[$timestamp] Response Code: $response_code\n", FILE_APPEND);
        
        $body = wp_remote_retrieve_body($response);
        file_put_contents($trello_debug_log, "[$timestamp] Response Body: $body\n", FILE_APPEND);
        
        $lists = json_decode($body, true);
        
        if (!$lists) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to decode JSON response\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Successfully got " . count($lists) . " lists\n", FILE_APPEND);
        
        return $lists;
    }
    
    /**
     * Get all cards from a specific list
     */
    private function get_list_cards($list_id) {
        $url = "{$this->api_base}/lists/{$list_id}/cards";
        $url .= "?key={$this->api_key}&token={$this->token}";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log('Trello API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $cards = json_decode($body, true);
        
        return $cards ?: array();
    }
    
    /**
     * Find project by business name in card title
     */
    private function find_project_by_business_name($business_name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'docket_client_projects';
        
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE business_name = %s",
            $business_name
        ));
        
        return $project;
    }
    
    /**
     * Update project status and timeline
     */
    private function update_project_status($project_id, $new_status) {
        global $wpdb;
        
        // Update current step in projects table
        $projects_table = $wpdb->prefix . 'docket_client_projects';
        $timeline_table = $wpdb->prefix . 'docket_project_timeline';
        
        // Update project current step
        $updated = $wpdb->update(
            $projects_table,
            array('current_step' => $new_status),
            array('id' => $project_id),
            array('%s'),
            array('%d')
        );
        
        if ($updated === false) {
            error_log("Failed to update project {$project_id} status to {$new_status}");
            return false;
        }
        
        // Get all steps in order
        $all_steps = array_values($this->status_mapping);
        $current_step_index = array_search($new_status, $all_steps);
        
        if ($current_step_index === false) {
            return false;
        }
        
        // Mark all previous steps as completed
        for ($i = 0; $i <= $current_step_index; $i++) {
            $step_name = $all_steps[$i];
            $status = ($i == $current_step_index) ? 'in_progress' : 'completed';
            
            // Check if step exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$timeline_table} WHERE project_id = %d AND step_name = %s",
                $project_id, $step_name
            ));
            
            if ($existing) {
                // Update existing step
                $wpdb->update(
                    $timeline_table,
                    array(
                        'status' => $status,
                        'completed_date' => ($status === 'completed') ? current_time('mysql') : null
                    ),
                    array('id' => $existing->id),
                    array('%s', '%s'),
                    array('%d')
                );
            } else {
                // Create new step
                $wpdb->insert(
                    $timeline_table,
                    array(
                        'project_id' => $project_id,
                        'step_name' => $step_name,
                        'status' => $status,
                        'completed_date' => ($status === 'completed') ? current_time('mysql') : null,
                        'created_at' => current_time('mysql')
                    ),
                    array('%d', '%s', '%s', '%s', '%s')
                );
            }
        }
        
        // Mark remaining steps as pending
        for ($i = $current_step_index + 1; $i < count($all_steps); $i++) {
            $step_name = $all_steps[$i];
            
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$timeline_table} WHERE project_id = %d AND step_name = %s",
                $project_id, $step_name
            ));
            
            if ($existing) {
                $wpdb->update(
                    $timeline_table,
                    array(
                        'status' => 'pending',
                        'completed_date' => null
                    ),
                    array('id' => $existing->id),
                    array('%s', '%s'),
                    array('%d')
                );
            }
        }
        
        return true;
    }
    
    /**
     * Sync all projects with Trello
     */
    public function sync_all_projects() {
        $lists = $this->get_board_lists();
        
        if (!$lists) {
            error_log('Trello Sync: Failed to get board lists');
            return array();
        }
        
        $updated_projects = array();
        
        foreach ($lists as $list) {
            $list_name = $list['name'];
            
            // Skip if list name doesn't match our mapping
            if (!isset($this->status_mapping[$list_name])) {
                continue;
            }
            
            $project_status = $this->status_mapping[$list_name];
            $cards = $this->get_list_cards($list['id']);
            
            if (!$cards) {
                continue;
            }
            
            foreach ($cards as $card) {
                $card_name = $card['name'];
                
                // Try to find project by business name
                // Cards might be formatted as "Business Name - Project Type" or just "Business Name"
                $business_name = $this->extract_business_name($card_name);
                $project = $this->find_project_by_business_name($business_name);
                
                if ($project) {
                    // Only update if status has changed
                    if ($project->current_step !== $project_status) {
                        $success = $this->update_project_status($project->id, $project_status);
                        
                        if ($success) {
                            $updated_projects[] = array(
                                'project_id' => $project->id,
                                'business_name' => $project->business_name,
                                'old_status' => $project->current_step,
                                'new_status' => $project_status,
                                'trello_list' => $list_name
                            );
                            
                            error_log("Updated project {$project->business_name} from {$project->current_step} to {$project_status}");
                        }
                    }
                } else {
                    error_log("No project found for Trello card: {$card_name}");
                }
            }
        }
        
        return $updated_projects;
    }
    
    /**
     * Extract business name from card title
     * Handles formats like "Business Name - Project Type" or "Business Name"
     */
    private function extract_business_name($card_name) {
        // If there's a dash, take everything before it
        $parts = explode(' - ', $card_name);
        return trim($parts[0]);
    }
    
    /**
     * Create Trello card when project is submitted
     */
    public function create_trello_card($project_data) {
        $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        
        file_put_contents($trello_debug_log, "[$timestamp] Getting board lists...\n", FILE_APPEND);
        $lists = $this->get_board_lists();
        if (!$lists) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to get board lists\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Found " . count($lists) . " lists\n", FILE_APPEND);
        
        // Find the first list (Docket Team)
        $first_list = null;
        file_put_contents($trello_debug_log, "[$timestamp] Looking for 'Docket Team' list...\n", FILE_APPEND);
        foreach ($lists as $list) {
            file_put_contents($trello_debug_log, "[$timestamp] Found list: " . $list['name'] . "\n", FILE_APPEND);
            if ($list['name'] === 'Docket Team') {
                $first_list = $list;
                break;
            }
        }
        
        if (!$first_list) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: 'Docket Team' list not found\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Found 'Docket Team' list with ID: " . $first_list['id'] . "\n", FILE_APPEND);
        
        // Build comprehensive card description
        $card_name = $project_data['business_name'] . ' - ' . ucwords(str_replace('_', ' ', $project_data['form_type']));
        $card_desc = $this->build_card_description($project_data);
        
        // Create the card
        $url = "{$this->api_base}/cards";
        $data = array(
            'key' => $this->api_key,
            'token' => $this->token,
            'idList' => $first_list['id'],
            'name' => $card_name,
            'desc' => $card_desc
        );
        
        file_put_contents($trello_debug_log, "[$timestamp] Creating card: $card_name\n", FILE_APPEND);
        
        $response = wp_remote_post($url, array(
            'body' => $data
        ));
        
        if (is_wp_error($response)) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Card creation failed - " . $response->get_error_message() . "\n", FILE_APPEND);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        file_put_contents($trello_debug_log, "[$timestamp] API Response: $body\n", FILE_APPEND);
        
        $card = json_decode($body, true);
        
        if (!$card || !isset($card['id'])) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Invalid card response\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Card created successfully with ID: " . $card['id'] . "\n", FILE_APPEND);
        
        // Add labels to the card
        $this->add_labels_to_card($card['id'], $project_data);
        
        return $card;
    }
    
    /**
     * Build comprehensive card description
     */
    private function build_card_description($project_data) {
        $desc = "## 🎯 PROJECT OVERVIEW\n";
        $desc .= "**Business:** {$project_data['business_name']}\n";
        $desc .= "**Project Type:** " . ucwords(str_replace('_', ' ', $project_data['form_type'])) . "\n";
        $desc .= "**Submitted:** " . date('M j, Y g:i A') . "\n\n";
        
        // Add important links section
        $desc .= "## 🔗 IMPORTANT LINKS\n";
        if (!empty($project_data['new_site_url'])) {
            $desc .= "**🌐 Dev Site:** {$project_data['new_site_url']}\n";
        }
        if (!empty($project_data['portal_url'])) {
            $desc .= "**📊 Client Portal:** {$project_data['portal_url']}\n";
        }
        $desc .= "\n";
        
        // Contact Information
        $desc .= "## 📞 CONTACT INFORMATION\n";
        $desc .= "**Contact Name:** " . ($project_data['contact_name'] ?? $project_data['name'] ?? 'Not provided') . "\n";
        $desc .= "**Contact Email:** " . ($project_data['contact_email_address'] ?? $project_data['email'] ?? 'Not provided') . "\n";
        $desc .= "**Business Phone:** " . ($project_data['business_phone_number'] ?? $project_data['phone_number'] ?? 'Not provided') . "\n";
        $desc .= "**Business Email:** " . ($project_data['business_email'] ?? 'Not provided') . "\n\n";
        
        // Business Address
        if (!empty($project_data['business_address'])) {
            $desc .= "## 📍 BUSINESS ADDRESS\n";
            $desc .= "**Address:** {$project_data['business_address']}\n";
            if (!empty($project_data['business_city'])) {
                $desc .= "**City:** {$project_data['business_city']}\n";
            }
            if (!empty($project_data['business_state'])) {
                $desc .= "**State:** {$project_data['business_state']}\n";
            }
            $desc .= "\n";
        }
        
        // Template Selection
        if (!empty($project_data['website_template_selection'])) {
            $desc .= "## 🎨 TEMPLATE SELECTION\n";
            $desc .= "**Selected Template:** {$project_data['website_template_selection']}\n\n";
        }
        
        // Service Areas
        if (!empty($project_data['service_areas'])) {
            $desc .= "## 🗺️ SERVICE AREAS\n";
            if (is_array($project_data['service_areas'])) {
                $desc .= implode(', ', $project_data['service_areas']) . "\n\n";
            } else {
                $desc .= "{$project_data['service_areas']}\n\n";
            }
        }
        
        // Branding Information
        $branding_fields = array(
            'logo_file' => '🎨 Logo File',
            'brand_colors' => '🎨 Brand Colors',
            'existing_website' => '🌐 Existing Website',
            'website_likes' => '👍 Website Likes',
            'website_dislikes' => '👎 Website Dislikes'
        );
        
        $has_branding = false;
        foreach ($branding_fields as $field => $label) {
            if (!empty($project_data[$field])) {
                if (!$has_branding) {
                    $desc .= "## 🎨 BRANDING & DESIGN\n";
                    $has_branding = true;
                }
                
                if ($field === 'logo_file' && filter_var($project_data[$field], FILTER_VALIDATE_URL)) {
                    $desc .= "**{$label}:** [View Logo]({$project_data[$field]})\n";
                } else {
                    $desc .= "**{$label}:** {$project_data[$field]}\n";
                }
            }
        }
        if ($has_branding) $desc .= "\n";
        
        // Rental Information (if applicable)
        $rental_fields = array(
            'rental_items' => '📦 Rental Items',
            'rental_pricing' => '💰 Rental Pricing',
            'delivery_areas' => '🚚 Delivery Areas'
        );
        
        $has_rental = false;
        foreach ($rental_fields as $field => $label) {
            if (!empty($project_data[$field])) {
                if (!$has_rental) {
                    $desc .= "## 🏗️ RENTAL INFORMATION\n";
                    $has_rental = true;
                }
                
                if (is_array($project_data[$field])) {
                    $desc .= "**{$label}:** " . implode(', ', $project_data[$field]) . "\n";
                } else {
                    $desc .= "**{$label}:** {$project_data[$field]}\n";
                }
            }
        }
        if ($has_rental) $desc .= "\n";
        
        // Marketing Information
        $marketing_fields = array(
            'marketing_goals' => '🎯 Marketing Goals',
            'target_audience' => '👥 Target Audience',
            'competitors' => '🏢 Competitors',
            'marketing_budget' => '💰 Marketing Budget'
        );
        
        $has_marketing = false;
        foreach ($marketing_fields as $field => $label) {
            if (!empty($project_data[$field])) {
                if (!$has_marketing) {
                    $desc .= "## 📈 MARKETING INFORMATION\n";
                    $has_marketing = true;
                }
                
                if (is_array($project_data[$field])) {
                    $desc .= "**{$label}:** " . implode(', ', $project_data[$field]) . "\n";
                } else {
                    $desc .= "**{$label}:** {$project_data[$field]}\n";
                }
            }
        }
        if ($has_marketing) $desc .= "\n";
        
        // Content Information (for standard/VIP builds)
        $content_fields = array(
            'content_provided' => '📝 Content Provided',
            'content_writing_needed' => '✍️ Content Writing Needed',
            'additional_pages' => '📄 Additional Pages',
            'special_features' => '⭐ Special Features'
        );
        
        $has_content = false;
        foreach ($content_fields as $field => $label) {
            if (!empty($project_data[$field])) {
                if (!$has_content) {
                    $desc .= "## 📝 CONTENT INFORMATION\n";
                    $has_content = true;
                }
                
                if (is_array($project_data[$field])) {
                    $desc .= "**{$label}:** " . implode(', ', $project_data[$field]) . "\n";
                } else {
                    $desc .= "**{$label}:** {$project_data[$field]}\n";
                }
            }
        }
        if ($has_content) $desc .= "\n";
        
        // Additional Notes
        if (!empty($project_data['additional_notes']) || !empty($project_data['special_requests'])) {
            $desc .= "## 📋 ADDITIONAL NOTES\n";
            if (!empty($project_data['additional_notes'])) {
                $desc .= "**Notes:** {$project_data['additional_notes']}\n";
            }
            if (!empty($project_data['special_requests'])) {
                $desc .= "**Special Requests:** {$project_data['special_requests']}\n";
            }
        }
        
        return $desc;
    }
    
    /**
     * Add appropriate labels to the card
     */
    private function add_labels_to_card($card_id, $project_data) {
        // Get all board labels
        $labels = $this->get_board_labels();
        if (!$labels) {
            return false;
        }
        
        $labels_to_add = array();
        
        // Determine plan type labels
        $plan = $project_data['plan'] ?? '';
        $management = $project_data['management'] ?? '';
        $form_type = $project_data['form_type'] ?? '';
        
        // Plan-based labels
        if (stripos($plan, 'grow') !== false || stripos($management, 'grow') !== false) {
            $labels_to_add[] = 'Grow';
        }
        if (stripos($plan, 'pro') !== false || stripos($management, 'pro') !== false) {
            $labels_to_add[] = 'Pro';
        }
        if (stripos($management, 'vip') !== false || $form_type === 'website_vip') {
            $labels_to_add[] = 'WebsiteVIP';
        }
        
        // Build type labels
        if ($form_type === 'fast_build' || stripos($form_type, 'fast') !== false) {
            $labels_to_add[] = 'Fast Build';
        }
        
        // Add each label to the card
        foreach ($labels_to_add as $label_name) {
            $label_id = $this->find_label_id($labels, $label_name);
            if ($label_id) {
                $this->attach_label_to_card($card_id, $label_id);
            }
        }
        
        return true;
    }
    
    /**
     * Get all labels from the board
     */
    private function get_board_labels() {
        $url = "{$this->api_base}/boards/{$this->board_id}/labels";
        $url .= "?key={$this->api_key}&token={$this->token}";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log('Trello API Error getting labels: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $labels = json_decode($body, true);
        
        return $labels ?: array();
    }
    
    /**
     * Find label ID by name
     */
    private function find_label_id($labels, $label_name) {
        foreach ($labels as $label) {
            if (strcasecmp($label['name'], $label_name) === 0) {
                return $label['id'];
            }
        }
        return null;
    }
    
    /**
     * Attach label to card
     */
    private function attach_label_to_card($card_id, $label_id) {
        $url = "{$this->api_base}/cards/{$card_id}/idLabels";
        
        $data = array(
            'key' => $this->api_key,
            'token' => $this->token,
            'value' => $label_id
        );
        
        $response = wp_remote_post($url, array(
            'body' => $data
        ));
        
        if (is_wp_error($response)) {
            error_log('Trello API Error adding label: ' . $response->get_error_message());
            return false;
        }
        
        return true;
    }
}

// Initialize Trello sync
new DocketTrelloSync();

// Add the cron interval filter
add_filter('cron_schedules', function($schedules) {
    $schedules['every_30_minutes'] = array(
        'interval' => 1800, // 30 minutes
        'display' => __('Every 30 Minutes')
    );
    return $schedules;
}); 