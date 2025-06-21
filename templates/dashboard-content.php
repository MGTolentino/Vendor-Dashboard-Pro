<?php
/**
 * Dashboard content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics if not provided
if (!isset($statistics) || empty($statistics)) {
    $statistics = VDP_Dashboard::get_demo_statistics();
}

// Get performance level
$performance_level = VDP_Dashboard::get_performance_level($statistics);

// Get sales trend
$sales_trend = VDP_Dashboard::get_sales_trend();

// Get quick actions
$quick_actions = VDP_Dashboard::get_quick_actions();

// Get greeting
$greeting = VDP_Dashboard::get_greeting();

// Get vendor name safely
$vendor_name = 'Vendor';
if (is_object($vendor) && method_exists($vendor, 'get_name')) {
    $vendor_name = $vendor->get_name();
} elseif (is_object($vendor) && isset($vendor->get_name) && is_callable($vendor->get_name)) {
    $callback = $vendor->get_name;
    $vendor_name = $callback();
}

?>

<div class="vdp-dashboard-content">
    <!-- Welcome Section -->
    <div class="vdp-welcome-section">
        <div class="vdp-welcome-header">
            <h2 class="vdp-welcome-title">
                <?php echo esc_html($greeting); ?>, <?php echo esc_html($vendor_name); ?>!
            </h2>
            <p class="vdp-welcome-subtitle">
                <?php esc_html_e('Here\'s what\'s happening with your store today.', 'vendor-dashboard-pro'); ?>
            </p>
        </div>
        
        <div class="vdp-performance-summary">
            <div class="vdp-performance-indicator vdp-performance-<?php echo esc_attr($performance_level); ?>">
                <div class="vdp-performance-icon">
                    <?php if ($performance_level === 'excellent') : ?>
                        <i class="fas fa-trophy"></i>
                    <?php elseif ($performance_level === 'good') : ?>
                        <i class="fas fa-thumbs-up"></i>
                    <?php elseif ($performance_level === 'average') : ?>
                        <i class="fas fa-check"></i>
                    <?php else : ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="vdp-performance-text">
                    <?php if ($performance_level === 'excellent') : ?>
                        <h3><?php esc_html_e('Excellent Performance!', 'vendor-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Your store is doing great. Keep up the good work!', 'vendor-dashboard-pro'); ?></p>
                    <?php elseif ($performance_level === 'good') : ?>
                        <h3><?php esc_html_e('Good Performance', 'vendor-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Your store is performing well. There\'s always room for improvement!', 'vendor-dashboard-pro'); ?></p>
                    <?php elseif ($performance_level === 'average') : ?>
                        <h3><?php esc_html_e('Average Performance', 'vendor-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Your store is doing okay. Check out the tips to improve your performance.', 'vendor-dashboard-pro'); ?></p>
                    <?php else : ?>
                        <h3><?php esc_html_e('Needs Improvement', 'vendor-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('There are several areas that need attention. Check out the recommendations below.', 'vendor-dashboard-pro'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="vdp-section vdp-quick-actions-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Quick Actions', 'vendor-dashboard-pro'); ?></h2>
        </div>
        
        <div class="vdp-quick-actions">
            <?php foreach ($quick_actions as $action) : ?>
                <a href="<?php echo esc_url($action['url']); ?>" class="vdp-quick-action-card" style="--action-color: <?php echo esc_attr($action['color']); ?>;">
                    <div class="vdp-quick-action-icon">
                        <i class="<?php echo esc_attr($action['icon']); ?>"></i>
                    </div>
                    <div class="vdp-quick-action-title">
                        <?php echo esc_html($action['title']); ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Stats Overview -->
    <div class="vdp-section vdp-stats-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Stats Overview', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-section-actions">
                <a href="<?php echo esc_url(vdp_get_dashboard_url('analytics')); ?>" class="vdp-btn vdp-btn-text">
                    <?php esc_html_e('View Details', 'vendor-dashboard-pro'); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="vdp-stats-grid">
            <!-- Sales Card -->
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Sales', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-<?php echo esc_attr($sales_trend); ?>">
                        <?php if ($sales_trend === 'up') : ?>
                            <i class="fas fa-arrow-up"></i> <?php esc_html_e('12%', 'vendor-dashboard-pro'); ?>
                        <?php elseif ($sales_trend === 'down') : ?>
                            <i class="fas fa-arrow-down"></i> <?php esc_html_e('5%', 'vendor-dashboard-pro'); ?>
                        <?php else : ?>
                            <i class="fas fa-equals"></i> <?php esc_html_e('0%', 'vendor-dashboard-pro'); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(vdp_format_price($statistics['sales_amount'])); ?>
                </div>
                <div class="vdp-stat-footer">
                    <?php echo esc_html(sprintf(__('%s sales this month', 'vendor-dashboard-pro'), vdp_format_number($statistics['sales_count']))); ?>
                </div>
                <canvas class="vdp-stat-chart" id="salesChart" width="100%" height="60"></canvas>
            </div>
            
            <!-- Views Card -->
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Views', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('8%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(vdp_format_number($statistics['views_count'])); ?>
                </div>
                <div class="vdp-stat-footer">
                    <?php esc_html_e('Total listing views', 'vendor-dashboard-pro'); ?>
                </div>
                <canvas class="vdp-stat-chart" id="viewsChart" width="100%" height="60"></canvas>
            </div>
            
            <!-- Conversion Rate Card -->
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Conversion', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('2%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html($statistics['conversion_rate']); ?>%
                </div>
                <div class="vdp-stat-footer">
                    <?php esc_html_e('Views to sales rate', 'vendor-dashboard-pro'); ?>
                </div>
                <canvas class="vdp-stat-chart" id="conversionChart" width="100%" height="60"></canvas>
            </div>
            
            <!-- Rating Card -->
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Rating', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-steady">
                        <i class="fas fa-equals"></i> <?php esc_html_e('0%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value vdp-rating-display">
                    <span><?php echo esc_html(number_format($statistics['average_rating'], 1)); ?></span>
                    <div class="vdp-rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <?php if ($i <= floor($statistics['average_rating'])) : ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $statistics['average_rating']) : ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else : ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="vdp-stat-footer">
                    <?php echo esc_html(sprintf(__('From %s reviews', 'vendor-dashboard-pro'), vdp_format_number($statistics['ratings_count']))); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="vdp-section vdp-recent-activity-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Recent Activity', 'vendor-dashboard-pro'); ?></h2>
        </div>
        
        <div class="vdp-activity-columns">
            <!-- Recent Products -->
            <div class="vdp-activity-column">
                <div class="vdp-activity-header">
                    <h3><?php esc_html_e('Recent Listings', 'vendor-dashboard-pro'); ?></h3>
                    <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-link-arrow">
                        <?php esc_html_e('View All', 'vendor-dashboard-pro'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="vdp-activity-list">
                    <?php if (empty($recent_listings)) : ?>
                        <div class="vdp-empty-state">
                            <div class="vdp-empty-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <p><?php esc_html_e('No listings yet. Add your first listing!', 'vendor-dashboard-pro'); ?></p>
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products', 'add')); ?>" class="vdp-btn vdp-btn-primary vdp-btn-sm vdp-add-listing-btn">
                                <i class="fas fa-plus"></i> <?php esc_html_e('Add Listing', 'vendor-dashboard-pro'); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="vdp-recent-listings">
                            <?php foreach ($recent_listings as $listing) : ?>
                                <div class="vdp-recent-listing-item">
                                    <div class="vdp-recent-listing-thumbnail">
                                        <?php if (!empty($listing['thumbnail'])) : ?>
                                            <img src="<?php echo esc_url($listing['thumbnail']); ?>" alt="<?php echo esc_attr($listing['title']); ?>">
                                        <?php else : ?>
                                            <div class="vdp-listing-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="vdp-status-badge vdp-status-<?php echo esc_attr($listing['status']); ?>">
                                            <?php echo esc_html(ucfirst($listing['status'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="vdp-recent-listing-content">
                                        <h4 class="vdp-recent-listing-title">
                                            <?php echo esc_html($listing['title']); ?>
                                        </h4>
                                        <div class="vdp-recent-listing-meta">
                                            <?php if ($listing['price'] > 0) : ?>
                                                <span class="vdp-price"><?php echo esc_html(vdp_format_price($listing['price'])); ?></span>
                                            <?php else : ?>
                                                <span class="vdp-price vdp-price-free"><?php esc_html_e('Free', 'vendor-dashboard-pro'); ?></span>
                                            <?php endif; ?>
                                            <span class="vdp-listing-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo esc_html(vdp_format_date($listing['date'])); ?>
                                            </span>
                                        </div>
                                        <div class="vdp-recent-listing-actions">
                                            <a href="<?php echo esc_url($listing['edit_url']); ?>" class="vdp-btn vdp-btn-secondary vdp-btn-sm">
                                                <i class="fas fa-edit"></i>
                                                <?php esc_html_e('Edit', 'vendor-dashboard-pro'); ?>
                                            </a>
                                            <a href="<?php echo esc_url(get_permalink($listing['id'])); ?>" class="vdp-btn vdp-btn-outline vdp-btn-sm" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                                <?php esc_html_e('View', 'vendor-dashboard-pro'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Messages -->
            <div class="vdp-activity-column">
                <div class="vdp-activity-header">
                    <h3><?php esc_html_e('Recent Messages', 'vendor-dashboard-pro'); ?></h3>
                    <a href="<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>" class="vdp-link-arrow">
                        <?php esc_html_e('View All', 'vendor-dashboard-pro'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="vdp-activity-list">
                    <div class="vdp-empty-state">
                        <div class="vdp-empty-icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <p><?php esc_html_e('No messages yet. Messages from customers will appear here.', 'vendor-dashboard-pro'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Los gráficos se inicializarán desde main.js -->