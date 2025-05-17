<?php
/**
 * Messages content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-messages-content">
    <div class="vdp-section vdp-messages-header-section">
        <div class="vdp-messages-stats">
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html(count($messages)); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Total Messages', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-unread">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $unread_count = 0;
                        foreach ($messages as $message) {
                            if (!$message['is_read']) {
                                $unread_count++;
                            }
                        }
                        echo esc_html($unread_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Unread', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-today">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $today_count = 0;
                        $today = date('Y-m-d');
                        foreach ($messages as $message) {
                            if (date('Y-m-d', strtotime($message['date'])) === $today) {
                                $today_count++;
                            }
                        }
                        echo esc_html($today_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Today', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-responded">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $responded_count = 0;
                        foreach ($messages as $message) {
                            if ($message['has_response']) {
                                $responded_count++;
                            }
                        }
                        echo esc_html($responded_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Responded', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vdp-section vdp-messages-list-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('All Messages', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-section-actions">
                <div class="vdp-filters">
                    <select class="vdp-filter-select" id="message-status-filter">
                        <option value=""><?php esc_html_e('All Messages', 'vendor-dashboard-pro'); ?></option>
                        <option value="unread"><?php esc_html_e('Unread', 'vendor-dashboard-pro'); ?></option>
                        <option value="read"><?php esc_html_e('Read', 'vendor-dashboard-pro'); ?></option>
                    </select>
                    
                    <div class="vdp-search-filter">
                        <input type="text" class="vdp-search-input" id="message-search" placeholder="<?php esc_attr_e('Search messages...', 'vendor-dashboard-pro'); ?>">
                        <button class="vdp-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="vdp-messages-list">
            <?php if (empty($messages)) : ?>
                <div class="vdp-empty-state">
                    <div class="vdp-empty-icon">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <p><?php esc_html_e('No messages found.', 'vendor-dashboard-pro'); ?></p>
                </div>
            <?php else : ?>
                <?php foreach ($messages as $message) : ?>
                    <div class="vdp-message-card <?php echo !$message['is_read'] ? 'vdp-unread' : ''; ?>" data-status="<?php echo !$message['is_read'] ? 'unread' : 'read'; ?>">
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
                        </div>
                        
                        <div class="vdp-message-content">
                            <div class="vdp-message-header">
                                <div class="vdp-message-info">
                                    <h3 class="vdp-message-sender-name">
                                        <?php echo esc_html($message['sender_name']); ?>
                                        <?php if (!$message['is_read']) : ?>
                                            <span class="vdp-unread-dot" title="<?php esc_attr_e('Unread message', 'vendor-dashboard-pro'); ?>"></span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="vdp-message-meta">
                                        <span class="vdp-message-time"><?php echo esc_html(vdp_time_ago($message['date'])); ?></span>
                                        <span class="vdp-message-product">
                                            <i class="fas fa-tag"></i> <?php echo esc_html($message['product_title']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="vdp-message-actions">
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('messages/view/' . $message['id'])); ?>" class="vdp-btn vdp-btn-primary vdp-btn-sm">
                                        <?php esc_html_e('View', 'vendor-dashboard-pro'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="vdp-message-preview">
                                <?php echo esc_html(wp_trim_words($message['content'], 30)); ?>
                            </div>
                            
                            <?php if ($message['has_response']) : ?>
                                <div class="vdp-message-status">
                                    <span class="vdp-responded-badge">
                                        <i class="fas fa-check"></i> <?php esc_html_e('Responded', 'vendor-dashboard-pro'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($total_pages > 1) : ?>
                    <div class="vdp-pagination">
                        <?php
                        $current_url = add_query_arg(array(), vdp_get_dashboard_url('messages'));
                        
                        // Previous page
                        if ($paged > 1) {
                            $prev_url = add_query_arg('paged', $paged - 1, $current_url);
                            echo '<a href="' . esc_url($prev_url) . '" class="vdp-pagination-item vdp-pagination-prev">';
                            echo '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'vendor-dashboard-pro');
                            echo '</a>';
                        } else {
                            echo '<span class="vdp-pagination-item vdp-pagination-prev vdp-pagination-disabled">';
                            echo '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'vendor-dashboard-pro');
                            echo '</span>';
                        }
                        
                        // Page numbers
                        $start_page = max(1, $paged - 2);
                        $end_page = min($total_pages, $paged + 2);
                        
                        if ($start_page > 1) {
                            $first_url = add_query_arg('paged', 1, $current_url);
                            echo '<a href="' . esc_url($first_url) . '" class="vdp-pagination-item">1</a>';
                            
                            if ($start_page > 2) {
                                echo '<span class="vdp-pagination-dots">...</span>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $paged) {
                                echo '<span class="vdp-pagination-item vdp-pagination-current">' . esc_html($i) . '</span>';
                            } else {
                                $page_url = add_query_arg('paged', $i, $current_url);
                                echo '<a href="' . esc_url($page_url) . '" class="vdp-pagination-item">' . esc_html($i) . '</a>';
                            }
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span class="vdp-pagination-dots">...</span>';
                            }
                            
                            $last_url = add_query_arg('paged', $total_pages, $current_url);
                            echo '<a href="' . esc_url($last_url) . '" class="vdp-pagination-item">' . esc_html($total_pages) . '</a>';
                        }
                        
                        // Next page
                        if ($paged < $total_pages) {
                            $next_url = add_query_arg('paged', $paged + 1, $current_url);
                            echo '<a href="' . esc_url($next_url) . '" class="vdp-pagination-item vdp-pagination-next">';
                            echo esc_html__('Next', 'vendor-dashboard-pro') . ' <i class="fas fa-chevron-right"></i>';
                            echo '</a>';
                        } else {
                            echo '<span class="vdp-pagination-item vdp-pagination-next vdp-pagination-disabled">';
                            echo esc_html__('Next', 'vendor-dashboard-pro') . ' <i class="fas fa-chevron-right"></i>';
                            echo '</span>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter by message status
    $('#message-status-filter').on('change', function() {
        var status = $(this).val();
        
        if (status === '') {
            $('.vdp-message-card').show();
        } else {
            $('.vdp-message-card').hide();
            $('.vdp-message-card[data-status="' + status + '"]').show();
        }
    });
    
    // Search messages
    $('#message-search').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        
        $('.vdp-message-card').each(function() {
            var $card = $(this);
            var sender = $card.find('.vdp-message-sender-name').text().toLowerCase();
            var preview = $card.find('.vdp-message-preview').text().toLowerCase();
            var product = $card.find('.vdp-message-product').text().toLowerCase();
            
            if (sender.indexOf(search) > -1 || preview.indexOf(search) > -1 || product.indexOf(search) > -1) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    });
});
</script>