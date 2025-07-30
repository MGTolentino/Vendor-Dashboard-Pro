<?php
/**
 * VDP Filters Class
 * Complete filtering system for Vendor Dashboard Pro Pipeline
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class VDP_Filters {
    private $query_handler;

    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_ajax_vdp_filter_leads', array($this, 'handle_filter_leads'));
        add_action('wp_ajax_vdp_update_lead_status', array($this, 'handle_update_lead_status'));
        add_action('wp_ajax_vdp_get_filter_options', array($this, 'handle_get_filter_options'));
    }
    
    public function handle_filter_leads() {
        check_ajax_referer('vdp_leads_nonce', 'nonce');

        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }

        // Recopilar todos los parámetros de filtro
        $args = array(
            'vendor_id' => $vendor->get_id(),
            
            // Fechas de ingreso (rango)
            'fecha_inicio' => isset($_POST['fecha_ingreso_inicio']) ? sanitize_text_field($_POST['fecha_ingreso_inicio']) : '',
            'fecha_fin' => isset($_POST['fecha_ingreso_fin']) ? sanitize_text_field($_POST['fecha_ingreso_fin']) : '',
            
            // Fechas de evento
            'fecha_evento_inicio' => isset($_POST['fecha_evento_inicio']) ? sanitize_text_field($_POST['fecha_evento_inicio']) : '',
            'fecha_evento_fin' => isset($_POST['fecha_evento_fin']) ? sanitize_text_field($_POST['fecha_evento_fin']) : '',
            
            // Búsqueda general
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            
            // Ordenamiento
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'fecha_solicitud',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC',
            
            // Paginación
            'paged' => isset($_POST['paged']) ? absint($_POST['paged']) : 1,
            'per_page' => isset($_POST['per_page']) ? absint($_POST['per_page']) : 50,
            
            // Filtros de categorización
            'tipo_evento' => isset($_POST['tipo_evento']) && is_array($_POST['tipo_evento']) ? 
                array_map('sanitize_text_field', $_POST['tipo_evento']) : array(),
            'status' => isset($_POST['status']) && is_array($_POST['status']) ? 
                array_map('sanitize_text_field', $_POST['status']) : array(),
            'invitados' => isset($_POST['invitados']) ? sanitize_text_field($_POST['invitados']) : '',
            
            // Filtros de calificación
            'prioridad' => isset($_POST['prioridad']) ? sanitize_text_field($_POST['prioridad']) : '',
            'valor_potencial' => isset($_POST['valor_potencial']) ? sanitize_text_field($_POST['valor_potencial']) : '',
            'probabilidad' => isset($_POST['probabilidad']) ? sanitize_text_field($_POST['probabilidad']) : '',
            
            // Filtros de seguimiento
            'responsable' => isset($_POST['responsable']) && is_array($_POST['responsable']) ? 
                array_map('sanitize_text_field', $_POST['responsable']) : array(),
            'ultima_interaccion' => isset($_POST['ultima_interaccion']) ? sanitize_text_field($_POST['ultima_interaccion']) : '',
            'tiempo_sin_actividad' => isset($_POST['tiempo_sin_actividad']) ? sanitize_text_field($_POST['tiempo_sin_actividad']) : '',
            'proxima_accion' => isset($_POST['proxima_accion']) ? sanitize_text_field($_POST['proxima_accion']) : '',
            
            // Filtros de origen
            'fuente' => isset($_POST['fuente']) && is_array($_POST['fuente']) ? 
                array_map('sanitize_text_field', $_POST['fuente']) : array(),
            'campana' => isset($_POST['campana']) && is_array($_POST['campana']) ? 
                array_map('sanitize_text_field', $_POST['campana']) : array(),
            
            // Filtros demográficos
            'ubicacion' => isset($_POST['ubicacion']) && is_array($_POST['ubicacion']) ? 
                array_map('sanitize_text_field', $_POST['ubicacion']) : array(),
            'industria' => isset($_POST['industria']) && is_array($_POST['industria']) ? 
                array_map('sanitize_text_field', $_POST['industria']) : array(),
            
            // Filtros de conversión
            'estado_propuesta' => isset($_POST['estado_propuesta']) ? sanitize_text_field($_POST['estado_propuesta']) : '',
            'rango_cotizacion' => isset($_POST['rango_cotizacion']) ? sanitize_text_field($_POST['rango_cotizacion']) : '',
            
            // Filtros de eventos específicos
            'temporada' => isset($_POST['temporada']) ? sanitize_text_field($_POST['temporada']) : '',
            'servicios_requeridos' => isset($_POST['servicios_requeridos']) && is_array($_POST['servicios_requeridos']) ? 
                array_map('sanitize_text_field', $_POST['servicios_requeridos']) : array(),
            'venue' => isset($_POST['venue']) && is_array($_POST['venue']) ? 
                array_map('sanitize_text_field', $_POST['venue']) : array(),
            
            // Filtros de etiquetas
            'etiquetas' => isset($_POST['etiquetas']) && is_array($_POST['etiquetas']) ? 
                array_map('sanitize_text_field', $_POST['etiquetas']) : array(),
        );

        try {
            $results = $this->get_filtered_leads($args);
            $total = $this->get_total_filtered_leads($args);

            wp_send_json_success(array(
                'data' => $results,
                'total' => $total,
                'pages' => ceil($total / $args['per_page']),
                'applied_filters' => $this->get_applied_filters_summary($args)
            ));
        } catch (Exception $e) {
            error_log('VDP Filter Error: ' . $e->getMessage());
            wp_send_json_error('Error filtering leads: ' . $e->getMessage());
        }
    }

    public function handle_update_lead_status() {
        check_ajax_referer('vdp_leads_nonce', 'nonce');

        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$lead_id || !$new_status) {
            wp_send_json_error('Missing lead ID or status');
        }

        // Verify lead belongs to vendor
        $lead = get_post($lead_id);
        if (!$lead || $lead->post_author != $vendor->get_id()) {
            wp_send_json_error('Unauthorized');
        }

        // Update lead status
        $updated = update_post_meta($lead_id, 'status', $new_status);
        
        if ($updated !== false) {
            // Log status change
            $this->log_status_change($lead_id, $new_status, $vendor->get_id());
            
            wp_send_json_success(array(
                'message' => 'Status updated successfully',
                'lead_id' => $lead_id,
                'new_status' => $new_status
            ));
        } else {
            wp_send_json_error('Failed to update status');
        }
    }

    public function handle_get_filter_options() {
        check_ajax_referer('vdp_leads_nonce', 'nonce');

        $vendor = vdp_get_current_vendor();
        if (!$vendor) {
            wp_send_json_error('Vendor not found');
        }

        $options = array(
            'tipo_evento' => $this->get_event_types($vendor->get_id()),
            'status' => $this->get_lead_statuses(),
            'fuente' => $this->get_lead_sources($vendor->get_id()),
            'responsable' => $this->get_responsables($vendor->get_id()),
            'ubicacion' => $this->get_locations($vendor->get_id()),
            'servicios_requeridos' => $this->get_services($vendor->get_id()),
            'venue' => $this->get_venues($vendor->get_id()),
            'etiquetas' => $this->get_tags($vendor->get_id())
        );

        wp_send_json_success($options);
    }

    private function get_filtered_leads($args) {
        global $wpdb;

        $vendor_id = $args['vendor_id'];
        $per_page = $args['per_page'];
        $offset = ($args['paged'] - 1) * $per_page;

        // Base query
        $query = "SELECT p.*, pm.* FROM {$wpdb->posts} p
                  LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead' 
                  AND p.post_author = %d
                  AND p.post_status IN ('publish', 'draft')";

        $query_params = array($vendor_id);
        $where_conditions = array();

        // Apply filters
        if (!empty($args['search'])) {
            $where_conditions[] = "(p.post_title LIKE %s OR p.post_content LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_params[] = $search_term;
            $query_params[] = $search_term;
        }

        if (!empty($args['fecha_inicio'])) {
            $where_conditions[] = "p.post_date >= %s";
            $query_params[] = $args['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($args['fecha_fin'])) {
            $where_conditions[] = "p.post_date <= %s";
            $query_params[] = $args['fecha_fin'] . ' 23:59:59';
        }

        if (!empty($args['tipo_evento'])) {
            $placeholders = implode(',', array_fill(0, count($args['tipo_evento']), '%s'));
            $where_conditions[] = "pm.meta_key = 'tipo_evento' AND pm.meta_value IN ($placeholders)";
            $query_params = array_merge($query_params, $args['tipo_evento']);
        }

        if (!empty($args['status'])) {
            $placeholders = implode(',', array_fill(0, count($args['status']), '%s'));
            $where_conditions[] = "pm.meta_key = 'status' AND pm.meta_value IN ($placeholders)";
            $query_params = array_merge($query_params, $args['status']);
        }

        // Add where conditions
        if (!empty($where_conditions)) {
            $query .= " AND (" . implode(' AND ', $where_conditions) . ")";
        }

        // Group by to avoid duplicates
        $query .= " GROUP BY p.ID";

        // Order by
        $query .= " ORDER BY p.post_date DESC";

        // Limit
        $query .= " LIMIT %d OFFSET %d";
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $prepared_query = $wpdb->prepare($query, $query_params);
        $results = $wpdb->get_results($prepared_query);

        // Process results
        $leads = array();
        foreach ($results as $row) {
            if (!isset($leads[$row->ID])) {
                $leads[$row->ID] = array(
                    'ID' => $row->ID,
                    'post_title' => $row->post_title,
                    'post_content' => $row->post_content,
                    'post_date' => $row->post_date,
                    'meta' => array()
                );
            }
            
            if ($row->meta_key) {
                $leads[$row->ID]['meta'][$row->meta_key] = $row->meta_value;
            }
        }

        return array_values($leads);
    }

    private function get_total_filtered_leads($args) {
        global $wpdb;

        $vendor_id = $args['vendor_id'];

        // Base query for count
        $query = "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
                  LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead' 
                  AND p.post_author = %d
                  AND p.post_status IN ('publish', 'draft')";

        $query_params = array($vendor_id);
        $where_conditions = array();

        // Apply same filters as main query
        if (!empty($args['search'])) {
            $where_conditions[] = "(p.post_title LIKE %s OR p.post_content LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_params[] = $search_term;
            $query_params[] = $search_term;
        }

        if (!empty($args['fecha_inicio'])) {
            $where_conditions[] = "p.post_date >= %s";
            $query_params[] = $args['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($args['fecha_fin'])) {
            $where_conditions[] = "p.post_date <= %s";
            $query_params[] = $args['fecha_fin'] . ' 23:59:59';
        }

        if (!empty($args['tipo_evento'])) {
            $placeholders = implode(',', array_fill(0, count($args['tipo_evento']), '%s'));
            $where_conditions[] = "pm.meta_key = 'tipo_evento' AND pm.meta_value IN ($placeholders)";
            $query_params = array_merge($query_params, $args['tipo_evento']);
        }

        if (!empty($args['status'])) {
            $placeholders = implode(',', array_fill(0, count($args['status']), '%s'));
            $where_conditions[] = "pm.meta_key = 'status' AND pm.meta_value IN ($placeholders)";
            $query_params = array_merge($query_params, $args['status']);
        }

        // Add where conditions
        if (!empty($where_conditions)) {
            $query .= " AND (" . implode(' AND ', $where_conditions) . ")";
        }

        $prepared_query = $wpdb->prepare($query, $query_params);
        return (int) $wpdb->get_var($prepared_query);
    }

    private function get_applied_filters_summary($args) {
        $summary = array();

        if (!empty($args['search'])) {
            $summary[] = array('type' => 'search', 'label' => 'Búsqueda', 'value' => $args['search']);
        }

        if (!empty($args['fecha_inicio']) || !empty($args['fecha_fin'])) {
            $date_label = '';
            if (!empty($args['fecha_inicio']) && !empty($args['fecha_fin'])) {
                $date_label = $args['fecha_inicio'] . ' - ' . $args['fecha_fin'];
            } elseif (!empty($args['fecha_inicio'])) {
                $date_label = 'Desde ' . $args['fecha_inicio'];
            } else {
                $date_label = 'Hasta ' . $args['fecha_fin'];
            }
            $summary[] = array('type' => 'date', 'label' => 'Fecha Ingreso', 'value' => $date_label);
        }

        if (!empty($args['tipo_evento'])) {
            $summary[] = array('type' => 'tipo_evento', 'label' => 'Tipo Evento', 'value' => implode(', ', $args['tipo_evento']));
        }

        if (!empty($args['status'])) {
            $summary[] = array('type' => 'status', 'label' => 'Estado', 'value' => implode(', ', $args['status']));
        }

        return $summary;
    }

    private function log_status_change($lead_id, $new_status, $vendor_id) {
        // Add entry to lead history
        add_post_meta($lead_id, 'status_history', array(
            'status' => $new_status,
            'date' => current_time('mysql'),
            'user_id' => $vendor_id,
            'type' => 'status_change'
        ));
    }

    // Helper methods for filter options
    private function get_event_types($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'tipo_evento'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }

    private function get_lead_statuses() {
        return array(
            array('value' => 'nuevo-no-contactado', 'label' => 'Nuevo (No Contactado)'),
            array('value' => 'contactado-interesado', 'label' => 'Contactado e Interesado'),
            array('value' => 'reunion-agendada', 'label' => 'Reunión Agendada'),
            array('value' => 'propuesta-enviada', 'label' => 'Propuesta Enviada'),
            array('value' => 'negociacion', 'label' => 'En Negociación'),
            array('value' => 'cerrado-ganado', 'label' => 'Cerrado (Ganado)'),
            array('value' => 'cerrado-perdido', 'label' => 'Cerrado (Perdido)'),
            array('value' => 'seguimiento', 'label' => 'En Seguimiento'),
        );
    }

    private function get_lead_sources($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'fuente'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }

    private function get_responsables($vendor_id) {
        // For now, return the vendor as the only responsible
        $vendor = get_user_by('ID', $vendor_id);
        return array(
            array('value' => $vendor_id, 'label' => $vendor->display_name)
        );
    }

    private function get_locations($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'ubicacion'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }

    private function get_services($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'servicios_requeridos'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }

    private function get_venues($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'venue'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }

    private function get_tags($vendor_id) {
        global $wpdb;
        
        $query = "SELECT DISTINCT pm.meta_value as value, pm.meta_value as label
                  FROM {$wpdb->posts} p
                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  WHERE p.post_type = 'vdp_lead'
                  AND p.post_author = %d
                  AND pm.meta_key = 'etiquetas'
                  AND pm.meta_value != ''
                  ORDER BY pm.meta_value";
        
        return $wpdb->get_results($wpdb->prepare($query, $vendor_id));
    }
}

// Initialize
new VDP_Filters();