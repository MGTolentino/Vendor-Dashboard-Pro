/**
 * VDP Pipeline Simple JavaScript - Adaptado de Leads Management Plugin
 * Integrado con el sistema VDP existente
 *
 * @package Vendor Dashboard Pro
 */

(function($) {
    'use strict';
    
    // Crear namespace VDPPipeline
    window.VDPPipeline = {
        // Variables globales
        currentFilters: {},
        allLeads: [],
        draggedElement: null,
        sourceColumn: null,
        initialized: false,
        
        /**
         * Inicializar pipeline
         */
        init: function() {
            if (this.initialized) return;
            
            // Verificar que estamos en la página correcta
            if (!$('.vdp-pipeline-container').length) {
                return;
            }
            
            // Asegurar que los contenedores de fecha estén ocultos al inicio
            $('#vdp_custom_date_range').hide().css('display', 'none');
            $('#vdp_specific_month_range').hide().css('display', 'none');
            
            this.initializeEvents();
            this.initializeDragAndDrop();
            this.loadPipelineData();
            this.initialized = true;
        },
        
        /**
         * Inicializar eventos
         */
        initializeEvents: function() {
            var self = this;
            
            // Botón agregar lead
            $(document).on('click', '#vdp_add_lead_btn', this.openAddLeadModal.bind(this));
            
            // Filtros
            $(document).on('click', '#vdp_apply_filters', this.applyFilters.bind(this));
            $(document).on('click', '#vdp_clear_filters', this.clearFilters.bind(this));
            $(document).on('input', '#vdp_quick_search', this.debounce(this.quickSearch.bind(this), 300));
            
            // Period filter change
            $(document).on('change', '#vdp_period_filter', function() {
                var selectedValue = $(this).val();
                
                // Ocultar todos los rangos primero
                $('#vdp_custom_date_range').hide().css('display', 'none');
                $('#vdp_specific_month_range').hide().css('display', 'none');
                
                // Mostrar el rango correspondiente
                if (selectedValue === 'custom') {
                    $('#vdp_custom_date_range').show().css('display', 'block');
                } else if (selectedValue === 'specific_month') {
                    $('#vdp_specific_month_range').show().css('display', 'block');
                } else {
                    // Para cualquier otro valor (incluido vacío), limpiar valores
                    $('#vdp_date_range').val('');
                    $('#vdp_mes_evento_basic').val('');
                    $('#vdp_anio_evento').val('');
                    if (selectedValue) {
                        self.applyFilters();
                    }
                }
            });
            
            // Trigger initial change to set correct visibility
            $('#vdp_period_filter').trigger('change');
            
            // Eventos para filtros de mes/año específico
            $(document).on('change', '#vdp_mes_evento_basic, #vdp_anio_evento', function() {
                self.applyFilters();
            });
            
            // Otros filtros
            $(document).on('change', '#vdp_event_type_filter, #vdp_priority_filter', function() {
                self.applyFilters();
            });
            
            // Checkbox leads sin evento
            $(document).on('change', '#vdp_show_leads_without_event', function() {
                self.applyFilters();
            });
            
            // Inicializar DateRangePicker después de un breve delay
            setTimeout(function() {
                self.initializeDateRangePicker();
            }, 100);
            
            // Modal events - NEW VERSION
            $(document).on('click', '.vdp-close-modal', this.closeModal.bind(this));
            $(document).on('click', '#vdp_add_lead_modal_unique', function(e) {
                if (e.target === this) {
                    console.log('VDPPipeline: Clicked outside modal backdrop');
                    VDPPipeline.closeModal();
                }
            });
            
            // Prevent modal content clicks from closing modal
            $(document).on('click', '.vdp-modal-content', function(e) {
                e.stopPropagation();
            });
            
            // Formulario agregar lead
            $(document).on('submit', '#vdp_lead_form', this.handleAddLead.bind(this));
            
            // Formulario actualizar status
            $(document).on('submit', '#vdp_status_form', this.handleUpdateStatus.bind(this));
            
            // Acciones de tarjetas de leads
            $(document).on('click', '.vdp-lead-card .vdp-update-status', this.openStatusModal.bind(this));
            $(document).on('click', '.vdp-lead-card .vdp-view-details', this.viewLeadDetails.bind(this));
        },
        
        /**
         * Inicializar drag and drop
         */
        initializeDragAndDrop: function() {
            // Hacer tarjetas draggables
            $(document).on('mousedown', '.vdp-lead-card', this.onDragStart.bind(this));
            $(document).on('mousemove', this.onDragMove.bind(this));
            $(document).on('mouseup', this.onDragEnd.bind(this));
            
            // Drop zones
            $('.vdp-column-content').on('dragover', this.onDragOver.bind(this));
            $('.vdp-column-content').on('drop', this.onDrop.bind(this));
        },
        
        /**
         * Cargar datos del pipeline
         */
        loadPipelineData: function() {
            const self = this;
            
            $.ajax({
                url: vdpLeads.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_get_pipeline_leads',
                    nonce: vdpLeads.nonce,
                    filters: this.currentFilters
                },
                beforeSend: function() {
                    $('.vdp-pipeline-container').addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        self.allLeads = response.data.leads || [];
                        self.renderPipeline();
                        self.updateStats();
                    } else {
                        self.showNotice(response.data.message || 'Error loading pipeline data', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error loading pipeline data', 'error');
                },
                complete: function() {
                    $('.vdp-pipeline-container').removeClass('loading');
                }
            });
        },
        
        /**
         * Renderizar pipeline
         */
        renderPipeline: function() {
            // Limpiar columnas
            $('.vdp-column-content').empty();
            
            // Agrupar leads por status
            const leadsByStatus = this.groupLeadsByStatus();
            
            // Renderizar cada columna
            Object.keys(leadsByStatus).forEach(status => {
                const leads = leadsByStatus[status];
                const $column = $('#vdp-' + status.replace(/-/g, '-') + '-cards');
                
                if (leads.length === 0) {
                    $column.html('<div class=\"vdp-empty-column\"><i class=\"fas fa-inbox\"></i>No leads in this stage</div>');
                } else {
                    leads.forEach(lead => {
                        $column.append(this.createLeadCard(lead));
                    });
                }
                
                // Actualizar contador
                $column.closest('.vdp-pipeline-column').find('.vdp-count').text(leads.length);
            });
        },
        
        /**
         * Agrupar leads por status
         */
        groupLeadsByStatus: function() {
            const groups = {
                'nuevo': [],
                'con-presupuesto': [],
                'por-cerrar': [],
                'con-contrato': [],
                'perdido': []
            };
            
            this.allLeads.forEach(lead => {
                const status = lead.evento_status || 'nuevo';
                if (groups[status]) {
                    groups[status].push(lead);
                }
            });
            
            return groups;
        },
        
        /**
         * Crear tarjeta de lead
         */
        createLeadCard: function(lead) {
            return $(`
                <div class=\"vdp-lead-card\" data-lead-id=\"${lead.lead_id}\" data-evento-id=\"${lead.evento_id || ''}\" data-status=\"${lead.evento_status}\" draggable=\"${lead.evento_id ? 'true' : 'false'}\">
                    <div class=\"vdp-lead-name\">${this.escapeHtml(lead.nombre_completo)}</div>
                    ${lead.tipo_evento ? `<div class=\"vdp-lead-info\">Evento: ${this.escapeHtml(lead.tipo_evento)}</div>` : ''}
                    ${lead.fecha_evento ? `<div class=\"vdp-lead-info\">Fecha: ${this.escapeHtml(lead.fecha_evento)}</div>` : ''}
                    ${lead.servicio_titulo ? `<div class=\"vdp-lead-info service-info\">Servicio: ${this.escapeHtml(lead.servicio_titulo)}</div>` : ''}
                    <div class=\"vdp-lead-actions\">
                        <a href=\"/lead-details/lead-${lead.lead_id}\" target=\"_blank\" onclick=\"event.stopPropagation();\">Ver lead</a>
                        ${lead.evento_id ? `<a href=\"/event-details/event-${lead.evento_id}\" target=\"_blank\" onclick=\"event.stopPropagation();\">Ver evento</a>` : ''}
                    </div>
                </div>
            `);
        },
        
        /**
         * Abrir modal agregar lead
         */
        openAddLeadModal: function() {
            const modal = $('#vdp_add_lead_modal_unique');
            
            if (modal.length) {
                // Force CSS styles
                modal.css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0', 
                    'right': '0',
                    'bottom': '0',
                    'width': '100vw',
                    'height': '100vh',
                    'background': 'rgba(0,0,0,0.6)',
                    'z-index': '999999',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'margin': '0',
                    'padding': '0',
                    'border': 'none',
                    'outline': 'none',
                    'opacity': '1',
                    'visibility': 'visible'
                });
                
                modal.show().addClass('vdp-active');
                
                // Force display attribute
                modal.attr('style', modal.attr('style') + '; display: flex !important;');
                
                // Force modal content styles
                modal.find('.vdp-modal-content').css({
                    'background': '#ffffff',
                    'width': '90%',
                    'max-width': '600px',
                    'min-width': '320px',
                    'max-height': '90vh',
                    'overflow-y': 'auto',
                    'border-radius': '8px',
                    'box-shadow': '0 20px 40px rgba(0,0,0,0.4)',
                    'position': 'relative',
                    'margin': '20px auto',
                    'padding': '0',
                    'border': 'none',
                    'outline': 'none',
                    'opacity': '1',
                    'z-index': '999999',
                    'display': 'block',
                    'visibility': 'visible'
                });
                
                // Reset form
                const form = modal.find('#vdp_lead_form');
                if (form.length) {
                    form[0].reset();
                }
            }
        },
        
        /**
         * Abrir modal actualizar status
         */
        openStatusModal: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const leadId = $(e.currentTarget).data('lead-id');
            const currentStatus = $(e.currentTarget).closest('.vdp-lead-card').data('status');
            
            $('#vdp_status_lead_id').val(leadId);
            $('#vdp_new_status').val(currentStatus);
            $('#vdp_status_modal').addClass('vdp-active');
        },
        
        /**
         * Cerrar modales
         */
        closeModal: function() {
            const modal = $('#vdp_add_lead_modal_unique');
            if (modal.length) {
                modal.css('display', 'none').removeClass('vdp-active');
            }
        },
        
        /**
         * Manejar agregar lead
         */
        handleAddLead: function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'vdp_add_pipeline_lead');
            formData.append('nonce', vdpLeads.nonce);
            
            const self = this;
            
            $.ajax({
                url: vdpLeads.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Lead added successfully', 'success');
                        self.closeModal();
                        self.loadPipelineData();
                    } else {
                        self.showNotice(response.data.message || 'Error adding lead', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error adding lead', 'error');
                }
            });
        },
        
        /**
         * Manejar actualizar status
         */
        handleUpdateStatus: function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'vdp_update_pipeline_status');
            formData.append('nonce', vdpLeads.nonce);
            
            const self = this;
            
            $.ajax({
                url: vdpLeads.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Status updated successfully', 'success');
                        self.closeModal();
                        self.loadPipelineData();
                    } else {
                        self.showNotice(response.data.message || 'Error updating status', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error updating status', 'error');
                }
            });
        },
        
        /**
         * Manejar drag start
         */
        onDragStart: function(e) {
            if (!$(e.target).closest('.vdp-lead-card').length) return;
            
            this.draggedElement = $(e.target).closest('.vdp-lead-card');
            this.sourceColumn = this.draggedElement.closest('.vdp-column-content');
            
            this.draggedElement.addClass('dragging');
            
            // Prevenir selección de texto
            e.preventDefault();
        },
        
        /**
         * Manejar drag move
         */
        onDragMove: function(e) {
            if (!this.draggedElement) return;
            
            // Lógica de movimiento visual si es necesaria
        },
        
        /**
         * Manejar drag end
         */
        onDragEnd: function(e) {
            if (!this.draggedElement) return;
            
            this.draggedElement.removeClass('dragging');
            $('.vdp-column-content').removeClass('drag-over');
            
            this.draggedElement = null;
            this.sourceColumn = null;
        },
        
        /**
         * Manejar drag over
         */
        onDragOver: function(e) {
            e.preventDefault();
            $(e.currentTarget).addClass('drag-over');
        },
        
        /**
         * Manejar drop
         */
        onDrop: function(e) {
            e.preventDefault();
            
            if (!this.draggedElement) return;
            
            const $targetColumn = $(e.currentTarget);
            const newStatus = $targetColumn.closest('.vdp-pipeline-column').data('status');
            const leadId = this.draggedElement.data('lead-id');
            
            this.updateLeadStatus(leadId, newStatus);
        },
        
        /**
         * Actualizar status de lead por drag & drop
         */
        updateLeadStatus: function(leadId, newStatus) {
            const self = this;
            
            $.ajax({
                url: vdpLeads.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_update_pipeline_status',
                    nonce: vdpLeads.nonce,
                    lead_id: leadId,
                    new_status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Status updated successfully', 'success');
                        self.loadPipelineData();
                    } else {
                        self.showNotice(response.data.message || 'Error updating status', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error updating status', 'error');
                }
            });
        },
        
        /**
         * Aplicar filtros (versión actualizada)
         */
        applyFilters: function() {
            var filters = {
                search: $('#vdp_quick_search').val(),
                period: $('#vdp_period_filter').val(),
                month: $('#vdp_mes_evento_basic').val(),
                year: $('#vdp_anio_evento').val(),
                dateRange: $('#vdp_date_range').val(),
                eventType: $('#vdp_event_type_filter').val(),
                priority: $('#vdp_priority_filter').val(),
                showWithoutEvent: $('#vdp_show_leads_without_event').is(':checked')
            };
            this.currentFilters = filters;
            this.loadPipelineData();
        },
        
        /**
         * Limpiar filtros (versión actualizada)
         */
        clearFilters: function() {
            $('#vdp_quick_search').val('');
            $('#vdp_period_filter').val('');
            $('#vdp_event_type_filter').val('');
            $('#vdp_priority_filter').val('');
            $('#vdp_date_range').val('');
            $('#vdp_mes_evento_basic').val('');
            $('#vdp_anio_evento').val('');
            $('#vdp_show_leads_without_event').prop('checked', false);
            
            // Ocultar rangos de fecha con CSS inline forzado
            $('#vdp_custom_date_range').hide().css('display', 'none');
            $('#vdp_specific_month_range').hide().css('display', 'none');
            
            this.currentFilters = {};
            this.loadPipelineData();
        },
        
        /**
         * Búsqueda rápida
         */
        quickSearch: function() {
            this.applyFilters();
        },
        
        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            var timeout;
            return function executedFunction() {
                var context = this;
                var args = arguments;
                var later = function() {
                    timeout = null;
                    func.apply(context, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        /**
         * Ver detalles del lead
         */
        viewLeadDetails: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const leadId = $(e.currentTarget).data('lead-id');
            
            // Redirigir a la vista detallada de leads
            const url = vdpLeads.dashboard_url + '?vdp-action=leads&vdp-item=' + leadId;
            window.location.href = url;
        },
        
        /**
         * Actualizar estadísticas
         */
        updateStats: function() {
            $('#vdp_total_leads').text(this.allLeads.length);
        },
        
        /**
         * Mostrar notificación
         */
        showNotice: function(message, type) {
            // Usar el sistema de notificaciones de VDP si está disponible
            if (window.VDP && typeof window.VDP.showNotice === 'function') {
                window.VDP.showNotice(message, type);
            } else {
                // Fallback simple
                alert(message);
            }
        },
        
        /**
         * Inicializar DateRangePicker
         */
        initializeDateRangePicker: function() {
            if (typeof daterangepicker === 'undefined' || typeof moment === 'undefined') {
                return;
            }
            
            var self = this;
            
            // Configurar DateRangePicker
            $('#vdp_date_range').daterangepicker({
                autoUpdateInput: false,
                autoApply: true,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1,
                    cancelLabel: 'Limpiar',
                    applyLabel: 'Aplicar'
                },
                singleDatePicker: false,
                showDropdowns: true,
                minYear: 2000,
                maxYear: parseInt(moment().format('YYYY'), 10) + 5
            });
            
            // Event handlers para DateRangePicker
            $('#vdp_date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                self.applyFilters();
            });
            
            $('#vdp_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                self.applyFilters();
            });
            
        },
        
        /**
         * Extraer nombre del servicio de URL
         */
        extractServiceName: function(url) {
            try {
                const urlObj = new URL(url);
                const pathParts = urlObj.pathname.split('/').filter(part => part);
                return pathParts[pathParts.length - 1] || 'Service';
            } catch (e) {
                return 'Service';
            }
        },
        
        /**
         * Escapar HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
    
    // Integrar con el sistema VDP existente
    if (window.VDP) {
        // Sobrescribir initLeads para usar pipeline
        window.VDP.initLeads = function() {
            // Check if we're on leads page
            if (!$('.vdp-leads-content').length && !$('.vdp-pipeline-container').length) {
                return;
            }
            
            // Initialize pipeline if container exists
            if ($('.vdp-pipeline-container').length) {
                window.VDPPipeline.init();
            }
            
            // Initialize table functionality if container exists
            if ($('.vdp-leads-content').length && window.VDPLeads && typeof window.VDPLeads.init === 'function') {
                window.VDPLeads.init();
            }
        };
    }
    
    // Auto-inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Solo inicializar si estamos en una página con pipeline
        if ($('.vdp-pipeline-container').length) {
            window.VDPPipeline.init();
        }
    });
    
})(jQuery);