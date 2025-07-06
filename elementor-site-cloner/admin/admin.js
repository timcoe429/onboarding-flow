jQuery(document).ready(function($) {
    // Handle form submission
    $('#esc-clone-form').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = {
            action: 'esc_start_clone',
            nonce: esc_ajax.nonce,
            source_site: $('#source_site').val(),
            site_name: $('#site_name').val()
        };
        
        // Add subdomain or path based on setup
        if ($('#site_subdomain').length) {
            formData.site_subdomain = $('#site_subdomain').val();
        } else {
            formData.site_path = $('#site_path').val();
        }
        
        // Disable form
        $('#start-clone').prop('disabled', true).text('Cloning...');
        
        // Show progress container
        $('.esc-progress-container').show();
        $('.esc-progress-fill').width('10%');
        $('.esc-status-message').text(esc_ajax.messages.creating_site);
        $('.esc-error-message').hide();
        
        // Start clone
        $.post(esc_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                // Clone completed successfully
                $('.esc-progress-fill').width('100%');
                $('.esc-status-message').html(
                    esc_ajax.messages.completed + 
                    ' <a href="' + response.data.admin_url + '" class="button button-primary">Visit New Site Admin</a>'
                );
                
                // Re-enable form
                $('#start-clone').prop('disabled', false).text('Start Cloning');
                $('#esc-clone-form')[0].reset();
                
                // Reload page after 3 seconds to update recent clones
                setTimeout(function() {
                    location.reload();
                }, 3000);
            } else {
                // Clone failed
                $('.esc-progress-fill').width('0%');
                $('.esc-status-message').text(esc_ajax.messages.failed);
                $('.esc-error-message').text(response.data.message).show();
                
                // Re-enable form
                $('#start-clone').prop('disabled', false).text('Start Cloning');
            }
        }).fail(function() {
            // AJAX request failed
            $('.esc-progress-fill').width('0%');
            $('.esc-status-message').text(esc_ajax.messages.failed);
            $('.esc-error-message').text('An unexpected error occurred.').show();
            
            // Re-enable form
            $('#start-clone').prop('disabled', false).text('Start Cloning');
        });
    });
    
    // Progress simulation (since we're not using real-time updates in this basic version)
    function simulateProgress() {
        var progress = 10;
        var messages = [
            { progress: 20, message: esc_ajax.messages.creating_site },
            { progress: 40, message: esc_ajax.messages.cloning_database },
            { progress: 50, message: esc_ajax.messages.updating_urls },
            { progress: 70, message: esc_ajax.messages.cloning_files },
            { progress: 85, message: esc_ajax.messages.processing_elementor },
            { progress: 95, message: esc_ajax.messages.finalizing }
        ];
        
        var currentStep = 0;
        var interval = setInterval(function() {
            if (currentStep < messages.length && $('#start-clone').prop('disabled')) {
                $('.esc-progress-fill').width(messages[currentStep].progress + '%');
                $('.esc-status-message').text(messages[currentStep].message);
                currentStep++;
            } else {
                clearInterval(interval);
            }
        }, 2000);
    }
    
    // Start progress simulation when cloning starts
    $('#esc-clone-form').on('submit', function() {
        setTimeout(simulateProgress, 100);
    });
}); 