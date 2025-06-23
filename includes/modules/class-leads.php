<?php
/**
 * Leads Module Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Leads module class for vendor-specific lead management.
 */
class VDP_Leads {
    /**
     * Instance of this class.
     *
     * @var VDP_Leads
     */
    protected static $instance = null;

    /**
     * Database tables
     */
    private $leads_table;
    private $eventos_table;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Leads
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->leads_table = $wpdb->prefix . 'jet_cct_leads';
        $this->eventos_table = $wpdb->prefix . 'jet_cct_eventos';
        
        // Initialize hooks
        add_action('vdp_leads_content', array($this, 'render_leads_dashboard'), 10);
        add_action('vdp_lead_view_content', array($this, 'render_lead_view'), 10);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX hooks
        add_action('wp_ajax_vdp_update_lead_status', array($this, 'ajax_update_lead_status'));
    }

    /**
     * Enqueue leads assets when on leads page.
     */
    public function enqueue_assets() {
        if (!vdp_is_dashboard_page() || vdp_get_current_action() !== 'leads') {
            return;
        }

        // Copy styles from leads management plugin
        wp_enqueue_style(
            'vdp-leads-admin',
            VDP_PLUGIN_URL . 'assets/css/leads.css',
            array(),
            VDP_VERSION
        );

        wp_enqueue_style(
            'vdp-leads-pipeline',
            VDP_PLUGIN_URL . 'assets/css/leads-pipeline.css',
            array(),
            VDP_VERSION
        );

        // jQuery UI for datepicker
        wp_enqueue_style(
            'jquery-ui-style',
            'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
            array(),
            '1.13.2'
        );

        wp_enqueue_script('jquery-ui-datepicker');
        
        wp_enqueue_script(
            'vdp-leads-pipeline',
            VDP_PLUGIN_URL . 'assets/js/leads-pipeline.js',
            array('jquery', 'jquery-ui-datepicker'),
            VDP_VERSION,
            true
        );

        wp_localize_script('vdp-leads-pipeline', 'vdpLeads', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vdp_leads_nonce'),
            'site_url' => site_url(),
            'vendor_id' => $this->get_current_vendor_id(),
            'statusOptions' => $this->get_status_options()
        ));
    }

    /**
     * Render leads dashboard.
     */
    public function render_leads_dashboard() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Vendor information not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }

        $vendor_id = $vendor->get_id();
        
        // Check if leads tables exist
        if (!$this->check_leads_tables()) {
            if (function_exists('vdp_debug_log')) {
                vdp_debug_log("VDP Leads - Tables check failed. Leads table: {$this->leads_table}, Events table: {$this->eventos_table}", "error");
            }
            echo '<div class="vdp-notice vdp-notice-warning">';
            echo '<p>' . esc_html__('Leads Management plugin tables not found. Please make sure the Leads Management plugin is installed and activated.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Debug additional table information
        if (function_exists('vdp_debug_log')) {
            global $wpdb;
            
            // Check table counts
            $leads_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->leads_table}");
            $eventos_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->eventos_table}");
            
            vdp_debug_log("VDP Leads - Total leads in database: " . $leads_count, "info");
            vdp_debug_log("VDP Leads - Total events in database: " . $eventos_count, "info");
            
            // Check specific vendor listings
            $vendor_listings = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, post_title, post_name FROM {$wpdb->posts} 
                WHERE post_type = 'hp_listing' AND post_parent = %d",
                $vendor_id
            ));
            
            vdp_debug_log("VDP Leads - Vendor {$vendor_id} listings: " . json_encode($vendor_listings), "info");
            
            // Check sample events with URLs
            $sample_events = $wpdb->get_results(
                "SELECT evento_servicio_de_interes, COUNT(*) as count 
                FROM {$this->eventos_table} 
                WHERE evento_servicio_de_interes IS NOT NULL 
                GROUP BY evento_servicio_de_interes 
                LIMIT 10"
            );
            
            vdp_debug_log("VDP Leads - Sample event URLs: " . json_encode($sample_events), "info");
        }

        // Include leads dashboard template
        include VDP_PLUGIN_DIR . 'templates/leads-content.php';
    }

    /**
     * Render lead view.
     */
    public function render_lead_view() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get lead ID
        $lead_id = get_query_var('vdp_item', 0);
        
        if (!$lead_id) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Lead not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Get lead details
        $lead = $this->get_lead_details($lead_id);
        
        if (!$lead) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Lead not found or you don\'t have permission to view it.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Set global for template compatibility
        $GLOBALS['ltb_lead_data'] = $lead;
        
        // Include lead view template
        include VDP_PLUGIN_DIR . 'templates/lead-view-content.php';
    }

    /**
     * Get leads for current vendor.
     *
     * @param array $args Query arguments.
     * @return array Array of leads.
     */
    public function get_vendor_leads($args = array()) {
        $defaults = array(
            'fecha_inicio' => '',
            'fecha_fin' => '',
            'fecha_evento_inicio' => '',
            'fecha_evento_fin' => '',
            'orderby' => 'lead_created_date',
            'order' => 'DESC',
            'per_page' => 20,
            'paged' => 1,
            'search' => '',
            'status' => ''
        );

        $args = wp_parse_args($args, $defaults);
        $vendor_id = $this->get_current_vendor_id();
        
        // Debug logging for leads query
        if (function_exists('vdp_debug_log')) {
            vdp_debug_log("VDP Leads - get_vendor_leads called with vendor_id: " . $vendor_id, "info");
            vdp_debug_log("VDP Leads - Query args: " . json_encode($args), "info");
        }
        
        if (!$vendor_id) {
            if (function_exists('vdp_debug_log')) {
                vdp_debug_log("VDP Leads - No vendor_id found, returning empty array", "warning");
            }
            return array();
        }

        global $wpdb;
        
        $where = array('1=1');
        $values = array();

        // Base query - filter by vendor's listings using URL matching
        $query = "
            SELECT DISTINCT
                l._ID,
                l._ID as lead_id,
                FROM_UNIXTIME(l.cct_created) as lead_created_date,
                l.lead_razon_social,
                l.lead_nombre as lead_name,
                l.lead_apellido,
                CONCAT(l.lead_nombre, ' ', COALESCE(l.lead_apellido, '')) as lead_full_name,
                l.lead_celular as lead_phone,
                l.lead_e_mail as lead_email,
                e._ID as evento_id,
                e.evento_status as lead_status,
                e.fecha_de_evento,
                e.tipo_de_evento as event_name,
                e.evento_servicio_de_interes,
                'website' as lead_source,
                (SELECT COUNT(*) FROM {$this->eventos_table} WHERE lead_id = l._ID) as total_eventos
            FROM {$this->leads_table} l
            LEFT JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                CONCAT('/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('/listing/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR listings.guid = e.evento_servicio_de_interes
                OR e.evento_servicio_de_interes LIKE CONCAT('%/', listings.post_name, '/%')
            )
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";
        
        array_unshift($values, $vendor_id);

        // Add filters
        if (!empty($args['fecha_inicio'])) {
            $where[] = "FROM_UNIXTIME(l.cct_created) >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "FROM_UNIXTIME(l.cct_created) <= %s";
            $values[] = $args['fecha_fin'];
        }

        if (!empty($args['status'])) {
            $where[] = "e.evento_status = %s";
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = "(l.lead_nombre LIKE %s OR l.lead_apellido LIKE %s OR l.lead_e_mail LIKE %s OR l.lead_razon_social LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        // Build final query
        if (!empty($where)) {
            $query .= ' AND ' . implode(' AND ', $where);
        }

        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        // Add pagination
        if ($args['per_page'] > 0) {
            $offset = ($args['paged'] - 1) * $args['per_page'];
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['per_page'], $offset);
        }

        // Debug logging final query
        if (function_exists('vdp_debug_log')) {
            $final_query = $wpdb->prepare($query, $values);
            vdp_debug_log("VDP Leads - Final SQL query: " . $final_query, "info");
            vdp_debug_log("VDP Leads - Query values: " . json_encode($values), "info");
        }

        $results = $wpdb->get_results($wpdb->prepare($query, $values));
        
        // Debug logging results
        if (function_exists('vdp_debug_log')) {
            vdp_debug_log("VDP Leads - Query returned " . count($results) . " results", "info");
            if (!empty($results)) {
                vdp_debug_log("VDP Leads - First result sample: " . json_encode($results[0]), "info");
            }
            
            // Check for database errors
            if ($wpdb->last_error) {
                vdp_debug_log("VDP Leads - Database error: " . $wpdb->last_error, "error");
            }
        }

        return $results;
    }

    /**
     * Get total count of vendor leads.
     *
     * @param array $args Query arguments.
     * @return int Total count.
     */
    public function get_vendor_leads_count($args = array()) {
        $vendor_id = $this->get_current_vendor_id();
        
        if (!$vendor_id) {
            return 0;
        }

        global $wpdb;
        
        $where = array('1=1');
        $values = array($vendor_id);

        $query = "
            SELECT COUNT(DISTINCT l._ID)
            FROM {$this->leads_table} l
            LEFT JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                CONCAT('/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('/listing/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR listings.guid = e.evento_servicio_de_interes
                OR e.evento_servicio_de_interes LIKE CONCAT('%/', listings.post_name, '/%')
            )
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";

        // Add same filters as get_vendor_leads
        if (!empty($args['fecha_inicio'])) {
            $where[] = "FROM_UNIXTIME(l.cct_created) >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "FROM_UNIXTIME(l.cct_created) <= %s";
            $values[] = $args['fecha_fin'];
        }

        if (!empty($args['status'])) {
            $where[] = "e.evento_status = %s";
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = "(l.lead_nombre LIKE %s OR l.lead_apellido LIKE %s OR l.lead_e_mail LIKE %s OR l.lead_razon_social LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($where)) {
            $query .= ' AND ' . implode(' AND ', $where);
        }

        return (int) $wpdb->get_var($wpdb->prepare($query, $values));
    }

    /**
     * Get lead details.
     *
     * @param int $lead_id Lead ID.
     * @return object|null Lead data.
     */
    public function get_lead_details($lead_id) {
        if (!$this->can_access_lead($lead_id)) {
            return null;
        }

        global $wpdb;

        // Get lead basic info
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->leads_table} WHERE _ID = %d",
            $lead_id
        ));

        if (!$lead) {
            return null;
        }

        // Get all events for this lead
        $eventos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->eventos_table} 
            WHERE lead_id = %d 
            ORDER BY fecha_de_evento DESC",
            $lead_id
        ));

        $lead->eventos = $eventos;
        $lead->fecha_solicitud = date('Y-m-d H:i:s', strtotime($lead->cct_created));

        return $lead;
    }

    /**
     * Check if current vendor can access a specific lead.
     *
     * @param int $lead_id Lead ID.
     * @return bool True if can access.
     */
    public function can_access_lead($lead_id) {
        $vendor_id = $this->get_current_vendor_id();
        
        if (!$vendor_id) {
            return false;
        }

        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$this->leads_table} l
            INNER JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                CONCAT('/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('/listing/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR listings.guid = e.evento_servicio_de_interes
                OR e.evento_servicio_de_interes LIKE CONCAT('%/', listings.post_name, '/%')
            )
            WHERE l._ID = %d 
            AND listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d",
            $lead_id,
            $vendor_id
        ));

        return $count > 0;
    }

    /**
     * Get current vendor ID.
     *
     * @return int|null Vendor ID.
     */
    private function get_current_vendor_id() {
        $vendor = vdp_get_current_vendor();
        $vendor_id = null;
        
        if ($vendor && method_exists($vendor, 'get_id')) {
            $vendor_id = $vendor->get_id();
        }
        
        // Debug logging
        if (function_exists('vdp_debug_log')) {
            vdp_debug_log("VDP Leads - Current vendor object type: " . get_class($vendor), "info");
            vdp_debug_log("VDP Leads - Current vendor ID: " . $vendor_id, "info");
        }
        
        return $vendor_id;
    }

    /**
     * Check if leads tables exist.
     *
     * @return bool True if tables exist.
     */
    private function check_leads_tables() {
        global $wpdb;
        
        $leads_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->leads_table}'") === $this->leads_table;
        $eventos_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->eventos_table}'") === $this->eventos_table;
        
        return $leads_exists && $eventos_exists;
    }

    /**
     * Get status options for leads.
     * Matches the Leads Management plugin status system.
     *
     * @return array Status options.
     */
    public function get_status_options() {
        // Check if Leads Management plugin status utility is available
        if (class_exists('LTB_Leads_Status_Utils')) {
            return LTB_Leads_Status_Utils::get_status_options();
        }
        
        // Fallback to match the Leads Management plugin default statuses
        return array(
            'nuevo' => __('Nuevo', 'vendor-dashboard-pro'),
            'con-presupuesto' => __('Con Presupuesto', 'vendor-dashboard-pro'),
            'por-cerrar' => __('Por cerrar', 'vendor-dashboard-pro'),
            'con-contrato' => __('Con contrato', 'vendor-dashboard-pro'),
            'perdido' => __('Perdido', 'vendor-dashboard-pro')
        );
    }

    /**
     * Get active status options (excluding closed states for pipeline).
     *
     * @return array Active status options.
     */
    public function get_active_status_options() {
        // Check if Leads Management plugin status utility is available
        if (class_exists('LTB_Leads_Status_Utils')) {
            return LTB_Leads_Status_Utils::get_active_status_options();
        }
        
        $all_statuses = $this->get_status_options();
        
        // Remove closed states for pipeline view (based on LM plugin structure)
        unset($all_statuses['con-contrato']);
        unset($all_statuses['perdido']);
        
        return $all_statuses;
    }

    /**
     * Get leads grouped by status for pipeline view.
     *
     * @param array $args Query arguments.
     * @return array Leads grouped by status.
     */
    public function get_leads_by_status($args = array()) {
        $leads = $this->get_vendor_leads($args);
        $grouped = array();
        
        // Initialize groups
        foreach ($this->get_status_options() as $status => $label) {
            $grouped[$status] = array();
        }
        
        // Group leads by status
        foreach ($leads as $lead) {
            $status = $lead->evento_status ?: 'nuevo'; // Changed default from 'inicial' to 'nuevo'
            if (isset($grouped[$status])) {
                $grouped[$status][] = $lead;
            }
        }
        
        return $grouped;
    }

    /**
     * AJAX handler to update lead status.
     */
    public function ajax_update_lead_status() {
        // Verify nonce
        if (!check_ajax_referer('vdp_leads_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
            return;
        }

        // Check if user is vendor
        if (!vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('Access denied.', 'vendor-dashboard-pro')));
            return;
        }

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_key($_POST['status']) : '';

        if (!$lead_id || !$new_status) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'vendor-dashboard-pro')));
            return;
        }

        // Verify vendor can access this lead
        if (!$this->can_access_lead($lead_id)) {
            wp_send_json_error(array('message' => __('You do not have permission to update this lead.', 'vendor-dashboard-pro')));
            return;
        }

        // Validate status
        $valid_statuses = array_keys($this->get_status_options());
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'vendor-dashboard-pro')));
            return;
        }

        // Update the lead status in the events table
        global $wpdb;

        $result = $wpdb->update(
            $this->eventos_table,
            array('evento_status' => $new_status),
            array('lead_id' => $lead_id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to update lead status.', 'vendor-dashboard-pro')));
            return;
        }

        wp_send_json_success(array(
            'message' => __('Lead status updated successfully.', 'vendor-dashboard-pro'),
            'new_status' => $new_status,
            'status_label' => vdp_get_lead_status_label($new_status)
        ));
    }

    /**
     * Get vendor lead statistics.
     *
     * @param int $vendor_id Vendor ID.
     * @return array Lead statistics by status.
     */
    public function get_vendor_lead_stats($vendor_id = null) {
        if (!$vendor_id) {
            $vendor_id = $this->get_current_vendor_id();
        }
        
        $leads = $this->get_vendor_leads();
        
        // Debug logging
        if (function_exists('vdp_debug_log')) {
            vdp_debug_log("VDP Leads - get_vendor_lead_stats called with vendor_id: " . $vendor_id, "info");
            vdp_debug_log("VDP Leads - Stats leads count: " . count($leads), "info");
        }
        
        // Initialize stats
        $stats = array('total' => count($leads));
        
        // Initialize all status counts to 0
        foreach ($this->get_status_options() as $status => $label) {
            $stats[$status] = 0;
        }
        
        // Count leads by status
        foreach ($leads as $lead) {
            $status = $lead->evento_status ?: 'nuevo'; // Changed to match event status field and default
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }
        
        return $stats;
    }
}

// Initialize Leads module
VDP_Leads::instance();