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
                
                // Ocultar todos los rangos
                $('#vdp_custom_date_range, #vdp_specific_month_range').hide();
                
                // Mostrar el rango correspondiente
                if (selectedValue === 'custom') {
                    $('#vdp_custom_date_range').show();
                } else if (selectedValue === 'specific_month') {
                    $('#vdp_specific_month_range').show();
                } else if (selectedValue) {
                    // Limpiar valores y aplicar filtros automáticamente
                    $('#vdp_date_range').val('');
                    $('#vdp_mes_evento_basic').val('');
                    $('#vdp_anio_evento').val('');
                    self.applyFilters();
                }
            });
            
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
            
            // Modal events
            $(document).on('click', '.vdp-close-modal', this.closeModal.bind(this));
            $(document).on('click', '.vdp-modal', function(e) {
                if (e.target === this) {
                    VDPPipeline.closeModal();
                }
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
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_get_pipeline_leads',
                    nonce: vdp_vars.nonce,
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
                const $column = $('#vdp-' + status + '-cards');
                
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
                'contactado': [],
                'cita-agendada': [],
                'propuesta-enviada': [],
                'negociacion': [],
                'cerrado-ganado': [],
                'cerrado-perdido': []
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
            $('#vdp_lead_modal').addClass('vdp-active');
            $('#vdp_lead_form')[0].reset();
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
            $('.vdp-modal').removeClass('vdp-active');
        },
        
        /**
         * Manejar agregar lead
         */
        handleAddLead: function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('action', 'vdp_add_pipeline_lead');
            formData.append('nonce', vdp_vars.nonce);
            
            const self = this;
            
            $.ajax({
                url: vdp_vars.ajax_url,
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
            formData.append('nonce', vdp_vars.nonce);
            
            const self = this;
            
            $.ajax({
                url: vdp_vars.ajax_url,
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
                url: vdp_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_update_pipeline_status',
                    nonce: vdp_vars.nonce,
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
         * Aplicar filtros
         */
        applyFilters: function() {
            this.currentFilters = {
                search: $('#vdp_quick_search').val(),
                period: $('#vdp_period_filter').val(),
                service: $('#vdp_service_filter').val(),
                priority: $('#vdp_priority_filter').val(),
                date_range: $('#vdp_date_range').val()
            };
            
            this.loadPipelineData();
        },
        
        /**
         * Limpiar filtros
         */
        clearFilters: function() {
            $('#vdp_quick_search').val('');
            $('#vdp_period_filter').val('');
            $('#vdp_service_filter').val('');
            $('#vdp_priority_filter').val('');
            $('#vdp_date_range').val('');
            $('#vdp_custom_date_range').hide();
            
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
         * Cambio en filtro de período
         */
        onPeriodFilterChange: function() {
            const period = $('#vdp_period_filter').val();
            
            if (period === 'custom') {
                $('#vdp_custom_date_range').show();
            } else {
                $('#vdp_custom_date_range').hide();
            }
        },
        
        /**
         * Ver detalles del lead
         */
        viewLeadDetails: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const leadId = $(e.currentTarget).data('lead-id');
            
            // Redirigir a la vista detallada de leads
            const url = vdp_vars.dashboard_url + '?vdp-action=leads&vdp-item=' + leadId;
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