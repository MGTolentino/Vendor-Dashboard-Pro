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
                    <?php esc_html_e('Total product views', 'vendor-dashboard-pro'); ?>
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
                    <h3><?php esc_html_e('Recent Products', 'vendor-dashboard-pro'); ?></h3>
                    <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-link-arrow">
                        <?php esc_html_e('View All', 'vendor-dashboard-pro'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="vdp-activity-list">
                    <div class="vdp-empty-state">
                        <div class="vdp-empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <p><?php esc_html_e('No products yet. Add your first product!', 'vendor-dashboard-pro'); ?></p>
                        <a href="<?php echo esc_url(vdp_get_dashboard_url('products', 'add')); ?>" class="vdp-btn vdp-btn-primary vdp-btn-sm">
                            <i class="fas fa-plus"></i> <?php esc_html_e('Add Product', 'vendor-dashboard-pro'); ?>
                        </a>
                    </div>
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

<script>
jQuery(document).ready(function($) {
    // Initialize charts if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        // Helper to create gradient
        function createGradient(ctx, startColor, endColor) {
            var gradient = ctx.createLinearGradient(0, 0, 0, 60);
            gradient.addColorStop(0, startColor);
            gradient.addColorStop(1, endColor);
            return gradient;
        }
        
        // Helper to create chart config
        function createChartConfig(label, data, color) {
            return {
                type: 'line',
                data: {
                    labels: Array(data.length).fill(''),
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: color,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 0,
                        fill: true,
                        backgroundColor: function(context) {
                            var ctx = context.chart.ctx;
                            return createGradient(ctx, color + '40', color + '00');
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            display: false,
                            min: 0
                        }
                    },
                    animation: {
                        duration: 1000
                    }
                }
            };
        }

        // Sales Chart
        var salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            salesCtx = salesCtx.getContext('2d');
            var salesData = [30, 40, 35, 50, 45, 60, 55, 65, 75, 70, 80, 75, 90, 85];
            var salesChart = new Chart(salesCtx, createChartConfig('Sales', salesData, '#3483fa'));
        }
        
        // Views Chart
        var viewsCtx = document.getElementById('viewsChart');
        if (viewsCtx) {
            viewsCtx = viewsCtx.getContext('2d');
            var viewsData = [300, 350, 320, 400, 380, 450, 470, 440, 500, 520, 480, 550, 530, 600];
            var viewsChart = new Chart(viewsCtx, createChartConfig('Views', viewsData, '#39b54a'));
        }
        
        // Conversion Chart
        var conversionCtx = document.getElementById('conversionChart');
        if (conversionCtx) {
            conversionCtx = conversionCtx.getContext('2d');
            var conversionData = [2.5, 3.0, 2.8, 3.2, 3.1, 3.5, 3.3, 3.8, 3.6, 4.0, 3.9, 4.2, 4.1, 4.5];
            var conversionChart = new Chart(conversionCtx, createChartConfig('Conversion', conversionData, '#f5a623'));
        }
    }
});
</script>