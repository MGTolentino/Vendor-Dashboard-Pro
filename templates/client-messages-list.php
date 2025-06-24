<?php
/**
 * Client Messages List Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-client-messages-container">
    <div class="vdp-section vdp-messages-header-section">
        <h2><?php esc_html_e('My Messages', 'vendor-dashboard-pro'); ?></h2>
        <p><?php esc_html_e('Here you can view and manage your conversations with vendors.', 'vendor-dashboard-pro'); ?></p>
    </div>
    
    <div class="vdp-section vdp-messages-list-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('All Messages', 'vendor-dashboard-pro'); ?></h2>
        </div>
        
        <div class="vdp-messages-list">
            <?php if (empty($messages)) : ?>
                <div class="vdp-empty-state">
                    <div class="vdp-empty-icon">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <p><?php esc_html_e('No messages yet. When you contact a vendor, your messages will appear here.', 'vendor-dashboard-pro'); ?></p>
                </div>
            <?php else : ?>
                <?php foreach ($messages as $message) : ?>
                    <div class="vdp-message-card">
                        <div class="vdp-message-sender">
                            <div class="vdp-avatar">
                                <?php if (!empty($message['vendor_avatar'])) : ?>
                                    <img src="<?php echo esc_url($message['vendor_avatar']); ?>" alt="<?php echo esc_attr($message['vendor_name']); ?>">
                                <?php else : ?>
                                    <div class="vdp-avatar-placeholder">
                                        <i class="fas fa-store"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="vdp-message-content">
                            <div class="vdp-message-header">
                                <div class="vdp-message-info">
                                    <h3 class="vdp-message-sender-name">
                                        <?php echo esc_html($message['vendor_name']); ?>
                                    </h3>
                                    <div class="vdp-message-meta">
                                        <span class="vdp-message-time"><?php echo esc_html(vdp_time_ago($message['date'])); ?></span>
                                        <span class="vdp-message-product">
                                            <i class="fas fa-tag"></i> <?php echo esc_html($message['listing_title']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="vdp-message-actions">
                                    <a href="<?php echo esc_url(add_query_arg('message_id', $message['id'])); ?>" class="vdp-btn vdp-btn-primary vdp-btn-sm" target="_blank">
                                        <?php esc_html_e('View', 'vendor-dashboard-pro'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="vdp-message-preview">
                                <strong><?php echo esc_html($message['subject']); ?></strong>: <?php echo esc_html(wp_trim_words($message['content'], 20)); ?>
                            </div>
                            
                            <?php if ($message['has_response']) : ?>
                                <div class="vdp-message-status">
                                    <span class="vdp-responded-badge">
                                        <i class="fas fa-check"></i> <?php esc_html_e('Vendor Responded', 'vendor-dashboard-pro'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>