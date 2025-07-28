<?php
/**
 * Analytics Content Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$summary = $analytics_data['summary'];
$sales_chart = $analytics_data['sales_chart'];
$top_products = $analytics_data['top_products'];
$performance_metrics = $analytics_data['performance_metrics'];
?>

<div class="vdp-analytics-wrapper">
    <!-- Analytics Header -->
    <div class="vdp-section-header">
        <h2 class="vdp-section-title"><?php esc_html_e('Analytics', 'vendor-dashboard-pro'); ?></h2>
        <div class="vdp-section-actions">
            <select id="analytics_period" class="vdp-filter-select">
                <option value="7days"><?php esc_html_e('Últimos 7 días', 'vendor-dashboard-pro'); ?></option>
                <option value="30days" selected><?php esc_html_e('Últimos 30 días', 'vendor-dashboard-pro'); ?></option>
                <option value="3months"><?php esc_html_e('Últimos 3 meses', 'vendor-dashboard-pro'); ?></option>
                <option value="6months"><?php esc_html_e('Últimos 6 meses', 'vendor-dashboard-pro'); ?></option>
                <option value="12months"><?php esc_html_e('Últimos 12 meses', 'vendor-dashboard-pro'); ?></option>
            </select>
            
            <button type="button" class="vdp-btn vdp-btn-secondary" id="export_analytics">
                <i class="fas fa-download"></i>
                <?php esc_html_e('Exportar Reporte', 'vendor-dashboard-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="vdp-analytics-summary">
        <div class="vdp-summary-grid">
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon sales">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['sales_count']); ?></h3>
                    <p><?php esc_html_e('Ventas Totales', 'vendor-dashboard-pro'); ?></p>
                    <div class="summary-trend <?php echo $summary['sales_increase'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $summary['sales_increase'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($summary['sales_increase']); ?>%
                    </div>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo wc_price($summary['sales_amount']); ?></h3>
                    <p><?php esc_html_e('Ingresos', 'vendor-dashboard-pro'); ?></p>
                    <div class="summary-trend <?php echo $summary['amount_increase'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $summary['amount_increase'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($summary['amount_increase']); ?>%
                    </div>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon views">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['views_count']); ?></h3>
                    <p><?php esc_html_e('Vistas de Productos', 'vendor-dashboard-pro'); ?></p>
                    <div class="summary-metric">
                        <?php esc_html_e('Total de vistas', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon conversion">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['conversion_rate'], 2); ?>%</h3>
                    <p><?php esc_html_e('Tasa de Conversión', 'vendor-dashboard-pro'); ?></p>
                    <div class="summary-metric">
                        <?php echo wc_price($summary['average_order_value']); ?> <?php esc_html_e('valor promedio', 'vendor-dashboard-pro'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="vdp-analytics-charts">
        <div class="vdp-chart-grid">
            <!-- Sales Chart -->
            <div class="vdp-chart-card">
                <div class="vdp-chart-header">
                    <h3><?php esc_html_e('Tendencia de Ventas', 'vendor-dashboard-pro'); ?></h3>
                    <div class="vdp-chart-controls">
                        <button type="button" class="vdp-chart-toggle active" data-type="sales">
                            <?php esc_html_e('Ingresos', 'vendor-dashboard-pro'); ?>
                        </button>
                        <button type="button" class="vdp-chart-toggle" data-type="orders">
                            <?php esc_html_e('Órdenes', 'vendor-dashboard-pro'); ?>
                        </button>
                    </div>
                </div>
                <div class="vdp-chart-container">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="vdp-metrics-card">
                <div class="vdp-metrics-header">
                    <h3><?php esc_html_e('Métricas de Rendimiento', 'vendor-dashboard-pro'); ?></h3>
                </div>
                <div class="vdp-metrics-grid">
                    <div class="vdp-metric-item">
                        <div class="metric-value"><?php echo number_format($performance_metrics['completion_rate']); ?>%</div>
                        <div class="metric-label"><?php esc_html_e('Tasa de Completación', 'vendor-dashboard-pro'); ?></div>
                    </div>
                    <div class="vdp-metric-item">
                        <div class="metric-value"><?php echo number_format($performance_metrics['return_rate']); ?>%</div>
                        <div class="metric-label"><?php esc_html_e('Tasa de Devolución', 'vendor-dashboard-pro'); ?></div>
                    </div>
                    <div class="vdp-metric-item">
                        <div class="metric-value"><?php echo $performance_metrics['response_time']; ?>h</div>
                        <div class="metric-label"><?php esc_html_e('Tiempo de Respuesta', 'vendor-dashboard-pro'); ?></div>
                    </div>
                    <div class="vdp-metric-item">
                        <div class="metric-value"><?php echo $performance_metrics['response_rate']; ?>%</div>
                        <div class="metric-label"><?php esc_html_e('Tasa de Respuesta', 'vendor-dashboard-pro'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="vdp-analytics-table">
        <div class="vdp-table-card">
            <div class="vdp-table-header">
                <h3><?php esc_html_e('Productos Más Vendidos', 'vendor-dashboard-pro'); ?></h3>
            </div>
            <div class="vdp-table-wrapper">
                <table class="vdp-products-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Producto', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Ventas', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Cantidad', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Ingresos', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Vistas', 'vendor-dashboard-pro'); ?></th>
                            <th><?php esc_html_e('Conversión', 'vendor-dashboard-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_products)) : ?>
                            <?php foreach ($top_products as $product) : ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <strong><?php echo esc_html($product['title']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="product-sales"><?php echo number_format($product['sales_count']); ?></span>
                                    </td>
                                    <td>
                                        <span class="product-quantity"><?php echo number_format($product['quantity_sold'] ?? $product['sales_count']); ?></span>
                                    </td>
                                    <td>
                                        <span class="product-revenue"><?php echo wc_price($product['sales_amount']); ?></span>
                                    </td>
                                    <td>
                                        <span class="product-views"><?php echo number_format($product['views']); ?></span>
                                    </td>
                                    <td>
                                        <span class="product-conversion"><?php echo number_format($product['conversion_rate'], 1); ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="no-data">
                                    <?php esc_html_e('No hay datos de productos disponibles.', 'vendor-dashboard-pro'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Chart.js configuration
    var salesChartData = <?php echo json_encode($sales_chart); ?>;
    var chartType = 'sales';
    var chart;
    
    function initSalesChart() {
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        // Prepare data
        var labels = salesChartData.map(function(item) {
            return new Date(item.date).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
        });
        
        var salesData = salesChartData.map(function(item) {
            return chartType === 'sales' ? item.sales : item.orders;
        });
        
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: chartType === 'sales' ? 'Ingresos' : 'Órdenes',
                    data: salesData,
                    borderColor: '#007cba',
                    backgroundColor: 'rgba(0, 124, 186, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#007cba',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (chartType === 'sales') {
                                    return new Intl.NumberFormat('es-ES', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    }).format(value);
                                }
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderWidth: 0,
                        callbacks: {
                            label: function(context) {
                                if (chartType === 'sales') {
                                    return 'Ingresos: ' + new Intl.NumberFormat('es-ES', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    }).format(context.parsed.y);
                                }
                                return 'Órdenes: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Initialize chart
    if (salesChartData.length > 0) {
        initSalesChart();
    }
    
    // Chart toggle
    $('.vdp-chart-toggle').on('click', function() {
        var newType = $(this).data('type');
        if (newType !== chartType) {
            chartType = newType;
            $('.vdp-chart-toggle').removeClass('active');
            $(this).addClass('active');
            
            if (chart) {
                chart.destroy();
            }
            initSalesChart();
        }
    });
    
    // Period change
    $('#analytics_period').on('change', function() {
        var period = $(this).val();
        // This would trigger an AJAX call to reload data
        console.log('Period changed to:', period);
    });
    
    // Export functionality
    $('#export_analytics').on('click', function() {
        // Implement export functionality
        alert('Funcionalidad de exportación próximamente...');
    });
});
</script>

<style>
.vdp-analytics-wrapper {
    margin: 20px 0;
}

.vdp-analytics-summary {
    margin-bottom: 30px;
}

.vdp-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.vdp-summary-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.vdp-summary-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.vdp-summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007cba, #0096d6);
}

.vdp-summary-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    flex-shrink: 0;
}

.vdp-summary-icon.sales {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.vdp-summary-icon.revenue {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.vdp-summary-icon.views {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.vdp-summary-icon.conversion {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.vdp-summary-content {
    flex: 1;
    min-width: 0;
}

.vdp-summary-content h3 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
}

.vdp-summary-content p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

.summary-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
}

.summary-trend.positive {
    color: #27ae60;
}

.summary-trend.negative {
    color: #e74c3c;
}

.summary-metric {
    font-size: 12px;
    color: #888;
}

.vdp-analytics-charts {
    margin-bottom: 30px;
}

.vdp-chart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.vdp-chart-card,
.vdp-metrics-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 12px;
    overflow: hidden;
}

.vdp-chart-header,
.vdp-metrics-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e1e1e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vdp-chart-header h3,
.vdp-metrics-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.vdp-chart-controls {
    display: flex;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 4px;
}

.vdp-chart-toggle {
    padding: 8px 16px;
    border: none;
    background: transparent;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
}

.vdp-chart-toggle.active {
    background: #007cba;
    color: #fff;
}

.vdp-chart-container {
    padding: 24px;
    height: 300px;
    position: relative;
}

.vdp-metrics-grid {
    padding: 24px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.vdp-metric-item {
    text-align: center;
}

.metric-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 4px;
}

.metric-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vdp-analytics-table {
    margin-bottom: 30px;
}

.vdp-table-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 12px;
    overflow: hidden;
}

.vdp-table-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e1e1e1;
}

.vdp-table-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.vdp-table-wrapper {
    overflow-x: auto;
}

.vdp-products-table {
    width: 100%;
    border-collapse: collapse;
}

.vdp-products-table th,
.vdp-products-table td {
    padding: 16px 24px;
    text-align: left;
    border-bottom: 1px solid #f1f3f4;
}

.vdp-products-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vdp-products-table tbody tr:hover {
    background: #f8f9fa;
}

.product-info strong {
    color: #333;
    font-weight: 600;
}

.product-sales,
.product-quantity,
.product-views {
    font-weight: 600;
    color: #333;
}

.product-revenue {
    font-weight: 600;
    color: #27ae60;
}

.product-conversion {
    font-weight: 600;
    color: #007cba;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px 24px;
}

@media (max-width: 1024px) {
    .vdp-chart-grid {
        grid-template-columns: 1fr;
    }
    
    .vdp-metrics-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .vdp-summary-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    
    .vdp-summary-card {
        padding: 20px;
    }
    
    .vdp-summary-content h3 {
        font-size: 24px;
    }
    
    .vdp-chart-header,
    .vdp-metrics-header,
    .vdp-table-header {
        padding: 16px 20px;
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .vdp-chart-container {
        padding: 20px;
        height: 250px;
    }
    
    .vdp-metrics-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .vdp-products-table th,
    .vdp-products-table td {
        padding: 12px 16px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .vdp-summary-grid {
        grid-template-columns: 1fr;
    }
    
    .vdp-section-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .vdp-section-actions {
        width: 100%;
        display: flex;
        gap: 8px;
    }
    
    #analytics_period {
        flex: 1;
    }
}
</style>