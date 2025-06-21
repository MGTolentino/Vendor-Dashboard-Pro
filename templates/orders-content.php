<?php
/**
 * Orders content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-orders-content">
    <div class="vdp-section vdp-orders-header-section">
        <div class="vdp-orders-stats">
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html(count($orders)); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Total Orders', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-processing">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $processing_count = 0;
                        foreach ($orders as $order) {
                            if ($order['status'] === 'processing') {
                                $processing_count++;
                            }
                        }
                        echo esc_html($processing_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Processing', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $pending_count = 0;
                        foreach ($orders as $order) {
                            if ($order['status'] === 'pending') {
                                $pending_count++;
                            }
                        }
                        echo esc_html($pending_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Pending', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value">
                        <?php
                        $completed_count = 0;
                        foreach ($orders as $order) {
                            if ($order['status'] === 'completed') {
                                $completed_count++;
                            }
                        }
                        echo esc_html($completed_count);
                        ?>
                    </div>
                    <div class="vdp-stat-label"><?php esc_html_e('Completed', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vdp-section vdp-orders-list-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('All Orders', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-section-actions">
                <div class="vdp-filters">
                    <select class="vdp-filter-select" id="order-status-filter">
                        <option value=""><?php esc_html_e('All Statuses', 'vendor-dashboard-pro'); ?></option>
                        <option value="pending"><?php esc_html_e('Pending', 'vendor-dashboard-pro'); ?></option>
                        <option value="processing"><?php esc_html_e('Processing', 'vendor-dashboard-pro'); ?></option>
                        <option value="completed"><?php esc_html_e('Completed', 'vendor-dashboard-pro'); ?></option>
                        <option value="on-hold"><?php esc_html_e('On Hold', 'vendor-dashboard-pro'); ?></option>
                        <option value="cancelled"><?php esc_html_e('Cancelled', 'vendor-dashboard-pro'); ?></option>
                    </select>
                    
                    <div class="vdp-search-filter">
                        <input type="text" class="vdp-search-input" id="order-search" placeholder="<?php esc_attr_e('Search orders...', 'vendor-dashboard-pro'); ?>">
                        <button class="vdp-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
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
                    <?php if (empty($orders)) : ?>
                        <tr>
                            <td colspan="6" class="vdp-empty-table">
                                <div class="vdp-empty-state">
                                    <div class="vdp-empty-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <p><?php esc_html_e('No orders found.', 'vendor-dashboard-pro'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($orders as $order) : ?>
                            <tr class="vdp-order-row" data-status="<?php echo esc_attr($order['status']); ?>">
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
                                    <span class="vdp-order-items"><?php echo esc_html(sprintf(_n('%d item', '%d items', $order['items'], 'vendor-dashboard-pro'), $order['items'])); ?></span>
                                </td>
                                <td class="vdp-order-actions">
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('orders/view/' . $order['id'])); ?>" class="vdp-btn vdp-btn-sm vdp-btn-icon" title="<?php esc_attr_e('View', 'vendor-dashboard-pro'); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($orders) && $total_pages > 1) : ?>
            <div class="vdp-pagination">
                <?php
                $current_url = add_query_arg(array(), vdp_get_dashboard_url('orders'));
                
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
    </div>
    
    <!-- Orders Summary Card -->
    <div class="vdp-section vdp-orders-summary">
        <div class="vdp-summary-header">
            <h2 class="vdp-summary-title"><?php esc_html_e('Recent Orders Summary', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-summary-period">
                <div class="vdp-period-selector active" data-period="7"><?php esc_html_e('7 Days', 'vendor-dashboard-pro'); ?></div>
                <div class="vdp-period-selector" data-period="30"><?php esc_html_e('30 Days', 'vendor-dashboard-pro'); ?></div>
                <div class="vdp-period-selector" data-period="90"><?php esc_html_e('90 Days', 'vendor-dashboard-pro'); ?></div>
            </div>
        </div>
        
        <div class="vdp-summary-stats">
            <div class="vdp-summary-stat">
                <div class="vdp-summary-value" id="vdp-period-orders">0</div>
                <div class="vdp-summary-label"><?php esc_html_e('Total Orders', 'vendor-dashboard-pro'); ?></div>
            </div>
            
            <div class="vdp-summary-stat">
                <div class="vdp-summary-value" id="vdp-period-revenue">$0.00</div>
                <div class="vdp-summary-label"><?php esc_html_e('Total Revenue', 'vendor-dashboard-pro'); ?></div>
            </div>
            
            <div class="vdp-summary-stat">
                <div class="vdp-summary-value" id="vdp-period-avg">$0.00</div>
                <div class="vdp-summary-label"><?php esc_html_e('Average Order', 'vendor-dashboard-pro'); ?></div>
            </div>
        </div>
        
        <div class="vdp-summary-chart">
            <canvas id="ordersChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Destroy any existing charts
    if (typeof VDP !== 'undefined' && typeof VDP.destroyExistingCharts === 'function') {
        VDP.destroyExistingCharts();
    }
    
    // Order status filter
    $('#order-status-filter').on('change', function() {
        var status = $(this).val();
        
        if (status === '') {
            $('.vdp-order-row').show();
        } else {
            $('.vdp-order-row').hide();
            $('.vdp-order-row[data-status="' + status + '"]').show();
        }
        
        // Add filter tag if not already present
        if (status !== '') {
            var statusLabel = $('#order-status-filter option:selected').text();
            addFilterTag('status', status, statusLabel);
        } else {
            // Remove any status filter tags
            $('.vdp-filter-tag[data-type="status"]').remove();
            
            // Hide filter tags section if empty
            if ($('.vdp-filter-tag').length === 0) {
                $('.vdp-filter-tags').hide();
            }
        }
    });
    
    // Order search
    $('#order-search').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        
        // Only proceed if search is at least 2 characters or empty
        if (search.length >= 2 || search === '') {
            $('.vdp-order-row').each(function() {
                var row = $(this);
                var orderNumber = row.find('.vdp-order-number').text().toLowerCase();
                var customer = row.find('.vdp-order-customer').text().toLowerCase();
                
                if (orderNumber.indexOf(search) > -1 || customer.indexOf(search) > -1) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            
            // Add search filter tag if not already present
            if (search.length >= 2) {
                addFilterTag('search', search, '"' + search + '"');
            } else {
                // Remove any search filter tags
                $('.vdp-filter-tag[data-type="search"]').remove();
                
                // Hide filter tags section if empty
                if ($('.vdp-filter-tag').length === 0) {
                    $('.vdp-filter-tags').hide();
                }
            }
        }
    });
    
    // Function to add filter tag
    function addFilterTag(type, value, label) {
        // First check if filter tags container exists, if not create it
        if ($('.vdp-filter-tags').length === 0) {
            $('.vdp-section-header').after('<div class="vdp-filter-tags"></div>');
        }
        
        // Show filter tags section
        $('.vdp-filter-tags').show();
        
        // Check if tag already exists
        var existingTag = $('.vdp-filter-tag[data-type="' + type + '"]');
        if (existingTag.length > 0) {
            // Update existing tag
            existingTag.attr('data-value', value);
            existingTag.find('.vdp-filter-tag-label').text(label);
            return;
        }
        
        // Create tag if it doesn't exist
        var tag = '<div class="vdp-filter-tag" data-type="' + type + '" data-value="' + value + '">';
        tag += '<span class="vdp-filter-tag-label">' + label + '</span>';
        tag += '<span class="vdp-filter-tag-remove"><i class="fas fa-times"></i></span>';
        tag += '</div>';
        
        $('.vdp-filter-tags').append(tag);
    }
    
    // Remove filter tag when clicking the remove button
    $(document).on('click', '.vdp-filter-tag-remove', function() {
        var tag = $(this).parent();
        var type = tag.data('type');
        
        // Remove the tag
        tag.remove();
        
        // Reset the corresponding filter
        if (type === 'status') {
            $('#order-status-filter').val('').trigger('change');
        } else if (type === 'search') {
            $('#order-search').val('').trigger('keyup');
        }
        
        // Hide filter tags section if empty
        if ($('.vdp-filter-tag').length === 0) {
            $('.vdp-filter-tags').hide();
        }
    });
    
    // Period selector for orders summary
    $('.vdp-period-selector').on('click', function() {
        // Update active class
        $('.vdp-period-selector').removeClass('active');
        $(this).addClass('active');
        
        // Get selected period
        var period = $(this).data('period');
        
        // Update chart and stats
        updateOrdersSummary(period);
    });
    
    // Initialize orders chart
    let ordersChartInstance = null;
    
    function initOrdersChart(labels, data) {
        // Get chart context
        var ctx = document.getElementById('ordersChart').getContext('2d');
        
        // If chart already exists, destroy it
        if (ordersChartInstance) {
            ordersChartInstance.destroy();
        }
        
        // Create gradient
        var gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(44, 114, 215, 0.5)');
        gradient.addColorStop(1, 'rgba(44, 114, 215, 0.1)');
        
        // Initialize chart
        ordersChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: '#2c72d7',
                    borderWidth: 2,
                    pointBackgroundColor: '#2c72d7',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
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
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        cornerRadius: 4,
                        caretSize: 6
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0,
                            color: '#6B7280',
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Function to update orders summary based on selected period
    function updateOrdersSummary(days) {
        // In a real implementation, this would fetch data from the server
        // For now, we'll use sample data
        
        // Set loading state
        $('#vdp-period-orders').text('...');
        $('#vdp-period-revenue').text('...');
        $('#vdp-period-avg').text('...');
        
        // Simulate AJAX call with setTimeout
        setTimeout(function() {
            // Sample data - in real implementation, this would be the AJAX response
            var sampleData = {
                totalOrders: Math.floor(Math.random() * 50) + 10,
                totalRevenue: (Math.random() * 5000 + 1000).toFixed(2),
                averageOrder: (Math.random() * 200 + 50).toFixed(2),
                chartLabels: [],
                chartData: []
            };
            
            // Generate chart data
            for (var i = 0; i < days; i++) {
                var date = new Date();
                date.setDate(date.getDate() - (days - i - 1));
                
                // Format date as short string (e.g., "Jun 12")
                var month = date.toLocaleString('default', { month: 'short' });
                var day = date.getDate();
                
                sampleData.chartLabels.push(month + ' ' + day);
                sampleData.chartData.push(Math.floor(Math.random() * 10) + 1);
            }
            
            // Update stats
            $('#vdp-period-orders').text(sampleData.totalOrders);
            $('#vdp-period-revenue').text('$' + sampleData.totalRevenue);
            $('#vdp-period-avg').text('$' + sampleData.averageOrder);
            
            // Update chart
            initOrdersChart(sampleData.chartLabels, sampleData.chartData);
        }, 500);
    }
    
    // Initialize with 7-day period
    updateOrdersSummary(7);
});
</script>