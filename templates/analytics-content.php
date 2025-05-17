<?php
/**
 * Analytics content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-analytics-content">
    <!-- Date Range Selector -->
    <div class="vdp-section vdp-date-range-section">
        <div class="vdp-date-range-selector">
            <div class="vdp-date-range-presets">
                <button type="button" class="vdp-btn vdp-btn-sm vdp-date-range-btn vdp-active" data-range="7">
                    <?php esc_html_e('Last 7 days', 'vendor-dashboard-pro'); ?>
                </button>
                <button type="button" class="vdp-btn vdp-btn-sm vdp-date-range-btn" data-range="30">
                    <?php esc_html_e('Last 30 days', 'vendor-dashboard-pro'); ?>
                </button>
                <button type="button" class="vdp-btn vdp-btn-sm vdp-date-range-btn" data-range="90">
                    <?php esc_html_e('Last 90 days', 'vendor-dashboard-pro'); ?>
                </button>
                <button type="button" class="vdp-btn vdp-btn-sm vdp-date-range-btn" data-range="year">
                    <?php esc_html_e('This Year', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <div class="vdp-date-range-custom">
                <div class="vdp-date-inputs">
                    <div class="vdp-form-group">
                        <label for="start_date" class="vdp-form-label"><?php esc_html_e('From', 'vendor-dashboard-pro'); ?></label>
                        <input type="date" id="start_date" class="vdp-form-control" value="<?php echo esc_attr(date('Y-m-d', strtotime('-7 days'))); ?>">
                    </div>
                    <div class="vdp-form-group">
                        <label for="end_date" class="vdp-form-label"><?php esc_html_e('To', 'vendor-dashboard-pro'); ?></label>
                        <input type="date" id="end_date" class="vdp-form-control" value="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                </div>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-btn-sm vdp-apply-date-range">
                    <?php esc_html_e('Apply', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Overview Stats -->
    <div class="vdp-section vdp-analytics-overview-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Performance Overview', 'vendor-dashboard-pro'); ?></h2>
        </div>
        
        <div class="vdp-analytics-stats">
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Total Sales', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('15%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(vdp_format_price($analytics['sales_amount'])); ?>
                </div>
                <div class="vdp-stat-footer">
                    <?php echo esc_html(sprintf(__('%s orders', 'vendor-dashboard-pro'), vdp_format_number($analytics['orders_count']))); ?>
                </div>
            </div>
            
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Product Views', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('8%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(vdp_format_number($analytics['views_count'])); ?>
                </div>
                <div class="vdp-stat-footer">
                    <?php esc_html_e('Total product views', 'vendor-dashboard-pro'); ?>
                </div>
            </div>
            
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Avg. Order Value', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('5%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(vdp_format_price($analytics['avg_order_value'])); ?>
                </div>
                <div class="vdp-stat-footer">
                    <?php esc_html_e('Per order average', 'vendor-dashboard-pro'); ?>
                </div>
            </div>
            
            <div class="vdp-stat-card">
                <div class="vdp-stat-header">
                    <div class="vdp-stat-title"><?php esc_html_e('Conversion Rate', 'vendor-dashboard-pro'); ?></div>
                    <div class="vdp-stat-trend vdp-trend-up">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('2%', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
                <div class="vdp-stat-value">
                    <?php echo esc_html(number_format($analytics['conversion_rate'], 2)); ?>%
                </div>
                <div class="vdp-stat-footer">
                    <?php esc_html_e('Views to orders', 'vendor-dashboard-pro'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sales Chart -->
    <div class="vdp-section vdp-sales-chart-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Sales Performance', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-chart-options">
                <select id="sales_chart_type" class="vdp-select-sm">
                    <option value="revenue"><?php esc_html_e('Revenue', 'vendor-dashboard-pro'); ?></option>
                    <option value="orders"><?php esc_html_e('Orders', 'vendor-dashboard-pro'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="vdp-chart-container">
            <canvas id="salesChart" width="100%" height="350"></canvas>
        </div>
    </div>
    
    <div class="vdp-analytics-grid">
        <!-- Top Products -->
        <div class="vdp-section vdp-top-products-section">
            <div class="vdp-section-header">
                <h2 class="vdp-section-title"><?php esc_html_e('Top Products', 'vendor-dashboard-pro'); ?></h2>
            </div>
            
            <div class="vdp-table-responsive">
                <table class="vdp-table vdp-top-products-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Product', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Sales', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Revenue', 'vendor-dashboard-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics['top_products'] as $product) : ?>
                            <tr>
                                <td class="vdp-product-cell">
                                    <div class="vdp-product-cell-inner">
                                        <?php if (!empty($product['image'])) : ?>
                                            <div class="vdp-product-thumb">
                                                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>">
                                            </div>
                                        <?php else : ?>
                                            <div class="vdp-product-thumb vdp-no-thumb">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="vdp-product-name">
                                            <?php echo esc_html($product['name']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="vdp-sales-cell">
                                    <?php echo esc_html($product['sales_count']); ?>
                                </td>
                                <td class="vdp-revenue-cell">
                                    <?php echo esc_html(vdp_format_price($product['revenue'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Traffic Sources -->
        <div class="vdp-section vdp-traffic-section">
            <div class="vdp-section-header">
                <h2 class="vdp-section-title"><?php esc_html_e('Traffic Sources', 'vendor-dashboard-pro'); ?></h2>
            </div>
            
            <div class="vdp-chart-container">
                <canvas id="trafficChart" width="100%" height="300"></canvas>
            </div>
            
            <div class="vdp-traffic-legend">
                <?php foreach ($analytics['traffic_sources'] as $source) : ?>
                    <div class="vdp-legend-item" style="--color: <?php echo esc_attr($source['color']); ?>">
                        <div class="vdp-legend-color"></div>
                        <div class="vdp-legend-label"><?php echo esc_html($source['label']); ?></div>
                        <div class="vdp-legend-value"><?php echo esc_html($source['value']); ?>%</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="vdp-section vdp-recent-orders-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Recent Orders', 'vendor-dashboard-pro'); ?></h2>
            <a href="<?php echo esc_url(vdp_get_dashboard_url('orders')); ?>" class="vdp-btn vdp-btn-text">
                <?php esc_html_e('View All Orders', 'vendor-dashboard-pro'); ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="vdp-table-responsive">
            <table class="vdp-table vdp-orders-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Order', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Date', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Customer', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Status', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Total', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Actions', 'vendor-dashboard-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['recent_orders'] as $order) : ?>
                        <tr>
                            <td class="vdp-order-number">
                                <a href="<?php echo esc_url(vdp_get_dashboard_url('orders/view/' . $order['id'])); ?>">
                                    <?php echo esc_html($order['order_number']); ?>
                                </a>
                            </td>
                            <td class="vdp-order-date">
                                <?php echo esc_html(vdp_format_date($order['date'])); ?>
                            </td>
                            <td class="vdp-order-customer">
                                <?php echo esc_html($order['customer_name']); ?>
                            </td>
                            <td class="vdp-order-status">
                                <span class="vdp-status-badge <?php echo esc_attr(VDP_Orders::get_status_class($order['status'])); ?>">
                                    <?php echo esc_html(VDP_Orders::get_status_label($order['status'])); ?>
                                </span>
                            </td>
                            <td class="vdp-order-total">
                                <?php echo esc_html(vdp_format_price($order['total'])); ?>
                            </td>
                            <td class="vdp-order-actions">
                                <a href="<?php echo esc_url(vdp_get_dashboard_url('orders/view/' . $order['id'])); ?>" class="vdp-btn vdp-btn-sm vdp-btn-icon" title="<?php esc_attr_e('View', 'vendor-dashboard-pro'); ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Download Reports -->
    <div class="vdp-section vdp-reports-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Export Reports', 'vendor-dashboard-pro'); ?></h2>
        </div>
        
        <div class="vdp-reports-grid">
            <div class="vdp-report-card">
                <div class="vdp-report-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="vdp-report-info">
                    <h3 class="vdp-report-title"><?php esc_html_e('Sales Report', 'vendor-dashboard-pro'); ?></h3>
                    <p class="vdp-report-desc"><?php esc_html_e('Detailed breakdown of your sales and revenue', 'vendor-dashboard-pro'); ?></p>
                </div>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-export-report-btn" data-type="sales">
                    <?php esc_html_e('Export', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <div class="vdp-report-card">
                <div class="vdp-report-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="vdp-report-info">
                    <h3 class="vdp-report-title"><?php esc_html_e('Product Performance', 'vendor-dashboard-pro'); ?></h3>
                    <p class="vdp-report-desc"><?php esc_html_e('Analysis of your best and worst performing products', 'vendor-dashboard-pro'); ?></p>
                </div>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-export-report-btn" data-type="products">
                    <?php esc_html_e('Export', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <div class="vdp-report-card">
                <div class="vdp-report-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="vdp-report-info">
                    <h3 class="vdp-report-title"><?php esc_html_e('Customer Report', 'vendor-dashboard-pro'); ?></h3>
                    <p class="vdp-report-desc"><?php esc_html_e('Insights about your customers and their buying behavior', 'vendor-dashboard-pro'); ?></p>
                </div>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-export-report-btn" data-type="customers">
                    <?php esc_html_e('Export', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
            
            <div class="vdp-report-card">
                <div class="vdp-report-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="vdp-report-info">
                    <h3 class="vdp-report-title"><?php esc_html_e('Marketing Effectiveness', 'vendor-dashboard-pro'); ?></h3>
                    <p class="vdp-report-desc"><?php esc_html_e('Analysis of your marketing campaigns and their ROI', 'vendor-dashboard-pro'); ?></p>
                </div>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-export-report-btn" data-type="marketing">
                    <?php esc_html_e('Export', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Chart.js setup
    if (typeof Chart !== 'undefined') {
        // Date range buttons
        $('.vdp-date-range-btn').on('click', function() {
            $('.vdp-date-range-btn').removeClass('vdp-active');
            $(this).addClass('vdp-active');
            
            var range = $(this).data('range');
            var endDate = new Date();
            var startDate = new Date();
            
            if (range === 7) {
                startDate.setDate(endDate.getDate() - 7);
            } else if (range === 30) {
                startDate.setDate(endDate.getDate() - 30);
            } else if (range === 90) {
                startDate.setDate(endDate.getDate() - 90);
            } else if (range === 'year') {
                startDate = new Date(endDate.getFullYear(), 0, 1);
            }
            
            $('#start_date').val(startDate.toISOString().slice(0, 10));
            $('#end_date').val(endDate.toISOString().slice(0, 10));
            
            // Update charts (would fetch new data in real implementation)
            updateCharts();
        });
        
        // Apply custom date range
        $('.vdp-apply-date-range').on('click', function() {
            $('.vdp-date-range-btn').removeClass('vdp-active');
            
            // Update charts (would fetch new data in real implementation)
            updateCharts();
        });
        
        // Sales chart
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [1200, 1900, 1500, 2200, 2500, 2800, 3100, 3400, 3200, 3800, 4100, 4500],
                    backgroundColor: 'rgba(52, 131, 250, 0.1)',
                    borderColor: 'rgba(52, 131, 250, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Switch between revenue and orders
        $('#sales_chart_type').on('change', function() {
            var type = $(this).val();
            var data;
            
            if (type === 'revenue') {
                data = [1200, 1900, 1500, 2200, 2500, 2800, 3100, 3400, 3200, 3800, 4100, 4500];
                salesChart.options.scales.y.ticks.callback = function(value) {
                    return '$' + value.toLocaleString();
                };
                salesChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return '$' + context.raw.toLocaleString();
                };
            } else {
                data = [25, 42, 38, 54, 62, 75, 85, 93, 87, 102, 115, 125];
                salesChart.options.scales.y.ticks.callback = function(value) {
                    return value;
                };
                salesChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return context.raw + ' orders';
                };
            }
            
            salesChart.data.datasets[0].data = data;
            salesChart.update();
        });
        
        // Traffic sources chart
        var trafficCtx = document.getElementById('trafficChart').getContext('2d');
        var trafficChart = new Chart(trafficCtx, {
            type: 'doughnut',
            data: {
                labels: ['Search Engines', 'Direct', 'Social Media', 'Referrals', 'Email'],
                datasets: [{
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: [
                        '#3483fa',
                        '#39b54a',
                        '#f5a623',
                        '#9b59b6',
                        '#e74c3c'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Function to update charts based on date range
        function updateCharts() {
            // In a real implementation, this would fetch new data from the server
            // For demo purposes, we'll just simulate a loading state
            
            // Show loading state
            $('.vdp-chart-container').addClass('vdp-loading');
            
            setTimeout(function() {
                // Generate random data
                var revenueData = [];
                for (var i = 0; i < 12; i++) {
                    revenueData.push(Math.floor(Math.random() * 5000) + 1000);
                }
                
                // Update sales chart
                salesChart.data.datasets[0].data = revenueData;
                salesChart.update();
                
                // Update traffic chart with random data
                var trafficData = [];
                var total = 0;
                for (var i = 0; i < 5; i++) {
                    var value = Math.floor(Math.random() * 30) + 5;
                    trafficData.push(value);
                    total += value;
                }
                
                // Normalize to sum to 100%
                trafficData = trafficData.map(function(value) {
                    return Math.round(value / total * 100);
                });
                
                // Make sure it sums to 100
                var sum = trafficData.reduce((a, b) => a + b, 0);
                if (sum < 100) {
                    trafficData[0] += (100 - sum);
                } else if (sum > 100) {
                    trafficData[0] -= (sum - 100);
                }
                
                trafficChart.data.datasets[0].data = trafficData;
                trafficChart.update();
                
                // Update legend values
                $('.vdp-legend-value').each(function(index) {
                    $(this).text(trafficData[index] + '%');
                });
                
                // Remove loading state
                $('.vdp-chart-container').removeClass('vdp-loading');
            }, 1000);
        }
        
        // Export report button
        $('.vdp-export-report-btn').on('click', function() {
            var reportType = $(this).data('type');
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            
            // Show loading state
            $(this).addClass('vdp-btn-loading').prop('disabled', true);
            
            // Simulate export (would be an AJAX request in a real implementation)
            setTimeout(function() {
                alert('Report export initiated! In a real implementation, this would generate and download a ' + reportType + ' report for the period ' + startDate + ' to ' + endDate);
                
                // Reset button state
                $('.vdp-export-report-btn').removeClass('vdp-btn-loading').prop('disabled', false);
            }, 1500);
        });
    }
});
</script>