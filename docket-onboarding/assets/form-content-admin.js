jQuery(document).ready(function($) {
    'use strict';
    
    let formType = $('#form-type').val();
    let stepNumber = $('#step-number').val();
    let hasUnsavedChanges = false;
    
    // Initialize
    setupEventHandlers();
    
    // Load steps for the initial form type
    if (formType) {
        loadStepsForForm(formType);
    }
    
    // Only load content if both form and step are selected
    if (formType && stepNumber) {
        loadContent();
    }
    
    function setupEventHandlers() {
        // Form type change handler
        $('#form-type').on('change', function() {
            if (hasUnsavedChanges) {
                if (!confirm(docketFormContent.strings.confirm)) {
                    $(this).val($(this).data('previous-value'));
                    return;
                }
            }
            
            formType = $(this).val();
            
            // Load steps for the selected form type
            loadStepsForForm(formType);
            
            // Clear content editor
            $('.content-editor').html('<p>Please select a step to edit content.</p>');
            updatePreview();
        });
        
        // Step change handler
        $('#step-number').on('change', function() {
            if (hasUnsavedChanges) {
                if (!confirm(docketFormContent.strings.confirm)) {
                    $(this).val($(this).data('previous-value'));
                    return;
                }
            }
            
            formType = $('#form-type').val();
            stepNumber = $(this).val();
            
            // Load content if both form and step are selected
            if (formType && stepNumber) {
                loadContent();
                updatePreview();
            } else {
                $('.content-editor').html('<p>Please select a step to edit content.</p>');
                updatePreview();
            }
        });
        
        // Store previous values for confirmation
        $('#form-type, #step-number').each(function() {
            $(this).data('previous-value', $(this).val());
        });
        
        // Content change handlers - just mark as changed
        $(document).on('input change', '.content-field input, .content-field textarea', function() {
            hasUnsavedChanges = true;
        });
        
        // TinyMCE change handler - just mark as changed, don't auto-save
        $(document).on('tinymce-editor-init', function(event, editor) {
            editor.on('NodeChange SetContent KeyUp Input', function() {
                hasUnsavedChanges = true;
            });
        });
        
        // Remove auto-save on blur - only manual save
        
        // Manual save button - use document.on for dynamic elements
        $(document).on('click', '#manual-save-btn', function() {
            console.log('SAVE BUTTON CLICKED!');
            
            // Save all visible content fields
            $('.content-field').each(function() {
                const input = $(this).find('input, textarea');
                if (input.length) {
                    const fieldName = input.attr('name');
                    let value = input.val();
                    
                    console.log('Found field:', fieldName);
                    
                    // For TinyMCE fields, get content from editor
                    if (tinymce.get('content-' + fieldName)) {
                        value = tinymce.get('content-' + fieldName).getContent();
                        console.log('Got TinyMCE content, length:', value.length);
                    }
                    
                    saveContent(fieldName, value);
                }
            });
        });
    }
    
    function loadContent() {
        $.ajax({
            url: docketFormContent.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_form_content',
                nonce: docketFormContent.nonce,
                form_type: formType,
                step_number: stepNumber
            },
            success: function(response) {
                if (response.success) {
                    updateContentFields(response.data);
                    updatePreview();
                } else {
                    console.error('Failed to load content:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }
    
    function updateContentFields(content) {
        // Clear existing content fields
        $('.content-field').remove();
        
        if (Object.keys(content).length === 0) {
            $('.content-editor').html('<p>No content found for this form and step.</p>');
            return;
        }
        
        let fieldsHtml = '<div class="content-fields">';
        
        for (const [key, value] of Object.entries(content)) {
            const fieldLabel = getFieldLabel(key);
            const fieldType = getFieldType(key);
            
            fieldsHtml += '<div class="content-field">';
            fieldsHtml += '<label for="content-' + key + '">' + fieldLabel + '</label>';
            
            if (fieldType === 'editor') {
                fieldsHtml += '<div class="wp-editor-container">';
                // Remove escape slashes when displaying
                const cleanValue = value.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
                fieldsHtml += '<textarea id="content-' + key + '" name="' + key + '" rows="6">' + cleanValue + '</textarea>';
                fieldsHtml += '</div>';
            } else if (fieldType === 'textarea') {
                const cleanValue = value.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
                fieldsHtml += '<textarea id="content-' + key + '" name="' + key + '" rows="4">' + cleanValue + '</textarea>';
            } else {
                const cleanValue = value.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
                fieldsHtml += '<input type="text" id="content-' + key + '" name="' + key + '" value="' + cleanValue + '" />';
            }
            
            fieldsHtml += '</div>';
        }
        
        fieldsHtml += '</div>';
        
        // Add save button to the HTML
        fieldsHtml += '<br><button type="button" id="manual-save-btn" class="button button-primary" style="font-size: 16px; padding: 10px 20px; margin-top: 20px;">SAVE CHANGES</button>';
        
        $('.content-editor').html(fieldsHtml);
        
        // Re-initialize TinyMCE for any editor fields
        if (typeof tinymce !== 'undefined') {
            tinymce.remove();
            $('.wp-editor-container textarea').each(function() {
                const fieldName = $(this).attr('name');
                const fieldType = getFieldType(fieldName);
                
                if (fieldType === 'editor') {
                    $(this).attr('id', 'content-' + fieldName);
                    tinymce.init({
                        selector: '#content-' + fieldName,
                        height: 300,
                        menubar: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | link unlink | bullist numlist | undo redo',
                        setup: function(editor) {
                            editor.on('NodeChange SetContent KeyUp Input', function() {
                                hasUnsavedChanges = true;
                                // Update preview as they type
                                clearTimeout(window.previewTimeout);
                                window.previewTimeout = setTimeout(updatePreview, 500);
                            });
                        }
                    });
                }
            });
        }
    }
    
    function getFieldLabel(key) {
        if (key === 'content') {
            return 'Step Content (Edit like a blog post)';
        }
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function getFieldType(key) {
        // Always use editor for content field
        if (key === 'content') {
            return 'editor';
        }
        return 'text';
    }
    
    function loadStepsForForm(formType) {
        // Get steps for the selected form type
        const availableSteps = {
            'fast-build': [1, 3],
            'standard-build': [1, 3],
            'website-vip': [1, 3]
        };
        
        const steps = availableSteps[formType] || [];
        const stepSelect = $('#step-number');
        
        // Clear existing options
        stepSelect.empty();
        
        // Add new options
        if (steps.length > 0) {
            stepSelect.append('<option value="">Select a step...</option>');
            steps.forEach(step => {
                stepSelect.append(`<option value="${step}">Step ${step}</option>`);
            });
        } else {
            stepSelect.append('<option value="">No steps available</option>');
        }
        
        // Reset step selection
        stepNumber = '';
    }
    
    function updateFormSelectors() {
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('form', formType);
        url.searchParams.set('step', stepNumber);
        window.history.pushState({}, '', url);
    }
    
    function saveContent(contentKey, contentValue) {
        console.log('SAVING:', {
            action: 'save_form_content',
            form_type: formType,
            step_number: stepNumber,
            content_key: contentKey,
            content_length: contentValue.length
        });
        
        $.ajax({
            url: docketFormContent.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_form_content',
                nonce: docketFormContent.nonce,
                form_type: formType,
                step_number: stepNumber,
                content_key: contentKey,
                content_value: contentValue
            },
            success: function(response) {
                console.log('SAVE RESPONSE:', response);
                
                if (response.success) {
                    hasUnsavedChanges = false;
                    // Update the button to show it saved
                    $('#manual-save-btn').text('SAVED!').removeClass('button-primary').addClass('button-success');
                    setTimeout(function() {
                        $('#manual-save-btn').text('SAVE CHANGES').addClass('button-primary').removeClass('button-success');
                    }, 2000);
                    // Update the preview
                    updatePreview();
                } else {
                    alert('Error saving content: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX ERROR:', xhr, status, error);
                alert('Error saving content: ' + error);
            }
        });
    }
    
    function updatePreview() {
        const previewFrame = $('#preview-frame');
        
        // Get the actual content from the editor
        let content = '';
        if (tinymce.get('content-content')) {
            content = tinymce.get('content-content').getContent();
        } else {
            content = $('#content-content').val() || '';
        }
        
        const previewContent = generatePreviewHTML(content);
        previewFrame.attr('srcdoc', previewContent);
    }
    
    function generatePreviewHTML(content) {
        let html = '<html><head><style>';
        html += 'body { font-family: Arial, sans-serif; margin: 20px; }';
        html += 'h2 { color: #333; margin-bottom: 10px; }';
        html += 'h5 { color: #666; margin: 15px 0 5px 0; }';
        html += 'p { margin: 10px 0; line-height: 1.5; }';
        html += 'ul { margin: 10px 0; padding-left: 20px; }';
        html += 'li { margin: 5px 0; }';
        html += '.terms-box { background: #f8f8f8; padding: 20px; }';
        html += '.terms-content { background: white; padding: 20px; }';
        html += '.terms-section { margin: 20px 0; }';
        html += '.info-box { background: #f8f8f8; padding: 20px; }';
        html += '.info-section { margin: 20px 0; }';
        html += '</style></head><body>';
        
        // Just output the content as-is
        html += content;
        
        html += '</body></html>';
        return html;
    }
    
    
    // Warn before leaving page with unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
});
