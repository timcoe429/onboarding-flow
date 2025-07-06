<?php
/**
 * Docket Onboarding - Cloner Settings Page
 * Manages API settings for connecting to the Elementor Site Cloner
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add settings menu
 */
add_action('admin_menu', 'docket_cloner_settings_menu');
function docket_cloner_settings_menu() {
    add_submenu_page(
        'options-general.php',
        'Docket Cloner Settings',
        'Docket Cloner',
        'manage_options',
        'docket-cloner-settings',
        'docket_cloner_settings_page'
    );
}

/**
 * Register settings
 */
add_action('admin_init', 'docket_cloner_settings_init');
function docket_cloner_settings_init() {
    register_setting('docket_cloner_settings', 'docket_cloner_api_url');
    register_setting('docket_cloner_settings', 'docket_cloner_api_key');
    register_setting('docket_cloner_settings', 'docket_disable_api_calls');
}

/**
 * Settings page content
 */
function docket_cloner_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Add settings saved message
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        add_settings_error('docket_cloner_messages', 'docket_cloner_message', 'Settings Saved', 'updated');
    }
    
    // Show error/update messages
    settings_errors('docket_cloner_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form action="options.php" method="post">
            <?php
            settings_fields('docket_cloner_settings');
            ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="docket_cloner_api_url">API URL</label>
                    </th>
                    <td>
                        <input type="url" 
                               id="docket_cloner_api_url" 
                               name="docket_cloner_api_url" 
                               value="<?php echo esc_attr(get_option('docket_cloner_api_url', 'https://dockethosting5.com')); ?>" 
                               class="regular-text" />
                        <p class="description">
                            The URL of the site where Elementor Site Cloner is installed (e.g., https://dockethosting5.com)
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="docket_cloner_api_key">API Key</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="docket_cloner_api_key" 
                               name="docket_cloner_api_key" 
                               value="<?php echo esc_attr(get_option('docket_cloner_api_key', 'esc_docket_2025_secure_key')); ?>" 
                               class="regular-text" />
                        <p class="description">
                            The API key for authenticating with the Elementor Site Cloner
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="docket_disable_api_calls">Disable API Calls</label>
                    </th>
                    <td>
                        <input type="checkbox" name="docket_disable_api_calls" value="1" <?php checked(get_option('docket_disable_api_calls', false)); ?> />
                        <p class="description">Check this to disable API calls for debugging. Forms will submit successfully but no sites will be created.</p>
                    </td>
                </tr>
            </table>
            
            <h2>Test Connection</h2>
            <p>Click the button below to test the connection to the Elementor Site Cloner API.</p>
            <p>
                <button type="button" id="test-connection" class="button button-secondary">Test Connection</button>
                <span id="test-result" style="margin-left: 10px;"></span>
            </p>
            
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-connection').on('click', function() {
                var button = $(this);
                var resultSpan = $('#test-result');
                var apiUrl = $('#docket_cloner_api_url').val();
                var apiKey = $('#docket_cloner_api_key').val();
                
                if (!apiUrl || !apiKey) {
                    resultSpan.html('<span style="color: red;">Please enter both API URL and API Key</span>');
                    return;
                }
                
                button.prop('disabled', true);
                resultSpan.html('<span style="color: gray;">Testing connection...</span>');
                
                $.ajax({
                    url: apiUrl + '/wp-json/elementor-site-cloner/v1/status',
                    method: 'GET',
                    headers: {
                        'X-API-Key': apiKey
                    },
                    success: function(response) {
                        if (response && response.status === 'active') {
                            resultSpan.html('<span style="color: green;">✓ Connection successful! Plugin version: ' + response.version + '</span>');
                        } else {
                            resultSpan.html('<span style="color: red;">Connection failed: Invalid response</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var message = 'Connection failed: ';
                        if (xhr.status === 401) {
                            message += 'Invalid API key';
                        } else if (xhr.status === 404) {
                            message += 'API endpoint not found (is the plugin installed?)';
                        } else {
                            message += error || 'Unknown error';
                        }
                        resultSpan.html('<span style="color: red;">' + message + '</span>');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });
        });
        </script>
    </div>
    <?php
} 