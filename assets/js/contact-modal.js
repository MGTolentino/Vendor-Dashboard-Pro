/**
 * Contact Modal Script for Vendor Dashboard Pro
 *
 * @package Vendor Dashboard Pro
 */

(function($) {
    'use strict';
    
    // Variables
    var $modal = $('#vdp-contact-modal');
    var $form = $('#vdp-contact-form');
    var $messages = $('#vdp-contact-form-messages');
    var $closeBtn = $('#vdp-modal-close, #vdp-modal-cancel');
    var $sendBtn = $('#vdp-send-message-btn');
    var $contactBtns = $('.vdp-contact-button');
    
    // Initialize
    function init() {
        bindEvents();
        
        // También enlazar eventos a botones añadidos dinámicamente
        $(document).on('click', '.vdp-contact-button', openModal);
    }
    
    // Bind events
    function bindEvents() {
        // Open modal when contact button is clicked (para botones que ya existen)
        $contactBtns.on('click', openModal);
        
        // Close modal when close button is clicked
        $closeBtn.on('click', closeModal);
        
        // Close modal when clicking outside
        $modal.on('click', function(e) {
            if ($(e.target).is($modal)) {
                closeModal();
            }
        });
        
        // Close modal on ESC key
        $(document).on('keyup', function(e) {
            if (e.key === "Escape" && $modal.hasClass('vdp-active')) {
                closeModal();
            }
        });
        
        // Send message when send button is clicked
        $sendBtn.on('click', sendMessage);
    }
    
    // Open modal
    function openModal() {
        var listingId = $(this).data('listing-id');
        var vendorId = $(this).data('vendor-id');
        var vendorName = $(this).data('vendor-name');
        var listingTitle = $(this).data('listing-title');
        
        // Set values in form
        $('#vdp-contact-listing-id').val(listingId);
        $('#vdp-contact-vendor-id').val(vendorId);
        
        // Set default subject with listing title
        if (listingTitle) {
            $('#vdp-contact-subject').val(vdp_contact_vars.texts.regarding + ' ' + listingTitle);
        }
        
        // Show modal
        $modal.addClass('vdp-active');
        
        // Focus on first field
        setTimeout(function() {
            $('#vdp-contact-subject').focus();
        }, 100);
    }
    
    // Close modal
    function closeModal() {
        $modal.removeClass('vdp-active');
        resetForm();
    }
    
    // Reset form
    function resetForm() {
        $form[0].reset();
        $messages.empty();
        $sendBtn.prop('disabled', false).removeClass('vdp-btn-loading');
    }
    
    // Send message
    function sendMessage() {
        // Validate form
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }
        
        // Disable button and show loading state
        $sendBtn.prop('disabled', true).addClass('vdp-btn-loading');
        
        // Get form data
        var formData = {
            action: 'vdp_send_message',
            nonce: vdp_contact_vars.nonce,
            subject: $('#vdp-contact-subject').val(),
            message: $('#vdp-contact-message').val(),
            listing_id: $('#vdp-contact-listing-id').val(),
            vendor_id: $('#vdp-contact-vendor-id').val()
        };
        
        // Send AJAX request
        $.ajax({
            url: vdp_contact_vars.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $messages.html('<div class="vdp-notification vdp-notification-success">' + vdp_contact_vars.texts.message_sent + '</div>');
                    
                    // Clear form
                    $('#vdp-contact-subject').val('');
                    $('#vdp-contact-message').val('');
                    
                    // Close modal after delay
                    setTimeout(function() {
                        closeModal();
                    }, 2000);
                } else {
                    // Show error message
                    $messages.html('<div class="vdp-notification vdp-notification-error">' + (response.data.message || vdp_contact_vars.texts.error) + '</div>');
                    $sendBtn.prop('disabled', false).removeClass('vdp-btn-loading');
                }
            },
            error: function() {
                // Show generic error message
                $messages.html('<div class="vdp-notification vdp-notification-error">' + vdp_contact_vars.texts.error + '</div>');
                $sendBtn.prop('disabled', false).removeClass('vdp-btn-loading');
            }
        });
    }
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);