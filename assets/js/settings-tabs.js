/**
 * Settings Tabs JavaScript
 *
 * @package Vendor Dashboard Pro
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize settings tabs when content loads
    function initSettingsTabs() {
        // Remove any existing event listeners to prevent duplicates
        $(document).off('click.settings-tabs', '.vdp-tab-btn');
        
        // Add event listener with namespace
        $(document).on('click.settings-tabs', '.vdp-tab-btn', function(e) {
        e.preventDefault();
        
        var $clickedTab = $(this);
        var targetTab = $clickedTab.data('tab');
        
        // Don't do anything if this tab is already active
        if ($clickedTab.hasClass('vdp-active')) {
            return;
        }
        
        // Remove active class from all tabs and content
        $('.vdp-tab-btn').removeClass('vdp-active');
        $('.vdp-tab-content').removeClass('vdp-active');
        
        // Add active class to clicked tab
        $clickedTab.addClass('vdp-active');
        
        // Show corresponding content
        $('#' + targetTab + '-tab').addClass('vdp-active');
        
            // Trigger custom event for other scripts that might need to know about tab changes
            $(document).trigger('vdp_settings_tab_changed', [targetTab]);
        });
    }

    // Initialize on page load
    initSettingsTabs();

    // Re-initialize when settings content is loaded (for AJAX)
    $(document).on('vdp_content_loaded', function(e, action) {
        if (action === 'settings') {
            initSettingsTabs();
        }
    });

    // File upload handling for images
    $(document).on('click', '.vdp-file-btn', function(e) {
        e.preventDefault();
        $(this).siblings('.vdp-file-input').click();
    });

    // Image removal handling
    $(document).on('click', '.vdp-remove-image-btn', function(e) {
        e.preventDefault();
        
        var $imageUploader = $(this).closest('.vdp-image-uploader');
        var $currentImage = $imageUploader.find('.vdp-current-image');
        
        // Replace image with placeholder
        if ($imageUploader.hasClass('vdp-logo-uploader')) {
            $currentImage.html('<div class="vdp-image-placeholder"><i class="fas fa-store"></i><span>No logo uploaded</span></div>');
        } else if ($imageUploader.hasClass('vdp-banner-uploader')) {
            $currentImage.html('<div class="vdp-image-placeholder"><i class="fas fa-image"></i><span>No banner uploaded</span></div>');
        }
        
        // Hide remove button
        $(this).hide();
        
        // Clear file input
        $imageUploader.find('.vdp-file-input').val('');
    });

    // File input change handling
    $(document).on('change', '.vdp-file-input', function(e) {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            var $imageUploader = $(this).closest('.vdp-image-uploader');
            var $currentImage = $imageUploader.find('.vdp-current-image');
            var $removeBtn = $imageUploader.find('.vdp-remove-image-btn');
            
            reader.onload = function(e) {
                var $img = $('<img>').attr('src', e.target.result);
                
                // Set alt text based on uploader type
                if ($imageUploader.hasClass('vdp-logo-uploader')) {
                    $img.attr('alt', 'Store Logo');
                } else if ($imageUploader.hasClass('vdp-banner-uploader')) {
                    $img.attr('alt', 'Store Banner');
                }
                
                $currentImage.html($img);
                $removeBtn.show();
            };
            
            reader.readAsDataURL(file);
        }
    });

    // Form submission handling
    $(document).on('submit', '.vdp-settings-form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Get form data
        var formData = new FormData(this);
        formData.append('action', 'vdp_save_settings');
        formData.append('nonce', vdp_vars.nonce);
        
        // Submit via AJAX
        $.ajax({
            url: vdp_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification('Settings saved successfully!', 'success');
                } else {
                    // Show error message
                    showNotification(response.data || 'Error saving settings. Please try again.', 'error');
                }
            },
            error: function() {
                showNotification('Error saving settings. Please try again.', 'error');
            },
            complete: function() {
                // Restore button
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // PDF Header Image Upload
    $(document).on('click', '.vdp-pdf-header-upload-btn', function(e) {
        e.preventDefault();
        $('#pdf-header-file-input').trigger('click');
    });

    $(document).on('change', '#pdf-header-file-input', function(e) {
        var file = e.target.files[0];
        if (file) {
            uploadPdfHeader(file);
        }
    });

    $(document).on('click', '.vdp-pdf-header-remove-btn', function(e) {
        e.preventDefault();
        removePdfHeader();
    });

    function uploadPdfHeader(file) {
        if (!file) return;

        // Validate file type
        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (allowedTypes.indexOf(file.type) === -1) {
            showNotification('Only JPG, JPEG, and PNG files are allowed.', 'error');
            return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showNotification('File size must not exceed 2MB.', 'error');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'vdp_upload_pdf_header');
        formData.append('nonce', vdp_vars.nonce);
        formData.append('pdf_header_image', file);

        $.ajax({
            url: vdp_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('.vdp-pdf-header-upload-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    
                    // Update UI
                    var previewHtml = '<div class="vdp-current-image">' +
                        '<img src="' + response.data.image_url + '" alt="PDF Header Image">' +
                        '<button type="button" class="vdp-pdf-header-remove-btn vdp-btn vdp-btn-secondary vdp-btn-small">' +
                            '<i class="fas fa-times"></i> Remove' +
                        '</button>' +
                    '</div>';
                    
                    $('.vdp-pdf-header-uploader').html(previewHtml);
                    
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('Upload failed. Please try again.', 'error');
            },
            complete: function() {
                $('.vdp-pdf-header-upload-btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Image');
                // Clear file input
                $('#pdf-header-file-input').val('');
            }
        });
    }

    function removePdfHeader() {
        if (!confirm('Are you sure you want to remove this image?')) {
            return;
        }

        $.ajax({
            url: vdp_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_remove_pdf_header',
                nonce: vdp_vars.nonce
            },
            beforeSend: function() {
                $('.vdp-pdf-header-remove-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Removing...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    
                    // Update UI
                    var placeholderHtml = '<div class="vdp-image-placeholder">' +
                        '<i class="fas fa-image"></i>' +
                        '<span>No header image uploaded</span>' +
                    '</div>' +
                    '<input type="file" id="pdf-header-file-input" accept="image/*" style="display: none;">' +
                    '<button type="button" class="vdp-pdf-header-upload-btn vdp-btn vdp-btn-primary">' +
                        '<i class="fas fa-upload"></i> Upload Image' +
                    '</button>';
                    
                    $('.vdp-pdf-header-uploader').html(placeholderHtml);
                    
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('Remove failed. Please try again.', 'error');
            },
            complete: function() {
                $('.vdp-pdf-header-remove-btn').prop('disabled', false).html('<i class="fas fa-times"></i> Remove');
            }
        });
    }

    // Helper function to show notifications
    function showNotification(message, type) {
        var $notification = $('<div class="vdp-notification vdp-notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        // Show notification
        setTimeout(function() {
            $notification.addClass('vdp-show');
        }, 100);
        
        // Hide and remove notification
        setTimeout(function() {
            $notification.removeClass('vdp-show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }
});