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
        
        // Content change handlers
        $(document).on('input change', '.content-field input, .content-field textarea', function() {
            hasUnsavedChanges = true;
            saveContent($(this).attr('name'), $(this).val());
        });
        
        // TinyMCE change handler
        $(document).on('tinymce-editor-init', function(event, editor) {
            editor.on('input change keyup', function() {
                hasUnsavedChanges = true;
                saveContent(editor.id.replace('content-', ''), editor.getContent());
            });
        });
        
        // Auto-save on blur
        $(document).on('blur', '.content-field input, .content-field textarea', function() {
            if (hasUnsavedChanges) {
                saveContent($(this).attr('name'), $(this).val());
            }
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
                fieldsHtml += '<textarea id="content-' + key + '" name="' + key + '" rows="6">' + value + '</textarea>';
                fieldsHtml += '</div>';
            } else if (fieldType === 'textarea') {
                fieldsHtml += '<textarea id="content-' + key + '" name="' + key + '" rows="4">' + value + '</textarea>';
            } else {
                fieldsHtml += '<input type="text" id="content-' + key + '" name="' + key + '" value="' + value + '" />';
            }
            
            fieldsHtml += '</div>';
        }
        
        fieldsHtml += '</div>';
        
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
                        height: 200,
                        menubar: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | link unlink | bullist numlist',
                        setup: function(editor) {
                            editor.on('input change keyup', function() {
                                hasUnsavedChanges = true;
                                saveContent(fieldName, editor.getContent());
                            });
                        }
                    });
                }
            });
        }
    }
    
    function getFieldLabel(key) {
        const labels = {
            'form_title': 'Form Title',
            'form_subtitle': 'Form Subtitle',
            'terms_content': 'Terms & Conditions Content',
            'info_content': 'Information Content',
            'acceptance_text': 'Acceptance Checkbox Text'
        };
        
        return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function getFieldType(key) {
        const editorFields = ['terms_content', 'info_content'];
        const textareaFields = ['acceptance_text'];
        
        if (editorFields.includes(key)) {
            return 'editor';
        } else if (textareaFields.includes(key)) {
            return 'textarea';
        } else {
            return 'text';
        }
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
                if (response.success) {
                    hasUnsavedChanges = false;
                    showSaveIndicator(true);
                } else {
                    showSaveIndicator(false, response.data.message);
                }
            },
            error: function(xhr, status, error) {
                showSaveIndicator(false, docketFormContent.strings.error);
            }
        });
    }
    
    function updatePreview() {
        // This would generate a preview of how the content looks
        // For now, we'll just show a placeholder
        const previewFrame = $('#preview-frame');
        const previewContent = generatePreviewHTML();
        
        previewFrame.attr('srcdoc', previewContent);
    }
    
    function generatePreviewHTML() {
        let html = '<html><head><style>';
        html += 'body { font-family: Arial, sans-serif; margin: 20px; }';
        html += 'h2 { color: #333; margin-bottom: 10px; }';
        html += 'h5 { color: #666; margin: 15px 0 5px 0; }';
        html += 'p { margin: 10px 0; line-height: 1.5; }';
        html += 'ul { margin: 10px 0; padding-left: 20px; }';
        html += 'li { margin: 5px 0; }';
        html += '.terms-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }';
        html += '</style></head><body>';
        
        // Get current form content and generate preview
        $('.content-field').each(function() {
            const label = $(this).find('label').text();
            const value = $(this).find('input, textarea').val() || '';
            
            if (value.trim()) {
                if (label.includes('Title')) {
                    html += '<h2>' + value + '</h2>';
                } else if (label.includes('Subtitle')) {
                    html += '<p style="color: #666; font-style: italic;">' + value + '</p>';
                } else if (label.includes('What You\'re Getting') || label.includes('Timeline') || label.includes('What We Need')) {
                    html += '<div class="terms-section">';
                    html += '<h5>' + label + '</h5>';
                    html += '<div>' + value + '</div>';
                    html += '</div>';
                } else {
                    html += '<div class="terms-section">';
                    html += '<h5>' + label + '</h5>';
                    html += '<div>' + value + '</div>';
                    html += '</div>';
                }
            }
        });
        
        html += '</body></html>';
        return html;
    }
    
    function showSaveIndicator(success, message) {
        // Remove existing indicators
        $('.save-indicator').remove();
        
        const indicator = $('<div class="save-indicator"></div>');
        
        if (success) {
            indicator.addClass('success').text(docketFormContent.strings.saved);
        } else {
            indicator.addClass('error').text(message || docketFormContent.strings.error);
        }
        
        $('.content-editor').prepend(indicator);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            indicator.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Warn before leaving page with unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
});
