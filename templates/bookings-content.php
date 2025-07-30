<?php
/**
 * Bookings Content Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$summary = $bookings_data['summary'];
$bookings = $bookings_data['bookings'];
$statistics = $bookings_data['statistics'];
$calendar_data = $bookings_data['calendar_data'];

// Get calendar configuration and language detection
$calendar_config = VDP_Calendar::get_calendar_config();
$locale = VDP_Calendar::get_calendar_locale();
$is_spanish = strpos($locale, 'es') === 0;

// Text labels based on language
$labels = $is_spanish ? array(
    'title' => 'Reservaciones',
    'export' => 'Exportar',
    'total_bookings' => 'Total Reservaciones',
    'confirmed' => 'Confirmadas',
    'pending' => 'Pendientes',
    'total_revenue' => 'Ingresos Totales',
    'list_view' => 'Lista',
    'calendar_view' => 'Calendario',
    'all_statuses' => 'Todos los estados',
    'cancelled' => 'Canceladas',
    'all_listings' => 'Todos los listings',
    'date_from' => 'Fecha desde',
    'date_to' => 'Fecha hasta',
    'filter' => 'Filtrar',
    'clear' => 'Limpiar',
    'listing' => 'Listing',
    'customer' => 'Cliente',
    'dates' => 'Fechas',
    'status' => 'Estado',
    'price' => 'Precio',
    'actions' => 'Acciones',
    'confirm' => 'Confirmar',
    'cancel' => 'Cancelar',
    'view_order' => 'Ver Orden',
    'no_bookings' => 'No se encontraron reservaciones.',
    'booking_details' => 'Detalles de la Reservación'
) : array(
    'title' => 'Bookings',
    'export' => 'Export',
    'total_bookings' => 'Total Bookings',
    'confirmed' => 'Confirmed',
    'pending' => 'Pending',
    'total_revenue' => 'Total Revenue',
    'list_view' => 'List',
    'calendar_view' => 'Calendar',
    'all_statuses' => 'All statuses',
    'cancelled' => 'Cancelled',
    'all_listings' => 'All listings',
    'date_from' => 'Date from',
    'date_to' => 'Date to',
    'filter' => 'Filter',
    'clear' => 'Clear',
    'listing' => 'Listing',
    'customer' => 'Customer',
    'dates' => 'Dates',
    'status' => 'Status',
    'price' => 'Price',
    'actions' => 'Actions',
    'confirm' => 'Confirm',
    'cancel' => 'Cancel',
    'view_order' => 'View Order',
    'no_bookings' => 'No bookings found.',
    'booking_details' => 'Booking Details'
);
?>

<div class="vdp-bookings-wrapper">
    <!-- Bookings Header -->
    <div class="vdp-section-header">
        <h2 class="vdp-section-title"><?php echo esc_html($labels['title']); ?></h2>
        <div class="vdp-section-actions">
            <button type="button" class="vdp-btn vdp-btn-secondary" id="vdp_export_bookings">
                <i class="fas fa-download"></i>
                <?php echo esc_html($labels['export']); ?>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="vdp-bookings-summary">
        <div class="vdp-summary-grid">
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['total_bookings']); ?></h3>
                    <p><?php echo esc_html($labels['total_bookings']); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['confirmed_bookings']); ?></h3>
                    <p><?php echo esc_html($labels['confirmed']); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo number_format($summary['pending_bookings']); ?></h3>
                    <p><?php echo esc_html($labels['pending']); ?></p>
                </div>
            </div>
            
            <div class="vdp-summary-card">
                <div class="vdp-summary-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="vdp-summary-content">
                    <h3><?php echo wc_price($summary['total_revenue']); ?></h3>
                    <p><?php echo esc_html($labels['total_revenue']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="vdp-view-toggle">
        <div class="vdp-toggle-buttons">
            <button type="button" class="vdp-toggle-btn active" data-view="list">
                <i class="fas fa-list"></i>
                <?php echo esc_html($labels['list_view']); ?>
            </button>
            <button type="button" class="vdp-toggle-btn" data-view="calendar">
                <i class="fas fa-calendar"></i>
                <?php echo esc_html($labels['calendar_view']); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="vdp-bookings-filters">
        <div class="vdp-filters">
            <select id="booking_status_filter" class="vdp-filter-select">
                <option value="all"><?php echo esc_html($labels['all_statuses']); ?></option>
                <option value="confirmed"><?php echo esc_html($labels['confirmed']); ?></option>
                <option value="pending"><?php echo esc_html($labels['pending']); ?></option>
                <option value="cancelled"><?php echo esc_html($labels['cancelled']); ?></option>
            </select>
            
            <select id="booking_listing_filter" class="vdp-filter-select">
                <option value=""><?php echo esc_html($labels['all_listings']); ?></option>
                <?php
                $vendor_listings = VDP_Bookings::get_vendor_listings($vendor->get_id());
                foreach ($vendor_listings as $listing_id) {
                    $listing = get_post($listing_id);
                    if ($listing) {
                        echo '<option value="' . esc_attr($listing_id) . '">' . esc_html($listing->post_title) . '</option>';
                    }
                }
                ?>
            </select>
            
            <input type="date" id="booking_date_from" class="vdp-filter-input" placeholder="<?php echo esc_attr($labels['date_from']); ?>">
            <input type="date" id="booking_date_to" class="vdp-filter-input" placeholder="<?php echo esc_attr($labels['date_to']); ?>">
            
            <button type="button" class="vdp-btn vdp-btn-primary" id="apply_booking_filters">
                <?php echo esc_html($labels['filter']); ?>
            </button>
            
            <button type="button" class="vdp-btn vdp-btn-secondary" id="clear_booking_filters">
                <?php echo esc_html($labels['clear']); ?>
            </button>
        </div>
    </div>

    <!-- List View -->
    <div id="bookings-list-view" class="vdp-bookings-view">
        <div class="vdp-bookings-table-wrapper">
            <table class="vdp-bookings-table">
                <thead>
                    <tr>
                        <th><?php echo esc_html($labels['listing']); ?></th>
                        <th><?php echo esc_html($labels['customer']); ?></th>
                        <th><?php echo esc_html($labels['dates']); ?></th>
                        <th><?php echo esc_html($labels['status']); ?></th>
                        <th><?php echo esc_html($labels['price']); ?></th>
                        <th><?php echo esc_html($labels['actions']); ?></th>
                    </tr>
                </thead>
                <tbody id="bookings-table-body">
                    <?php if (!empty($bookings)) : ?>
                        <?php foreach ($bookings as $booking) : ?>
                            <tr data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                                <td>
                                    <div class="booking-listing">
                                        <strong><?php echo esc_html($booking['listing_title']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="booking-customer">
                                        <strong><?php echo esc_html($booking['customer_name']); ?></strong>
                                        <div class="customer-email"><?php echo esc_html($booking['customer_email']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="booking-dates">
                                        <div class="date-from"><?php echo date('d/m/Y H:i', $booking['start_time']); ?></div>
                                        <div class="date-to"><?php echo date('d/m/Y H:i', $booking['end_time']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="booking-status booking-status-<?php echo esc_attr($booking['status']); ?>">
                                        <?php
                                        switch ($booking['status']) {
                                            case 'publish':
                                                esc_html_e('Confirmada', 'vendor-dashboard-pro');
                                                break;
                                            case 'pending':
                                                esc_html_e('Pendiente', 'vendor-dashboard-pro');
                                                break;
                                            case 'draft':
                                                esc_html_e('Sin pagar', 'vendor-dashboard-pro');
                                                break;
                                            case 'trash':
                                                esc_html_e('Cancelada', 'vendor-dashboard-pro');
                                                break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="booking-price">
                                        <?php echo $booking['price'] ? wc_price($booking['price']) : '-'; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="booking-actions">
                                        <?php if ($booking['status'] === 'pending') : ?>
                                            <button type="button" class="vdp-btn vdp-btn-sm vdp-btn-success update-booking-status" 
                                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>" 
                                                    data-status="confirmed">
                                                <?php echo esc_html($labels['confirm']); ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($booking['status'], ['pending', 'publish'])) : ?>
                                            <button type="button" class="vdp-btn vdp-btn-sm vdp-btn-danger update-booking-status" 
                                                    data-booking-id="<?php echo esc_attr($booking['id']); ?>" 
                                                    data-status="cancelled">
                                                <?php echo esc_html($labels['cancel']); ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($booking['order_id'])) : ?>
                                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $booking['order_id'] . '&action=edit')); ?>" 
                                               class="vdp-btn vdp-btn-sm vdp-btn-secondary" target="_blank">
                                                <?php echo esc_html($labels['view_order']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="no-bookings">
                                <?php echo esc_html($labels['no_bookings']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Calendar View -->
    <div id="bookings-calendar-view" class="vdp-bookings-view" style="display: none;">
        <div class="vdp-calendar-integrated">
            <div id="bookings-calendar" class="vdp-fullcalendar"></div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="booking-details-modal" class="vdp-modal" style="display: none;">
    <div class="vdp-modal-content">
        <div class="vdp-modal-header">
            <h3><?php echo esc_html($labels['booking_details']); ?></h3>
            <button type="button" class="vdp-modal-close">&times;</button>
        </div>
        <div class="vdp-modal-body">
            <div id="booking-details-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let bookingsCalendar;
    const calendarConfig = <?php echo json_encode($calendar_config); ?>;
    const calendarData = <?php echo json_encode($calendar_data); ?>;
    const isSpanish = <?php echo json_encode($is_spanish); ?>;
    const labels = <?php echo json_encode($labels); ?>;
    
    // Initialize Bookings Calendar
    function initBookingsCalendar() {
        if (bookingsCalendar) {
            return; // Already initialized
        }
        
        const calendarEl = document.getElementById('bookings-calendar');
        
        bookingsCalendar = new FullCalendar.Calendar(calendarEl, {
            locale: calendarConfig.locale,
            firstDay: calendarConfig.firstDay,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: calendarConfig.buttonText,
            initialView: 'dayGridMonth',
            height: 'auto',
            weekNumbers: false,
            dayMaxEvents: 3,
            events: calendarData.events || [],
            
            // Event click handler
            eventClick: function(info) {
                showBookingDetails(info.event);
            },
            
            // Event display
            eventDisplay: 'block',
            eventTextColor: '#fff',
            
            // Custom event rendering
            eventDidMount: function(info) {
                const event = info.event;
                const extendedProps = event.extendedProps;
                
                // Add tooltip with booking details
                info.el.title = `${event.title}\n${labels.customer}: ${extendedProps.customer}\n${labels.status}: ${getStatusText(extendedProps.status)}`;
                
                // Add custom classes based on status
                info.el.classList.add('booking-status-' + extendedProps.status);
            },
            
            // Date click for new bookings (optional)
            dateClick: function(info) {
                console.log('Date clicked:', info.dateStr);
                // Could open a "new booking" modal here
            }
        });
        
        bookingsCalendar.render();
    }
    
    // Show booking details modal
    function showBookingDetails(event) {
        const extendedProps = event.extendedProps;
        const content = `
            <div class="booking-detail-item">
                <strong>${labels.listing}:</strong> ${extendedProps.listing_title}
            </div>
            <div class="booking-detail-item">
                <strong>${labels.customer}:</strong> ${extendedProps.customer}
            </div>
            <div class="booking-detail-item">
                <strong>${labels.dates}:</strong> ${event.startStr} - ${event.endStr}
            </div>
            <div class="booking-detail-item">
                <strong>${labels.status}:</strong> 
                <span class="booking-status booking-status-${extendedProps.status}">
                    ${getStatusText(extendedProps.status)}
                </span>
            </div>
            ${extendedProps.amount ? `
            <div class="booking-detail-item">
                <strong>${labels.price}:</strong> ${formatPrice(extendedProps.amount)}
            </div>` : ''}
        `;
        
        $('#booking-details-content').html(content);
        $('#booking-details-modal').show();
    }
    
    // View toggle
    $('.vdp-toggle-btn').on('click', function() {
        var view = $(this).data('view');
        
        $('.vdp-toggle-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.vdp-bookings-view').hide();
        $('#bookings-' + view + '-view').show();
        
        if (view === 'calendar') {
            initBookingsCalendar();
        }
    });
    
    // Filter bookings
    $('#apply_booking_filters').on('click', function() {
        filterBookings();
    });
    
    $('#clear_booking_filters').on('click', function() {
        $('#booking_status_filter').val('all');
        $('#booking_listing_filter').val('');
        $('#booking_date_from').val('');
        $('#booking_date_to').val('');
        filterBookings();
    });
    
    // Update booking status
    $(document).on('click', '.update-booking-status', function() {
        var bookingId = $(this).data('booking-id');
        var status = $(this).data('status');
        var $button = $(this);
        
        if (confirm('¿Está seguro de que desea cambiar el estado de esta reservación?')) {
            $.ajax({
                url: vdp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_update_booking_status',
                    booking_id: bookingId,
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
                        alert('Error: ' + response.data.message);
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
    
    function filterBookings() {
        var filters = {
            status: $('#booking_status_filter').val(),
            listing_id: $('#booking_listing_filter').val(),
            date_from: $('#booking_date_from').val(),
            date_to: $('#booking_date_to').val()
        };
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_filter_bookings',
                ...filters,
                nonce: vdp_ajax.nonce
            },
            beforeSend: function() {
                $('#bookings-table-body').html('<tr><td colspan="6">Cargando...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    updateBookingsTable(response.data.bookings);
                    updateBookingsSummary(response.data.summary);
                }
            },
            error: function() {
                $('#bookings-table-body').html('<tr><td colspan="6">Error al cargar las reservaciones.</td></tr>');
            }
        });
    }
    
    function updateBookingsTable(bookings) {
        var html = '';
        
        if (bookings.length === 0) {
            html = '<tr><td colspan="6" class="no-bookings">No se encontraron reservaciones.</td></tr>';
        } else {
            bookings.forEach(function(booking) {
                var statusText = getStatusText(booking.status);
                var statusClass = booking.status;
                
                html += '<tr data-booking-id="' + booking.id + '">';
                html += '<td><div class="booking-listing"><strong>' + booking.listing_title + '</strong></div></td>';
                html += '<td><div class="booking-customer"><strong>' + booking.customer_name + '</strong><div class="customer-email">' + booking.customer_email + '</div></div></td>';
                html += '<td><div class="booking-dates"><div class="date-from">' + formatDate(booking.start_time) + '</div><div class="date-to">' + formatDate(booking.end_time) + '</div></div></td>';
                html += '<td><span class="booking-status booking-status-' + statusClass + '">' + statusText + '</span></td>';
                html += '<td><div class="booking-price">' + (booking.price ? formatPrice(booking.price) : '-') + '</div></td>';
                html += '<td><div class="booking-actions">';
                
                if (booking.status === 'pending') {
                    html += '<button type="button" class="vdp-btn vdp-btn-sm vdp-btn-success update-booking-status" data-booking-id="' + booking.id + '" data-status="confirmed">Confirmar</button>';
                }
                
                if (booking.status === 'pending' || booking.status === 'publish') {
                    html += '<button type="button" class="vdp-btn vdp-btn-sm vdp-btn-danger update-booking-status" data-booking-id="' + booking.id + '" data-status="cancelled">Cancelar</button>';
                }
                
                if (booking.order_id) {
                    html += '<a href="/wp-admin/post.php?post=' + booking.order_id + '&action=edit" class="vdp-btn vdp-btn-sm vdp-btn-secondary" target="_blank">Ver Orden</a>';
                }
                
                html += '</div></td>';
                html += '</tr>';
            });
        }
        
        $('#bookings-table-body').html(html);
    }
    
    function updateBookingsSummary(summary) {
        $('.vdp-summary-grid .vdp-summary-card').each(function(index) {
            var $card = $(this);
            var value;
            
            switch(index) {
                case 0:
                    value = summary.total_bookings.toLocaleString();
                    break;
                case 1:
                    value = summary.confirmed_bookings.toLocaleString();
                    break;
                case 2:
                    value = summary.pending_bookings.toLocaleString();
                    break;
                case 3:
                    value = formatPrice(summary.total_revenue);
                    break;
            }
            
            $card.find('h3').text(value);
        });
    }
    
    function getStatusText(status) {
        var statusMap = {
            'publish': 'Confirmada',
            'pending': 'Pendiente',
            'draft': 'Sin pagar',
            'trash': 'Cancelada'
        };
        
        return statusMap[status] || status;
    }
    
    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);
        return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
    }
    
    function formatPrice(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR' // Adjust currency as needed
        }).format(amount);
    }
});
</script>

<style>
.vdp-bookings-wrapper {
    margin: 0;
    padding: 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.vdp-section-header {
    background: #fff;
    padding: 24px 32px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.vdp-section-title {
    font-size: 24px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.vdp-section-actions {
    display: flex;
    gap: 12px;
}

.vdp-bookings-summary {
    padding: 0 32px;
    margin-bottom: 32px;
}

.vdp-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.vdp-summary-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.vdp-summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: currentColor;
}

.vdp-summary-card:nth-child(1)::before {
    background: #6366f1;
}

.vdp-summary-card:nth-child(2)::before {
    background: #10b981;
}

.vdp-summary-card:nth-child(3)::before {
    background: #f59e0b;
}

.vdp-summary-card:nth-child(4)::before {
    background: #3b82f6;
}

.vdp-summary-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.vdp-summary-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
    background: #f1f5f9;
}

.vdp-summary-card:nth-child(1) .vdp-summary-icon {
    background: #e0e7ff;
    color: #6366f1;
}

.vdp-summary-card:nth-child(2) .vdp-summary-icon {
    background: #d1fae5;
    color: #10b981;
}

.vdp-summary-card:nth-child(3) .vdp-summary-icon {
    background: #fed7aa;
    color: #f59e0b;
}

.vdp-summary-card:nth-child(4) .vdp-summary-icon {
    background: #dbeafe;
    color: #3b82f6;
}

.vdp-summary-content h3 {
    margin: 0 0 8px 0;
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
}

.vdp-summary-content p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vdp-view-toggle {
    padding: 0 32px;
    margin-bottom: 24px;
    display: flex;
    gap: 8px;
    background: #fff;
    padding: 12px 32px;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
}

.vdp-toggle-buttons {
    display: inline-flex;
    background: #e9ecef;
    padding: 4px;
    border-radius: 8px;
    gap: 4px;
}

.vdp-toggle-btn {
    padding: 8px 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s ease;
}

.vdp-toggle-btn:hover {
    color: #374151;
}

.vdp-toggle-btn.active {
    background: #fff;
    color: #1f2937;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.vdp-bookings-filters {
    background: #fff;
    padding: 24px 32px;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 32px;
}

.vdp-filters {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.vdp-filters select,
.vdp-filters input[type="date"] {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    color: #374151;
    background: #fff;
    transition: border-color 0.15s ease;
}

.vdp-filters select:focus,
.vdp-filters input[type="date"]:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.vdp-filters .vdp-btn {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.vdp-filters .vdp-btn-primary {
    background: #6366f1;
    color: #fff;
    border: 1px solid #6366f1;
}

.vdp-filters .vdp-btn-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
}

.vdp-filters .vdp-btn-secondary {
    background: #fff;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.vdp-filters .vdp-btn-secondary:hover {
    background: #f9fafb;
    color: #374151;
}

.vdp-bookings-table-wrapper {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin: 0 32px;
}

.vdp-bookings-table {
    width: 100%;
    border-collapse: collapse;
}

.vdp-bookings-table th,
.vdp-bookings-table td {
    padding: 16px 24px;
    text-align: left;
}

.vdp-bookings-table th {
    background: #f9fafb;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}

.vdp-bookings-table td {
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #374151;
}

.vdp-bookings-table tbody tr:hover {
    background: #f9fafb;
}

.vdp-bookings-table tbody tr:last-child td {
    border-bottom: none;
}

.booking-customer .customer-email {
    font-size: 12px;
    color: #666;
}

.booking-dates .date-to {
    font-size: 12px;
    color: #666;
}

.booking-listing strong {
    font-size: 14px;
    color: #1f2937;
    font-weight: 600;
}

.booking-customer strong {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.booking-customer .customer-email {
    font-size: 13px;
    color: #6b7280;
    margin-top: 2px;
}

.booking-dates {
    font-size: 13px;
    color: #4b5563;
}

.booking-dates .date-to {
    margin-top: 2px;
    color: #6b7280;
}

.booking-price {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.booking-status {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.booking-status-publish {
    background: #d1fae5;
    color: #065f46;
}

.booking-status-pending {
    background: #fef3c7;
    color: #92400e;
}

.booking-status-draft {
    background: #fee2e2;
    color: #991b1b;
}

.booking-status-trash {
    background: #f3f4f6;
    color: #4b5563;
}

.booking-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.vdp-btn {
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.vdp-btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
}

.vdp-btn-success {
    background: #10b981;
    color: #fff;
}

.vdp-btn-success:hover {
    background: #059669;
}

.vdp-btn-danger {
    background: #ef4444;
    color: #fff;
}

.vdp-btn-danger:hover {
    background: #dc2626;
}

.vdp-btn-secondary {
    background: #6b7280;
    color: #fff;
}

.vdp-btn-secondary:hover {
    background: #4b5563;
}

.vdp-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.no-bookings {
    text-align: center;
    color: #666;
    font-style: italic;
}

.vdp-calendar-integrated {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    padding: 32px;
    margin: 0 32px 32px;
}

#bookings-calendar {
    min-height: 600px;
}

/* Booking status colors in calendar */
.fc-event.booking-status-publish {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
}

.fc-event.booking-status-pending {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #000 !important;
}

.fc-event.booking-status-draft {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.fc-event.booking-status-trash {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

/* Booking details modal styling */
.booking-detail-item {
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.booking-detail-item strong {
    color: #333;
    margin-right: 8px;
}

/* Modal improvements */
.vdp-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vdp-modal-content {
    background: #fff;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.vdp-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e1e1e1;
}

.vdp-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.vdp-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vdp-modal-body {
    padding: 20px;
}

@media (max-width: 768px) {
    .vdp-summary-grid {
        grid-template-columns: 1fr;
    }
    
    .vdp-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .vdp-filters > * {
        width: 100%;
    }
    
    .booking-actions {
        flex-direction: column;
    }
}
</style>