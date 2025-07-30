jQuery(function($) {
    // Estado de los filtros
    let currentFilters = {
        // Añadir identificador para filtros guardados
        filter_name: '',
        
        fecha_ingreso_inicio: '',
        fecha_ingreso_fin: '',
        fecha_evento_inicio: '',
        fecha_evento_fin: '',
        tipo_evento: [],
        status: [],
        invitados: '',
        prioridad: '',
        valor_potencial: '',
        probabilidad: '',
        responsable: [],
        ultima_interaccion: '',
        tiempo_sin_actividad: '',
        proxima_accion: '',
        fuente: [],
        campana: [],
        ubicacion: [],
        industria: [],
        estado_propuesta: '',
        rango_cotizacion: '',
        temporada: '',
        servicios_requeridos: [],
        venue: [],
        etiquetas: [],
        search: '',
        orderby: 'fecha_solicitud',
        order: 'DESC',
        paged: 1,
        per_page: 25,
    };
    
    // Elementos del DOM
    const $filterContainer = $('#vdp_filters_container');
    const $toggleFiltersBtn = $('#vdp_toggle_filters_btn');
    const $applyFiltersBtn = $('#vdp_aplicar_filtros');
    const $clearFiltersBtn = $('#vdp_limpiar_filtros');
    const $clearBasicFiltersBtn = $('#vdp_limpiar_filtros_basicos');
    const $activeFiltersCount = $('#vdp_active_filters_count');
    const $activeFiltersContainer = $('#vdp_filtros_activos');
    const $savedFilterSelect = $('#vdp_saved_filter_select');
    const $saveCurrentFilter = $('#vdp_save_current_filter');
    
    // Cargar filtros guardados del localStorage
    loadSavedFilters();
    
    // Inicializar Select2
    $('.vdp-select2-multi').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        closeOnSelect: false
    });
    
    // Inicializar Select2 para etiquetas (con opción de agregar nuevas)
    $('.vdp-select2-tags').select2({
        placeholder: 'Seleccionar o crear...',
        allowClear: true,
        closeOnSelect: false,
        tags: true
    });
    
    // Inicializar secciones colapsables con comportamiento de acordeón
    $('.vdp-section-header').on('click', function() {
        const target = $(this).data('target');
        const $content = $('#' + target);
        const $icon = $(this).find('.vdp-toggle-icon');
        const $allHeaders = $('.vdp-section-header');
        const $allContents = $('.vdp-section-content');
        const $allIcons = $('.vdp-section-header .vdp-toggle-icon');
        
        // Si ya está abierto, simplemente ciérralo
        if ($content.hasClass('open')) {
            $content.slideUp(200, function() {
                $content.removeClass('open');
                $icon.removeClass('open');
                $(this).removeClass('active');
            });
            return;
        }
        
        // Cierra todos los contenidos abiertos
        $allContents.slideUp(200, function() {
            $allContents.removeClass('open');
        });
        
        // Resetea todos los headers y iconos
        $allHeaders.removeClass('active');
        $allIcons.removeClass('open');
        
        // Activa el header actual
        $(this).addClass('active');
        
        // Abre solo el contenido seleccionado
        $content.slideDown(200, function() {
            $content.addClass('open');
            $icon.addClass('open');
        });
    });
    
    // Toggle de filtros avanzados
    $toggleFiltersBtn.on('click', function() {
        const $icon = $(this).find('i');
        
        if ($filterContainer.is(':visible')) {
            $filterContainer.slideUp(300);
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $(this).removeClass('active');
        } else {
            $filterContainer.slideDown(300);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $(this).addClass('active');
        }
    });
    
    // Event listeners para filtros
    $applyFiltersBtn.on('click', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    $clearFiltersBtn.on('click', function(e) {
        e.preventDefault();
        clearAllFilters();
    });
    
    $clearBasicFiltersBtn.on('click', function(e) {
        e.preventDefault();
        clearBasicFilters();
    });
    
    // Guardar filtro actual
    $saveCurrentFilter.on('click', function(e) {
        e.preventDefault();
        saveCurrentFilterDialog();
    });
    
    // Cargar filtro guardado
    $savedFilterSelect.on('change', function() {
        const selectedFilter = $(this).val();
        if (selectedFilter) {
            loadSavedFilter(selectedFilter);
        }
    });
    
    // Event listeners para campos de filtro individuales
    $('#vdp_search_input').on('keyup', debounce(function() {
        if ($(this).val().length >= 3 || $(this).val().length === 0) {
            currentFilters.search = $(this).val();
            applyFilters();
        }
    }, 500));
    
    // Date range pickers
    if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#vdp_fecha_ingreso_range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            autoUpdateInput: false,
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        
        $('#vdp_fecha_ingreso_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            currentFilters.fecha_ingreso_inicio = picker.startDate.format('YYYY-MM-DD');
            currentFilters.fecha_ingreso_fin = picker.endDate.format('YYYY-MM-DD');
            applyFilters();
        });
        
        $('#vdp_fecha_evento_range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            autoUpdateInput: false,
            ranges: {
                'Hoy': [moment(), moment()],
                'Mañana': [moment().add(1, 'days'), moment().add(1, 'days')],
                'Próximos 7 días': [moment(), moment().add(6, 'days')],
                'Próximos 30 días': [moment(), moment().add(29, 'days')],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Próximo mes': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')]
            }
        });
        
        $('#vdp_fecha_evento_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            currentFilters.fecha_evento_inicio = picker.startDate.format('YYYY-MM-DD');
            currentFilters.fecha_evento_fin = picker.endDate.format('YYYY-MM-DD');
            applyFilters();
        });
    }
    
    // Función para aplicar filtros
    function applyFilters() {
        // Recopilar valores de todos los campos de filtro
        collectFilterValues();
        
        // Mostrar loading
        showLoadingState();
        
        // Realizar petición AJAX
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_filter_leads',
                nonce: vdp_ajax.nonce,
                ...currentFilters
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar la vista del pipeline
                    updatePipelineView(response.data);
                    
                    // Actualizar chips de filtros activos
                    updateActiveFiltersChips(response.data.applied_filters);
                    
                    // Actualizar contador de filtros activos
                    updateActiveFiltersCount(response.data.applied_filters.length);
                } else {
                    console.error('Error applying filters:', response.data);
                    showNotification('Error al aplicar filtros', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotification('Error de conexión al aplicar filtros', 'error');
            },
            complete: function() {
                hideLoadingState();
            }
        });
    }
    
    // Función para recopilar valores de filtros
    function collectFilterValues() {
        // Filtros básicos
        currentFilters.search = $('#vdp_search_input').val() || '';
        
        // Filtros de fecha
        // Ya se manejan en los event listeners de daterangepicker
        
        // Filtros de select múltiple
        currentFilters.tipo_evento = $('#vdp_tipo_evento').val() || [];
        currentFilters.status = $('#vdp_status').val() || [];
        currentFilters.fuente = $('#vdp_fuente').val() || [];
        currentFilters.responsable = $('#vdp_responsable').val() || [];
        currentFilters.ubicacion = $('#vdp_ubicacion').val() || [];
        currentFilters.industria = $('#vdp_industria').val() || [];
        currentFilters.servicios_requeridos = $('#vdp_servicios_requeridos').val() || [];
        currentFilters.venue = $('#vdp_venue').val() || [];
        currentFilters.etiquetas = $('#vdp_etiquetas').val() || [];
        currentFilters.campana = $('#vdp_campana').val() || [];
        
        // Filtros de select simple
        currentFilters.invitados = $('#vdp_invitados').val() || '';
        currentFilters.prioridad = $('#vdp_prioridad').val() || '';
        currentFilters.valor_potencial = $('#vdp_valor_potencial').val() || '';
        currentFilters.probabilidad = $('#vdp_probabilidad').val() || '';
        currentFilters.ultima_interaccion = $('#vdp_ultima_interaccion').val() || '';
        currentFilters.tiempo_sin_actividad = $('#vdp_tiempo_sin_actividad').val() || '';
        currentFilters.proxima_accion = $('#vdp_proxima_accion').val() || '';
        currentFilters.estado_propuesta = $('#vdp_estado_propuesta').val() || '';
        currentFilters.rango_cotizacion = $('#vdp_rango_cotizacion').val() || '';
        currentFilters.temporada = $('#vdp_temporada').val() || '';
    }
    
    // Función para actualizar la vista del pipeline
    function updatePipelineView(data) {
        if (typeof window.VDPPipeline !== 'undefined') {
            window.VDPPipeline.updateLeads(data.data);
        } else {
            // Fallback: recargar la página
            location.reload();
        }
    }
    
    // Función para actualizar chips de filtros activos
    function updateActiveFiltersChips(appliedFilters) {
        $activeFiltersContainer.empty();
        
        if (appliedFilters && appliedFilters.length > 0) {
            appliedFilters.forEach(function(filter) {
                const chip = $(`
                    <span class="vdp-filter-chip" data-filter-type="${filter.type}">
                        <span class="vdp-filter-label">${filter.label}:</span>
                        <span class="vdp-filter-value">${filter.value}</span>
                        <button type="button" class="vdp-filter-remove" title="Remover filtro">×</button>
                    </span>
                `);
                
                chip.find('.vdp-filter-remove').on('click', function() {
                    removeFilter(filter.type);
                });
                
                $activeFiltersContainer.append(chip);
            });
            
            $activeFiltersContainer.show();
        } else {
            $activeFiltersContainer.hide();
        }
    }
    
    // Función para actualizar contador de filtros activos
    function updateActiveFiltersCount(count) {
        if (count > 0) {
            $activeFiltersCount.text(count).show();
        } else {
            $activeFiltersCount.hide();
        }
    }
    
    // Función para remover un filtro específico
    function removeFilter(filterType) {
        switch (filterType) {
            case 'search':
                currentFilters.search = '';
                $('#vdp_search_input').val('');
                break;
            case 'date':
                currentFilters.fecha_ingreso_inicio = '';
                currentFilters.fecha_ingreso_fin = '';
                $('#vdp_fecha_ingreso_range').val('');
                break;
            case 'tipo_evento':
                currentFilters.tipo_evento = [];
                $('#vdp_tipo_evento').val([]).trigger('change');
                break;
            case 'status':
                currentFilters.status = [];
                $('#vdp_status').val([]).trigger('change');
                break;
            // Agregar más casos según sea necesario
        }
        
        applyFilters();
    }
    
    // Función para limpiar todos los filtros
    function clearAllFilters() {
        // Resetear objeto de filtros
        for (let key in currentFilters) {
            if (Array.isArray(currentFilters[key])) {
                currentFilters[key] = [];
            } else {
                currentFilters[key] = '';
            }
        }
        
        // Resetear valores por defecto
        currentFilters.orderby = 'fecha_solicitud';
        currentFilters.order = 'DESC';
        currentFilters.paged = 1;
        currentFilters.per_page = 25;
        
        // Limpiar campos del formulario
        clearFormFields();
        
        // Aplicar filtros vacíos
        applyFilters();
    }
    
    // Función para limpiar filtros básicos
    function clearBasicFilters() {
        currentFilters.search = '';
        currentFilters.fecha_ingreso_inicio = '';
        currentFilters.fecha_ingreso_fin = '';
        
        $('#vdp_search_input').val('');
        $('#vdp_fecha_ingreso_range').val('');
        
        applyFilters();
    }
    
    // Función para limpiar campos del formulario
    function clearFormFields() {
        // Campos de texto
        $('#vdp_search_input').val('');
        
        // Date range pickers
        $('#vdp_fecha_ingreso_range, #vdp_fecha_evento_range').val('');
        
        // Select2 múltiple
        $('.vdp-select2-multi, .vdp-select2-tags').val([]).trigger('change');
        
        // Select simples
        $('select:not(.vdp-select2-multi):not(.vdp-select2-tags)').val('');
    }
    
    // Función para mostrar estado de carga
    function showLoadingState() {
        $applyFiltersBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Aplicando...');
    }
    
    // Función para ocultar estado de carga
    function hideLoadingState() {
        $applyFiltersBtn.prop('disabled', false).html('<i class="fas fa-filter"></i> Aplicar Filtros');
    }
    
    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        // Implementar sistema de notificaciones
        console.log(`${type.toUpperCase()}: ${message}`);
    }
    
    // Función para guardar filtros
    function loadSavedFilters() {
        try {
            const savedFilters = JSON.parse(localStorage.getItem('vdp_saved_filters') || '{}');
            
            $savedFilterSelect.empty().append('<option value="">Seleccionar filtro guardado...</option>');
            
            Object.keys(savedFilters).forEach(function(filterName) {
                $savedFilterSelect.append(`<option value="${filterName}">${filterName}</option>`);
            });
        } catch (error) {
            console.error('Error loading saved filters:', error);
        }
    }
    
    // Función para guardar filtro actual
    function saveCurrentFilterDialog() {
        const filterName = prompt('Nombre para el filtro guardado:');
        if (filterName && filterName.trim()) {
            saveFilter(filterName.trim());
        }
    }
    
    // Función para guardar filtro
    function saveFilter(name) {
        try {
            const savedFilters = JSON.parse(localStorage.getItem('vdp_saved_filters') || '{}');
            savedFilters[name] = { ...currentFilters };
            localStorage.setItem('vdp_saved_filters', JSON.stringify(savedFilters));
            
            loadSavedFilters();
            showNotification(`Filtro "${name}" guardado correctamente`, 'success');
        } catch (error) {
            console.error('Error saving filter:', error);
            showNotification('Error al guardar el filtro', 'error');
        }
    }
    
    // Función para cargar filtro guardado
    function loadSavedFilter(filterName) {
        try {
            const savedFilters = JSON.parse(localStorage.getItem('vdp_saved_filters') || '{}');
            if (savedFilters[filterName]) {
                currentFilters = { ...savedFilters[filterName] };
                populateFormFields();
                applyFilters();
                showNotification(`Filtro "${filterName}" aplicado`, 'success');
            }
        } catch (error) {
            console.error('Error loading saved filter:', error);
            showNotification('Error al cargar el filtro guardado', 'error');
        }
    }
    
    // Función para poblar campos del formulario con filtros cargados
    function populateFormFields() {
        $('#vdp_search_input').val(currentFilters.search);
        
        if (currentFilters.fecha_ingreso_inicio && currentFilters.fecha_ingreso_fin) {
            $('#vdp_fecha_ingreso_range').val(currentFilters.fecha_ingreso_inicio + ' - ' + currentFilters.fecha_ingreso_fin);
        }
        
        if (currentFilters.fecha_evento_inicio && currentFilters.fecha_evento_fin) {
            $('#vdp_fecha_evento_range').val(currentFilters.fecha_evento_inicio + ' - ' + currentFilters.fecha_evento_fin);
        }
        
        // Poblar selects múltiples
        $('#vdp_tipo_evento').val(currentFilters.tipo_evento).trigger('change');
        $('#vdp_status').val(currentFilters.status).trigger('change');
        $('#vdp_fuente').val(currentFilters.fuente).trigger('change');
        $('#vdp_responsable').val(currentFilters.responsable).trigger('change');
        $('#vdp_ubicacion').val(currentFilters.ubicacion).trigger('change');
        $('#vdp_industria').val(currentFilters.industria).trigger('change');
        $('#vdp_servicios_requeridos').val(currentFilters.servicios_requeridos).trigger('change');
        $('#vdp_venue').val(currentFilters.venue).trigger('change');
        $('#vdp_etiquetas').val(currentFilters.etiquetas).trigger('change');
        $('#vdp_campana').val(currentFilters.campana).trigger('change');
        
        // Poblar selects simples
        $('#vdp_invitados').val(currentFilters.invitados);
        $('#vdp_prioridad').val(currentFilters.prioridad);
        $('#vdp_valor_potencial').val(currentFilters.valor_potencial);
        $('#vdp_probabilidad').val(currentFilters.probabilidad);
        $('#vdp_ultima_interaccion').val(currentFilters.ultima_interaccion);
        $('#vdp_tiempo_sin_actividad').val(currentFilters.tiempo_sin_actividad);
        $('#vdp_proxima_accion').val(currentFilters.proxima_accion);
        $('#vdp_estado_propuesta').val(currentFilters.estado_propuesta);
        $('#vdp_rango_cotizacion').val(currentFilters.rango_cotizacion);
        $('#vdp_temporada').val(currentFilters.temporada);
    }
    
    // Función de debounce para optimizar búsquedas
    function debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Auto-aplicar filtros cuando se inicializa la página
    $(document).ready(function() {
        // Cargar opciones de filtros dinámicos
        loadFilterOptions();
        
        // Aplicar filtros iniciales si existen parámetros en URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('filter')) {
            const filterParam = urlParams.get('filter');
            if (filterParam === 'today') {
                currentFilters.fecha_ingreso_inicio = moment().format('YYYY-MM-DD');
                currentFilters.fecha_ingreso_fin = moment().format('YYYY-MM-DD');
                $('#vdp_fecha_ingreso_range').val(moment().format('YYYY-MM-DD') + ' - ' + moment().format('YYYY-MM-DD'));
                applyFilters();
            }
        }
    });
    
    // Función para cargar opciones de filtros dinámicos
    function loadFilterOptions() {
        $.ajax({
            url: vdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vdp_get_filter_options',
                nonce: vdp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateFilterOptions(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading filter options:', error);
            }
        });
    }
    
    // Función para poblar opciones de filtros
    function populateFilterOptions(options) {
        // Poblar tipo_evento
        if (options.tipo_evento) {
            const $tipoEvento = $('#vdp_tipo_evento');
            $tipoEvento.empty();
            options.tipo_evento.forEach(function(option) {
                $tipoEvento.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar fuente
        if (options.fuente) {
            const $fuente = $('#vdp_fuente');
            $fuente.empty();
            options.fuente.forEach(function(option) {
                $fuente.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar ubicacion
        if (options.ubicacion) {
            const $ubicacion = $('#vdp_ubicacion');
            $ubicacion.empty();
            options.ubicacion.forEach(function(option) {
                $ubicacion.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar servicios_requeridos
        if (options.servicios_requeridos) {
            const $servicios = $('#vdp_servicios_requeridos');
            $servicios.empty();
            options.servicios_requeridos.forEach(function(option) {
                $servicios.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar venue
        if (options.venue) {
            const $venue = $('#vdp_venue');
            $venue.empty();
            options.venue.forEach(function(option) {
                $venue.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar etiquetas
        if (options.etiquetas) {
            const $etiquetas = $('#vdp_etiquetas');
            $etiquetas.empty();
            options.etiquetas.forEach(function(option) {
                $etiquetas.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Poblar responsables
        if (options.responsable) {
            const $responsable = $('#vdp_responsable');
            $responsable.empty();
            options.responsable.forEach(function(option) {
                $responsable.append(`<option value="${option.value}">${option.label}</option>`);
            });
        }
        
        // Reinicializar Select2 después de poblar opciones
        $('.vdp-select2-multi, .vdp-select2-tags').select2('destroy').select2({
            placeholder: 'Seleccionar...',
            allowClear: true,
            closeOnSelect: false
        });
    }
    
    // Exponer funciones globalmente para uso externo
    window.VDPFilters = {
        applyFilters: applyFilters,
        clearAllFilters: clearAllFilters,
        getCurrentFilters: function() { return currentFilters; },
        setFilters: function(filters) { 
            currentFilters = { ...currentFilters, ...filters };
            populateFormFields();
            applyFilters();
        }
    };
});