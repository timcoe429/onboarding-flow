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
        '1. Docket Team' => 'docket_team',
        '2. QA' => 'qa',
        '3. New Builds Ready to Send' => 'ready_to_send',
        '4. Waiting on Review Scheduling' => 'waiting_review_scheduling',
        '5. Client Reviewing' => 'client_reviewing',
        '6. Edits to Complete' => 'edits_to_complete',
        '7. Review Edits Completed' => 'review_edits_completed',
        '8. Pre Launch' => 'pre_launch',
        '9. Ready for Launch' => 'ready_for_launch',
        '10. Web Complete: Grow/Legacy' => 'web_complete_grow',
        '10. Web Complete: Pro/ServiceCore' => 'web_complete_pro'
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
        
        // ENHANCED DEBUG: Log what data we received in Trello class
        file_put_contents($trello_debug_log, "[$timestamp] === TRELLO CLASS DEBUG START ===\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received template: " . ($project_data['website_template_selection'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received business_name: " . ($project_data['business_name'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received form_type: " . ($project_data['form_type'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received name: " . ($project_data['name'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received email: " . ($project_data['email'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received phone_number: " . ($project_data['phone_number'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received business_email: " . ($project_data['business_email'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Trello received business_address: " . ($project_data['business_address'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] === TRELLO CLASS DEBUG END ===\n", FILE_APPEND);
        
        file_put_contents($trello_debug_log, "[$timestamp] Getting board lists...\n", FILE_APPEND);
        $lists = $this->get_board_lists();
        if (!$lists) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to get board lists\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Found " . count($lists) . " lists\n", FILE_APPEND);
        
        // Find the first list (1. Docket Team)
        $first_list = null;
        file_put_contents($trello_debug_log, "[$timestamp] Looking for '1. Docket Team' list...\n", FILE_APPEND);
        foreach ($lists as $list) {
            file_put_contents($trello_debug_log, "[$timestamp] Found list: " . $list['name'] . "\n", FILE_APPEND);
            if ($list['name'] === '1. Docket Team') {
                $first_list = $list;
                break;
            }
        }
        
        if (!$first_list) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: '1. Docket Team' list not found\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Found '1. Docket Team' list with ID: " . $first_list['id'] . "\n", FILE_APPEND);
        
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
        
        // Wait 3 seconds then update description to override automation
        sleep(3);
        file_put_contents($trello_debug_log, "[$timestamp] Updating card description after automation...\n", FILE_APPEND);
        $this->update_card_description($card['id'], $card_desc);
        
        return $card;
    }
    
    /**
     * Build comprehensive card description
     */
    private function build_card_description($project_data) {
        $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        
        // ENHANCED DEBUG: Log what we're building the description from
        file_put_contents($trello_debug_log, "[$timestamp] === BUILDING CARD DESCRIPTION ===\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from business_name: " . ($project_data['business_name'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from form_type: " . ($project_data['form_type'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from select_your_docket_plan: " . ($project_data['select_your_docket_plan'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from name: " . ($project_data['name'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from email: " . ($project_data['email'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from phone_number: " . ($project_data['phone_number'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from business_email: " . ($project_data['business_email'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from business_address: " . ($project_data['business_address'] ?? 'MISSING') . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Building desc from website_template_selection: " . ($project_data['website_template_selection'] ?? 'MISSING') . "\n", FILE_APPEND);
        
        $desc = "PROJECT OVERVIEW\n";
        $desc .= "Business: {$project_data['business_name']}\n";
        $desc .= "Project Type: " . ucwords(str_replace('_', ' ', $project_data['form_type'])) . "\n";
        $desc .= "Plan: " . ($project_data['select_your_docket_plan'] ?? 'Not specified') . "\n";
        $desc .= "Submitted: " . date('M j, Y g:i A') . "\n\n";
        
        // Important links
        $desc .= "IMPORTANT LINKS\n";
        if (!empty($project_data['new_site_url'])) {
            $desc .= "Dev Site: {$project_data['new_site_url']}\n";
        }
        if (!empty($project_data['portal_url'])) {
            $desc .= "Client Portal: {$project_data['portal_url']}\n";
        }
        $desc .= "\n";
        
        // Contact Information
        $desc .= "CONTACT INFORMATION\n";
        $desc .= "Name: " . ($project_data['name'] ?? 'Not provided') . "\n";
        $desc .= "Email: " . ($project_data['email'] ?? 'Not provided') . "\n";
        $desc .= "Phone: " . ($project_data['phone_number'] ?? 'Not provided') . "\n";
        $desc .= "Business Email: " . ($project_data['business_email'] ?? 'Not provided') . "\n\n";
        
        // Business Address
        if (!empty($project_data['business_address'])) {
            $desc .= "BUSINESS ADDRESS\n";
            $desc .= "Address: {$project_data['business_address']}\n";
            if (!empty($project_data['business_city'])) {
                $desc .= "City: {$project_data['business_city']}\n";
            }
            if (!empty($project_data['business_state'])) {
                $desc .= "State: {$project_data['business_state']}\n";
            }
            $desc .= "\n";
        }
        
        // Template Selection
        if (!empty($project_data['website_template_selection'])) {
            $desc .= "TEMPLATE SELECTION\n";
            $desc .= "Selected Template: {$project_data['website_template_selection']}\n\n";
        }
        
        // Services and Colors
        if (!empty($project_data['services_offered'])) {
            $desc .= "SERVICES\n";
            if (is_array($project_data['services_offered'])) {
                $desc .= "Services: " . implode(', ', $project_data['services_offered']) . "\n";
            }
            if (!empty($project_data['dumpster_types'])) {
                $desc .= "Dumpster Types: " . implode(', ', $project_data['dumpster_types']) . "\n";
            }
            if (!empty($project_data['company_colors'])) {
                $desc .= "Primary Color: {$project_data['company_colors']}\n";
            }
            if (!empty($project_data['company_colors2'])) {
                $desc .= "Secondary Color: {$project_data['company_colors2']}\n";
            }
            if (!empty($project_data['dumpster_color'])) {
                $desc .= "Dumpster Color: {$project_data['dumpster_color']}\n";
            }
            $desc .= "\n";
        }
        
        // Uploaded Files
        if (!empty($project_data['logo_files_urls']) || !empty($project_data['dumpster_images_urls'])) {
            $desc .= "UPLOADED FILES\n";
            if (!empty($project_data['logo_files_urls'])) {
                $desc .= "Logo Files:\n";
                foreach ($project_data['logo_files_urls'] as $url) {
                    $desc .= "- {$url}\n";
                }
            }
            if (!empty($project_data['dumpster_images_urls'])) {
                $desc .= "Dumpster Images:\n";
                foreach ($project_data['dumpster_images_urls'] as $url) {
                    $desc .= "- {$url}\n";
                }
            }
            $desc .= "\n";
        }
        
        // Additional Notes
        if (!empty($project_data['additional_notes']) || !empty($project_data['special_requests'])) {
            $desc .= "ADDITIONAL NOTES\n";
            if (!empty($project_data['additional_notes'])) {
                $desc .= "Notes: {$project_data['additional_notes']}\n";
            }
            if (!empty($project_data['special_requests'])) {
                $desc .= "Special Requests: {$project_data['special_requests']}\n";
            }
        }
        
        // ENHANCED DEBUG: Log the final description
        file_put_contents($trello_debug_log, "[$timestamp] === FINAL CARD DESCRIPTION ===\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Final description length: " . strlen($desc) . " characters\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] Final description:\n" . $desc . "\n", FILE_APPEND);
        file_put_contents($trello_debug_log, "[$timestamp] === END DESCRIPTION DEBUG ===\n", FILE_APPEND);
        
        return $desc;
    }
    
    /**
     * Update card description (to override automation)
     */
    private function update_card_description($card_id, $description) {
        $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $url = "{$this->api_base}/cards/{$card_id}";
        $data = array(
            'key' => $this->api_key,
            'token' => $this->token,
            'desc' => $description
        );
        
        $response = wp_remote_request($url, array(
            'method' => 'PUT',
            'body' => $data
        ));
        
        if (is_wp_error($response)) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to update card description - " . $response->get_error_message() . "\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Card description updated successfully\n", FILE_APPEND);
        return true;
    }
    
    /**
     * Add appropriate labels to the card
     */
    private function add_labels_to_card($card_id, $project_data) {
        $trello_debug_log = WP_CONTENT_DIR . '/trello-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        
        // Get all board labels
        $labels = $this->get_board_labels();
        if (!$labels) {
            file_put_contents($trello_debug_log, "[$timestamp] ERROR: Failed to get board labels\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Found " . count($labels) . " labels on board\n", FILE_APPEND);
        
        $labels_to_add = array();
        
        // Determine plan type labels
        $plan = $project_data['select_your_docket_plan'] ?? $project_data['plan'] ?? '';
        $management = $project_data['docket_management_type'] ?? $project_data['management'] ?? '';
        $form_type = $project_data['form_type'] ?? '';
        
        file_put_contents($trello_debug_log, "[$timestamp] Plan: '$plan', Management: '$management', Form Type: '$form_type'\n", FILE_APPEND);
        
        // Plan-based labels
        if (stripos($plan, 'grow') !== false || stripos($management, 'grow') !== false) {
            $labels_to_add[] = 'Grow';
            file_put_contents($trello_debug_log, "[$timestamp] Adding 'Grow' label\n", FILE_APPEND);
        }
        if (stripos($plan, 'pro') !== false || stripos($management, 'pro') !== false) {
            $labels_to_add[] = 'Pro';
            file_put_contents($trello_debug_log, "[$timestamp] Adding 'Pro' label\n", FILE_APPEND);
        }
        if (stripos($management, 'vip') !== false || $form_type === 'website_vip') {
            $labels_to_add[] = 'WebsiteVIP';
            file_put_contents($trello_debug_log, "[$timestamp] Adding 'WebsiteVIP' label\n", FILE_APPEND);
        }
        
        // Build type labels
        if ($form_type === 'fast_build' || stripos($form_type, 'fast') !== false) {
            $labels_to_add[] = 'Fast Build';
            file_put_contents($trello_debug_log, "[$timestamp] Adding 'Fast Build' label\n", FILE_APPEND);
        }
        
        file_put_contents($trello_debug_log, "[$timestamp] Labels to add: " . implode(', ', $labels_to_add) . "\n", FILE_APPEND);
        
        // Add each label to the card
        foreach ($labels_to_add as $label_name) {
            $label_id = $this->find_label_id($labels, $label_name);
            if ($label_id) {
                file_put_contents($trello_debug_log, "[$timestamp] Found label '$label_name' with ID: $label_id\n", FILE_APPEND);
                $this->attach_label_to_card($card_id, $label_id);
            } else {
                file_put_contents($trello_debug_log, "[$timestamp] ERROR: Label '$label_name' not found on board\n", FILE_APPEND);
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