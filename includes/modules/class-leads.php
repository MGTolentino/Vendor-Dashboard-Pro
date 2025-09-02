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
        
        add_action('vdp_leads_content', array($this, 'render_leads_dashboard'), 10);
        add_action('vdp_lead_view_content', array($this, 'render_lead_view'), 10);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        add_action('wp_ajax_vdp_update_lead_status', array($this, 'ajax_update_lead_status'));
        add_action('wp_ajax_nopriv_vdp_update_lead_status', array($this, 'ajax_update_lead_status'));
        add_action('wp_ajax_vdp_get_pipeline_leads', array($this, 'ajax_get_pipeline_leads'));
        add_action('wp_ajax_nopriv_vdp_get_pipeline_leads', array($this, 'ajax_get_pipeline_leads'));
        add_action('wp_ajax_vdp_add_pipeline_lead', array($this, 'ajax_add_pipeline_lead'));
        add_action('wp_ajax_nopriv_vdp_add_pipeline_lead', array($this, 'ajax_add_pipeline_lead'));
        add_action('wp_ajax_vdp_update_pipeline_status', array($this, 'ajax_update_pipeline_status'));
        add_action('wp_ajax_nopriv_vdp_update_pipeline_status', array($this, 'ajax_update_pipeline_status'));
        
        // Service search for autocomplete
        add_action('wp_ajax_vdp_search_services', array($this, 'ajax_search_services'));
        add_action('wp_ajax_nopriv_vdp_search_services', array($this, 'ajax_search_services'));
    }

    /**
     * Enqueue leads assets when on leads page.
     */
    public function enqueue_assets() {
        if (!vdp_is_dashboard_page() || vdp_get_current_action() !== 'leads') {
            return;
        }

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

        // DateRangePicker for date filters
        wp_enqueue_style(
            'daterangepicker',
            'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
            array(),
            '3.1.0'
        );

        // jQuery UI CSS for autocomplete
        wp_enqueue_style(
            'jquery-ui-css',
            'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css',
            array(),
            '1.13.2'
        );
        
        wp_enqueue_script(
            'moment',
            'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
            array(),
            'latest',
            true
        );
        
        wp_enqueue_script(
            'daterangepicker',
            'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
            array('jquery', 'moment'),
            '3.1.0',
            true
        );

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-autocomplete');
        
        wp_enqueue_script(
            'vdp-leads-pipeline',
            VDP_PLUGIN_URL . 'assets/js/leads-pipeline.js',
            array('jquery', 'jquery-ui-datepicker', 'jquery-ui-autocomplete'),
            VDP_VERSION,
            true
        );

        wp_enqueue_script(
            'vdp-pipeline-simple',
            VDP_PLUGIN_URL . 'assets/js/vdp-pipeline-simple.js',
            array('jquery', 'jquery-ui-datepicker', 'jquery-ui-autocomplete'),
            VDP_VERSION,
            true
        );
        
        // CRITICAL: Load our modal CSS with maximum priority
        wp_enqueue_style(
            'vdp-modal-override',
            VDP_PLUGIN_URL . 'assets/css/vdp-pipeline-simple.css',
            array(),
            VDP_VERSION . '-' . time(), // Cache busting
            'all'
        );
        
        // Add inline CSS with maximum specificity as backup
        $modal_override_css = '
        /* MODAL OVERRIDE - ABSOLUTE PRIORITY */
        html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"].vdp-modal.vdp-add-lead-modal,
        html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"].vdp-modal,
        html body #vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"] {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0,0,0,0.6) !important;
            z-index: 2147483647 !important;
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            transform: none !important;
            opacity: 1 !important;
        }
        
        html body div#vdp_add_lead_modal_unique[id="vdp_add_lead_modal_unique"].vdp-active {
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
            padding: 0 !important;
            border: none !important;
            outline: none !important;
            transform: none !important;
            opacity: 1 !important;
            z-index: 2147483647 !important;
        }
        ';
        wp_add_inline_style('vdp-modal-override', $modal_override_css);

        wp_localize_script('vdp-leads-pipeline', 'vdpLeads', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vdp-ajax-nonce'),
            'site_url' => site_url(),
            'vendor_id' => $this->get_current_vendor_id(),
            'user_role' => $this->get_user_role(),
            'statusOptions' => $this->get_status_options()
        ));

        wp_localize_script('vdp-pipeline-simple', 'vdpLeads', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vdp-ajax-nonce'),
            'site_url' => site_url(),
            'vendor_id' => $this->get_current_vendor_id(),
            'user_role' => $this->get_user_role(),
            'statusOptions' => $this->get_status_options()
        ));
    }

    /**
     * Render leads dashboard.
     */
    public function render_leads_dashboard() {
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
                echo '<div class="vdp-notice vdp-notice-warning">';
            echo '<p>' . esc_html__('Leads Management plugin tables not found. Please make sure the Leads Management plugin is installed and activated.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
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
        
        
        if (!$vendor_id) {
            return array();
        }

        global $wpdb;
        
        $where = array('1=1');
        $values = array();

        // Base query - filter by vendor's listings using URL matching
        $site_url = trailingslashit(site_url());
        $query = "
            SELECT DISTINCT
                l._ID,
                l._ID as lead_id,
                COALESCE(FROM_UNIXTIME(e.fecha_de_evento), FROM_UNIXTIME(l.cct_created)) as lead_created_date,
                l.lead_razon_social,
                l.lead_nombre as lead_name,
                l.lead_apellido,
                CONCAT(l.lead_nombre, ' ', COALESCE(l.lead_apellido, '')) as lead_full_name,
                l.lead_celular as lead_phone,
                l.lead_e_mail as lead_email,
                e._ID as evento_id,
                COALESCE(e.evento_status, 'nuevo') as lead_status,
                e.fecha_de_evento,
                COALESCE(e.tipo_de_evento, 'General Inquiry') as event_name,
                e.evento_servicio_de_interes,
                listings.post_title as service_name,
                listings.ID as listing_id,
                'website' as lead_source,
                (SELECT COUNT(*) FROM {$this->eventos_table} WHERE lead_id = l._ID) as total_eventos
            FROM {$this->leads_table} l
            LEFT JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                listings.guid = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}contrata-el-servicio-de/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}book-service-for/', listings.post_name, '/') = e.evento_servicio_de_interes
            )
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";
        
        array_unshift($values, $vendor_id);

        // Add filters
        if (!empty($args['fecha_inicio'])) {
            $where[] = "COALESCE(FROM_UNIXTIME(e.fecha_de_evento), FROM_UNIXTIME(l.cct_created)) >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "COALESCE(FROM_UNIXTIME(e.fecha_de_evento), FROM_UNIXTIME(l.cct_created)) <= %s";
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


        $results = $wpdb->get_results($wpdb->prepare($query, $values));
        

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

        $site_url = trailingslashit(site_url());
        $query = "
            SELECT COUNT(DISTINCT l._ID)
            FROM {$this->leads_table} l
            LEFT JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                listings.guid = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}contrata-el-servicio-de/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}book-service-for/', listings.post_name, '/') = e.evento_servicio_de_interes
            )
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";

        // Add same filters as get_vendor_leads
        if (!empty($args['fecha_inicio'])) {
            $where[] = "COALESCE(FROM_UNIXTIME(e.fecha_de_evento), FROM_UNIXTIME(l.cct_created)) >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "COALESCE(FROM_UNIXTIME(e.fecha_de_evento), FROM_UNIXTIME(l.cct_created)) <= %s";
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

        $site_url = trailingslashit(site_url());
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$this->leads_table} l
            INNER JOIN {$this->eventos_table} e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON (
                listings.guid = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}contrata-el-servicio-de/', listings.post_name, '/') = e.evento_servicio_de_interes
                OR CONCAT('{$site_url}book-service-for/', listings.post_name, '/') = e.evento_servicio_de_interes
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
        
        
        return $vendor_id;
    }
    
    /**
     * Get current user role
     */
    private function get_user_role() {
        $user = wp_get_current_user();
        if (!$user || $user->ID === 0) {
            return '';
        }
        
        $roles = (array) $user->roles;
        
        // Check for vendor role first
        if (in_array('vendor', $roles) || in_array('hp_vendor', $roles)) {
            return 'vendor';
        }
        
        // Return first role if no vendor role found
        return !empty($roles) ? $roles[0] : '';
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
        if (!check_ajax_referer('vdp-ajax-nonce', 'nonce', false)) {
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
    
    /**
     * Get mapped status options for pipeline (VDP system).
     * Maps from LM system statuses to VDP system statuses.
     *
     * @return array Mapped status options.
     */
    public function get_pipeline_status_options() {
        return array(
            'nuevo' => __('New', 'vendor-dashboard-pro'),
            'contactado' => __('Contacted', 'vendor-dashboard-pro'),
            'cita-agendada' => __('Appointment Scheduled', 'vendor-dashboard-pro'),
            'propuesta-enviada' => __('Proposal Sent', 'vendor-dashboard-pro'),
            'negociacion' => __('Negotiation', 'vendor-dashboard-pro'),
            'cerrado-ganado' => __('Closed Won', 'vendor-dashboard-pro'),
            'cerrado-perdido' => __('Closed Lost', 'vendor-dashboard-pro')
        );
    }
    
    /**
     * Map LM status to VDP status.
     *
     * @param string $lm_status LM system status.
     * @return string VDP system status.
     */
    public function map_lm_to_vdp_status($lm_status) {
        $mapping = array(
            'nuevo' => 'nuevo',
            'con-presupuesto' => 'contactado',
            'por-cerrar' => 'negociacion',
            'con-contrato' => 'cerrado-ganado',
            'perdido' => 'cerrado-perdido'
        );
        
        return isset($mapping[$lm_status]) ? $mapping[$lm_status] : 'nuevo';
    }
    
    /**
     * Map VDP status to LM status.
     *
     * @param string $vdp_status VDP system status.
     * @return string LM system status.
     */
    public function map_vdp_to_lm_status($vdp_status) {
        $mapping = array(
            'nuevo' => 'nuevo',
            'contactado' => 'con-presupuesto',
            'cita-agendada' => 'con-presupuesto',
            'propuesta-enviada' => 'con-presupuesto',
            'negociacion' => 'por-cerrar',
            'cerrado-ganado' => 'con-contrato',
            'cerrado-perdido' => 'perdido'
        );
        
        return isset($mapping[$vdp_status]) ? $mapping[$vdp_status] : 'nuevo';
    }
    
    /**
     * AJAX handler to get pipeline leads data.
     */
    public function ajax_get_pipeline_leads() {
        // Verify request
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is logged in and is vendor
        if (!is_user_logged_in() || !vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('Access denied.', 'vendor-dashboard-pro')));
        }
        
        // Get filters
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        
        // Get leads data with proper pipeline format
        $leads = $this->get_pipeline_leads_data();
        
        // Apply filters if any
        if (!empty($filters)) {
            $leads = $this->apply_pipeline_filters($leads, $filters);
        }
        
        // Prepare response data
        $response_data = array(
            'leads' => $leads,
            'total' => count($leads)
        );
        
        wp_send_json_success($response_data);
    }
    
    /**
     * Get properly formatted leads data for pipeline.
     */
    private function get_pipeline_leads_data() {
        $raw_leads = $this->get_vendor_leads();
        $formatted_leads = array();
        
        foreach ($raw_leads as $lead) {
            $formatted_leads[] = array(
                'lead_id' => $lead->lead_id,
                'evento_id' => $lead->evento_id ?? null,
                'nombre_completo' => trim($lead->lead_name . ' ' . ($lead->lead_apellido ?? '')),
                'lead_nombre' => $lead->lead_name,
                'lead_apellido' => $lead->lead_apellido ?? '',
                'lead_email' => $lead->lead_email,
                'lead_celular' => $lead->lead_phone,
                'evento_status' => $lead->lead_status ?? 'nuevo',
                'tipo_evento' => $lead->event_name ?? 'Consulta General',
                'fecha_evento' => !empty($lead->fecha_de_evento) ? date('Y-m-d', $lead->fecha_de_evento) : null,
                'servicio_titulo' => $lead->service_name ?? '',
                'service_url' => $lead->evento_servicio_de_interes ?? '',
                'lead_created' => $lead->lead_created_date,
                'lead_priority' => 'media' // Default priority
            );
        }
        
        return $formatted_leads;
    }
    
    /**
     * AJAX handler for service search (autocomplete)
     */
    public function ajax_search_services() {
        // Verify nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is logged in and is vendor
        if (!is_user_logged_in() || !vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('Access denied.', 'vendor-dashboard-pro')));
        }
        
        $search_term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => __('Search term is empty.', 'vendor-dashboard-pro')));
        }
        
        // Get current vendor ID
        $vendor_id = $this->get_current_vendor_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
        }
        
        // Search only vendor's listings
        $args = array(
            'post_type' => 'hp_listing',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            's' => $search_term,
            'post_parent' => $vendor_id, // Only vendor's listings
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        $query = new WP_Query($args);
        $results = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $title = get_the_title();
                $url = get_permalink($post_id);
                
                $results[] = array(
                    'label' => $title,
                    'value' => $title,
                    'url' => $url,
                    'id' => $post_id
                );
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler to add new pipeline lead.
     */
    public function ajax_add_pipeline_lead() {
        // Verify request
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is logged in and is vendor
        if (!is_user_logged_in() || !vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('Access denied.', 'vendor-dashboard-pro')));
        }
        
        // Get vendor ID
        $vendor_id = $this->get_current_vendor_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-dashboard-pro')));
        }
        
        // Validate required fields
        $required_fields = array('lead_nombre', 'lead_apellido', 'lead_email', 'lead_celular');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => sprintf(__('Field %s is required.', 'vendor-dashboard-pro'), $field)));
            }
        }
        
        // Sanitize input data
        $lead_data = array(
            'lead_nombre' => sanitize_text_field($_POST['lead_nombre']),
            'lead_apellido' => sanitize_text_field($_POST['lead_apellido']),
            'lead_email' => sanitize_email($_POST['lead_email']),
            'lead_celular' => sanitize_text_field($_POST['lead_celular']),
            'service_url' => esc_url_raw($_POST['service_url'] ?? ''),
            'lead_notas' => sanitize_textarea_field($_POST['lead_notas'] ?? ''),
            'evento_status' => sanitize_text_field($_POST['evento_status'] ?? 'nuevo'),
            'vendor_id' => $vendor_id,
            'lead_created' => current_time('mysql')
        );
        
        // Insert lead into database
        global $wpdb;
        $result = $wpdb->insert(
            $this->leads_table,
            $lead_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to create lead.', 'vendor-dashboard-pro')));
        }
        
        wp_send_json_success(array(
            'message' => __('Lead created successfully.', 'vendor-dashboard-pro'),
            'lead_id' => $wpdb->insert_id
        ));
    }
    
    /**
     * AJAX handler to update pipeline lead status.
     */
    public function ajax_update_pipeline_status() {
        // Verify request
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vdp-ajax-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-dashboard-pro')));
        }
        
        // Check if user is logged in and is vendor
        if (!is_user_logged_in() || !vdp_is_user_vendor()) {
            wp_send_json_error(array('message' => __('Access denied.', 'vendor-dashboard-pro')));
        }
        
        // Get parameters
        $lead_id = absint($_POST['lead_id'] ?? 0);
        $new_status = sanitize_text_field($_POST['new_status'] ?? '');
        $notes = sanitize_textarea_field($_POST['status_notes'] ?? '');
        
        if (!$lead_id || !$new_status) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'vendor-dashboard-pro')));
        }
        
        // Verify lead belongs to current vendor
        $vendor_id = $this->get_current_vendor_id();
        $lead = $this->get_lead_by_id($lead_id);
        
        if (!$lead || $lead->vendor_id != $vendor_id) {
            wp_send_json_error(array('message' => __('Lead not found or access denied.', 'vendor-dashboard-pro')));
        }
        
        // Update lead status
        global $wpdb;
        $result = $wpdb->update(
            $this->leads_table,
            array(
                'evento_status' => $new_status,
                'lead_updated' => current_time('mysql')
            ),
            array('_ID' => $lead_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to update lead status.', 'vendor-dashboard-pro')));
        }
        
        // Add notes if provided
        if (!empty($notes)) {
            $this->add_lead_note($lead_id, $notes, 'Status changed to: ' . $new_status);
        }
        
        wp_send_json_success(array(
            'message' => __('Lead status updated successfully.', 'vendor-dashboard-pro'),
            'new_status' => $new_status
        ));
    }
    
    /**
     * Apply filters to pipeline leads.
     *
     * @param array $leads Array of lead objects.
     * @param array $filters Filter parameters.
     * @return array Filtered leads.
     */
    private function apply_pipeline_filters($leads, $filters) {
        if (empty($filters)) {
            return $leads;
        }
        
        return array_filter($leads, function($lead) use ($filters) {
            // Search filter
            if (!empty($filters['search'])) {
                $search = strtolower($filters['search']);
                $searchable = strtolower($lead->lead_nombre . ' ' . $lead->lead_apellido . ' ' . $lead->lead_email);
                if (strpos($searchable, $search) === false) {
                    return false;
                }
            }
            
            // Service filter
            if (!empty($filters['service']) && !empty($lead->service_url)) {
                if (strpos($lead->service_url, $filters['service']) === false) {
                    return false;
                }
            }
            
            // Priority filter
            if (!empty($filters['priority']) && !empty($lead->lead_priority)) {
                if ($lead->lead_priority !== $filters['priority']) {
                    return false;
                }
            }
            
            // Period filter
            if (!empty($filters['period'])) {
                $created_date = strtotime($lead->lead_created);
                if (!$this->check_period_filter($created_date, $filters['period'], $filters['date_range'] ?? '')) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    /**
     * Check if date matches period filter.
     *
     * @param int $timestamp Unix timestamp.
     * @param string $period Period filter.
     * @param string $date_range Custom date range.
     * @return bool True if matches filter.
     */
    private function check_period_filter($timestamp, $period, $date_range = '') {
        $now = time();
        
        switch ($period) {
            case 'today':
                return date('Y-m-d', $timestamp) === date('Y-m-d', $now);
                
            case 'this_week':
                $week_start = strtotime('monday this week', $now);
                return $timestamp >= $week_start;
                
            case 'this_month':
                $month_start = strtotime('first day of this month', $now);
                return $timestamp >= $month_start;
                
            case 'this_year':
                $year_start = strtotime('first day of January this year', $now);
                return $timestamp >= $year_start;
                
            case 'custom':
                if (!empty($date_range) && strpos($date_range, ' - ') !== false) {
                    list($start, $end) = explode(' - ', $date_range);
                    $start_time = strtotime($start);
                    $end_time = strtotime($end . ' 23:59:59');
                    return $timestamp >= $start_time && $timestamp <= $end_time;
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Get lead by ID.
     *
     * @param int $lead_id Lead ID.
     * @return object|null Lead object or null if not found.
     */
    private function get_lead_by_id($lead_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->leads_table} WHERE _ID = %d",
            $lead_id
        ));
    }
    
    /**
     * Add note to lead.
     *
     * @param int $lead_id Lead ID.
     * @param string $note Note content.
     * @param string $context Note context.
     */
    private function add_lead_note($lead_id, $note, $context = '') {
        global $wpdb;
        
        // For now, append to existing notes
        $existing_notes = $wpdb->get_var($wpdb->prepare(
            "SELECT lead_notas FROM {$this->leads_table} WHERE _ID = %d",
            $lead_id
        ));
        
        $new_note = date('Y-m-d H:i:s') . ' - ' . ($context ? $context . ': ' : '') . $note;
        $updated_notes = $existing_notes ? $existing_notes . "\n" . $new_note : $new_note;
        
        $wpdb->update(
            $this->leads_table,
            array('lead_notas' => $updated_notes),
            array('_ID' => $lead_id),
            array('%s'),
            array('%d')
        );
    }
}

// Initialize Leads module
VDP_Leads::instance();