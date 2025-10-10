<?php
/**
 * Plugin Name: Vendor Dashboard Pro
 * Plugin URI: https://yourwebsite.com/vendor-dashboard-pro
 * Description: Professional vendor dashboard with a Mercado Libre-inspired interface for HivePress vendors.
 * Version: 1.0.0
 * Author: Miguel Tolentino
 * Author URI: https://yourwebsite.com
 * Text Domain: vendor-dashboard-pro
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('VDP_VERSION', '1.0.0');
define('VDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VDP_PLUGIN_BASE', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Vendor_Dashboard_Pro {
    /**
     * Instance of this class.
     *
     * @var Vendor_Dashboard_Pro
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return Vendor_Dashboard_Pro
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
        $this->includes();
        $this->init_hooks();
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        if (file_exists(VDP_PLUGIN_DIR . 'includes/modules/class-leads.php')) {
            require_once VDP_PLUGIN_DIR . 'includes/modules/class-leads.php';
        }
        
        add_action('plugins_loaded', array($this, 'check_tables'));
    }
    
    public function check_tables() {
        if (class_exists('VDP_Installer') && method_exists('VDP_Installer', 'needs_db_update') && VDP_Installer::needs_db_update()) {
            VDP_Installer::install();
        }
    }

    /**
     * Include required files.
     */
    private function includes() {
        require_once VDP_PLUGIN_DIR . 'includes/functions.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-api.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-router.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-assets.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-ajax-handler.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-installer.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-client-messages.php';
        

        // Module files
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-dashboard.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-products.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-orders.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-messages.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-analytics.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-bookings.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-calendar.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-vdp-filters.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-settings.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-contracts.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-translations.php';
        
        require_once VDP_PLUGIN_DIR . 'includes/integrations/class-listing-integration.php';

        if (is_admin()) {
            require_once VDP_PLUGIN_DIR . 'includes/admin/class-admin.php';
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action('init', array('VDP_Router', 'init'));
        add_action('init', array('VDP_Assets', 'init'));
        add_action('init', array('VDP_Ajax_Handler', 'init'));
        add_shortcode('vendor_dashboard_pro', array('VDP_Router', 'shortcode_callback'));
        add_action('wp_ajax_vdp_load_content', array('VDP_Router', 'ajax_load_content'));
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain('vendor-dashboard-pro', false, dirname(VDP_PLUGIN_BASE) . '/languages');
    }

    /**
     * Activate plugin.
     */
    public static function activate() {
        if (class_exists('VDP_Installer')) {
            VDP_Installer::install();
        } else {
            require_once VDP_PLUGIN_DIR . 'includes/class-installer.php';
            VDP_Installer::install();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivate plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function vendor_dashboard_pro() {
    return Vendor_Dashboard_Pro::instance();
}
vendor_dashboard_pro();

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('Vendor_Dashboard_Pro', 'activate'));
register_deactivation_hook(__FILE__, array('Vendor_Dashboard_Pro', 'deactivate'));