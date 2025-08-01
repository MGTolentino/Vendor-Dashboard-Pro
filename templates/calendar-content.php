<?php
/**
 * Calendar Content Template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$calendar_config = VDP_Calendar::get_calendar_config();
$locale = VDP_Calendar::get_calendar_locale();
$is_spanish = strpos($locale, 'es') === 0;
?>

<div class="vdp-calendar-wrapper">
    <!-- Calendar Header -->
    <div class="vdp-section-header">
        <h2 class="vdp-section-title">
            <?php echo $is_spanish ? 'Calendario' : 'Calendar'; ?>
        </h2>
        <div class="vdp-section-actions">
            <select id="calendar_listing_filter" class="vdp-filter-select">
                <option value="">
                    <?php echo $is_spanish ? 'Todos los listings' : 'All listings'; ?>
                </option>
                <?php foreach ($listings as $listing) : ?>
                    <option value="<?php echo esc_attr($listing['id']); ?>">
                        <?php echo esc_html($listing['title']); ?>
                        <?php if ($listing['status'] !== 'publish') : ?>
                            <span class="listing-status">(<?php echo esc_html($listing['status']); ?>)</span>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="button" class="vdp-btn vdp-btn-secondary" id="refresh_calendar">
                <i class="fas fa-sync-alt"></i>
                <?php echo $is_spanish ? 'Actualizar' : 'Refresh'; ?>
            </button>
        </div>
    </div>

    <!-- Calendar Tools -->
    <div class="vdp-calendar-tools">
        <div class="vdp-calendar-actions">
            <button type="button" class="vdp-btn vdp-btn-danger" id="block_dates_btn" disabled>
                <i class="fas fa-lock"></i>
                <?php echo $is_spanish ? 'Bloquear Fechas' : 'Block Dates'; ?>
            </button>
            
            <button type="button" class="vdp-btn vdp-btn-success" id="unblock_dates_btn" disabled>
                <i class="fas fa-unlock"></i>
                <?php echo $is_spanish ? 'Desbloquear Fechas' : 'Unblock Dates'; ?>
            </button>
            
            <button type="button" class="vdp-btn vdp-btn-warning" id="set_pricing_btn" disabled>
                <i class="fas fa-dollar-sign"></i>
                <?php echo $is_spanish ? 'Configurar Precios' : 'Set Pricing'; ?>
            </button>
        </div>
        
        <div class="vdp-calendar-legend">
            <div class="legend-item">
                <span class="legend-color confirmed"></span>
                <?php echo $is_spanish ? 'Confirmadas' : 'Confirmed'; ?>
            </div>
            <div class="legend-item">
                <span class="legend-color pending"></span>
                <?php echo $is_spanish ? 'Pendientes' : 'Pending'; ?>
            </div>
            <div class="legend-item">
                <span class="legend-color blocked"></span>
                <?php echo $is_spanish ? 'Bloqueadas' : 'Blocked'; ?>
            </div>
            <div class="legend-item">
                <span class="legend-color special-price"></span>
                <?php echo $is_spanish ? 'Precio Especial' : 'Special Price'; ?>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="vdp-calendar-container">
        <div id="vendor-calendar" class="vdp-fullcalendar"></div>
    </div>

    <!-- Selected Dates Info -->
    <div id="selected-dates-info" class="vdp-selected-dates" style="display: none;">
        <div class="selected-dates-header">
            <h4><?php echo $is_spanish ? 'Fechas Seleccionadas' : 'Selected Dates'; ?></h4>
            <button type="button" class="close-selected" id="clear_selection">×</button>
        </div>
        <div class="selected-dates-content">
            <p id="selected-range-text"></p>
            <p id="selected-listing-text"></p>
        </div>
    </div>
</div>

<!-- Price Range Modal -->
<div id="price-range-modal" class="vdp-modal" style="display: none;">
    <div class="vdp-modal-content">
        <div class="vdp-modal-header">
            <h3><?php echo $is_spanish ? 'Configurar Precio' : 'Set Price'; ?></h3>
            <button type="button" class="vdp-modal-close">&times;</button>
        </div>
        <div class="vdp-modal-body">
            <form id="price-range-form">
                <div class="form-group">
                    <label for="range_start_date">
                        <?php echo $is_spanish ? 'Fecha de Inicio' : 'Start Date'; ?>
                    </label>
                    <input type="date" id="range_start_date" name="start_date" class="vdp-input" required>
                </div>
                
                <div class="form-group">
                    <label for="range_end_date">
                        <?php echo $is_spanish ? 'Fecha de Fin' : 'End Date'; ?>
                    </label>
                    <input type="date" id="range_end_date" name="end_date" class="vdp-input" required>
                </div>
                
                <div class="form-group">
                    <label for="range_price">
                        <?php echo $is_spanish ? 'Precio por Noche' : 'Price per Night'; ?>
                    </label>
                    <input type="number" id="range_price" name="price" class="vdp-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="range_listing">
                        <?php echo $is_spanish ? 'Listing' : 'Listing'; ?>
                    </label>
                    <select id="range_listing" name="listing_id" class="vdp-input" required>
                        <option value=""><?php echo $is_spanish ? 'Seleccionar listing' : 'Select listing'; ?></option>
                        <?php foreach ($listings as $listing) : ?>
                            <option value="<?php echo esc_attr($listing['id']); ?>">
                                <?php echo esc_html($listing['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="vdp-modal-footer">
            <button type="button" class="vdp-btn vdp-btn-secondary" id="cancel_price_range">
                <?php echo $is_spanish ? 'Cancelar' : 'Cancel'; ?>
            </button>
            <button type="button" class="vdp-btn vdp-btn-primary" id="save_price_range">
                <?php echo $is_spanish ? 'Guardar' : 'Save'; ?>
            </button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let calendar;
    let selectedDates = [];
    let selectedListing = $('#calendar_listing_filter').val() || '';
    
    // Calendar configuration
    const calendarConfig = <?php echo json_encode($calendar_config); ?>;
    const calendarData = <?php echo json_encode($calendar_data); ?>;
    const isSpanish = <?php echo json_encode($is_spanish); ?>;
    
    // Debug calendar data
    console.log('Calendar Data:', calendarData);
    console.log('Calendar Events Count:', calendarData.events ? calendarData.events.length : 0);
    
    // Debug: Log calendar data to console
    console.log('Main Calendar Data:', calendarData);
    console.log('Main Calendar Data Type:', typeof calendarData);
    console.log('Main Calendar Events Property:', calendarData ? calendarData.events : 'calendarData is null');
    console.log('Main Calendar Events Count:', calendarData && calendarData.events ? calendarData.events.length : 0);
    
    // Debug structure comparison
    if (calendarData) {
        console.log('Calendar Data Keys:', Object.keys(calendarData));
        if (calendarData.events) {
            console.log('First Event Sample:', calendarData.events[0]);
        }
    }
    
    // Initialize FullCalendar
    function initCalendar() {
        const calendarEl = document.getElementById('vendor-calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: calendarConfig.locale,
            firstDay: calendarConfig.firstDay,
            headerToolbar: calendarConfig.headerToolbar,
            buttonText: calendarConfig.buttonText,
            initialView: 'dayGridMonth',
            height: 'auto',
            selectable: true,
            selectMirror: true,
            weekNumbers: true,
            dayMaxEvents: true,
            events: (calendarData && calendarData.events) ? calendarData.events : [],
            
            // Date selection
            select: function(info) {
                handleDateSelection(info);
            },
            
            // Event click
            eventClick: function(info) {
                handleEventClick(info);
            },
            
            // Event display
            eventDisplay: 'block',
            eventTextColor: '#fff',
            
            // Custom event rendering
            eventDidMount: function(info) {
                // Add tooltip
                info.el.title = info.event.title + '\n' + 
                    (info.event.extendedProps.customer || '') + '\n' +
                    (info.event.extendedProps.amount ? wc_price(info.event.extendedProps.amount) : '');
            }
        });
        
        calendar.render();
    }
    
    // Handle date selection
    function handleDateSelection(info) {
        if (!selectedListing) {
            alert(isSpanish ? 'Por favor selecciona un listing primero' : 'Please select a listing first');
            calendar.unselect();
            return;
        }
        
        selectedDates = {
            start: info.startStr,
            end: info.endStr,
            listing_id: selectedListing
        };
        
        updateSelectedDatesInfo();
        enableActionButtons();
    }
    
    // Handle event click
    function handleEventClick(info) {
        const event = info.event;
        const details = `${event.title}\n` +
            `${isSpanish ? 'Estado' : 'Status'}: ${event.extendedProps.status}\n` +
            `${isSpanish ? 'Cliente' : 'Customer'}: ${event.extendedProps.customer}\n` +
            `${isSpanish ? 'Fecha' : 'Date'}: ${event.startStr} - ${event.endStr}`;
        
        if (confirm(details + '\n\n' + (isSpanish ? '¿Ver detalles?' : 'View details?'))) {
            // Would open booking details modal or redirect
        }
    }
    
    // Update selected dates info
    function updateSelectedDatesInfo() {
        if (selectedDates.start && selectedDates.end) {
            const listingTitle = getListingTitle(selectedDates.listing_id);
            $('#selected-range-text').text(
                `${isSpanish ? 'Desde' : 'From'}: ${selectedDates.start} ${isSpanish ? 'hasta' : 'to'} ${selectedDates.end}`
            );
            $('#selected-listing-text').text(
                `${isSpanish ? 'Listing' : 'Listing'}: ${listingTitle}`
            );
            $('#selected-dates-info').show();
        } else {
            $('#selected-dates-info').hide();
        }
    }
    
    // Enable action buttons
    function enableActionButtons() {
        $('#block_dates_btn, #unblock_dates_btn, #set_pricing_btn').prop('disabled', false);
    }
    
    // Disable action buttons
    function disableActionButtons() {
        $('#block_dates_btn, #unblock_dates_btn, #set_pricing_btn').prop('disabled', true);
    }
    
    // Get listing title by ID
    function getListingTitle(listingId) {
        const listing = <?php echo json_encode($listings); ?>.find(l => l.id == listingId);
        return listing ? listing.title : '';
    }
    
    // Clear selection
    function clearSelection() {
        selectedDates = [];
        // Don't clear selectedListing - keep the selected listing from dropdown
        calendar.unselect();
        $('#selected-dates-info').hide();
        disableActionButtons();
    }
    
    // Load calendar data
    function loadCalendarData(listingId = '') {
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_get_calendar_data',
                listing_id: listingId,
                start: calendar.view.activeStart.toISOString().split('T')[0],
                end: calendar.view.activeEnd.toISOString().split('T')[0],
                nonce: vdp_ajax.calendar_nonce
            },
            success: function(response) {
                if (response.success) {
                    calendar.removeAllEvents();
                    calendar.addEventSource(response.data.events);
                }
            }
        });
    }
    
    // Block dates
    function blockDates() {
        if (!selectedDates.start) {
            alert(isSpanish ? 'Selecciona fechas primero' : 'Select dates first');
            return;
        }
        if (!selectedDates.listing_id || selectedDates.listing_id === '') {
            alert(isSpanish ? 'Selecciona un listing específico primero' : 'Select a specific listing first');
            return;
        }
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_block_dates',
                listing_id: selectedDates.listing_id,
                start_date: selectedDates.start,
                end_date: selectedDates.end,
                nonce: vdp_ajax.calendar_nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadCalendarData(selectedListing);
                    clearSelection();
                } else {
                    alert(response.data.message);
                }
            }
        });
    }
    
    // Unblock dates
    function unblockDates() {
        if (!selectedDates.start) {
            alert(isSpanish ? 'Selecciona fechas primero' : 'Select dates first');
            return;
        }
        if (!selectedDates.listing_id || selectedDates.listing_id === '') {
            alert(isSpanish ? 'Selecciona un listing específico primero' : 'Select a specific listing first');
            return;
        }
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_unblock_dates',
                listing_id: selectedDates.listing_id,
                start_date: selectedDates.start,
                end_date: selectedDates.end,
                nonce: vdp_ajax.calendar_nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadCalendarData(selectedListing);
                    clearSelection();
                } else {
                    alert(response.data.message);
                }
            }
        });
    }
    
    // Show price range modal
    function showPriceModal() {
        if (!selectedDates.start) {
            alert(isSpanish ? 'Selecciona fechas primero' : 'Select dates first');
            return;
        }
        if (!selectedDates.listing_id || selectedDates.listing_id === '') {
            alert(isSpanish ? 'Selecciona un listing específico primero' : 'Select a specific listing first');
            return;
        }
        
        if (selectedDates.start && selectedDates.end) {
            $('#range_start_date').val(selectedDates.start);
            $('#range_end_date').val(selectedDates.end);
            $('#range_listing').val(selectedDates.listing_id);
        }
        $('#price-range-modal').show();
    }
    
    // Save price range
    function savePriceRange() {
        const formData = {
            action: 'vdp_update_price_range',
            listing_id: $('#range_listing').val(),
            start_date: $('#range_start_date').val(),
            end_date: $('#range_end_date').val(),
            price: $('#range_price').val(),
            nonce: vdp_ajax.nonce
        };
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $('#price-range-modal').hide();
                    loadCalendarData(selectedListing);
                    clearSelection();
                } else {
                    alert(response.data.message);
                }
            }
        });
    }
    
    // Event handlers
    $('#calendar_listing_filter').on('change', function() {
        selectedListing = $(this).val();
        loadCalendarData(selectedListing);
        clearSelection();
    });
    
    $('#refresh_calendar').on('click', function() {
        loadCalendarData(selectedListing);
    });
    
    $('#clear_selection').on('click', clearSelection);
    $('#block_dates_btn').on('click', blockDates);
    $('#unblock_dates_btn').on('click', unblockDates);
    $('#set_pricing_btn').on('click', showPriceModal);
    
    // Modal handlers
    $('.vdp-modal-close, #cancel_price_range').on('click', function() {
        $('#price-range-modal').hide();
    });
    
    $('#save_price_range').on('click', savePriceRange);
    
    // Initialize calendar
    initCalendar();
});
</script>

<style>
.vdp-calendar-wrapper {
    margin: 15px 0;
}

.vdp-calendar-tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e1e1e1;
}

.vdp-calendar-actions {
    display: flex;
    gap: 8px;
}

.vdp-calendar-actions .vdp-btn {
    padding: 6px 12px !important;
    font-size: 12px !important;
}

.vdp-calendar-legend {
    display: flex;
    gap: 12px;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #666;
}

.legend-color {
    width: 10px;
    height: 10px;
    border-radius: 2px;
}

.legend-color.confirmed {
    background: #28a745;
}

.legend-color.pending {
    background: #ffc107;
}

.legend-color.blocked {
    background: #dc3545;
}

.legend-color.special-price {
    background: #6f42c1;
}

.vdp-calendar-container {
    background: #fff;
    border-radius: 6px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.vdp-fullcalendar {
    max-width: 100%;
    font-size: 13px;
}

.vdp-selected-dates {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 6px;
    padding: 12px;
    margin-top: 15px;
}

.selected-dates-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.selected-dates-header h4 {
    margin: 0;
    color: #1976d2;
    font-size: 16px;
}

.close-selected {
    background: none;
    border: none;
    font-size: 20px;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.selected-dates-content p {
    margin: 5px 0;
    color: #333;
    font-size: 14px;
}

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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.vdp-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.vdp-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e1e1e1;
}

/* FullCalendar custom styling - Smaller */
.fc-event {
    border-radius: 3px !important;
    border: none !important;
    font-size: 10px !important;
    padding: 1px 3px !important;
}

.fc-daygrid-event {
    margin-bottom: 1px !important;
}

.fc-button {
    border-radius: 3px !important;
    padding: 4px 8px !important;
    font-size: 11px !important;
}

.fc-button-primary {
    background: #007cba !important;
    border-color: #007cba !important;
}

.fc-button-primary:hover {
    background: #005a87 !important;
    border-color: #005a87 !important;
}

.fc-today-button:disabled {
    opacity: 0.6;
}

.fc-col-header-cell {
    font-size: 11px !important;
}

.fc-daygrid-day-number {
    font-size: 12px !important;
}

@media (max-width: 768px) {
    .vdp-calendar-tools {
        flex-direction: column;
        gap: 15px;
    }
    
    .vdp-calendar-actions {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .vdp-calendar-legend {
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }
    
    .vdp-modal-content {
        width: 95%;
        margin: 10px;
    }
    
    .vdp-calendar-container {
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .vdp-calendar-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .vdp-calendar-actions button {
        width: 100%;
    }
    
    .fc-header-toolbar {
        flex-direction: column !important;
        gap: 10px !important;
    }
    
    .fc-toolbar-chunk {
        display: flex !important;
        justify-content: center !important;
    }
}
</style>