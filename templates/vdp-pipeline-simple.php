<?php
/**
 * VDP Pipeline Simple Template - Adaptado de Leads Management Plugin
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos de vendor
if (!is_user_logged_in() || !vdp_is_user_vendor()) {
    echo '<div class="vdp-notice vdp-notice-error">';
    echo '<p>' . esc_html__('You must be a vendor to access this content.', 'vendor-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Obtener leads del vendor
$vendor = vdp_get_current_vendor();
if (!$vendor) {
    echo '<div class="vdp-notice vdp-notice-error">';
    echo '<p>' . esc_html__('Vendor profile not found.', 'vendor-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Status mapping - usar exactamente los mismos estados del plugin original Leads-Management
$vdp_status_options = array(
    'nuevo' => 'Nuevo',
    'con-presupuesto' => 'Con Cotización',
    'por-cerrar' => 'Por cerrar',
    'con-contrato' => 'Con contrato',
    'perdido' => 'Perdido'
);
?>

<div class="vdp-pipeline-container">
    <!-- Barra de herramientas superior -->
    <div class="vdp-pipeline-toolbar">
        <button id="vdp_add_lead_btn" class="vdp-btn vdp-btn-primary">
            <i class="fas fa-plus"></i>
            <?php esc_html_e('Add Lead', 'vendor-dashboard-pro'); ?>
        </button>
        
        <!-- Filtros estilo Excel en línea -->
        <div class="vdp-inline-filters">
            <input type="text" id="vdp_quick_search" placeholder="Buscar..." class="vdp-filter-input">
            
            <select id="vdp_period_filter" class="vdp-filter-select">
                <option value="">Todos los períodos</option>
                <option value="today">Hoy</option>
                <option value="this_week">Esta semana</option>
                <option value="this_month">Este mes</option>
                <option value="this_year">Este año</option>
                <option value="specific_month">Mes/Año específico</option>
                <option value="custom">Rango personalizado</option>
            </select>
            
            <div id="vdp_custom_date_range" class="vdp-date-range-container" style="display:none;">
                <input type="text" id="vdp_date_range" class="vdp-filter-input" title="Rango de fechas" placeholder="Seleccionar rango de fechas" readonly>
            </div>
            
            <div id="vdp_specific_month_range" class="vdp-month-year-container" style="display:none;">
                <select id="vdp_mes_evento_basic" class="vdp-filter-select">
                    <option value="">Seleccionar mes</option>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
                
                <select id="vdp_anio_evento" class="vdp-filter-select">
                    <option value="">Seleccionar año</option>
                    <?php
                    $current_year = date('Y');
                    for ($year = 2000; $year <= ($current_year + 5); $year++) {
                        echo '<option value="' . $year . '">' . $year . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <select id="vdp_event_type_filter" class="vdp-filter-select">
                <option value="">Todos los tipos</option>
                <!-- Los tipos se cargarán dinámicamente -->
            </select>
            
            <select id="vdp_priority_filter" class="vdp-filter-select">
                <option value="">Todas las prioridades</option>
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
            
            <button id="vdp_apply_filters" class="vdp-btn vdp-btn-secondary">Filtrar</button>
            <button id="vdp_clear_filters" class="vdp-btn vdp-btn-text">Limpiar</button>
        </div>
        
        <div class="vdp-filter-extras">
            <label>
                <input type="checkbox" id="vdp_show_leads_without_event">
                Mostrar leads sin evento
            </label>
        </div>
        
        <div class="vdp-pipeline-stats">
            Total: <span id="vdp_total_leads">0</span>
        </div>
    </div>

    <!-- Vista Pipeline -->
    <div class="vdp-pipeline-board">
        <?php foreach ($vdp_status_options as $status_value => $status_label) : ?>
            <div class="vdp-pipeline-column" data-status="<?php echo esc_attr($status_value); ?>">
                <div class="vdp-column-header">
                    <h3><?php echo esc_html($status_label); ?></h3>
                    <span class="vdp-count">0</span>
                </div>
                <div class="vdp-column-content" id="vdp-<?php echo esc_attr($status_value); ?>-cards">
                    <!-- Las tarjetas se cargarán aquí -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FORCE MODAL CSS -->
    <style id="vdp-modal-force-css">
    /* ABSOLUTE OVERRIDE - NOTHING CAN BEAT THIS */
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"][class*="vdp-modal"] {
        position: fixed !important;
        top: 0px !important;
        left: 0px !important;
        right: 0px !important;
        bottom: 0px !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0,0,0,0.6) !important;
        z-index: 2147483647 !important;
        display: none !important;
        margin: 0px !important;
        padding: 0px !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 1 !important;
        font-family: inherit !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"][class*="vdp-active"] {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow-y: auto !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] .vdp-modal-content {
        background: #ffffff !important;
        width: 90% !important;
        max-width: 600px !important;
        min-width: 320px !important;
        max-height: 90vh !important;
        overflow-y: auto !important;
        border-radius: 8px !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important;
        position: relative !important;
        margin: 20px auto !important;
        padding: 0px !important;
        border: none !important;
        outline: none !important;
        transform: none !important;
        opacity: 1 !important;
        z-index: 2147483647 !important;
        font-family: inherit !important;
        box-sizing: border-box !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] .vdp-modal-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 20px !important;
        border-bottom: 1px solid #dee2e6 !important;
        background: #ffffff !important;
        border-radius: 8px 8px 0 0 !important;
        margin: 0px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        font-family: inherit !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] .vdp-close-modal {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: none !important;
        border: none !important;
        font-size: 24px !important;
        cursor: pointer !important;
        color: #6c757d !important;
        padding: 0px !important;
        margin: 0px !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        transition: background-color 0.2s !important;
        z-index: 2147483647 !important;
        outline: none !important;
        box-shadow: none !important;
        text-decoration: none !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] .vdp-close-modal:hover {
        background: #e9ecef !important;
    }
    
    html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] .vdp-simple-form {
        display: block !important;
        padding: 20px !important;
        background: #ffffff !important;
        width: 100% !important;
        box-sizing: border-box !important;
        margin: 0px !important;
        border: none !important;
        font-family: inherit !important;
    }
    </style>
    
    <!-- Modal simplificado para agregar lead -->
    <div id="vdp_add_lead_modal_unique" class="vdp-modal vdp-add-lead-modal">
        <div class="vdp-modal-content">
            <div class="vdp-modal-header">
<?php $translations = VDP_Translations::instance(); ?>
                <h2><?php echo esc_html($translations->get_translation('Add Lead')); ?></h2>
                <button class="vdp-close-modal">&times;</button>
            </div>
            
            <form id="vdp_lead_form" class="vdp-simple-form">
                <!-- Información básica del lead -->
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php echo esc_html($translations->get_translation('Name')); ?> *</label>
                        <input type="text" name="lead_nombre" required>
                    </div>
                    <div class="vdp-form-field">
                        <label><?php echo esc_html($translations->get_translation('Last Name')); ?> *</label>
                        <input type="text" name="lead_apellido" required>
                    </div>
                </div>
                
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php echo esc_html($translations->get_translation('Phone')); ?> *</label>
                        <input type="tel" name="lead_celular" required>
                    </div>
                    <div class="vdp-form-field">
                        <label><?php echo esc_html($translations->get_translation('Email')); ?> *</label>
                        <input type="email" name="lead_e_mail" required>
                    </div>
                </div>
                
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php echo esc_html($translations->get_translation('Company Name')); ?></label>
                        <input type="text" name="lead_razon_social">
                    </div>
                </div>
                
                <!-- Información del evento (opcional) -->
                <div class="vdp-form-section">
                    <label class="vdp-checkbox-label">
                        <input type="checkbox" id="vdp_include_event"> <?php echo esc_html($translations->get_translation('Include event information')); ?>
                    </label>
                </div>
                
                <div id="vdp_event_fields" style="display:none;">
                    <div class="vdp-form-row">
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Event Date')); ?></label>
                            <input type="date" id="vdp_evento_fecha" name="fecha_de_evento">
                        </div>
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Event Type')); ?></label>
                            <select id="vdp_evento_tipo" name="tipo_de_evento">
                                <option value=""><?php echo esc_html($translations->get_translation('Select...')); ?></option>
                                <option value="boda"><?php echo esc_html($translations->get_translation('Wedding')); ?></option>
                                <option value="quinceanos"><?php echo esc_html($translations->get_translation('Quinceañera')); ?></option>
                                <option value="bautizo"><?php echo esc_html($translations->get_translation('Baptism')); ?></option>
                                <option value="cumpleanos"><?php echo esc_html($translations->get_translation('Birthday')); ?></option>
                                <option value="corporativo"><?php echo esc_html($translations->get_translation('Corporate')); ?></option>
                                <option value="otro"><?php echo esc_html($translations->get_translation('Other')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vdp-form-row">
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Number of Guests')); ?></label>
                            <input type="number" id="vdp_evento_asistentes" name="evento_asistentes" min="1">
                        </div>
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Status')); ?></label>
                            <select id="vdp_evento_status" name="evento_status">
                                <?php foreach ($vdp_status_options as $status_value => $status_label) : ?>
                                    <option value="<?php echo esc_attr($status_value); ?>" <?php selected($status_value, 'nuevo'); ?>>
                                        <?php echo esc_html($status_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vdp-form-row">
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Event Address')); ?></label>
                            <input type="text" id="vdp_evento_direccion" name="direccion_evento" placeholder="<?php echo esc_attr($translations->get_translation('Complete event address')); ?>">
                        </div>
                        <div class="vdp-form-field">
                            <label><?php echo esc_html($translations->get_translation('Service of Interest')); ?></label>
                            <input type="text" id="vdp_evento_servicio_search" placeholder="<?php echo esc_attr($translations->get_translation('Search service...')); ?>">
                            <input type="hidden" id="vdp_evento_servicio" name="evento_servicio_de_interes">
                        </div>
                    </div>
                    
                    <div class="vdp-form-row">
                        <div class="vdp-form-field full-width">
                            <label><?php echo esc_html($translations->get_translation('Additional Comments')); ?></label>
                            <textarea id="vdp_evento_comentarios" name="comentarios_evento" rows="3" placeholder="<?php echo esc_attr($translations->get_translation('Notes or comments about the event...')); ?>"></textarea>
                        </div>
                    </div>
                    
                    <input type="hidden" id="vdp_evento_ubicacion" name="evento_ubicacion">
                </div>
                
                <div class="vdp-form-actions">
                    <button type="button" class="vdp-btn vdp-btn-secondary vdp-close-modal"><?php echo esc_html($translations->get_translation('Cancel')); ?></button>
                    <button type="submit" class="vdp-btn vdp-btn-primary"><?php echo esc_html($translations->get_translation('Save Lead')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cargar librerías requeridas directamente -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">


<!-- Modal para actualizar status -->
<div id="vdp_status_modal" class="vdp-modal">
    <div class="vdp-modal-content vdp-modal-small">
        <div class="vdp-modal-header">
            <h2><?php esc_html_e('Update Status', 'vendor-dashboard-pro'); ?></h2>
            <button class="vdp-close-modal">&times;</button>
        </div>
        
        <form id="vdp_status_form">
            <input type="hidden" id="vdp_status_lead_id" name="lead_id">
            
            <div class="vdp-form-field">
                <label><?php esc_html_e('New Status', 'vendor-dashboard-pro'); ?></label>
                <select id="vdp_new_status" name="new_status">
                    <?php foreach ($vdp_status_options as $status_value => $status_label) : ?>
                        <option value="<?php echo esc_attr($status_value); ?>">
                            <?php echo esc_html($status_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="vdp-form-field">
                <label><?php esc_html_e('Notes', 'vendor-dashboard-pro'); ?></label>
                <textarea name="status_notes" rows="3" placeholder="<?php esc_attr_e('Reason for status change...', 'vendor-dashboard-pro'); ?>"></textarea>
            </div>
            
            <div class="vdp-form-actions">
                <button type="button" class="vdp-btn vdp-btn-secondary vdp-close-modal"><?php esc_html_e('Cancel', 'vendor-dashboard-pro'); ?></button>
                <button type="submit" class="vdp-btn vdp-btn-primary"><?php esc_html_e('Update Status', 'vendor-dashboard-pro'); ?></button>
            </div>
        </form>
    </div>
</div>