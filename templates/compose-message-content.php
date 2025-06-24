<?php
/**
 * Compose Message Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get lead information if provided
$lead_name = '';
$lead_email = isset($lead_email) ? $lead_email : '';
$lead_id = isset($lead_id) ? $lead_id : 0;

// If we have a lead ID, try to get more information
if ($lead_id > 0) {
    global $wpdb;
    $leads_table = $wpdb->prefix . 'jet_cct_leads';
    
    $lead_info = $wpdb->get_row($wpdb->prepare(
        "SELECT lead_nombre, lead_apellido, lead_e_mail FROM {$leads_table} WHERE _ID = %d",
        $lead_id
    ));
    
    if ($lead_info) {
        $lead_name = trim($lead_info->lead_nombre . ' ' . $lead_info->lead_apellido);
        $lead_email = $lead_info->lead_e_mail;
    }
}
?>

<div class="vdp-compose-message">
    <div class="vdp-section-header">
        <h2 class="vdp-section-title">
            <i class="fas fa-edit"></i>
            <?php _e('Compose New Message', 'vendor-dashboard-pro'); ?>
        </h2>
        <div class="vdp-section-actions">
            <a href="<?php echo esc_url(add_query_arg('vdp-action', 'messages', vdp_get_dashboard_url())); ?>" class="vdp-btn vdp-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?php _e('Back to Messages', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    </div>

    <div class="vdp-compose-form-container">
        <form id="vdp-compose-form" class="vdp-compose-form">
            <?php wp_nonce_field('vdp_compose_message', 'vdp_compose_nonce'); ?>
            
            <div class="vdp-form-row">
                <div class="vdp-form-group vdp-form-group-half">
                    <label for="recipient_name"><?php _e('Recipient Name', 'vendor-dashboard-pro'); ?></label>
                    <input type="text" 
                           id="recipient_name" 
                           name="recipient_name" 
                           value="<?php echo esc_attr($lead_name); ?>" 
                           class="vdp-form-control" 
                           <?php echo $lead_name ? 'readonly' : ''; ?>
                           required>
                </div>
                
                <div class="vdp-form-group vdp-form-group-half">
                    <label for="recipient_email"><?php _e('Recipient Email', 'vendor-dashboard-pro'); ?></label>
                    <input type="email" 
                           id="recipient_email" 
                           name="recipient_email" 
                           value="<?php echo esc_attr($lead_email); ?>" 
                           class="vdp-form-control" 
                           <?php echo $lead_email ? 'readonly' : ''; ?>
                           required>
                </div>
            </div>
            
            <div class="vdp-form-group">
                <label for="message_subject"><?php _e('Subject', 'vendor-dashboard-pro'); ?></label>
                <input type="text" 
                       id="message_subject" 
                       name="subject" 
                       class="vdp-form-control" 
                       placeholder="<?php esc_attr_e('Enter message subject...', 'vendor-dashboard-pro'); ?>"
                       required>
            </div>
            
            <div class="vdp-form-group">
                <label for="message_content"><?php _e('Message', 'vendor-dashboard-pro'); ?></label>
                <textarea id="message_content" 
                          name="content" 
                          rows="8" 
                          class="vdp-form-control" 
                          placeholder="<?php esc_attr_e('Type your message here...', 'vendor-dashboard-pro'); ?>"
                          required></textarea>
            </div>
            
            <!-- Quick responses -->
            <div class="vdp-form-group">
                <label><?php _e('Quick Responses', 'vendor-dashboard-pro'); ?></label>
                <div class="vdp-quick-responses">
                    <?php 
                    $predefined_responses = VDP_Messages::get_predefined_responses();
                    foreach ($predefined_responses as $response): 
                    ?>
                        <button type="button" 
                                class="vdp-quick-response-btn" 
                                data-content="<?php echo esc_attr($response['content']); ?>">
                            <?php echo esc_html($response['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="vdp-form-actions">
                <button type="button" class="vdp-btn vdp-btn-secondary" onclick="history.back()">
                    <?php _e('Cancel', 'vendor-dashboard-pro'); ?>
                </button>
                <button type="submit" class="vdp-btn vdp-btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    <?php _e('Send Message', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <!-- Hidden fields for lead info -->
            <?php if ($lead_id): ?>
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead_id); ?>">
            <?php endif; ?>
        </form>
    </div>
</div>

<style>
.vdp-compose-message {
    max-width: 800px;
}

.vdp-compose-form-container {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.vdp-compose-form .vdp-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 0;
}

.vdp-compose-form .vdp-form-group {
    margin-bottom: 25px;
}

.vdp-compose-form .vdp-form-group-half {
    flex: 1;
    margin-bottom: 25px;
}

.vdp-compose-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.vdp-compose-form .vdp-form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
    box-sizing: border-box;
}

.vdp-compose-form .vdp-form-control:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 3px rgba(0,124,186,0.1);
}

.vdp-compose-form .vdp-form-control[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
}

.vdp-compose-form textarea.vdp-form-control {
    resize: vertical;
    min-height: 150px;
}

.vdp-quick-responses {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.vdp-quick-response-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #495057;
}

.vdp-quick-response-btn:hover {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
}

.vdp-form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.vdp-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.vdp-btn-primary {
    background: #007cba;
    color: #fff;
}

.vdp-btn-primary:hover {
    background: #005a87;
}

.vdp-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.vdp-btn-secondary:hover {
    background: #545b62;
}

@media (max-width: 768px) {
    .vdp-compose-form .vdp-form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .vdp-form-actions {
        flex-direction: column;
    }
    
    .vdp-compose-form-container {
        padding: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Quick response functionality
    $('.vdp-quick-response-btn').on('click', function() {
        var content = $(this).data('content');
        var currentContent = $('#message_content').val();
        
        if (currentContent) {
            $('#message_content').val(currentContent + '\n\n' + content);
        } else {
            $('#message_content').val(content);
        }
        
        // Focus on textarea
        $('#message_content').focus();
    });
    
    // Form submission
    $('#vdp-compose-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        
        // Disable submit button
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?php _e("Sending...", "vendor-dashboard-pro"); ?>');
        
        // Get form data
        var formData = {
            action: 'vdp_send_message',
            recipient_name: $('#recipient_name').val(),
            recipient_email: $('#recipient_email').val(),
            subject: $('#message_subject').val(),
            content: $('#message_content').val(),
            lead_id: $('input[name="lead_id"]').val(),
            nonce: $('input[name="vdp_compose_nonce"]').val()
        };
        
        // Send AJAX request
        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('<div class="vdp-notice vdp-notice-success"><p>' + response.data.message + '</p></div>')
                        .insertBefore($form).delay(3000).fadeOut();
                    
                    // Reset form
                    $form[0].reset();
                    
                    // Redirect after short delay
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(add_query_arg("vdp-action", "messages", vdp_get_dashboard_url())); ?>';
                    }, 2000);
                } else {
                    // Show error message
                    $('<div class="vdp-notice vdp-notice-error"><p>' + (response.data.message || '<?php _e("Failed to send message. Please try again.", "vendor-dashboard-pro"); ?>') + '</p></div>')
                        .insertBefore($form).delay(5000).fadeOut();
                }
            },
            error: function() {
                // Show error message
                $('<div class="vdp-notice vdp-notice-error"><p><?php _e("Network error. Please try again.", "vendor-dashboard-pro"); ?></p></div>')
                    .insertBefore($form).delay(5000).fadeOut();
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>