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
            <input type="text" id="vdp_quick_search" placeholder="<?php esc_attr_e('Search...', 'vendor-dashboard-pro'); ?>" class="vdp-filter-input">
            
            <select id="vdp_period_filter" class="vdp-filter-select">
                <option value=""><?php esc_html_e('All periods', 'vendor-dashboard-pro'); ?></option>
                <option value="today"><?php esc_html_e('Today', 'vendor-dashboard-pro'); ?></option>
                <option value="this_week"><?php esc_html_e('This week', 'vendor-dashboard-pro'); ?></option>
                <option value="this_month"><?php esc_html_e('This month', 'vendor-dashboard-pro'); ?></option>
                <option value="this_year"><?php esc_html_e('This year', 'vendor-dashboard-pro'); ?></option>
                <option value="custom"><?php esc_html_e('Custom range', 'vendor-dashboard-pro'); ?></option>
            </select>
            
            <div id="vdp_custom_date_range" style="display:none;">
                <input type="text" id="vdp_date_range" class="vdp-filter-input" title="<?php esc_attr_e('Date range', 'vendor-dashboard-pro'); ?>" placeholder="<?php esc_attr_e('Select date range', 'vendor-dashboard-pro'); ?>">
            </div>
            
            <select id="vdp_service_filter" class="vdp-filter-select">
                <option value=""><?php esc_html_e('All services', 'vendor-dashboard-pro'); ?></option>
                <!-- Los servicios se cargarán dinámicamente -->
            </select>
            
            <select id="vdp_priority_filter" class="vdp-filter-select">
                <option value=""><?php esc_html_e('All priorities', 'vendor-dashboard-pro'); ?></option>
                <option value="alta"><?php esc_html_e('High', 'vendor-dashboard-pro'); ?></option>
                <option value="media"><?php esc_html_e('Medium', 'vendor-dashboard-pro'); ?></option>
                <option value="baja"><?php esc_html_e('Low', 'vendor-dashboard-pro'); ?></option>
            </select>
            
            <button id="vdp_apply_filters" class="vdp-btn vdp-btn-secondary"><?php esc_html_e('Filter', 'vendor-dashboard-pro'); ?></button>
            <button id="vdp_clear_filters" class="vdp-btn vdp-btn-text"><?php esc_html_e('Clear', 'vendor-dashboard-pro'); ?></button>
        </div>
        
        <div class="vdp-pipeline-stats">
            <?php esc_html_e('Total:', 'vendor-dashboard-pro'); ?> <span id="vdp_total_leads">0</span>
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

    <!-- Modal simplificado para agregar lead -->
    <div id="vdp_lead_modal" class="vdp-modal">
        <div class="vdp-modal-content">
            <div class="vdp-modal-header">
                <h2><?php esc_html_e('Add Lead', 'vendor-dashboard-pro'); ?></h2>
                <button class="vdp-close-modal">&times;</button>
            </div>
            
            <form id="vdp_lead_form" class="vdp-simple-form">
                <!-- Información básica del lead -->
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Name', 'vendor-dashboard-pro'); ?> *</label>
                        <input type="text" name="lead_nombre" required>
                    </div>
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Last Name', 'vendor-dashboard-pro'); ?> *</label>
                        <input type="text" name="lead_apellido" required>
                    </div>
                </div>
                
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Phone', 'vendor-dashboard-pro'); ?> *</label>
                        <input type="tel" name="lead_celular" required>
                    </div>
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Email', 'vendor-dashboard-pro'); ?> *</label>
                        <input type="email" name="lead_email" required>
                    </div>
                </div>
                
                <div class="vdp-form-row">
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Service URL', 'vendor-dashboard-pro'); ?></label>
                        <input type="url" name="service_url" placeholder="<?php esc_attr_e('URL of the service they\'re interested in', 'vendor-dashboard-pro'); ?>">
                    </div>
                    <div class="vdp-form-field">
                        <label><?php esc_html_e('Status', 'vendor-dashboard-pro'); ?></label>
                        <select name="evento_status">
                            <?php foreach ($vdp_status_options as $status_value => $status_label) : ?>
                                <option value="<?php echo esc_attr($status_value); ?>" <?php selected($status_value, 'nuevo'); ?>>
                                    <?php echo esc_html($status_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="vdp-form-row">
                    <div class="vdp-form-field full-width">
                        <label><?php esc_html_e('Notes', 'vendor-dashboard-pro'); ?></label>
                        <textarea name="lead_notas" rows="3" placeholder="<?php esc_attr_e('Additional information about this lead...', 'vendor-dashboard-pro'); ?>"></textarea>
                    </div>
                </div>
                
                <div class="vdp-form-actions">
                    <button type="button" class="vdp-btn vdp-btn-secondary vdp-close-modal"><?php esc_html_e('Cancel', 'vendor-dashboard-pro'); ?></button>
                    <button type="submit" class="vdp-btn vdp-btn-primary"><?php esc_html_e('Add Lead', 'vendor-dashboard-pro'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

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