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
 * Leads module class.
 */
class VDP_Leads {
    /**
     * Instance of this class.
     *
     * @var VDP_Leads
     */
    protected static $instance = null;

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
        // Initialize hooks
        add_action('vdp_leads_content', array($this, 'render_leads_list'), 10);
        add_action('vdp_lead_view_content', array($this, 'render_lead_view'), 10);
    }

    /**
     * Render leads list.
     */
    public function render_leads_list() {
        // Get vendor
        $vendor = vdp_get_current_vendor();
        
        if (!$vendor) {
            return;
        }
        
        // Get current page
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Get leads per page
        $per_page = 10;
        
        // For demo purposes, we'll create sample leads
        // In a real implementation, you would get actual leads from a database or API
        $leads = self::get_demo_leads();
        
        // Calculate total pages (for demo)
        $total_leads = count($leads);
        $total_pages = ceil($total_leads / $per_page);
        
        // Include leads list template
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
        
        // For demo purposes, we'll get a sample lead
        // In a real implementation, you would get the actual lead from a database or API
        $lead = self::get_demo_lead($lead_id);
        
        if (!$lead) {
            echo '<div class="vdp-notice vdp-notice-error">';
            echo '<p>' . esc_html__('Lead not found.', 'vendor-dashboard-pro') . '</p>';
            echo '</div>';
            return;
        }
        
        // Include lead view template
        include VDP_PLUGIN_DIR . 'templates/lead-view-content.php';
    }

    /**
     * Get demo leads for testing.
     *
     * @return array
     */
    public static function get_demo_leads() {
        $statuses = array('new', 'contacted', 'qualified', 'converted', 'lost');
        $sources = array('contact_form', 'website', 'referral', 'social_media', 'google');
        
        $leads = array();
        
        for ($i = 1; $i <= 15; $i++) {
            $status = $statuses[array_rand($statuses)];
            $source = $sources[array_rand($sources)];
            $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            
            $leads[] = array(
                'id' => $i,
                'name' => 'Lead ' . $i,
                'email' => 'lead' . $i . '@example.com',
                'phone' => rand(1, 2) == 1 ? '555-' . rand(100, 999) . '-' . rand(1000, 9999) : '',
                'message' => 'This is a sample lead message ' . $i . '. In a real implementation, this would contain actual message content.',
                'date' => $date,
                'status' => $status,
                'source' => $source,
            );
        }
        
        return $leads;
    }

    /**
     * Get a demo lead by ID.
     *
     * @param int $lead_id Lead ID.
     * @return array|null
     */
    public static function get_demo_lead($lead_id) {
        $leads = self::get_demo_leads();
        
        foreach ($leads as $lead) {
            if ($lead['id'] == $lead_id) {
                // Add more details for the single lead view
                $lead['notes'] = self::get_demo_lead_notes();
                $lead['activities'] = self::get_demo_lead_activities();
                
                return $lead;
            }
        }
        
        return null;
    }

    /**
     * Get demo lead notes.
     *
     * @return array
     */
    public static function get_demo_lead_notes() {
        $notes = array();
        $num_notes = rand(1, 3);
        
        for ($i = 1; $i <= $num_notes; $i++) {
            $notes[] = array(
                'id' => $i,
                'content' => 'This is a note ' . $i . ' about the lead.',
                'date' => date('Y-m-d H:i:s', strtotime('-' . (5 - $i) . ' days')),
                'author' => 'Vendor',
            );
        }
        
        return $notes;
    }

    /**
     * Get demo lead activities.
     *
     * @return array
     */
    public static function get_demo_lead_activities() {
        $activities = array();
        $num_activities = rand(3, 6);
        
        $types = array(
            'email' => 'Sent email',
            'call' => 'Phone call',
            'meeting' => 'Meeting scheduled',
            'status' => 'Status changed',
            'note' => 'Note added',
        );
        
        for ($i = 1; $i <= $num_activities; $i++) {
            $type = array_rand($types);
            $date = date('Y-m-d H:i:s', strtotime('-' . ($num_activities - $i) . ' days'));
            
            $activities[] = array(
                'id' => $i,
                'type' => $type,
                'description' => $types[$type] . ': ' . self::get_activity_description($type),
                'date' => $date,
                'user' => 'Vendor',
            );
        }
        
        // Sort by date, newest first
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $activities;
    }

    /**
     * Get random activity description.
     *
     * @param string $type Activity type.
     * @return string
     */
    public static function get_activity_description($type) {
        switch ($type) {
            case 'email':
                $subjects = array(
                    'Follow-up on your inquiry',
                    'Information about our services',
                    'Special offer for new customers',
                    'Thank you for your interest',
                );
                return $subjects[array_rand($subjects)];
                
            case 'call':
                $results = array(
                    'Left voicemail',
                    'Discussed product options',
                    'Scheduled follow-up call',
                    'No answer',
                );
                return $results[array_rand($results)];
                
            case 'meeting':
                $types = array(
                    'Initial consultation',
                    'Product demo',
                    'Proposal review',
                    'Contract signing',
                );
                return $types[array_rand($types)];
                
            case 'status':
                $statuses = array(
                    'New to Contacted',
                    'Contacted to Qualified',
                    'Qualified to Converted',
                    'Qualified to Lost',
                );
                return $statuses[array_rand($statuses)];
                
            case 'note':
                $notes = array(
                    'Customer seems very interested',
                    'Need to follow up next week',
                    'Requested more information about pricing',
                    'Interested in premium package',
                );
                return $notes[array_rand($notes)];
                
            default:
                return 'Activity recorded';
        }
    }

    /**
     * Get lead status label.
     *
     * @param string $status Lead status.
     * @return string
     */
    public static function get_status_label($status) {
        $statuses = array(
            'new' => __('New', 'vendor-dashboard-pro'),
            'contacted' => __('Contacted', 'vendor-dashboard-pro'),
            'qualified' => __('Qualified', 'vendor-dashboard-pro'),
            'converted' => __('Converted', 'vendor-dashboard-pro'),
            'lost' => __('Lost', 'vendor-dashboard-pro'),
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Get lead status class.
     *
     * @param string $status Lead status.
     * @return string
     */
    public static function get_status_class($status) {
        $classes = array(
            'new' => 'vdp-status-new',
            'contacted' => 'vdp-status-contacted',
            'qualified' => 'vdp-status-qualified',
            'converted' => 'vdp-status-converted',
            'lost' => 'vdp-status-lost',
        );
        
        return isset($classes[$status]) ? $classes[$status] : '';
    }

    /**
     * Get lead source label.
     *
     * @param string $source Lead source.
     * @return string
     */
    public static function get_source_label($source) {
        $sources = array(
            'contact_form' => __('Contact Form', 'vendor-dashboard-pro'),
            'website' => __('Website', 'vendor-dashboard-pro'),
            'referral' => __('Referral', 'vendor-dashboard-pro'),
            'social_media' => __('Social Media', 'vendor-dashboard-pro'),
            'google' => __('Google', 'vendor-dashboard-pro'),
            'other' => __('Other', 'vendor-dashboard-pro'),
        );
        
        return isset($sources[$source]) ? $sources[$source] : $source;
    }
}

// Initialize Leads module
VDP_Leads::instance();