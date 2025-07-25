<?php
/**
 * Client Message View Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-client-message-view">
    <?php if (!$message) : ?>
        <div class="vdp-notice vdp-notice-error">
            <p><?php esc_html_e('Message not found or you do not have permission to view it.', 'vendor-dashboard-pro'); ?></p>
        </div>
    <?php else : ?>
        <!-- Message Header -->
        <div class="vdp-section vdp-message-header-section">
            <div class="vdp-message-subject">
                <h2><?php echo esc_html($message['subject']); ?></h2>
                <div class="vdp-message-meta">
                    <span class="vdp-message-product">
                        <i class="fas fa-tag"></i> 
                        <a href="<?php echo esc_url(get_permalink($message['listing_id'])); ?>" target="_blank">
                            <?php echo esc_html($message['listing_title']); ?>
                        </a>
                    </span>
                    <span class="vdp-message-date">
                        <i class="fas fa-calendar-alt"></i> <?php echo esc_html(vdp_format_date($message['date'])); ?>
                    </span>
                </div>
            </div>
            
            <div class="vdp-conversation-actions">
                <?php if (!$message['has_response']) : ?>
                    <span class="vdp-status-badge vdp-status-awaiting">
                        <i class="fas fa-clock"></i> <?php esc_html_e('Awaiting Vendor Response', 'vendor-dashboard-pro'); ?>
                    </span>
                <?php else : ?>
                    <span class="vdp-status-badge vdp-status-responded">
                        <i class="fas fa-check"></i> <?php esc_html_e('Vendor Responded', 'vendor-dashboard-pro'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Conversation -->
        <div class="vdp-section vdp-conversation-section">
            <div class="vdp-conversation">
                <!-- Original Message -->
                <div class="vdp-conversation-item vdp-customer-message">
                    <div class="vdp-message-sender">
                        <div class="vdp-avatar">
                            <?php 
                            $current_user = wp_get_current_user();
                            $user_avatar = get_avatar_url($current_user->ID, array('size' => 96));
                            ?>
                            <?php if (!empty($user_avatar)) : ?>
                                <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>">
                            <?php else : ?>
                                <div class="vdp-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="vdp-sender-name">
                            <?php echo esc_html($current_user->display_name); ?>
                        </div>
                    </div>
                    <div class="vdp-message-bubble">
                        <div class="vdp-message-content">
                            <?php echo wp_kses_post(wpautop($message['content'])); ?>
                        </div>
                        <div class="vdp-message-time">
                            <?php echo esc_html(vdp_format_date($message['date']) . ' ' . date_i18n(get_option('time_format'), strtotime($message['date']))); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Replies -->
                <?php if (!empty($message['replies'])) : ?>
                    <?php foreach ($message['replies'] as $reply) : ?>
                        <div class="vdp-conversation-item <?php echo $reply['is_vendor'] ? 'vdp-vendor-message' : 'vdp-customer-message'; ?>">
                            <div class="vdp-message-sender">
                                <div class="vdp-avatar">
                                    <?php if (!empty($reply['avatar'])) : ?>
                                        <img src="<?php echo esc_url($reply['avatar']); ?>" alt="<?php echo esc_attr($reply['name']); ?>">
                                    <?php else : ?>
                                        <div class="vdp-avatar-placeholder">
                                            <i class="<?php echo $reply['is_vendor'] ? 'fas fa-store' : 'fas fa-user'; ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="vdp-sender-name">
                                    <?php echo esc_html($reply['name']); ?>
                                </div>
                            </div>
                            <div class="vdp-message-bubble">
                                <div class="vdp-message-content">
                                    <?php echo wp_kses_post(wpautop($reply['content'])); ?>
                                </div>
                                <div class="vdp-message-time">
                                    <?php echo esc_html(vdp_format_date($reply['date']) . ' ' . date_i18n(get_option('time_format'), strtotime($reply['date']))); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Reply Form -->
            <div class="vdp-reply-form">
                <h3><?php esc_html_e('Reply to this conversation', 'vendor-dashboard-pro'); ?></h3>
                
                <div class="vdp-form-group">
                    <textarea id="client_reply_content" class="vdp-form-control" rows="6" placeholder="<?php esc_attr_e('Type your reply here...', 'vendor-dashboard-pro'); ?>"></textarea>
                </div>
                
                <div class="vdp-form-actions">
                    <button type="button" class="vdp-btn vdp-btn-primary vdp-send-client-reply-btn" data-message-id="<?php echo esc_attr($message['id']); ?>">
                        <i class="fas fa-paper-plane"></i> <?php esc_html_e('Send Reply', 'vendor-dashboard-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Vendor Info -->
        <div class="vdp-section vdp-vendor-info-section">
            <div class="vdp-section-header">
                <h2 class="vdp-section-title"><?php esc_html_e('Vendor Information', 'vendor-dashboard-pro'); ?></h2>
            </div>
            
            <div class="vdp-vendor-card">
                <div class="vdp-vendor-header">
                    <div class="vdp-vendor-avatar">
                        <?php if (!empty($message['vendor_avatar'])) : ?>
                            <img src="<?php echo esc_url($message['vendor_avatar']); ?>" alt="<?php echo esc_attr($message['vendor_name']); ?>">
                        <?php else : ?>
                            <div class="vdp-avatar-placeholder large">
                                <i class="fas fa-store"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="vdp-vendor-details">
                        <h3 class="vdp-vendor-name"><?php echo esc_html($message['vendor_name']); ?></h3>
                        <div class="vdp-vendor-meta">
                            <a href="<?php echo esc_url(get_permalink($message['vendor_id'])); ?>" target="_blank" class="vdp-btn vdp-btn-sm vdp-btn-outline">
                                <i class="fas fa-store"></i> <?php esc_html_e('View Vendor Profile', 'vendor-dashboard-pro'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Actions -->
        <div class="vdp-bottom-actions">
            <a href="<?php echo esc_url(remove_query_arg('message_id')); ?>" class="vdp-btn vdp-btn-text">
                <i class="fas fa-arrow-left"></i> <?php esc_html_e('Back to Messages', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Send reply
    $('.vdp-send-client-reply-btn').on('click', function() {
        var messageId = $(this).data('message-id');
        var replyContent = $('#client_reply_content').val();
        
        if (!replyContent) {
            alert('<?php esc_html_e('Please enter a reply.', 'vendor-dashboard-pro'); ?>');
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).addClass('vdp-btn-loading');
        
        // AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'vdp_client_send_reply',
                nonce: '<?php echo wp_create_nonce('vdp-ajax-nonce'); ?>',
                message_id: messageId,
                content: replyContent
            },
            success: function(response) {
                if (response.success) {
                    // Obtener datos de la respuesta
                    var reply = response.data.reply;
                    
                    // Añadir el HTML de la respuesta
                    var replyHtml = '<div class="vdp-conversation-item vdp-customer-message">' +
                                   '<div class="vdp-message-sender">' +
                                   '<div class="vdp-avatar">';
                    
                    if (reply.avatar) {
                        replyHtml += '<img src="' + reply.avatar + '" alt="' + reply.name + '">';
                    } else {
                        replyHtml += '<div class="vdp-avatar-placeholder">' +
                                    '<i class="fas fa-user"></i>' +
                                    '</div>';
                    }
                    
                    replyHtml += '</div>' +
                               '<div class="vdp-sender-name">' + reply.name + '</div>' +
                               '</div>' +
                               '<div class="vdp-message-bubble">' +
                               '<div class="vdp-message-content">' + 
                               '<p>' + replyContent.replace(/\n/g, '<br>') + '</p>' +
                               '</div>' +
                               '<div class="vdp-message-time">' + reply.formatted_date + '</div>' +
                               '</div>' +
                               '</div>';
                    
                    $('.vdp-conversation').append(replyHtml);
                    
                    // Reset form
                    $('#client_reply_content').val('');
                    
                    // Scroll to the new reply
                    $('html, body').animate({
                        scrollTop: $('.vdp-conversation-item:last').offset().top - 100
                    }, 500);
                    
                    // Show success notification
                    var notification = $('<div class="vdp-notification vdp-notification-success">' + response.data.message + '</div>');
                    $('.vdp-client-message-view').prepend(notification);
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php esc_html_e('An error occurred. Please try again.', 'vendor-dashboard-pro'); ?>';
                    var notification = $('<div class="vdp-notification vdp-notification-error">' + errorMsg + '</div>');
                    $('.vdp-client-message-view').prepend(notification);
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            },
            error: function() {
                // Show generic error notification
                var notification = $('<div class="vdp-notification vdp-notification-error"><?php esc_html_e('Failed to send reply. Please try again.', 'vendor-dashboard-pro'); ?></div>');
                $('.vdp-client-message-view').prepend(notification);
                
                // Hide notification after 3 seconds
                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            },
            complete: function() {
                // Reset button state
                $button.prop('disabled', false).removeClass('vdp-btn-loading');
            }
        });
    });
});
</script>