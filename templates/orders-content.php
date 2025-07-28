<?php
/**
 * Orders Content Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$orders = $orders_data['orders'];
$summary = $orders_data['summary'];
$pagination = $orders_data['pagination'];
$statistics = $orders_data['statistics'];
?>

<div class="vdp-orders-wrapper">
    <!-- Orders Header -->
    <div class="vdp-section-header">
        <h2 class="vdp-section-title"><?php esc_html_e('Mis Órdenes', 'vendor-dashboard-pro'); ?></h2>
        <div class="vdp-section-actions">
            <button type="button" class="vdp-btn vdp-btn-secondary" id="vdp_export_orders">
                <i class="fas fa-download"></i>
                <?php esc_html_e('Exportar', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="vdp-orders-summary">
        <div class="vdp-summary-grid">
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['total']); ?></h3>
                    <p><?php esc_html_e('Total Órdenes', 'vendor-dashboard-pro'); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon processing">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['processing']); ?></h3>
                    <p><?php esc_html_e('Procesando', 'vendor-dashboard-pro'); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['pending']); ?></h3>
                    <p><?php esc_html_e('Pendientes', 'vendor-dashboard-pro'); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['completed']); ?></h3>
                    <p><?php esc_html_e('Completadas', 'vendor-dashboard-pro'); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo wc_price($summary['total_revenue']); ?></h3>
                    <p><?php esc_html_e('Ingresos Totales', 'vendor-dashboard-pro'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="vdp-orders-filters">
        <div class="vdp-filters">
            <input type="text" id="orders_search" class="vdp-filter-input" placeholder="<?php esc_attr_e('Buscar por número de orden o cliente...', 'vendor-dashboard-pro'); ?>">
            
            <select id="orders_status_filter" class="vdp-filter-select">
                <option value="any"><?php esc_html_e('Todos los estados', 'vendor-dashboard-pro'); ?></option>
                <option value="pending"><?php esc_html_e('Pendientes', 'vendor-dashboard-pro'); ?></option>
                <option value="processing"><?php esc_html_e('Procesando', 'vendor-dashboard-pro'); ?></option>
                <option value="on-hold"><?php esc_html_e('En espera', 'vendor-dashboard-pro'); ?></option>
                <option value="completed"><?php esc_html_e('Completadas', 'vendor-dashboard-pro'); ?></option>
                <option value="cancelled"><?php esc_html_e('Canceladas', 'vendor-dashboard-pro'); ?></option>
                <option value="refunded"><?php esc_html_e('Reembolsadas', 'vendor-dashboard-pro'); ?></option>
            </select>
            
            <input type="date" id="orders_date_from" class="vdp-filter-input" placeholder="<?php esc_attr_e('Fecha desde', 'vendor-dashboard-pro'); ?>">
            <input type="date" id="orders_date_to" class="vdp-filter-input" placeholder="<?php esc_attr_e('Fecha hasta', 'vendor-dashboard-pro'); ?>">
            
            <button type="button" class="vdp-btn vdp-btn-primary" id="apply_orders_filters">
                <?php esc_html_e('Filtrar', 'vendor-dashboard-pro'); ?>
            </button>
            
            <button type="button" class="vdp-btn vdp-btn-secondary" id="clear_orders_filters">
                <?php esc_html_e('Limpiar', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="vdp-orders-table-wrapper">
        <table class="vdp-orders-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Orden', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Cliente', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Fecha', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Estado', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Total', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Items', 'vendor-dashboard-pro'); ?></th>
                    <th><?php esc_html_e('Acciones', 'vendor-dashboard-pro'); ?></th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                <?php if (!empty($orders)) : ?>
                    <?php foreach ($orders as $order) : ?>
                        <tr data-order-id="<?php echo esc_attr($order['id']); ?>" class="<?php echo $order['needs_processing'] ? 'needs-attention' : ''; ?>">
                            <td>
                                <div class="order-number">
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('orders', $order['id'])); ?>" class="vdp-ajax-link" data-action="order_view" data-item="<?php echo esc_attr($order['id']); ?>">
                                        <strong>#<?php echo esc_html($order['order_number']); ?></strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="order-customer">
                                    <strong><?php echo esc_html($order['customer_name']); ?></strong>
                                    <div class="customer-email"><?php echo esc_html($order['customer_email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="order-date">
                                    <div class="date-main"><?php echo date('d/m/Y', strtotime($order['date'])); ?></div>
                                    <div class="date-time"><?php echo date('H:i', strtotime($order['date'])); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="order-status <?php echo esc_attr(VDP_Orders::get_status_class($order['status'])); ?>">
                                    <?php echo esc_html(VDP_Orders::get_status_label($order['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="order-total">
                                    <strong><?php echo wc_price($order['total'], array('currency' => $order['currency'])); ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="order-items">
                                    <?php echo sprintf(_n('%d item', '%d items', $order['items_count'], 'vendor-dashboard-pro'), $order['items_count']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('orders', $order['id'])); ?>" 
                                       class="vdp-btn vdp-btn-sm vdp-btn-primary vdp-ajax-link" 
                                       data-action="order_view" 
                                       data-item="<?php echo esc_attr($order['id']); ?>">
                                        <?php esc_html_e('Ver', 'vendor-dashboard-pro'); ?>
                                    </a>
                                    
                                    <?php if ($order['needs_processing']) : ?>
                                        <button type="button" class="vdp-btn vdp-btn-sm vdp-btn-success update-order-status" 
                                                data-order-id="<?php echo esc_attr($order['id']); ?>" 
                                                data-status="completed">
                                            <?php esc_html_e('Completar', 'vendor-dashboard-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="no-orders">
                            <?php esc_html_e('No se encontraron órdenes.', 'vendor-dashboard-pro'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1) : ?>
        <div class="vdp-pagination">
            <div class="vdp-pagination-info">
                <?php
                printf(
                    esc_html__('Mostrando %d-%d de %d órdenes', 'vendor-dashboard-pro'),
                    (($pagination['current_page'] - 1) * $pagination['per_page']) + 1,
                    min($pagination['current_page'] * $pagination['per_page'], $pagination['total_orders']),
                    $pagination['total_orders']
                );
                ?>
            </div>
            <div class="vdp-pagination-links">
                <?php
                // Previous page
                if ($pagination['current_page'] > 1) {
                    echo '<a href="#" class="vdp-pagination-link" data-page="' . ($pagination['current_page'] - 1) . '">&laquo; ' . esc_html__('Anterior', 'vendor-dashboard-pro') . '</a>';
                }
                
                // Page numbers
                for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++) {
                    $class = $i === $pagination['current_page'] ? 'vdp-pagination-link current' : 'vdp-pagination-link';
                    echo '<a href="#" class="' . $class . '" data-page="' . $i . '">' . $i . '</a>';
                }
                
                // Next page
                if ($pagination['current_page'] < $pagination['total_pages']) {
                    echo '<a href="#" class="vdp-pagination-link" data-page="' . ($pagination['current_page'] + 1) . '">' . esc_html__('Siguiente', 'vendor-dashboard-pro') . ' &raquo;</a>';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter orders
    $('#apply_orders_filters').on('click', function() {
        filterOrders();
    });
    
    $('#clear_orders_filters').on('click', function() {
        $('#orders_search').val('');
        $('#orders_status_filter').val('any');
        $('#orders_date_from').val('');
        $('#orders_date_to').val('');
        filterOrders();
    });
    
    // Search on enter
    $('#orders_search').on('keypress', function(e) {
        if (e.which === 13) {
            filterOrders();
        }
    });
    
    // Pagination
    $(document).on('click', '.vdp-pagination-link:not(.current)', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        filterOrders(page);
    });
    
    // Update order status
    $(document).on('click', '.update-order-status', function() {
        var orderId = $(this).data('order-id');
        var status = $(this).data('status');
        var $button = $(this);
        
        if (confirm('¿Está seguro de que desea cambiar el estado de esta orden?')) {
            $.ajax({
                url: vdp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_update_order_status',
                    order_id: orderId,
                    status: status,
                    nonce: vdp_ajax.nonce
                },
                beforeSend: function() {
                    $button.prop('disabled', true).text('Procesando...');
                },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload to show updated status
                    } else {
                        alert('Error: ' + (response.data ? response.data.message : 'Error desconocido'));
                    }
                },
                error: function() {
                    alert('Error al procesar la solicitud.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        }
    });
    
    function filterOrders(page = 1) {
        var filters = {
            search: $('#orders_search').val(),
            status: $('#orders_status_filter').val(),
            date_from: $('#orders_date_from').val(),
            date_to: $('#orders_date_to').val(),
            page: page
        };
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_filter_orders',
                ...filters,
                nonce: vdp_ajax.nonce
            },
            beforeSend: function() {
                $('#orders-table-body').html('<tr><td colspan="7" class="loading">Cargando...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    updateOrdersTable(response.data.orders);
                    updateOrdersSummary(response.data.summary);
                    updatePagination(response.data.pagination);
                }
            },
            error: function() {
                $('#orders-table-body').html('<tr><td colspan="7" class="error">Error al cargar las órdenes.</td></tr>');
            }
        });
    }
    
    function updateOrdersTable(orders) {
        var html = '';
        
        if (orders.length === 0) {
            html = '<tr><td colspan="7" class="no-orders">No se encontraron órdenes.</td></tr>';
        } else {
            orders.forEach(function(order) {
                var statusClass = getStatusClass(order.status);
                var statusLabel = getStatusLabel(order.status);
                var needsProcessing = order.needs_processing ? 'needs-attention' : '';
                
                html += '<tr data-order-id="' + order.id + '" class="' + needsProcessing + '">';
                html += '<td><div class="order-number"><a href="#" class="vdp-ajax-link" data-action="order_view" data-item="' + order.id + '"><strong>#' + order.order_number + '</strong></a></div></td>';
                html += '<td><div class="order-customer"><strong>' + order.customer_name + '</strong><div class="customer-email">' + order.customer_email + '</div></div></td>';
                html += '<td><div class="order-date"><div class="date-main">' + formatDate(order.date) + '</div><div class="date-time">' + formatTime(order.date) + '</div></div></td>';
                html += '<td><span class="order-status ' + statusClass + '">' + statusLabel + '</span></td>';
                html += '<td><div class="order-total"><strong>' + formatPrice(order.total, order.currency) + '</strong></div></td>';
                html += '<td><div class="order-items">' + order.items_count + ' item' + (order.items_count !== 1 ? 's' : '') + '</div></td>';
                html += '<td><div class="order-actions">';
                html += '<a href="#" class="vdp-btn vdp-btn-sm vdp-btn-primary vdp-ajax-link" data-action="order_view" data-item="' + order.id + '">Ver</a>';
                
                if (order.needs_processing) {
                    html += '<button type="button" class="vdp-btn vdp-btn-sm vdp-btn-success update-order-status" data-order-id="' + order.id + '" data-status="completed">Completar</button>';
                }
                
                html += '</div></td>';
                html += '</tr>';
            });
        }
        
        $('#orders-table-body').html(html);
    }
    
    function updateOrdersSummary(summary) {
        $('.vdp-summary-grid .vdp-summary-card').each(function(index) {
            var $card = $(this);
            var value;
            
            switch(index) {
                case 0:
                    value = summary.total.toLocaleString();
                    break;
                case 1:
                    value = summary.processing.toLocaleString();
                    break;
                case 2:
                    value = summary.pending.toLocaleString();
                    break;
                case 3:
                    value = summary.completed.toLocaleString();
                    break;
                case 4:
                    value = formatPrice(summary.total_revenue);
                    break;
            }
            
            $card.find('h3').text(value);
        });
    }
    
    function updatePagination(pagination) {
        // Update pagination would be implemented here
        // For now, we'll just reload the page for pagination
    }
    
    function getStatusClass(status) {
        var classes = {
            'pending': 'vdp-status-pending',
            'processing': 'vdp-status-processing',
            'on-hold': 'vdp-status-on-hold',
            'completed': 'vdp-status-completed',
            'cancelled': 'vdp-status-cancelled',
            'refunded': 'vdp-status-refunded',
            'failed': 'vdp-status-failed'
        };
        
        return classes[status] || 'vdp-status-default';
    }
    
    function getStatusLabel(status) {
        var labels = {
            'pending': 'Pendiente',
            'processing': 'Procesando',
            'on-hold': 'En espera',
            'completed': 'Completada',
            'cancelled': 'Cancelada',
            'refunded': 'Reembolsada',
            'failed': 'Fallida'
        };
        
        return labels[status] || status;
    }
    
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }
    
    function formatTime(dateString) {
        var date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
    }
    
    function formatPrice(amount, currency = 'EUR') {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
});
</script>

<style>
.vdp-orders-wrapper {
    margin: 20px 0;
}

.vdp-orders-summary {
    margin-bottom: 30px;
}

.vdp-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.vdp-summary-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: box-shadow 0.3s ease;
}

.vdp-summary-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.vdp-summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
    background: #666;
}

.vdp-summary-icon.processing {
    background: #f39c12;
}

.vdp-summary-icon.pending {
    background: #e74c3c;
}

.vdp-summary-icon.completed {
    background: #27ae60;
}

.vdp-summary-icon.revenue {
    background: #3498db;
}

.vdp-summary-content h3 {
    margin: 0 0 5px 0;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.vdp-summary-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.vdp-orders-filters {
    margin-bottom: 20px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.vdp-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.vdp-orders-table-wrapper {
    overflow-x: auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.vdp-orders-table {
    width: 100%;
    border-collapse: collapse;
}

.vdp-orders-table th,
.vdp-orders-table td {
    padding: 15px 12px;
    text-align: left;
    border-bottom: 1px solid #e1e1e1;
}

.vdp-orders-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vdp-orders-table tbody tr:hover {
    background: #f8f9fa;
}

.vdp-orders-table tbody tr.needs-attention {
    border-left: 4px solid #f39c12;
}

.order-number a {
    color: #007cba;
    text-decoration: none;
    font-weight: 600;
}

.order-number a:hover {
    text-decoration: underline;
}

.order-customer .customer-email {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.order-date .date-time {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.order-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vdp-status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.vdp-status-processing {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.vdp-status-on-hold {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.vdp-status-completed {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.vdp-status-cancelled {
    background: #f1f3f4;
    color: #5f6368;
    border: 1px solid #dadce0;
}

.vdp-status-refunded {
    background: #e2e3ff;
    color: #3f4771;
    border: 1px solid #c7c9ff;
}

.vdp-status-failed {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.order-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.vdp-btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
}

.no-orders,
.loading,
.error {
    text-align: center;
    padding: 40px 20px;
    color: #666;
    font-style: italic;
}

.loading {
    color: #007cba;
}

.error {
    color: #e74c3c;
}

.vdp-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.vdp-pagination-info {
    color: #666;
    font-size: 14px;
}

.vdp-pagination-links {
    display: flex;
    gap: 5px;
}

.vdp-pagination-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.vdp-pagination-link:hover {
    background: #f8f9fa;
    text-decoration: none;
}

.vdp-pagination-link.current {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
}

@media (max-width: 768px) {
    .vdp-summary-grid {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 15px;
    }
    
    .vdp-summary-card {
        padding: 15px;
    }
    
    .vdp-summary-content h3 {
        font-size: 20px;
    }
    
    .vdp-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .vdp-filters > * {
        width: 100%;
    }
    
    .vdp-orders-table th,
    .vdp-orders-table td {
        padding: 10px 8px;
        font-size: 14px;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .vdp-pagination {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .vdp-summary-grid {
        grid-template-columns: 1fr;
    }
    
    .vdp-orders-table-wrapper {
        font-size: 12px;
    }
    
    .vdp-orders-table th,
    .vdp-orders-table td {
        padding: 8px 6px;
    }
}
</style>