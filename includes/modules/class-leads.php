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

        $vendor_id = $vendor->ID;
        
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
            'orderby' => 'fecha_solicitud',
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

        // Base query - filter by vendor's listings
        $query = "
            SELECT DISTINCT
                l._ID as lead_id,
                l.cct_created as fecha_solicitud,
                l.lead_razon_social,
                l.lead_nombre,
                l.lead_apellido,
                l.lead_celular,
                l.lead_e_mail,
                e._ID as evento_id,
                e.evento_status,
                e.fecha_de_evento,
                e.tipo_de_evento,
                e.evento_servicio_de_interes,
                (SELECT COUNT(*) FROM {$this->eventos_table} WHERE lead_id = l._ID) as total_eventos
            FROM {$this->leads_table} l
            LEFT JOIN (
                SELECT e1.*
                FROM {$this->eventos_table} e1
                LEFT JOIN {$this->eventos_table} e2
                ON e1.lead_id = e2.lead_id AND e1.fecha_de_evento < e2.fecha_de_evento
                WHERE e2.lead_id IS NULL
            ) e ON e.lead_id = l._ID
            INNER JOIN {$wpdb->posts} listings ON listings.post_title = e.evento_servicio_de_interes
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";
        
        array_unshift($values, $vendor_id);

        // Add filters
        if (!empty($args['fecha_inicio'])) {
            $where[] = "l.cct_created >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "l.cct_created <= %s";
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

        return $wpdb->get_results($wpdb->prepare($query, $values));
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
            INNER JOIN {$wpdb->posts} listings ON listings.post_title = e.evento_servicio_de_interes
            WHERE listings.post_type = 'hp_listing' 
            AND listings.post_parent = %d
        ";

        // Add same filters as get_vendor_leads
        if (!empty($args['fecha_inicio'])) {
            $where[] = "l.cct_created >= %s";
            $values[] = $args['fecha_inicio'];
        }
        
        if (!empty($args['fecha_fin'])) {
            $where[] = "l.cct_created <= %s";
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
            INNER JOIN {$wpdb->posts} listings ON listings.post_title = e.evento_servicio_de_interes
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
        return $vendor ? $vendor->ID : null;
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
     *
     * @return array Status options.
     */
    public function get_status_options() {
        // Based on the original leads plugin structure
        return array(
            'inicial' => __('Inicial', 'vendor-dashboard-pro'),
            'contactado' => __('Contactado', 'vendor-dashboard-pro'),
            'cita-agendada' => __('Cita Agendada', 'vendor-dashboard-pro'),
            'propuesta-enviada' => __('Propuesta Enviada', 'vendor-dashboard-pro'),
            'negociacion' => __('Negociación', 'vendor-dashboard-pro'),
            'cerrado-ganado' => __('Cerrado Ganado', 'vendor-dashboard-pro'),
            'cerrado-perdido' => __('Cerrado Perdido', 'vendor-dashboard-pro')
        );
    }

    /**
     * Get active status options (excluding closed states for pipeline).
     *
     * @return array Active status options.
     */
    public function get_active_status_options() {
        $all_statuses = $this->get_status_options();
        
        // Remove closed states for pipeline view
        unset($all_statuses['cerrado-ganado']);
        unset($all_statuses['cerrado-perdido']);
        
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
            $status = $lead->evento_status ?: 'inicial';
            if (isset($grouped[$status])) {
                $grouped[$status][] = $lead;
            }
        }
        
        return $grouped;
    }
}

// Initialize Leads module
VDP_Leads::instance();