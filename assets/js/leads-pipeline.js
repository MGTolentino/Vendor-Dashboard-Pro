/**
 * Leads Pipeline JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    $(document).on('click', '.vdp-actions-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $dropdown = $(this).closest('.vdp-actions-dropdown');
        var $menu = $dropdown.find('.vdp-actions-menu');
        
        $('.vdp-actions-dropdown').not($dropdown).removeClass('active dropup');
        
        var dropdownOffset = $dropdown.offset();
        var menuHeight = 200;
        var windowHeight = $(window).height();
        var windowScroll = $(window).scrollTop();
        
        if (dropdownOffset.top - windowScroll + menuHeight > windowHeight - 50) {
            $dropdown.addClass('dropup');
        } else {
            $dropdown.removeClass('dropup');
        }
        
        $dropdown.toggleClass('active');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.vdp-actions-dropdown').length) {
            $('.vdp-actions-dropdown').removeClass('active');
        }
    });

    $(document).on('click', '.vdp-update-lead-status', function(e) {
        e.preventDefault();
        
        var leadId = $(this).data('lead-id');
        var currentStatus = $(this).data('current-status') || 'nuevo';
        
        showStatusUpdateModal(leadId, currentStatus);
    });

    // Contact lead functionality
    $(document).on('click', '.vdp-contact-lead', function(e) {
        e.preventDefault();
        
        var leadId = $(this).data('lead-id');
        
        // For now, just show a simple alert
        // In a real implementation, this would open a contact modal
        alert('Contact functionality for lead #' + leadId + ' - Coming soon!');
    });

    /**
     * Show status update modal
     */
    function showStatusUpdateModal(leadId, currentStatus) {
        var statusOptions = vdpLeads.statusOptions || {
            'nuevo': 'Nuevo',
            'con-presupuesto': 'Con Presupuesto',
            'por-cerrar': 'Por cerrar',
            'con-contrato': 'Con contrato',
            'perdido': 'Perdido'
        };

        var modalHtml = '<div class="vdp-modal-overlay" id="vdp-status-modal">' +
            '<div class="vdp-modal">' +
                '<div class="vdp-modal-header">' +
                    '<h3>Update Lead Status</h3>' +
                    '<button type="button" class="vdp-modal-close">&times;</button>' +
                '</div>' +
                '<div class="vdp-modal-body">' +
                    '<form id="vdp-status-form">' +
                        '<div class="vdp-form-group">' +
                            '<label for="lead-status">Select new status:</label>' +
                            '<select id="lead-status" name="status" class="vdp-form-control">';

        // Add status options
        Object.keys(statusOptions).forEach(function(key) {
            var selected = key === currentStatus ? ' selected' : '';
            modalHtml += '<option value="' + key + '"' + selected + '>' + statusOptions[key] + '</option>';
        });

        modalHtml += '</select>' +
                        '</div>' +
                        '<div class="vdp-form-actions">' +
                            '<button type="button" class="vdp-btn vdp-btn-secondary" data-action="cancel">Cancel</button>' +
                            '<button type="submit" class="vdp-btn vdp-btn-primary">Update Status</button>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>' +
        '</div>';

        // Add modal to page
        $('body').append(modalHtml);

        // Handle form submission
        $('#vdp-status-form').on('submit', function(e) {
            e.preventDefault();
            updateLeadStatus(leadId, $('#lead-status').val());
        });

        // Handle close buttons
        $('.vdp-modal-close, [data-action="cancel"]').on('click', function() {
            $('#vdp-status-modal').remove();
        });

        // Close on overlay click
        $('.vdp-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                $('#vdp-status-modal').remove();
            }
        });
    }

    /**
     * Update lead status via AJAX
     */
    function updateLeadStatus(leadId, newStatus) {
        $.ajax({
            url: vdpLeads.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_update_lead_status',
                lead_id: leadId,
                status: newStatus,
                nonce: vdpLeads.nonce
            },
            beforeSend: function() {
                $('#vdp-status-form button[type="submit"]').prop('disabled', true).text('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#vdp-status-modal').remove();
                    
                    // Show success message
                    showNotification('Lead status updated successfully!', 'success');
                    
                    // Reload leads table
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(response.data.message || 'Failed to update status', 'error');
                    $('#vdp-status-form button[type="submit"]').prop('disabled', false).text('Update Status');
                }
            },
            error: function() {
                showNotification('Network error. Please try again.', 'error');
                $('#vdp-status-form button[type="submit"]').prop('disabled', false).text('Update Status');
            }
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        var notificationHtml = '<div class="vdp-notification vdp-notification-' + type + '">' + message + '</div>';
        
        $('body').append(notificationHtml);
        
        var $notification = $('.vdp-notification').last();
        
        // Show notification
        setTimeout(function() {
            $notification.addClass('show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    // Initialize tooltips if available
    if (typeof $.fn.tooltip === 'function') {
        $('[title]').tooltip();
    }
});

/* Modal and notification styles */
var modalStyles = `
<style>
.vdp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vdp-modal {
    background: #fff;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.vdp-modal-header {
    padding: 20px 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vdp-modal-header h3 {
    margin: 0;
    color: #333;
}

.vdp-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 30px;
    height: 30px;
}

.vdp-modal-close:hover {
    color: #333;
}

.vdp-modal-body {
    padding: 20px;
}

.vdp-form-group {
    margin-bottom: 20px;
}

.vdp-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.vdp-form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.vdp-form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.vdp-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.vdp-btn-primary {
    background: #007cba;
    color: #fff;
}

.vdp-btn-primary:hover {
    background: #005a87;
}

.vdp-btn-secondary {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.vdp-btn-secondary:hover {
    background: #e9ecef;
}

.vdp-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 4px;
    color: #fff;
    font-weight: 500;
    z-index: 10001;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.vdp-notification.show {
    transform: translateX(0);
}

.vdp-notification-success {
    background: #28a745;
}

.vdp-notification-error {
    background: #dc3545;
}
</style>
`;

// Inject styles
jQuery('head').append(modalStyles);