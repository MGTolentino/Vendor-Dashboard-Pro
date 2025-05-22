<?php
/**
 * Leads content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get current vendor and initialize leads class
$current_user_id = get_current_user_id();
$vendor = vdp_get_current_vendor();

if (!$vendor) {
    echo '<div class="vdp-notice vdp-notice-error">' . __('Vendor not found.', 'vendor-dashboard-pro') . '</div>';
    return;
}

// Initialize VDP_Leads class
$leads_handler = new VDP_Leads();

// Get pagination parameters
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 10;
$offset = ($paged - 1) * $per_page;

// Get filter parameters
$status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : '';
$source_filter = isset($_GET['source_filter']) ? sanitize_text_field($_GET['source_filter']) : '';
$search_filter = isset($_GET['search_filter']) ? sanitize_text_field($_GET['search_filter']) : '';

// Build filters array
$filters = array();
if (!empty($status_filter)) {
    $filters['status'] = $status_filter;
}
if (!empty($source_filter)) {
    $filters['source'] = $source_filter;
}
if (!empty($search_filter)) {
    $filters['search'] = $search_filter;
}

// Get vendor leads
$leads_data = $leads_handler->get_vendor_leads($vendor->ID, $per_page, $offset, $filters);
$leads = $leads_data['leads'];
$total_leads = $leads_data['total'];
$total_pages = ceil($total_leads / $per_page);

// Get lead statistics
$stats = $leads_handler->get_vendor_lead_stats($vendor->ID);

// Initialize total pages and current page for pagination
if (!isset($total_pages)) {
    $total_pages = 1;
}

if (!isset($paged)) {
    $paged = 1;
}

// Get lead status labels and classes
function vdp_get_lead_status_label($status) {
    $labels = array(
        'inicial' => __('Inicial', 'vendor-dashboard-pro'),
        'contactado' => __('Contactado', 'vendor-dashboard-pro'),
        'cita-agendada' => __('Cita Agendada', 'vendor-dashboard-pro'),
        'propuesta-enviada' => __('Propuesta Enviada', 'vendor-dashboard-pro'),
        'negociacion' => __('Negociación', 'vendor-dashboard-pro'),
        'cerrado-ganado' => __('Cerrado Ganado', 'vendor-dashboard-pro'),
        'cerrado-perdido' => __('Cerrado Perdido', 'vendor-dashboard-pro'),
    );
    
    return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
}

function vdp_get_lead_status_class($status) {
    $classes = array(
        'inicial' => 'vdp-status-new',
        'contactado' => 'vdp-status-contacted',
        'cita-agendada' => 'vdp-status-scheduled',
        'propuesta-enviada' => 'vdp-status-proposal',
        'negociacion' => 'vdp-status-negotiation',
        'cerrado-ganado' => 'vdp-status-won',
        'cerrado-perdido' => 'vdp-status-lost',
    );
    
    return isset($classes[$status]) ? $classes[$status] : 'vdp-status-default';
}

function vdp_get_lead_source_label($source) {
    $labels = array(
        'contact_form' => __('Contact Form', 'vendor-dashboard-pro'),
        'website' => __('Website', 'vendor-dashboard-pro'),
        'referral' => __('Referral', 'vendor-dashboard-pro'),
        'social_media' => __('Social Media', 'vendor-dashboard-pro'),
        'google' => __('Google', 'vendor-dashboard-pro'),
        'other' => __('Other', 'vendor-dashboard-pro'),
    );
    
    return isset($labels[$source]) ? $labels[$source] : $source;
}

// Get lead statistics from data
$total_leads_count = $stats['total'];
$inicial_leads = $stats['inicial'] ?? 0;
$contacted_leads = $stats['contactado'] ?? 0;
$scheduled_leads = $stats['cita-agendada'] ?? 0;
$proposal_leads = $stats['propuesta-enviada'] ?? 0;
$negotiation_leads = $stats['negociacion'] ?? 0;
$won_leads = $stats['cerrado-ganado'] ?? 0;
$lost_leads = $stats['cerrado-perdido'] ?? 0;

// Calculate conversion rate
$conversion_rate = $total_leads_count > 0 ? round(($won_leads / $total_leads_count) * 100, 1) : 0;
?>

<div class="vdp-leads-content">
    <div class="vdp-section vdp-leads-overview-section">
        <div class="vdp-leads-stats">
            <!-- Total Leads -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($total_leads_count); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Total Leads', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <!-- Initial Leads -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-new">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($inicial_leads); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Iniciales', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <!-- Contacted -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-contacted">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($contacted_leads); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Contactados', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <!-- In Progress -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-negotiation">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($scheduled_leads + $proposal_leads + $negotiation_leads); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('En Proceso', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <!-- Won -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon vdp-status-won">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($won_leads); ?></div>
                    <div class="vdp-stat-label"><?php esc_html_e('Ganados', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
            
            <!-- Conversion Rate -->
            <div class="vdp-stat-box">
                <div class="vdp-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="vdp-stat-content">
                    <div class="vdp-stat-value"><?php echo esc_html($conversion_rate); ?>%</div>
                    <div class="vdp-stat-label"><?php esc_html_e('Conversion Rate', 'vendor-dashboard-pro'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vdp-section vdp-leads-list-section">
        <div class="vdp-section-header">
            <h2 class="vdp-section-title"><?php esc_html_e('Mis Leads', 'vendor-dashboard-pro'); ?></h2>
            <div class="vdp-section-actions">
                <div class="vdp-filters">
                    <select class="vdp-filter-select" id="lead-status-filter">
                        <option value=""><?php esc_html_e('Todos los Estados', 'vendor-dashboard-pro'); ?></option>
                        <option value="inicial"><?php esc_html_e('Inicial', 'vendor-dashboard-pro'); ?></option>
                        <option value="contactado"><?php esc_html_e('Contactado', 'vendor-dashboard-pro'); ?></option>
                        <option value="cita-agendada"><?php esc_html_e('Cita Agendada', 'vendor-dashboard-pro'); ?></option>
                        <option value="propuesta-enviada"><?php esc_html_e('Propuesta Enviada', 'vendor-dashboard-pro'); ?></option>
                        <option value="negociacion"><?php esc_html_e('Negociación', 'vendor-dashboard-pro'); ?></option>
                        <option value="cerrado-ganado"><?php esc_html_e('Cerrado Ganado', 'vendor-dashboard-pro'); ?></option>
                        <option value="cerrado-perdido"><?php esc_html_e('Cerrado Perdido', 'vendor-dashboard-pro'); ?></option>
                    </select>
                    
                    <div class="vdp-search-filter">
                        <input type="text" class="vdp-search-input" id="lead-search" placeholder="<?php esc_attr_e('Buscar leads...', 'vendor-dashboard-pro'); ?>">
                        <button class="vdp-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="vdp-table-responsive">
            <table class="vdp-table vdp-leads-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Cliente', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Contacto', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Evento', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Fecha', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Estado', 'vendor-dashboard-pro'); ?></th>
                        <th><?php esc_html_e('Acciones', 'vendor-dashboard-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)) : ?>
                        <tr>
                            <td colspan="6" class="vdp-empty-table">
                                <div class="vdp-empty-state">
                                    <div class="vdp-empty-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <p><?php esc_html_e('No tienes leads aún. Los leads aparecerán aquí cuando los clientes se interesen en tus servicios.', 'vendor-dashboard-pro'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($leads as $lead) : ?>
                            <tr class="vdp-lead-row" data-status="<?php echo esc_attr($lead->lead_status); ?>" data-source="<?php echo esc_attr($lead->lead_source ?? 'website'); ?>">
                                <td class="vdp-lead-name">
                                    <a href="<?php echo esc_url(add_query_arg('lead_id', $lead->_ID, vdp_get_dashboard_url('lead-view'))); ?>" class="vdp-lead-view" data-lead-id="<?php echo esc_attr($lead->_ID); ?>">
                                        <?php echo esc_html($lead->lead_name); ?>
                                    </a>
                                </td>
                                <td class="vdp-lead-contact">
                                    <div class="vdp-lead-email">
                                        <i class="fas fa-envelope"></i> <?php echo esc_html($lead->lead_email); ?>
                                    </div>
                                    <?php if (!empty($lead->lead_phone)) : ?>
                                        <div class="vdp-lead-phone">
                                            <i class="fas fa-phone"></i> <?php echo esc_html($lead->lead_phone); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="vdp-lead-source">
                                    <?php echo esc_html($lead->event_name); ?>
                                </td>
                                <td class="vdp-lead-date">
                                    <div class="vdp-lead-date-display">
                                        <?php echo esc_html(date_i18n('M j, Y', strtotime($lead->lead_created_date))); ?>
                                    </div>
                                    <div class="vdp-lead-time-ago">
                                        <?php echo esc_html(human_time_diff(strtotime($lead->lead_created_date), current_time('timestamp')) . ' ago'); ?>
                                    </div>
                                </td>
                                <td class="vdp-lead-status">
                                    <span class="vdp-status-badge <?php echo esc_attr(vdp_get_lead_status_class($lead->lead_status)); ?>">
                                        <?php echo esc_html(vdp_get_lead_status_label($lead->lead_status)); ?>
                                    </span>
                                </td>
                                <td class="vdp-lead-actions">
                                    <div class="vdp-table-actions">
                                        <a href="<?php echo esc_url(add_query_arg('lead_id', $lead->_ID, vdp_get_dashboard_url('lead-view'))); ?>" class="vdp-btn vdp-btn-sm vdp-btn-icon vdp-lead-view" data-lead-id="<?php echo esc_attr($lead->_ID); ?>" title="<?php esc_attr_e('View', 'vendor-dashboard-pro'); ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="vdp-btn vdp-btn-sm vdp-btn-icon vdp-update-lead-status" data-lead-id="<?php echo esc_attr($lead->_ID); ?>" title="<?php esc_attr_e('Update Status', 'vendor-dashboard-pro'); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($leads) && $total_pages > 1) : ?>
            <div class="vdp-pagination">
                <?php
                $current_url = add_query_arg(array(), vdp_get_dashboard_url('leads'));
                
                // Previous page
                if ($paged > 1) {
                    $prev_url = add_query_arg('paged', $paged - 1, $current_url);
                    echo '<a href="' . esc_url($prev_url) . '" class="vdp-pagination-item vdp-pagination-prev">';
                    echo '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'vendor-dashboard-pro');
                    echo '</a>';
                } else {
                    echo '<span class="vdp-pagination-item vdp-pagination-prev vdp-pagination-disabled">';
                    echo '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'vendor-dashboard-pro');
                    echo '</span>';
                }
                
                // Page numbers
                $start_page = max(1, $paged - 2);
                $end_page = min($total_pages, $paged + 2);
                
                if ($start_page > 1) {
                    $first_url = add_query_arg('paged', 1, $current_url);
                    echo '<a href="' . esc_url($first_url) . '" class="vdp-pagination-item">1</a>';
                    
                    if ($start_page > 2) {
                        echo '<span class="vdp-pagination-dots">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $paged) {
                        echo '<span class="vdp-pagination-item vdp-pagination-current">' . esc_html($i) . '</span>';
                    } else {
                        $page_url = add_query_arg('paged', $i, $current_url);
                        echo '<a href="' . esc_url($page_url) . '" class="vdp-pagination-item">' . esc_html($i) . '</a>';
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="vdp-pagination-dots">...</span>';
                    }
                    
                    $last_url = add_query_arg('paged', $total_pages, $current_url);
                    echo '<a href="' . esc_url($last_url) . '" class="vdp-pagination-item">' . esc_html($total_pages) . '</a>';
                }
                
                // Next page
                if ($paged < $total_pages) {
                    $next_url = add_query_arg('paged', $paged + 1, $current_url);
                    echo '<a href="' . esc_url($next_url) . '" class="vdp-pagination-item vdp-pagination-next">';
                    echo esc_html__('Next', 'vendor-dashboard-pro') . ' <i class="fas fa-chevron-right"></i>';
                    echo '</a>';
                } else {
                    echo '<span class="vdp-pagination-item vdp-pagination-next vdp-pagination-disabled">';
                    echo esc_html__('Next', 'vendor-dashboard-pro') . ' <i class="fas fa-chevron-right"></i>';
                    echo '</span>';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Lead Modal -->
    <div class="vdp-modal" id="vdp-lead-modal">
        <div class="vdp-modal-content">
            <div class="vdp-modal-header">
                <h3 class="vdp-modal-title"><?php esc_html_e('Lead Details', 'vendor-dashboard-pro'); ?></h3>
                <button type="button" class="vdp-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="vdp-modal-body">
                <div class="vdp-lead-details">
                    <!-- Lead details will be loaded here via JavaScript -->
                    <div class="vdp-loading"><div class="vdp-loading-spinner"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leads JavaScript -->
<script>
jQuery(document).ready(function($) {
    // Leads filtering
    $('#lead-status-filter').on('change', function() {
        filterLeads();
    });
    
    $('#lead-source-filter').on('change', function() {
        filterLeads();
    });
    
    $('#lead-search').on('keyup', function() {
        filterLeads();
    });
    
    function filterLeads() {
        var status = $('#lead-status-filter').val();
        var source = $('#lead-source-filter').val();
        var search = $('#lead-search').val().toLowerCase();
        
        $('.vdp-lead-row').each(function() {
            var $row = $(this);
            var leadStatus = $row.data('status');
            var leadSource = $row.data('source');
            var leadName = $row.find('.vdp-lead-name').text().toLowerCase();
            var leadContact = $row.find('.vdp-lead-contact').text().toLowerCase();
            
            var statusMatch = !status || status === leadStatus;
            var sourceMatch = !source || source === leadSource;
            var searchMatch = !search || leadName.indexOf(search) !== -1 || leadContact.indexOf(search) !== -1;
            
            if (statusMatch && sourceMatch && searchMatch) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }
    
    // Lead modal
    $('.vdp-lead-view, .vdp-lead-edit').on('click', function(e) {
        e.preventDefault();
        
        var leadId = $(this).data('lead-id');
        var isEdit = $(this).hasClass('vdp-lead-edit');
        
        // Open the modal
        $('#vdp-lead-modal').addClass('vdp-modal-open');
        
        // In a real implementation, you would load the lead details via AJAX
        // For this demo, we'll simulate loading with a timeout
        $('.vdp-lead-details').html('<div class="vdp-loading"><div class="vdp-loading-spinner"></div></div>');
        
        setTimeout(function() {
            // Find the lead in our demo data
            var lead = null;
            $('.vdp-lead-row').each(function() {
                var $row = $(this);
                if ($row.find('.vdp-lead-view').data('lead-id') == leadId) {
                    lead = {
                        id: leadId,
                        name: $row.find('.vdp-lead-name a').text().trim(),
                        email: $row.find('.vdp-lead-email').text().trim().replace('✉️ ', ''),
                        phone: $row.find('.vdp-lead-phone').text().trim().replace('📞 ', ''),
                        status: $row.data('status'),
                        source: $row.data('source'),
                        date: $row.find('.vdp-lead-date-display').text().trim(),
                        message: 'This is a sample lead message. In a real implementation, this would contain the actual message from the lead.'
                    };
                    return false;
                }
            });
            
            if (!lead) {
                $('.vdp-lead-details').html('<div class="vdp-notice vdp-notice-error">Lead not found.</div>');
                return;
            }
            
            if (isEdit) {
                // Show edit form
                var html = '<form class="vdp-lead-form">';
                html += '<div class="vdp-form-row"><label>Name</label><input type="text" name="name" value="' + lead.name + '"></div>';
                html += '<div class="vdp-form-row"><label>Email</label><input type="email" name="email" value="' + lead.email + '"></div>';
                html += '<div class="vdp-form-row"><label>Phone</label><input type="text" name="phone" value="' + lead.phone + '"></div>';
                html += '<div class="vdp-form-row"><label>Status</label><select name="status">';
                html += '<option value="new"' + (lead.status === 'new' ? ' selected' : '') + '>New</option>';
                html += '<option value="contacted"' + (lead.status === 'contacted' ? ' selected' : '') + '>Contacted</option>';
                html += '<option value="qualified"' + (lead.status === 'qualified' ? ' selected' : '') + '>Qualified</option>';
                html += '<option value="converted"' + (lead.status === 'converted' ? ' selected' : '') + '>Converted</option>';
                html += '<option value="lost"' + (lead.status === 'lost' ? ' selected' : '') + '>Lost</option>';
                html += '</select></div>';
                html += '<div class="vdp-form-row"><label>Source</label><select name="source">';
                html += '<option value="contact_form"' + (lead.source === 'contact_form' ? ' selected' : '') + '>Contact Form</option>';
                html += '<option value="website"' + (lead.source === 'website' ? ' selected' : '') + '>Website</option>';
                html += '<option value="referral"' + (lead.source === 'referral' ? ' selected' : '') + '>Referral</option>';
                html += '<option value="social_media"' + (lead.source === 'social_media' ? ' selected' : '') + '>Social Media</option>';
                html += '<option value="google"' + (lead.source === 'google' ? ' selected' : '') + '>Google</option>';
                html += '<option value="other"' + (lead.source === 'other' ? ' selected' : '') + '>Other</option>';
                html += '</select></div>';
                html += '<div class="vdp-form-row"><label>Message</label><textarea name="message">' + lead.message + '</textarea></div>';
                html += '<div class="vdp-form-actions">';
                html += '<button type="button" class="vdp-btn vdp-btn-secondary vdp-modal-close">Cancel</button>';
                html += '<button type="submit" class="vdp-btn vdp-btn-primary">Save Changes</button>';
                html += '</div>';
                html += '</form>';
                
                $('.vdp-lead-details').html(html);
                
                // Handle form submission
                $('.vdp-lead-form').on('submit', function(e) {
                    e.preventDefault();
                    alert('In a real implementation, this would save the lead data.');
                    $('#vdp-lead-modal').removeClass('vdp-modal-open');
                });
            } else {
                // Show lead details
                var statusClass = 'vdp-status-' + lead.status;
                var statusLabel = '';
                switch (lead.status) {
                    case 'new': statusLabel = 'New'; break;
                    case 'contacted': statusLabel = 'Contacted'; break;
                    case 'qualified': statusLabel = 'Qualified'; break;
                    case 'converted': statusLabel = 'Converted'; break;
                    case 'lost': statusLabel = 'Lost'; break;
                    default: statusLabel = lead.status;
                }
                
                var sourceLabel = '';
                switch (lead.source) {
                    case 'contact_form': sourceLabel = 'Contact Form'; break;
                    case 'website': sourceLabel = 'Website'; break;
                    case 'referral': sourceLabel = 'Referral'; break;
                    case 'social_media': sourceLabel = 'Social Media'; break;
                    case 'google': sourceLabel = 'Google'; break;
                    case 'other': sourceLabel = 'Other'; break;
                    default: sourceLabel = lead.source;
                }
                
                var html = '<div class="vdp-lead-info">';
                html += '<div class="vdp-lead-info-header">';
                html += '<h3>' + lead.name + '</h3>';
                html += '<span class="vdp-status-badge ' + statusClass + '">' + statusLabel + '</span>';
                html += '</div>';
                
                html += '<div class="vdp-lead-info-meta">';
                html += '<div><strong>Source:</strong> ' + sourceLabel + '</div>';
                html += '<div><strong>Date:</strong> ' + lead.date + '</div>';
                html += '</div>';
                
                html += '<div class="vdp-lead-info-contact">';
                html += '<div><i class="fas fa-envelope"></i> <a href="mailto:' + lead.email + '">' + lead.email + '</a></div>';
                if (lead.phone) {
                    html += '<div><i class="fas fa-phone"></i> <a href="tel:' + lead.phone + '">' + lead.phone + '</a></div>';
                }
                html += '</div>';
                
                html += '<div class="vdp-lead-info-message">';
                html += '<h4>Message</h4>';
                html += '<p>' + lead.message + '</p>';
                html += '</div>';
                
                html += '<div class="vdp-lead-info-actions">';
                html += '<button class="vdp-btn vdp-btn-primary vdp-lead-edit-btn" data-lead-id="' + lead.id + '"><i class="fas fa-edit"></i> Edit Lead</button>';
                html += '<button class="vdp-btn vdp-btn-secondary vdp-lead-status-btn" data-lead-id="' + lead.id + '"><i class="fas fa-exchange-alt"></i> Change Status</button>';
                html += '</div>';
                
                html += '</div>';
                
                $('.vdp-lead-details').html(html);
                
                // Handle edit button click
                $('.vdp-lead-edit-btn').on('click', function() {
                    var leadId = $(this).data('lead-id');
                    $('.vdp-lead-edit[data-lead-id="' + leadId + '"]').trigger('click');
                });
                
                // Handle status change button click
                $('.vdp-lead-status-btn').on('click', function() {
                    alert('In a real implementation, this would open a status change dialog.');
                });
            }
        }, 500);
    });
    
    // Close the modal when clicking the close button or outside the modal
    $('.vdp-modal-close').on('click', function() {
        $('#vdp-lead-modal').removeClass('vdp-modal-open');
    });
    
    $(document).on('click', function(e) {
        if ($(e.target).is('.vdp-modal')) {
            $('.vdp-modal').removeClass('vdp-modal-open');
        }
    });
    
    // Add lead button
    $('.vdp-add-lead-btn').on('click', function(e) {
        e.preventDefault();
        alert('In a real implementation, this would open a form to add a new lead.');
    });
    
    // Delete lead button
    $('.vdp-delete-lead').on('click', function() {
        var leadId = $(this).data('lead-id');
        if (confirm('Are you sure you want to delete this lead?')) {
            alert('In a real implementation, this would delete the lead with ID ' + leadId);
        }
    });
});
</script>

<style>
/* Leads styles */
.vdp-leads-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.vdp-leads-overview-section {
    margin-bottom: 30px;
}

.vdp-status-new {
    color: #3483fa;
    background-color: rgba(52, 131, 250, 0.1);
}

.vdp-status-contacted {
    color: #f5a623;
    background-color: rgba(245, 166, 35, 0.1);
}

.vdp-status-qualified {
    color: #39b54a;
    background-color: rgba(57, 181, 74, 0.1);
}

.vdp-status-converted {
    color: #27ae60;
    background-color: rgba(39, 174, 96, 0.1);
}

.vdp-status-lost {
    color: #e74c3c;
    background-color: rgba(231, 76, 60, 0.1);
}

.vdp-status-scheduled {
    color: #17a2b8;
    background-color: rgba(23, 162, 184, 0.1);
}

.vdp-status-proposal {
    color: #6f42c1;
    background-color: rgba(111, 66, 193, 0.1);
}

.vdp-status-negotiation {
    color: #fd7e14;
    background-color: rgba(253, 126, 20, 0.1);
}

.vdp-status-won {
    color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
}

.vdp-lead-contact {
    line-height: 1.5;
}

.vdp-lead-email, .vdp-lead-phone {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.vdp-lead-time-ago {
    font-size: 0.8em;
    color: #888;
}

/* Lead modal styles */
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
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: vdp-modal-in 0.3s ease-out;
}

@keyframes vdp-modal-in {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
    overflow-y: auto;
    max-height: calc(80vh - 70px);
}

.vdp-lead-info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.vdp-lead-info-header h3 {
    margin: 0;
    font-size: 1.4em;
}

.vdp-lead-info-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    color: #666;
    font-size: 0.9em;
}

.vdp-lead-info-contact {
    background-color: #f9f9f9;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.vdp-lead-info-contact div {
    margin-bottom: 5px;
}

.vdp-lead-info-contact div:last-child {
    margin-bottom: 0;
}

.vdp-lead-info-contact a {
    color: #3483fa;
    text-decoration: none;
}

.vdp-lead-info-contact a:hover {
    text-decoration: underline;
}

.vdp-lead-info-message {
    margin-bottom: 20px;
}

.vdp-lead-info-message h4 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.1em;
}

.vdp-lead-info-message p {
    margin: 0;
    line-height: 1.5;
}

.vdp-lead-info-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.vdp-lead-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.vdp-form-row {
    display: flex;
    flex-direction: column;
}

.vdp-form-row label {
    font-weight: 600;
    margin-bottom: 5px;
}

.vdp-form-row input, 
.vdp-form-row select, 
.vdp-form-row textarea {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.vdp-form-row textarea {
    min-height: 100px;
    resize: vertical;
}

.vdp-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}
</style>