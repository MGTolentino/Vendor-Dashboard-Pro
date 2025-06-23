<?php
/**
 * Message view content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-message-view-content">
    <!-- Estilos críticos inline para asegurar carga correcta -->
    <style>
        /* Estilos críticos para la vista de mensajes */
        .vdp-message-view-content {
            max-width: 100%;
        }
        
        .vdp-message-header-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .vdp-conversation-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .vdp-customer-info-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        /* Estilos adicionales para asegurar la vista de detalle */
        .vdp-message-subject h2 {
            margin: 0 0 0.5rem 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .vdp-message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 14px;
            color: var(--vdp-message-text-light, #757575);
        }
        
        .vdp-conversation {
            margin-bottom: 2rem;
        }
        
        .vdp-conversation-item {
            display: flex;
            margin-bottom: 1.5rem;
        }
        
        .vdp-customer-message {
            flex-direction: row;
        }
        
        .vdp-vendor-message {
            flex-direction: row-reverse;
        }
        
        .vdp-reply-form {
            background-color: var(--vdp-gray-50, #f9fafb);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px dashed var(--vdp-gray-200, #e5e7eb);
        }
    </style>
    
    <!-- Script para detectar si estamos en la vista correcta -->
    <script>
    console.log("CARGANDO PLANTILLA DE VISTA DE MENSAJE INDIVIDUAL");
    document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM cargado en vista de mensaje individual");
    });
    </script>
    
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
                        <a href="<?php echo esc_url($message['product_url']); ?>" target="_blank">
                            <?php echo esc_html($message['product_title']); ?>
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
                        <i class="fas fa-clock"></i> <?php esc_html_e('Awaiting Response', 'vendor-dashboard-pro'); ?>
                    </span>
                <?php else : ?>
                    <span class="vdp-status-badge vdp-status-responded">
                        <i class="fas fa-check"></i> <?php esc_html_e('Responded', 'vendor-dashboard-pro'); ?>
                    </span>
                <?php endif; ?>
                
                <div class="vdp-message-actions">
                    <button type="button" class="vdp-btn vdp-btn-outline vdp-btn-sm vdp-mark-read-btn" <?php echo $message['is_read'] ? 'disabled' : ''; ?> data-message-id="<?php echo esc_attr($message['id']); ?>">
                        <i class="fas fa-envelope-open"></i> <?php esc_html_e('Mark as Read', 'vendor-dashboard-pro'); ?>
                    </button>
                    <button type="button" class="vdp-btn vdp-btn-outline vdp-btn-sm vdp-archive-btn" data-message-id="<?php echo esc_attr($message['id']); ?>">
                        <i class="fas fa-archive"></i> <?php esc_html_e('Archive', 'vendor-dashboard-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Conversation -->
        <div class="vdp-section vdp-conversation-section">
            <div class="vdp-conversation">
                <!-- Original Message -->
                <div class="vdp-conversation-item vdp-customer-message">
                    <div class="vdp-message-sender">
                        <div class="vdp-avatar">
                            <?php if (!empty($message['sender_avatar'])) : ?>
                                <img src="<?php echo esc_url($message['sender_avatar']); ?>" alt="<?php echo esc_attr($message['sender_name']); ?>">
                            <?php else : ?>
                                <div class="vdp-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="vdp-sender-name">
                            <?php echo esc_html($message['sender_name']); ?>
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
                <h3><?php esc_html_e('Reply to this message', 'vendor-dashboard-pro'); ?></h3>
                
                <div class="vdp-form-group">
                    <textarea id="reply_content" class="vdp-form-control" rows="6" placeholder="<?php esc_attr_e('Type your reply here...', 'vendor-dashboard-pro'); ?>"></textarea>
                </div>
                
                <div class="vdp-quick-replies">
                    <div class="vdp-quick-replies-header">
                        <h4><?php esc_html_e('Quick Replies', 'vendor-dashboard-pro'); ?></h4>
                    </div>
                    <div class="vdp-quick-replies-list">
                        <button type="button" class="vdp-quick-reply-item" data-reply="<?php esc_attr_e('Thank you for your message! Yes, this item is still available.', 'vendor-dashboard-pro'); ?>">
                            <?php esc_html_e('Item Available', 'vendor-dashboard-pro'); ?>
                        </button>
                        <button type="button" class="vdp-quick-reply-item" data-reply="<?php esc_attr_e('Thank you for your message! I\'m sorry, but this item is currently out of stock.', 'vendor-dashboard-pro'); ?>">
                            <?php esc_html_e('Out of Stock', 'vendor-dashboard-pro'); ?>
                        </button>
                        <button type="button" class="vdp-quick-reply-item" data-reply="<?php esc_attr_e('Thank you for your interest! Shipping usually takes 3-5 business days.', 'vendor-dashboard-pro'); ?>">
                            <?php esc_html_e('Shipping Info', 'vendor-dashboard-pro'); ?>
                        </button>
                        <button type="button" class="vdp-quick-reply-item" data-reply="<?php esc_attr_e('Thank you for your question. Let me look into this and get back to you as soon as possible.', 'vendor-dashboard-pro'); ?>">
                            <?php esc_html_e('Will Check', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="vdp-form-actions">
                    <button type="button" class="vdp-btn vdp-btn-primary vdp-send-reply-btn" data-message-id="<?php echo esc_attr($message['id']); ?>">
                        <i class="fas fa-paper-plane"></i> <?php esc_html_e('Send Reply', 'vendor-dashboard-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="vdp-section vdp-customer-info-section">
            <div class="vdp-section-header">
                <h2 class="vdp-section-title"><?php esc_html_e('Customer Information', 'vendor-dashboard-pro'); ?></h2>
            </div>
            
            <div class="vdp-customer-card">
                <div class="vdp-customer-header">
                    <div class="vdp-customer-avatar">
                        <?php if (!empty($message['sender_avatar'])) : ?>
                            <img src="<?php echo esc_url($message['sender_avatar']); ?>" alt="<?php echo esc_attr($message['sender_name']); ?>">
                        <?php else : ?>
                            <div class="vdp-avatar-placeholder large">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="vdp-customer-details">
                        <h3 class="vdp-customer-name"><?php echo esc_html($message['sender_name']); ?></h3>
                        <div class="vdp-customer-meta">
                            <span class="vdp-customer-since">
                                <i class="fas fa-user-clock"></i> <?php printf(esc_html__('Customer since %s', 'vendor-dashboard-pro'), 
                                    isset($message['customer_since']) && !empty($message['customer_since']) ? 
                                    esc_html(vdp_format_date($message['customer_since'])) : 
                                    esc_html__('N/A', 'vendor-dashboard-pro')); 
                                ?>
                            </span>
                            <?php if (!empty($message['orders_count'])) : ?>
                                <span class="vdp-customer-orders">
                                    <i class="fas fa-shopping-cart"></i> <?php printf(esc_html(_n('%d order', '%d orders', $message['orders_count'], 'vendor-dashboard-pro')), esc_html($message['orders_count'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="vdp-customer-interactions">
                    <div class="vdp-interaction-header">
                        <h4><?php esc_html_e('Recent Interactions', 'vendor-dashboard-pro'); ?></h4>
                    </div>
                    
                    <?php if (empty($message['interactions'])) : ?>
                        <div class="vdp-no-interactions">
                            <p><?php esc_html_e('No previous interactions with this customer.', 'vendor-dashboard-pro'); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="vdp-interactions-list">
                            <?php foreach ($message['interactions'] as $interaction) : ?>
                                <div class="vdp-interaction-item">
                                    <div class="vdp-interaction-icon">
                                        <i class="<?php echo esc_attr($interaction['icon']); ?>"></i>
                                    </div>
                                    <div class="vdp-interaction-content">
                                        <div class="vdp-interaction-title">
                                            <?php echo esc_html($interaction['title']); ?>
                                        </div>
                                        <div class="vdp-interaction-meta">
                                            <?php echo esc_html(vdp_time_ago($interaction['date'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Bottom Actions -->
        <div class="vdp-bottom-actions">
            <a href="<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>" class="vdp-btn vdp-btn-text">
                <i class="fas fa-arrow-left"></i> <?php esc_html_e('Back to Messages', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Mark as read
    $('.vdp-mark-read-btn').on('click', function() {
        var messageId = $(this).data('message-id');
        var $button = $(this);
        
        $button.prop('disabled', true).addClass('vdp-btn-loading');
        
        // Real AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'vdp_mark_message_read',
                nonce: '<?php echo wp_create_nonce('vdp-messages-nonce'); ?>',
                message_id: messageId
            },
            success: function(response) {
                if (response.success) {
                    $button.html('<i class="fas fa-check"></i> ' + '<?php esc_html_e('Marked as Read', 'vendor-dashboard-pro'); ?>');
                    
                    // Display success message
                    var notification = $('<div class="vdp-notification vdp-notification-success">' + response.data.message + '</div>');
                    $('.vdp-message-view-content').prepend(notification);
                    
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
                    $('.vdp-message-view-content').prepend(notification);
                    
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
                var notification = $('<div class="vdp-notification vdp-notification-error"><?php esc_html_e('Failed to mark message as read. Please try again.', 'vendor-dashboard-pro'); ?></div>');
                $('.vdp-message-view-content').prepend(notification);
                
                // Hide notification after 3 seconds
                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            },
            complete: function() {
                $button.removeClass('vdp-btn-loading');
            }
        });
    });
    
    // Archive message
    $('.vdp-archive-btn').on('click', function() {
        var messageId = $(this).data('message-id');
        
        if (confirm('<?php esc_html_e('Are you sure you want to archive this message?', 'vendor-dashboard-pro'); ?>')) {
            var $button = $(this);
            $button.prop('disabled', true).addClass('vdp-btn-loading');
            
            // Real AJAX request
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'vdp_archive_message',
                    nonce: '<?php echo wp_create_nonce('vdp-messages-nonce'); ?>',
                    message_id: messageId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success notification briefly before redirecting
                        var notification = $('<div class="vdp-notification vdp-notification-success">' + response.data.message + '</div>');
                        $('.vdp-message-view-content').prepend(notification);
                        
                        // Redirect back to messages list after a short delay
                        setTimeout(function() {
                            window.location.href = '<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>';
                        }, 1000);
                    } else {
                        // Show error message
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php esc_html_e('An error occurred. Please try again.', 'vendor-dashboard-pro'); ?>';
                        var notification = $('<div class="vdp-notification vdp-notification-error">' + errorMsg + '</div>');
                        $('.vdp-message-view-content').prepend(notification);
                        
                        // Reset button state
                        $button.prop('disabled', false).removeClass('vdp-btn-loading');
                        
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
                    var notification = $('<div class="vdp-notification vdp-notification-error"><?php esc_html_e('Failed to archive message. Please try again.', 'vendor-dashboard-pro'); ?></div>');
                    $('.vdp-message-view-content').prepend(notification);
                    
                    // Reset button state
                    $button.prop('disabled', false).removeClass('vdp-btn-loading');
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            });
        }
    });
    
    // Quick replies
    $('.vdp-quick-reply-item').on('click', function() {
        var replyText = $(this).data('reply');
        $('#reply_content').val(replyText);
    });
    
    // Send reply
    $('.vdp-send-reply-btn').on('click', function() {
        var messageId = $(this).data('message-id');
        var replyContent = $('#reply_content').val();
        
        if (!replyContent) {
            alert('<?php esc_html_e('Please enter a reply.', 'vendor-dashboard-pro'); ?>');
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).addClass('vdp-btn-loading');
        
        // Real AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'vdp_send_reply',
                nonce: '<?php echo wp_create_nonce('vdp-messages-nonce'); ?>',
                message_id: messageId,
                content: replyContent
            },
            success: function(response) {
                if (response.success) {
                    // Obtener la fecha formateada
                    var dateCreated = response.data.date || '';
                    var formattedDate = '<?php echo esc_html__('Just now', 'vendor-dashboard-pro'); ?>';
                    
                    // Obtener el nombre de usuario actual
                    var userName = '<?php echo esc_js(wp_get_current_user()->display_name); ?>' || '<?php echo esc_html__('You', 'vendor-dashboard-pro'); ?>';
                    
                    // Añadir el HTML de la respuesta
                    var replyHtml = '<div class="vdp-conversation-item vdp-vendor-message">' +
                                  '<div class="vdp-message-sender">' +
                                  '<div class="vdp-avatar">' +
                                  '<div class="vdp-avatar-placeholder">' +
                                  '<i class="fas fa-store"></i>' +
                                  '</div>' +
                                  '</div>' +
                                  '<div class="vdp-sender-name">' + userName + '</div>' +
                                  '</div>' +
                                  '<div class="vdp-message-bubble">' +
                                  '<div class="vdp-message-content">' + 
                                  '<p>' + replyContent.replace(/\n/g, '<br>') + '</p>' +
                                  '</div>' +
                                  '<div class="vdp-message-time">' + formattedDate + '</div>' +
                                  '</div>' +
                                  '</div>';
                    
                    $('.vdp-conversation').append(replyHtml);
                    
                    // Update status
                    $('.vdp-status-badge.vdp-status-awaiting')
                        .removeClass('vdp-status-awaiting')
                        .addClass('vdp-status-responded')
                        .html('<i class="fas fa-check"></i> <?php esc_html_e('Responded', 'vendor-dashboard-pro'); ?>');
                    
                    // Reset form
                    $('#reply_content').val('');
                    
                    // Scroll to the new reply
                    $('html, body').animate({
                        scrollTop: $('.vdp-conversation-item:last').offset().top - 100
                    }, 500);
                    
                    // Show success notification
                    var notification = $('<div class="vdp-notification vdp-notification-success">' + response.data.message + '</div>');
                    $('.vdp-message-view-content').prepend(notification);
                    
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
                    $('.vdp-message-view-content').prepend(notification);
                    
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
                $('.vdp-message-view-content').prepend(notification);
                
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