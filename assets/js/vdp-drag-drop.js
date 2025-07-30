(function($) {
    'use strict';
    
    // Variables globales para el drag & drop
    let isDragging = false;
    let currentDragElement = null;
    let originalColumn = null;
    let dragGhost = null;
    let currentFilters = {};
    
    // Inicialización cuando el DOM está listo
    $(document).ready(function() {
        // Inicializar drag & drop
        initDragAndDrop();
        initStatusUpdateModal();
        
        // Cargar datos del pipeline
        loadPipelineData();
        
        // Manejar filtros
        $(document).on('filtersApplied', function(e, filters) {
            currentFilters = filters;
            loadPipelineData();
        });
    });
    
    /**
     * Carga los datos de leads agrupados por status
     */
    function loadPipelineData() {
        showLoading();
        
        // Hacer la petición AJAX para cargar datos
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_get_pipeline_leads',
                nonce: vdp_ajax.nonce,
                filters: currentFilters
            },
            success: function(response) {
                if (response.success) {
                    updatePipelineView(response.data);
                } else {
                    console.error('Error loading pipeline data:', response.data);
                    showNotification('Error al cargar los datos del pipeline', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotification('Error de conexión al cargar pipeline', 'error');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    /**
     * Actualiza la vista del pipeline con nuevos datos
     */
    function updatePipelineView(data) {
        if (!data.leads_by_status) return;
        
        // Definir estados del pipeline
        const statuses = [
            'nuevo-no-contactado',
            'contactado-interesado', 
            'reunion-agendada',
            'propuesta-enviada',
            'negociacion',
            'cerrado-ganado',
            'cerrado-perdido',
            'seguimiento'
        ];
        
        // Actualizar cada columna
        statuses.forEach(function(status) {
            const $column = $(`.vdp-column[data-status="${status}"]`);
            const $leadsContainer = $column.find('.vdp-column-leads');
            const leads = data.leads_by_status[status] || [];
            
            // Actualizar contador
            $column.find('.vdp-column-count').text(leads.length);
            
            // Limpiar leads existentes
            $leadsContainer.empty();
            
            // Agregar nuevos leads
            leads.forEach(function(lead) {
                const leadCard = createLeadCard(lead);
                $leadsContainer.append(leadCard);
            });
        });
        
        // Reinicializar drag & drop después de actualizar
        initDragAndDrop();
    }
    
    /**
     * Crea una tarjeta de lead
     */
    function createLeadCard(lead) {
        const hasEvent = lead.meta && lead.meta.fecha_evento;
        const eventDate = hasEvent ? formatDate(lead.meta.fecha_evento) : '';
        const eventType = lead.meta && lead.meta.tipo_evento ? lead.meta.tipo_evento : '';
        const customerName = lead.post_title || 'Lead sin nombre';
        const priority = lead.meta && lead.meta.prioridad ? lead.meta.prioridad : '';
        
        let priorityClass = '';
        switch(priority) {
            case 'alta': priorityClass = 'priority-high'; break;
            case 'media': priorityClass = 'priority-medium'; break;
            case 'baja': priorityClass = 'priority-low'; break;
        }
        
        const card = $(`
            <div class="vdp-lead-card ${priorityClass}" data-lead-id="${lead.ID}" ${!hasEvent ? 'data-no-event="true"' : ''}>
                <div class="vdp-lead-header">
                    <h4 class="vdp-lead-name">${customerName}</h4>
                    ${priority ? `<span class="vdp-priority-badge priority-${priority}">${priority.toUpperCase()}</span>` : ''}
                </div>
                
                ${eventType ? `<div class="vdp-lead-event-type">${eventType}</div>` : ''}
                ${eventDate ? `<div class="vdp-lead-event-date">${eventDate}</div>` : ''}
                
                <div class="vdp-lead-meta">
                    ${lead.meta && lead.meta.valor_potencial ? `<span class="vdp-meta-item">Valor: $${lead.meta.valor_potencial}</span>` : ''}
                    ${lead.meta && lead.meta.probabilidad ? `<span class="vdp-meta-item">Prob: ${lead.meta.probabilidad}%</span>` : ''}
                </div>
                
                <div class="vdp-lead-actions">
                    <button type="button" class="vdp-btn vdp-btn-xs vdp-btn-primary vdp-view-lead" data-lead-id="${lead.ID}">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button type="button" class="vdp-btn vdp-btn-xs vdp-btn-secondary vdp-edit-status" data-lead-id="${lead.ID}">
                        <i class="fas fa-edit"></i> Estado
                    </button>
                </div>
                
                <div class="vdp-drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
        `);
        
        return card;
    }
    
    /**
     * Inicializa el sistema de drag & drop
     */
    function initDragAndDrop() {
        // Hacer elementos arrastrables
        $(document).off('mousedown', '.vdp-lead-card');
        $(document).on('mousedown', '.vdp-lead-card', function(e) {
            // Solo permitir drag desde el handle o el card mismo (no desde botones)
            if ($(e.target).closest('.vdp-lead-actions').length > 0) {
                return;
            }
            
            const $card = $(this);
            
            // Verificar si el lead tiene evento (solo leads con evento pueden moverse)
            if ($card.data('no-event') === true) {
                showNotification('Solo los leads con eventos programados pueden moverse entre estados', 'warning');
                return;
            }
            
            e.preventDefault();
            startDrag($card, e);
        });
        
        // Manejar movimiento del mouse
        $(document).off('mousemove.drag');
        $(document).on('mousemove.drag', function(e) {
            if (isDragging && dragGhost) {
                updateDragGhost(e);
                handleDragOver(e);
            }
        });
        
        // Manejar soltar
        $(document).off('mouseup.drag');
        $(document).on('mouseup.drag', function(e) {
            if (isDragging) {
                handleDrop(e);
            }
        });
        
        // Prevenir drag por defecto del navegador
        $(document).off('dragstart', '.vdp-lead-card');
        $(document).on('dragstart', '.vdp-lead-card', function(e) {
            e.preventDefault();
        });
    }
    
    /**
     * Inicia el proceso de drag
     */
    function startDrag($card, e) {
        isDragging = true;
        currentDragElement = $card;
        originalColumn = $card.closest('.vdp-column');
        
        // Crear elemento fantasma
        createDragGhost($card);
        
        // Agregar clases visuales
        $card.addClass('vdp-dragging');
        $('.vdp-column').addClass('vdp-drop-zone');
        
        // Ocultar el elemento original temporalmente
        $card.css('opacity', '0.3');
        
        // Actualizar posición inicial del fantasma
        updateDragGhost(e);
    }
    
    /**
     * Crea el elemento fantasma para el drag
     */
    function createDragGhost($card) {
        dragGhost = $card.clone();
        dragGhost.addClass('vdp-drag-ghost').css({
            'position': 'fixed',
            'z-index': '9999',
            'pointer-events': 'none',
            'transform': 'rotate(-5deg)',
            'box-shadow': '0 10px 25px rgba(0,0,0,0.3)',
            'opacity': '0.9'
        });
        
        $('body').append(dragGhost);
    }
    
    /**
     * Actualiza la posición del elemento fantasma
     */
    function updateDragGhost(e) {
        if (dragGhost) {
            dragGhost.css({
                'left': e.pageX - 150 + 'px',
                'top': e.pageY - 50 + 'px'
            });
        }
    }
    
    /**
     * Maneja el hover sobre las columnas durante el drag
     */
    function handleDragOver(e) {
        const $target = $(e.target).closest('.vdp-column');
        
        // Remover highlight de todas las columnas
        $('.vdp-column').removeClass('vdp-drag-over');
        
        if ($target.length > 0 && $target.hasClass('vdp-drop-zone')) {
            $target.addClass('vdp-drag-over');
        }
    }
    
    /**
     * Maneja el drop del elemento
     */
    function handleDrop(e) {
        const $targetColumn = $(e.target).closest('.vdp-column');
        
        if ($targetColumn.length > 0 && $targetColumn.hasClass('vdp-drop-zone')) {
            const newStatus = $targetColumn.data('status');
            const originalStatus = originalColumn.data('status');
            
            if (newStatus !== originalStatus) {
                // Mover el lead a la nueva columna
                moveLeadToColumn(currentDragElement, $targetColumn, newStatus);
            } else {
                // Restaurar posición original
                restoreOriginalPosition();
            }
        } else {
            // Restaurar posición original
            restoreOriginalPosition();
        }
        
        // Limpiar drag
        cleanupDrag();
    }
    
    /**
     * Mueve un lead a una nueva columna
     */
    function moveLeadToColumn($card, $targetColumn, newStatus) {
        const leadId = $card.data('lead-id');
        
        // Mostrar confirmación
        if (confirm(`¿Confirmas mover este lead a "${getStatusLabel(newStatus)}"?`)) {
            // Mostrar loading en la tarjeta
            $card.addClass('vdp-updating');
            
            // Hacer petición AJAX para actualizar el status
            $.ajax({
                url: vdp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'vdp_update_lead_status',
                    nonce: vdp_ajax.nonce,
                    lead_id: leadId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Mover visualmente el elemento
                        $card.removeClass('vdp-updating');
                        $targetColumn.find('.vdp-column-leads').append($card);
                        
                        // Actualizar contadores
                        updateColumnCounts();
                        
                        // Mostrar notificación de éxito
                        showNotification('Lead movido exitosamente', 'success');
                    } else {
                        // Error: restaurar posición
                        $card.removeClass('vdp-updating');
                        restoreOriginalPosition();
                        showNotification('Error al mover el lead: ' + response.data, 'error');
                    }
                },
                error: function() {
                    // Error: restaurar posición
                    $card.removeClass('vdp-updating');
                    restoreOriginalPosition();
                    showNotification('Error de conexión al mover el lead', 'error');
                }
            });
        } else {
            // Usuario canceló: restaurar posición
            restoreOriginalPosition();
        }
    }
    
    /**
     * Restaura la posición original del elemento
     */
    function restoreOriginalPosition() {
        if (currentDragElement && originalColumn) {
            originalColumn.find('.vdp-column-leads').append(currentDragElement);
        }
    }
    
    /**
     * Limpia el estado del drag
     */
    function cleanupDrag() {
        isDragging = false;
        
        if (currentDragElement) {
            currentDragElement.removeClass('vdp-dragging').css('opacity', '');
            currentDragElement = null;
        }
        
        if (dragGhost) {
            dragGhost.remove();
            dragGhost = null;
        }
        
        $('.vdp-column').removeClass('vdp-drop-zone vdp-drag-over');
        originalColumn = null;
    }
    
    /**
     * Actualiza los contadores de las columnas
     */
    function updateColumnCounts() {
        $('.vdp-column').each(function() {
            const $column = $(this);
            const count = $column.find('.vdp-lead-card').length;
            $column.find('.vdp-column-count').text(count);
        });
    }
    
    /**
     * Obtiene la etiqueta legible de un status
     */
    function getStatusLabel(status) {
        const labels = {
            'nuevo-no-contactado': 'Nuevo (No Contactado)',
            'contactado-interesado': 'Contactado e Interesado',
            'reunion-agendada': 'Reunión Agendada',
            'propuesta-enviada': 'Propuesta Enviada',
            'negociacion': 'En Negociación',
            'cerrado-ganado': 'Cerrado (Ganado)',
            'cerrado-perdido': 'Cerrado (Perdido)',
            'seguimiento': 'En Seguimiento'
        };
        
        return labels[status] || status;
    }
    
    /**
     * Inicializa el modal de actualización de status
     */
    function initStatusUpdateModal() {
        // Manejar click en botón de editar status
        $(document).on('click', '.vdp-edit-status', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const leadId = $(this).data('lead-id');
            const $card = $(this).closest('.vdp-lead-card');
            const currentStatus = $card.closest('.vdp-column').data('status');
            
            showStatusUpdateModal(leadId, currentStatus);
        });
        
        // Manejar click en botón de ver lead
        $(document).on('click', '.vdp-view-lead', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const leadId = $(this).data('lead-id');
            // Abrir modal o redirigir a página de detalle del lead
            window.open(`${window.location.origin}/wp-admin/post.php?post=${leadId}&action=edit`, '_blank');
        });
    }
    
    /**
     * Muestra el modal de actualización de status
     */
    function showStatusUpdateModal(leadId, currentStatus) {
        const statuses = [
            { value: 'nuevo-no-contactado', label: 'Nuevo (No Contactado)' },
            { value: 'contactado-interesado', label: 'Contactado e Interesado' },
            { value: 'reunion-agendada', label: 'Reunión Agendada' },
            { value: 'propuesta-enviada', label: 'Propuesta Enviada' },
            { value: 'negociacion', label: 'En Negociación' },
            { value: 'cerrado-ganado', label: 'Cerrado (Ganado)' },
            { value: 'cerrado-perdido', label: 'Cerrado (Perdido)' },
            { value: 'seguimiento', label: 'En Seguimiento' }
        ];
        
        let optionsHtml = '';
        statuses.forEach(function(status) {
            const selected = status.value === currentStatus ? 'selected' : '';
            optionsHtml += `<option value="${status.value}" ${selected}>${status.label}</option>`;
        });
        
        const modalHtml = `
            <div id="vdp-status-modal" class="vdp-modal">
                <div class="vdp-modal-content">
                    <div class="vdp-modal-header">
                        <h3>Actualizar Estado del Lead</h3>
                        <button type="button" class="vdp-modal-close">&times;</button>
                    </div>
                    <div class="vdp-modal-body">
                        <div class="vdp-form-group">
                            <label for="vdp-new-status">Nuevo Estado:</label>
                            <select id="vdp-new-status" class="vdp-form-control">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="vdp-form-group">
                            <label for="vdp-status-notes">Notas (opcional):</label>
                            <textarea id="vdp-status-notes" class="vdp-form-control" rows="3" placeholder="Agregar notas sobre el cambio de estado..."></textarea>
                        </div>
                    </div>
                    <div class="vdp-modal-footer">
                        <button type="button" class="vdp-btn vdp-btn-secondary" id="vdp-cancel-status">Cancelar</button>
                        <button type="button" class="vdp-btn vdp-btn-primary" id="vdp-save-status" data-lead-id="${leadId}">Actualizar</button>
                    </div>
                </div>
            </div>
        `;
        
        // Remover modal existente si hay uno
        $('#vdp-status-modal').remove();
        
        // Agregar nuevo modal
        $('body').append(modalHtml);
        $('#vdp-status-modal').show();
        
        // Event listeners del modal
        $('#vdp-modal-close, #vdp-cancel-status').on('click', function() {
            $('#vdp-status-modal').remove();
        });
        
        $('#vdp-save-status').on('click', function() {
            const newStatus = $('#vdp-new-status').val();
            const notes = $('#vdp-status-notes').val();
            
            updateLeadStatus(leadId, newStatus, notes);
        });
    }
    
    /**
     * Actualiza el status de un lead
     */
    function updateLeadStatus(leadId, newStatus, notes) {
        const $saveBtn = $('#vdp-save-status');
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_update_lead_status',
                nonce: vdp_ajax.nonce,
                lead_id: leadId,
                status: newStatus,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $('#vdp-status-modal').remove();
                    showNotification('Estado actualizado exitosamente', 'success');
                    
                    // Recargar datos del pipeline
                    loadPipelineData();
                } else {
                    showNotification('Error al actualizar el estado: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Error de conexión al actualizar el estado', 'error');
            },
            complete: function() {
                $saveBtn.prop('disabled', false).html('Actualizar');
            }
        });
    }
    
    /**
     * Funciones de utilidad
     */
    function showLoading() {
        $('.vdp-pipeline-container').addClass('vdp-loading');
    }
    
    function hideLoading() {
        $('.vdp-pipeline-container').removeClass('vdp-loading');
    }
    
    function showNotification(message, type = 'success') {
        // Crear notificación
        const notification = $(`
            <div class="vdp-notification vdp-notification-${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button type="button" class="vdp-notification-close">&times;</button>
            </div>
        `);
        
        // Agregar al container de notificaciones
        let $notificationContainer = $('#vdp-notifications');
        if ($notificationContainer.length === 0) {
            $notificationContainer = $('<div id="vdp-notifications"></div>');
            $('body').append($notificationContainer);
        }
        
        $notificationContainer.append(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(function() {
            notification.fadeOut(function() {
                notification.remove();
            });
        }, 5000);
        
        // Manejar click en cerrar
        notification.find('.vdp-notification-close').on('click', function() {
            notification.fadeOut(function() {
                notification.remove();
            });
        });
    }
    
    function formatDate(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        
        return date.toLocaleDateString('es-ES', options);
    }
    
    // Exponer funciones globalmente
    window.VDPPipeline = {
        loadPipelineData: loadPipelineData,
        updateLeads: function(data) {
            updatePipelineView({ leads_by_status: data });
        },
        getCurrentFilters: function() { return currentFilters; },
        setFilters: function(filters) { 
            currentFilters = filters; 
            loadPipelineData();
        }
    };
    
})(jQuery);