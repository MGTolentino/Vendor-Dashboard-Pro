/**
 * Settings Tabs JavaScript
 *
 * @package Vendor Dashboard Pro
 */

jQuery(document).ready(function($) {
    'use strict';

    // Settings tabs functionality
    $(document).on('click', '.vdp-tab-btn', function(e) {
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