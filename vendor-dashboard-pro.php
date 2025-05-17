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

// Define plugin constants
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
        // Check dependencies
        if (!$this->check_dependencies()) {
            return;
        }

        // Include required files
        $this->includes();

        // Initialize hooks
        $this->init_hooks();

        // Load textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Check if all dependencies are available.
     *
     * @return bool
     */
    private function check_dependencies() {
        // Check if HivePress is active
        if (!class_exists('HivePress')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('Vendor Dashboard Pro requires HivePress to be installed and activated.', 'vendor-dashboard-pro'); ?></p>
                </div>
                <?php
            });
            return false;
        }

        return true;
    }

    /**
     * Include required files.
     */
    private function includes() {
        // Core files
        require_once VDP_PLUGIN_DIR . 'includes/functions.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-api.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-router.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-assets.php';
        require_once VDP_PLUGIN_DIR . 'includes/class-ajax-handler.php';

        // Module files
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-dashboard.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-products.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-orders.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-messages.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-analytics.php';
        require_once VDP_PLUGIN_DIR . 'includes/modules/class-settings.php';

        // Admin files
        if (is_admin()) {
            require_once VDP_PLUGIN_DIR . 'includes/admin/class-admin.php';
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Initialize router
        add_action('init', array('VDP_Router', 'init'), 10);
        
        // Initialize assets
        add_action('init', array('VDP_Assets', 'init'), 10);
        
        // Initialize Ajax handler
        add_action('init', array('VDP_Ajax_Handler', 'init'), 10);
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
        // Add rewrite rules
        VDP_Router::add_rewrite_rules();
        
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