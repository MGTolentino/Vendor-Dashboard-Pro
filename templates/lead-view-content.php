<?php
/**
 * Lead view content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get current vendor and lead ID
$current_user_id = get_current_user_id();
$vendor = vdp_get_current_vendor();
$lead_id = isset($_GET['lead_id']) ? intval($_GET['lead_id']) : 0;

if (!$vendor || !$lead_id) {
    echo '<div class="vdp-notice vdp-notice-error">' . __('Lead no encontrado.', 'vendor-dashboard-pro') . '</div>';
    return;
}

// Initialize VDP_Leads class and get lead details
$leads_handler = new VDP_Leads();
$lead = $leads_handler->get_lead_details($lead_id);

// Check if vendor can access this lead
if (!$lead || !$leads_handler->can_access_lead($vendor->ID, $lead_id)) {
    echo '<div class="vdp-notice vdp-notice-error">' . __('No tienes permisos para ver este lead.', 'vendor-dashboard-pro') . '</div>';
    return;
}

// Get lead status labels and classes
function vdp_get_lead_status_label($status) {
    $labels = array(
        'nuevo' => __('Nuevo', 'vendor-dashboard-pro'),
        'contactado' => __('Contactado', 'vendor-dashboard-pro'),
        'interesado' => __('Interesado', 'vendor-dashboard-pro'),
        'contratado' => __('Contratado', 'vendor-dashboard-pro'),
        'perdido' => __('Perdido', 'vendor-dashboard-pro'),
    );
    
    return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
}

function vdp_get_lead_status_class($status) {
    $classes = array(
        'nuevo' => 'vdp-status-new',
        'contactado' => 'vdp-status-contacted',
        'interesado' => 'vdp-status-qualified',
        'contratado' => 'vdp-status-converted',
        'perdido' => 'vdp-status-lost',
    );
    
    return isset($classes[$status]) ? $classes[$status] : 'vdp-status-default';
}
?>

<div class="vdp-lead-view">
    <!-- Lead Header -->
    <div class="vdp-section vdp-lead-header-section">
        <div class="vdp-lead-header">
            <div class="vdp-lead-header-info">
                <h2 class="vdp-lead-name"><?php echo esc_html($lead->lead_name); ?></h2>
                <div class="vdp-lead-meta">
                    <span class="vdp-lead-event">
                        <i class="fas fa-calendar"></i>
                        <?php echo esc_html($lead->event_name); ?>
                    </span>
                    <span class="vdp-lead-date">
                        <i class="fas fa-clock"></i>
                        <?php echo esc_html(date_i18n('M j, Y - g:i A', strtotime($lead->lead_created_date))); ?>
                    </span>
                </div>
            </div>
            <div class="vdp-lead-header-actions">
                <span class="vdp-status-badge <?php echo esc_attr(vdp_get_lead_status_class($lead->lead_status)); ?>">
                    <?php echo esc_html(vdp_get_lead_status_label($lead->lead_status)); ?>
                </span>
                <button type="button" class="vdp-btn vdp-btn-primary vdp-update-status-btn" data-lead-id="<?php echo esc_attr($lead->_ID); ?>">
                    <i class="fas fa-edit"></i>
                    <?php esc_html_e('Cambiar Estado', 'vendor-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Lead Details -->
    <div class="vdp-section vdp-lead-details-section">
        <div class="vdp-lead-details-grid">
            <!-- Contact Information -->
            <div class="vdp-lead-card">
                <div class="vdp-card-header">
                    <h3 class="vdp-card-title">
                        <i class="fas fa-user"></i>
                        <?php esc_html_e('Información de Contacto', 'vendor-dashboard-pro'); ?>
                    </h3>
                </div>
                <div class="vdp-card-content">
                    <div class="vdp-contact-info">
                        <div class="vdp-contact-item">
                            <div class="vdp-contact-label">
                                <i class="fas fa-envelope"></i>
                                <?php esc_html_e('Email:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-contact-value">
                                <a href="mailto:<?php echo esc_attr($lead->lead_email); ?>">
                                    <?php echo esc_html($lead->lead_email); ?>
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($lead->lead_phone)) : ?>
                        <div class="vdp-contact-item">
                            <div class="vdp-contact-label">
                                <i class="fas fa-phone"></i>
                                <?php esc_html_e('Teléfono:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-contact-value">
                                <a href="tel:<?php echo esc_attr($lead->lead_phone); ?>">
                                    <?php echo esc_html($lead->lead_phone); ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->lead_company)) : ?>
                        <div class="vdp-contact-item">
                            <div class="vdp-contact-label">
                                <i class="fas fa-building"></i>
                                <?php esc_html_e('Empresa:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-contact-value">
                                <?php echo esc_html($lead->lead_company); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="vdp-lead-card">
                <div class="vdp-card-header">
                    <h3 class="vdp-card-title">
                        <i class="fas fa-calendar-alt"></i>
                        <?php esc_html_e('Información del Evento', 'vendor-dashboard-pro'); ?>
                    </h3>
                </div>
                <div class="vdp-card-content">
                    <div class="vdp-event-info">
                        <div class="vdp-event-item">
                            <div class="vdp-event-label">
                                <?php esc_html_e('Evento:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-event-value">
                                <?php echo esc_html($lead->event_name); ?>
                            </div>
                        </div>
                        <?php if (!empty($lead->event_date)) : ?>
                        <div class="vdp-event-item">
                            <div class="vdp-event-label">
                                <?php esc_html_e('Fecha del Evento:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-event-value">
                                <?php echo esc_html(date_i18n('M j, Y', strtotime($lead->event_date))); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->event_location)) : ?>
                        <div class="vdp-event-item">
                            <div class="vdp-event-label">
                                <?php esc_html_e('Ubicación:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-event-value">
                                <?php echo esc_html($lead->event_location); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->event_guests)) : ?>
                        <div class="vdp-event-item">
                            <div class="vdp-event-label">
                                <?php esc_html_e('Número de Invitados:', 'vendor-dashboard-pro'); ?>
                            </div>
                            <div class="vdp-event-value">
                                <?php echo esc_html($lead->event_guests); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Message -->
    <?php if (!empty($lead->lead_message)) : ?>
    <div class="vdp-section vdp-lead-message-section">
        <div class="vdp-lead-card">
            <div class="vdp-card-header">
                <h3 class="vdp-card-title">
                    <i class="fas fa-comment"></i>
                    <?php esc_html_e('Mensaje del Cliente', 'vendor-dashboard-pro'); ?>
                </h3>
            </div>
            <div class="vdp-card-content">
                <div class="vdp-lead-message">
                    <?php echo wp_kses_post(wpautop($lead->lead_message)); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lead Actions -->
    <div class="vdp-section vdp-lead-actions-section">
        <div class="vdp-lead-actions">
            <a href="<?php echo esc_url(vdp_get_dashboard_url('leads')); ?>" class="vdp-btn vdp-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?php esc_html_e('Volver a Leads', 'vendor-dashboard-pro'); ?>
            </a>
            <a href="mailto:<?php echo esc_attr($lead->lead_email); ?>" class="vdp-btn vdp-btn-primary">
                <i class="fas fa-envelope"></i>
                <?php esc_html_e('Enviar Email', 'vendor-dashboard-pro'); ?>
            </a>
            <?php if (!empty($lead->lead_phone)) : ?>
            <a href="tel:<?php echo esc_attr($lead->lead_phone); ?>" class="vdp-btn vdp-btn-success">
                <i class="fas fa-phone"></i>
                <?php esc_html_e('Llamar', 'vendor-dashboard-pro'); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="vdp-modal" id="vdp-status-modal">
    <div class="vdp-modal-content">
        <div class="vdp-modal-header">
            <h3 class="vdp-modal-title"><?php esc_html_e('Cambiar Estado del Lead', 'vendor-dashboard-pro'); ?></h3>
            <button type="button" class="vdp-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="vdp-modal-body">
            <form id="vdp-status-form">
                <div class="vdp-form-row">
                    <label for="lead-status"><?php esc_html_e('Nuevo Estado:', 'vendor-dashboard-pro'); ?></label>
                    <select name="lead_status" id="lead-status" class="vdp-form-control">
                        <option value="nuevo" <?php selected($lead->lead_status, 'nuevo'); ?>><?php esc_html_e('Nuevo', 'vendor-dashboard-pro'); ?></option>
                        <option value="contactado" <?php selected($lead->lead_status, 'contactado'); ?>><?php esc_html_e('Contactado', 'vendor-dashboard-pro'); ?></option>
                        <option value="interesado" <?php selected($lead->lead_status, 'interesado'); ?>><?php esc_html_e('Interesado', 'vendor-dashboard-pro'); ?></option>
                        <option value="contratado" <?php selected($lead->lead_status, 'contratado'); ?>><?php esc_html_e('Contratado', 'vendor-dashboard-pro'); ?></option>
                        <option value="perdido" <?php selected($lead->lead_status, 'perdido'); ?>><?php esc_html_e('Perdido', 'vendor-dashboard-pro'); ?></option>
                    </select>
                </div>
                <div class="vdp-form-row">
                    <label for="status-notes"><?php esc_html_e('Notas (Opcional):', 'vendor-dashboard-pro'); ?></label>
                    <textarea name="status_notes" id="status-notes" class="vdp-form-control" rows="3" placeholder="<?php esc_attr_e('Agregar notas sobre el cambio de estado...', 'vendor-dashboard-pro'); ?>"></textarea>
                </div>
                <div class="vdp-form-actions">
                    <button type="button" class="vdp-btn vdp-btn-secondary vdp-modal-close">
                        <?php esc_html_e('Cancelar', 'vendor-dashboard-pro'); ?>
                    </button>
                    <button type="submit" class="vdp-btn vdp-btn-primary">
                        <?php esc_html_e('Actualizar Estado', 'vendor-dashboard-pro'); ?>
                    </button>
                </div>
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead->_ID); ?>">
                <input type="hidden" name="action" value="vdp_update_lead_status">
                <?php wp_nonce_field('vdp_update_lead_status', 'vdp_nonce'); ?>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Open status update modal
    $('.vdp-update-status-btn').on('click', function() {
        $('#vdp-status-modal').addClass('vdp-modal-open');
    });
    
    // Close modal
    $('.vdp-modal-close').on('click', function() {
        $('.vdp-modal').removeClass('vdp-modal-open');
    });
    
    // Close modal when clicking outside
    $(document).on('click', function(e) {
        if ($(e.target).is('.vdp-modal')) {
            $('.vdp-modal').removeClass('vdp-modal-open');
        }
    });
    
    // Handle status update form
    $('#vdp-status-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: vdp_vars.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#vdp-status-form button[type="submit"]').prop('disabled', true).text('<?php esc_html_e('Actualizando...', 'vendor-dashboard-pro'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page to show updated status
                    location.reload();
                } else {
                    alert(response.data || '<?php esc_html_e('Error al actualizar el estado.', 'vendor-dashboard-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error al procesar la solicitud.', 'vendor-dashboard-pro'); ?>');
            },
            complete: function() {
                $('#vdp-status-form button[type="submit"]').prop('disabled', false).text('<?php esc_html_e('Actualizar Estado', 'vendor-dashboard-pro'); ?>');
            }
        });
    });
});
</script>

<style>
/* Lead View Styles */
.vdp-lead-view {
    max-width: 1200px;
    margin: 0 auto;
}

.vdp-lead-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.vdp-lead-name {
    margin: 0 0 10px 0;
    font-size: 1.8em;
    color: #333;
}

.vdp-lead-meta {
    display: flex;
    gap: 20px;
    color: #666;
    font-size: 0.9em;
}

.vdp-lead-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.vdp-lead-header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.vdp-lead-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.vdp-lead-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.vdp-card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.vdp-card-title {
    margin: 0;
    font-size: 1.1em;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.vdp-card-content {
    padding: 20px;
}

.vdp-contact-item,
.vdp-event-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.vdp-contact-item:last-child,
.vdp-event-item:last-child {
    border-bottom: none;
}

.vdp-contact-label,
.vdp-event-label {
    font-weight: 600;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
    min-width: 100px;
}

.vdp-contact-value,
.vdp-event-value {
    color: #333;
    text-align: right;
}

.vdp-contact-value a,
.vdp-event-value a {
    color: #3483fa;
    text-decoration: none;
}

.vdp-contact-value a:hover,
.vdp-event-value a:hover {
    text-decoration: underline;
}

.vdp-lead-message {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    border-left: 4px solid #3483fa;
    line-height: 1.6;
    color: #333;
}

.vdp-lead-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    padding: 25px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Status badges */
.vdp-status-new {
    background-color: #3483fa;
    color: white;
}

.vdp-status-contacted {
    background-color: #f5a623;
    color: white;
}

.vdp-status-qualified {
    background-color: #39b54a;
    color: white;
}

.vdp-status-converted {
    background-color: #27ae60;
    color: white;
}

.vdp-status-lost {
    background-color: #e74c3c;
    color: white;
}

/* Modal styles */
.vdp-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.vdp-modal-open {
    display: flex;
    align-items: center;
    justify-content: center;
}

.vdp-modal-content {
    background-color: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.vdp-modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.vdp-modal-title {
    margin: 0;
    font-size: 1.2em;
}

.vdp-modal-close {
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.vdp-modal-close:hover {
    background-color: #f5f5f5;
}

.vdp-modal-body {
    padding: 20px;
}

.vdp-form-row {
    margin-bottom: 15px;
}

.vdp-form-row label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.vdp-form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
    box-sizing: border-box;
}

.vdp-form-control:focus {
    outline: none;
    border-color: #3483fa;
    box-shadow: 0 0 0 2px rgba(52, 131, 250, 0.2);
}

.vdp-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .vdp-lead-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .vdp-lead-header-actions {
        justify-content: space-between;
    }
    
    .vdp-lead-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .vdp-lead-details-grid {
        grid-template-columns: 1fr;
    }
    
    .vdp-contact-item,
    .vdp-event-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .vdp-contact-label,
    .vdp-event-label {
        min-width: auto;
    }
    
    .vdp-contact-value,
    .vdp-event-value {
        text-align: left;
    }
    
    .vdp-lead-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>