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
</div>

<script>
jQuery(document).ready(function($) {
    // Order status filter
    $('#order-status-filter').on('change', function() {
        var status = $(this).val();
        
        if (status === '') {
            $('.vdp-order-row').show();
        } else {
            $('.vdp-order-row').hide();
            $('.vdp-order-row[data-status="' + status + '"]').show();
        }
    });
    
    // Order search
    $('#order-search').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        
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
    });
});
</script>