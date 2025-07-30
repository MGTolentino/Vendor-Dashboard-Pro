<?php
/**
 * VDP Enhanced Pipeline Template - Complete Implementation
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

// Estados del pipeline con labels en español e inglés
$pipeline_statuses = array(
    'nuevo-no-contactado' => array(
        'label_es' => 'Nuevo (No Contactado)',
        'label_en' => 'New (Not Contacted)',
        'icon' => 'fas fa-user-plus',
        'color' => '#0d47a1'
    ),
    'contactado-interesado' => array(
        'label_es' => 'Contactado e Interesado',
        'label_en' => 'Contacted & Interested',
        'icon' => 'fas fa-phone',
        'color' => '#0f5132'
    ),
    'reunion-agendada' => array(
        'label_es' => 'Reunión Agendada',
        'label_en' => 'Meeting Scheduled',
        'icon' => 'fas fa-calendar',
        'color' => '#664d03'
    ),
    'propuesta-enviada' => array(
        'label_es' => 'Propuesta Enviada',
        'label_en' => 'Proposal Sent',
        'icon' => 'fas fa-file-alt',
        'color' => '#41464b'
    ),
    'negociacion' => array(
        'label_es' => 'En Negociación',
        'label_en' => 'In Negotiation',
        'icon' => 'fas fa-handshake',
        'color' => '#842029'
    ),
    'cerrado-ganado' => array(
        'label_es' => 'Cerrado (Ganado)',
        'label_en' => 'Closed (Won)',
        'icon' => 'fas fa-trophy',
        'color' => '#0a3622'
    ),
    'cerrado-perdido' => array(
        'label_es' => 'Cerrado (Perdido)',
        'label_en' => 'Closed (Lost)',
        'icon' => 'fas fa-times-circle',
        'color' => '#58151c'
    ),
    'seguimiento' => array(
        'label_es' => 'En Seguimiento',
        'label_en' => 'Follow Up',
        'icon' => 'fas fa-eye',
        'color' => '#004085'
    )
);

$is_spanish = (get_locale() === 'es_ES' || strpos(get_locale(), 'es_') === 0);
?>

<div class="vdp-leads-pipeline-page">
    <!-- Filtros Avanzados -->
    <div class="vdp-filters-container" id="vdp_filters_container" style="display: none;">
        <div class="vdp-filters-header">
            <h3 class="vdp-filters-title">
                <i class="fas fa-filter"></i>
                <?php echo $is_spanish ? 'Filtros Avanzados' : 'Advanced Filters'; ?>
                <span class="vdp-active-filters-count" id="vdp_active_filters_count" style="display: none;">0</span>
            </h3>
            <div class="vdp-saved-filters">
                <select id="vdp_saved_filter_select" class="vdp-saved-filter-select">
                    <option value=""><?php echo $is_spanish ? 'Filtro guardado...' : 'Saved filter...'; ?></option>
                </select>
                <button type="button" id="vdp_save_current_filter" class="vdp-save-filter-btn">
                    <i class="fas fa-save"></i> <?php echo $is_spanish ? 'Guardar' : 'Save'; ?>
                </button>
            </div>
        </div>
        
        <div class="vdp-filters-body">
            <!-- Filtros Básicos -->
            <div class="vdp-basic-filters">
                <div class="vdp-basic-filters-row">
                    <div class="vdp-search-group">
                        <input type="text" id="vdp_search_input" class="vdp-search-input" placeholder="<?php echo $is_spanish ? 'Buscar leads...' : 'Search leads...'; ?>">
                        <i class="fas fa-search vdp-search-icon"></i>
                    </div>
                    <div class="vdp-date-range-group">
                        <input type="text" id="vdp_fecha_ingreso_range" class="vdp-date-range-input" placeholder="<?php echo $is_spanish ? 'Fecha ingreso' : 'Entry date'; ?>" readonly>
                    </div>
                    <div class="vdp-date-range-group">
                        <input type="text" id="vdp_fecha_evento_range" class="vdp-date-range-input" placeholder="<?php echo $is_spanish ? 'Fecha evento' : 'Event date'; ?>" readonly>
                    </div>
                    <button type="button" id="vdp_aplicar_filtros" class="vdp-btn vdp-btn-primary">
                        <i class="fas fa-filter"></i> <?php echo $is_spanish ? 'Aplicar' : 'Apply'; ?>
                    </button>
                    <button type="button" id="vdp_limpiar_filtros_basicos" class="vdp-btn vdp-btn-outline">
                        <i class="fas fa-times"></i> <?php echo $is_spanish ? 'Limpiar' : 'Clear'; ?>
                    </button>
                </div>
            </div>
            
            <!-- Filtros Activos -->
            <div class="vdp-active-filters" id="vdp_filtros_activos">
                <div class="vdp-active-filters-title"><?php echo $is_spanish ? 'Filtros activos:' : 'Active filters:'; ?></div>
                <div class="vdp-filter-chips" id="vdp_filter_chips"></div>
            </div>
            
            <!-- Filtros Avanzados -->
            <div class="vdp-advanced-filters">
                <!-- Sección: Categorización -->
                <div class="vdp-filter-section">
                    <div class="vdp-section-header" data-target="vdp_categorizacion_content">
                        <h4 class="vdp-section-title">
                            <i class="fas fa-tags vdp-section-icon"></i>
                            <?php echo $is_spanish ? 'Categorización' : 'Categorization'; ?>
                        </h4>
                        <i class="fas fa-chevron-down vdp-toggle-icon"></i>
                    </div>
                    <div id="vdp_categorizacion_content" class="vdp-section-content">
                        <div class="vdp-filters-grid">
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Tipo de Evento' : 'Event Type'; ?></label>
                                <select id="vdp_tipo_evento" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Estado' : 'Status'; ?></label>
                                <select id="vdp_status" class="vdp-select2-multi" multiple>
                                    <?php foreach ($pipeline_statuses as $status => $config) : ?>
                                        <option value="<?php echo esc_attr($status); ?>">
                                            <?php echo esc_html($is_spanish ? $config['label_es'] : $config['label_en']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Invitados' : 'Guests'; ?></label>
                                <select id="vdp_invitados" class="vdp-filter-select">
                                    <option value=""><?php echo $is_spanish ? 'Cualquier cantidad' : 'Any amount'; ?></option>
                                    <option value="1-50">1-50</option>
                                    <option value="51-100">51-100</option>
                                    <option value="101-200">101-200</option>
                                    <option value="200+"><?php echo $is_spanish ? 'Más de 200' : '200+'; ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Calificación -->
                <div class="vdp-filter-section">
                    <div class="vdp-section-header" data-target="vdp_calificacion_content">
                        <h4 class="vdp-section-title">
                            <i class="fas fa-star vdp-section-icon"></i>
                            <?php echo $is_spanish ? 'Calificación' : 'Qualification'; ?>
                        </h4>
                        <i class="fas fa-chevron-down vdp-toggle-icon"></i>
                    </div>
                    <div id="vdp_calificacion_content" class="vdp-section-content">
                        <div class="vdp-filters-grid">
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Prioridad' : 'Priority'; ?></label>
                                <select id="vdp_prioridad" class="vdp-filter-select">
                                    <option value=""><?php echo $is_spanish ? 'Todas' : 'All'; ?></option>
                                    <option value="alta"><?php echo $is_spanish ? 'Alta' : 'High'; ?></option>
                                    <option value="media"><?php echo $is_spanish ? 'Media' : 'Medium'; ?></option>
                                    <option value="baja"><?php echo $is_spanish ? 'Baja' : 'Low'; ?></option>
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Valor Potencial' : 'Potential Value'; ?></label>
                                <select id="vdp_valor_potencial" class="vdp-filter-select">
                                    <option value=""><?php echo $is_spanish ? 'Cualquier valor' : 'Any value'; ?></option>
                                    <option value="0-1000">$0 - $1,000</option>
                                    <option value="1000-5000">$1,000 - $5,000</option>
                                    <option value="5000-10000">$5,000 - $10,000</option>
                                    <option value="10000+"><?php echo $is_spanish ? 'Más de $10,000' : '$10,000+'; ?></option>
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Probabilidad' : 'Probability'; ?></label>
                                <select id="vdp_probabilidad" class="vdp-filter-select">
                                    <option value=""><?php echo $is_spanish ? 'Cualquier probabilidad' : 'Any probability'; ?></option>
                                    <option value="0-25">0-25%</option>
                                    <option value="26-50">26-50%</option>
                                    <option value="51-75">51-75%</option>
                                    <option value="76-100">76-100%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Origen -->
                <div class="vdp-filter-section">
                    <div class="vdp-section-header" data-target="vdp_origen_content">
                        <h4 class="vdp-section-title">
                            <i class="fas fa-external-link-alt vdp-section-icon"></i>
                            <?php echo $is_spanish ? 'Origen y Fuente' : 'Origin & Source'; ?>
                        </h4>
                        <i class="fas fa-chevron-down vdp-toggle-icon"></i>
                    </div>
                    <div id="vdp_origen_content" class="vdp-section-content">
                        <div class="vdp-filters-grid">
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Fuente' : 'Source'; ?></label>
                                <select id="vdp_fuente" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Campaña' : 'Campaign'; ?></label>
                                <select id="vdp_campana" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Responsable' : 'Responsible'; ?></label>
                                <select id="vdp_responsable" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Ubicación -->
                <div class="vdp-filter-section">
                    <div class="vdp-section-header" data-target="vdp_ubicacion_content">
                        <h4 class="vdp-section-title">
                            <i class="fas fa-map-marker-alt vdp-section-icon"></i>
                            <?php echo $is_spanish ? 'Ubicación y Venue' : 'Location & Venue'; ?>
                        </h4>
                        <i class="fas fa-chevron-down vdp-toggle-icon"></i>
                    </div>
                    <div id="vdp_ubicacion_content" class="vdp-section-content">
                        <div class="vdp-filters-grid">
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Ubicación' : 'Location'; ?></label>
                                <select id="vdp_ubicacion" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label">Venue</label>
                                <select id="vdp_venue" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Servicios Requeridos' : 'Required Services'; ?></label>
                                <select id="vdp_servicios_requeridos" class="vdp-select2-multi" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Etiquetas -->
                <div class="vdp-filter-section">
                    <div class="vdp-section-header" data-target="vdp_etiquetas_content">
                        <h4 class="vdp-section-title">
                            <i class="fas fa-tags vdp-section-icon"></i>
                            <?php echo $is_spanish ? 'Etiquetas y Notas' : 'Tags & Notes'; ?>
                        </h4>
                        <i class="fas fa-chevron-down vdp-toggle-icon"></i>
                    </div>
                    <div id="vdp_etiquetas_content" class="vdp-section-content">
                        <div class="vdp-filters-grid">
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Etiquetas' : 'Tags'; ?></label>
                                <select id="vdp_etiquetas" class="vdp-select2-tags" multiple>
                                    <!-- Options loaded dynamically -->
                                </select>
                            </div>
                            <div class="vdp-filter-group">
                                <label class="vdp-filter-label"><?php echo $is_spanish ? 'Temporada' : 'Season'; ?></label>
                                <select id="vdp_temporada" class="vdp-filter-select">
                                    <option value=""><?php echo $is_spanish ? 'Cualquier temporada' : 'Any season'; ?></option>
                                    <option value="primavera"><?php echo $is_spanish ? 'Primavera' : 'Spring'; ?></option>
                                    <option value="verano"><?php echo $is_spanish ? 'Verano' : 'Summer'; ?></option>
                                    <option value="otono"><?php echo $is_spanish ? 'Otoño' : 'Fall'; ?></option>
                                    <option value="invierno"><?php echo $is_spanish ? 'Invierno' : 'Winter'; ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="vdp-filters-actions">
                <div class="vdp-filters-buttons">
                    <button type="button" id="vdp_aplicar_filtros" class="vdp-btn vdp-btn-primary">
                        <i class="fas fa-filter"></i> <?php echo $is_spanish ? 'Aplicar Filtros' : 'Apply Filters'; ?>
                    </button>
                    <button type="button" id="vdp_limpiar_filtros" class="vdp-btn vdp-btn-secondary">
                        <i class="fas fa-times"></i> <?php echo $is_spanish ? 'Limpiar Todo' : 'Clear All'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toolbar Principal -->
    <div class="vdp-pipeline-toolbar">
        <div class="vdp-toolbar-left">
            <button type="button" id="vdp_toggle_filters_btn" class="vdp-toggle-filters-btn">
                <i class="fas fa-chevron-down"></i>
                <?php echo $is_spanish ? 'Filtros Avanzados' : 'Advanced Filters'; ?>
                <span class="vdp-active-filters-count" id="vdp_active_filters_count" style="display: none;">0</span>
            </button>
        </div>
        
        <div class="vdp-toolbar-right">
            <button type="button" id="vdp_add_lead_btn" class="vdp-btn vdp-btn-primary">
                <i class="fas fa-plus"></i>
                <?php echo $is_spanish ? 'Nuevo Lead' : 'New Lead'; ?>
            </button>
            <button type="button" id="vdp_export_leads_btn" class="vdp-btn vdp-btn-secondary">
                <i class="fas fa-download"></i>
                <?php echo $is_spanish ? 'Exportar' : 'Export'; ?>
            </button>
        </div>
    </div>
    
    <!-- Pipeline Columns -->
    <div class="vdp-pipeline-container">
        <div class="vdp-pipeline-wrapper">
            <div class="vdp-pipeline-scrollable">
                <div class="vdp-pipeline-columns">
                    <?php foreach ($pipeline_statuses as $status => $config) : ?>
                        <div class="vdp-column" data-status="<?php echo esc_attr($status); ?>">
                            <div class="vdp-column-header">
                                <div class="vdp-column-title">
                                    <i class="<?php echo esc_attr($config['icon']); ?>"></i>
                                    <?php echo esc_html($is_spanish ? $config['label_es'] : $config['label_en']); ?>
                                </div>
                                <span class="vdp-column-count">0</span>
                            </div>
                            <div class="vdp-column-leads">
                                <!-- Lead cards will be populated here -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize enhanced pipeline system
    console.log('VDP Enhanced Pipeline initialized');
    
    // Load initial pipeline data
    if (typeof window.VDPPipeline !== 'undefined') {
        window.VDPPipeline.loadPipelineData();
    }
    
    // Setup filter event handlers
    if (typeof window.VDPFilters !== 'undefined') {
        // Trigger initial load
        setTimeout(function() {
            window.VDPFilters.applyFilters();
        }, 1000);
    }
    
    // Add lead button handler
    $('#vdp_add_lead_btn').on('click', function() {
        // Redirect to lead creation page or show modal
        window.location.href = '/wp-admin/post-new.php?post_type=vdp_lead';
    });
    
    // Export leads button handler
    $('#vdp_export_leads_btn').on('click', function() {
        // Implement export functionality
        alert('<?php echo $is_spanish ? 'Funcionalidad de exportación próximamente' : 'Export functionality coming soon'; ?>');
    });
});
</script>

<style>
/* Basic toolbar styles */
.vdp-pipeline-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px 0;
}

.vdp-toolbar-left,
.vdp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.vdp-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    line-height: 1;
}

.vdp-btn-primary {
    background: #007cba;
    color: white;
}

.vdp-btn-primary:hover {
    background: #005a87;
    transform: translateY(-1px);
}

.vdp-btn-secondary {
    background: #6c757d;
    color: white;
}

.vdp-btn-secondary:hover {
    background: #545b62;
    transform: translateY(-1px);
}
</style>