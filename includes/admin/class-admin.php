<?php
/**
 * Admin Class
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class for handling admin functionality.
 */
class VDP_Admin {
    /**
     * Instance of this class.
     *
     * @var VDP_Admin
     */
    protected static $instance = null;

    /**
     * Get the instance of this class.
     *
     * @return VDP_Admin
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
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . VDP_PLUGIN_BASE, array($this, 'add_plugin_action_links'));
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Vendor Dashboard Pro', 'vendor-dashboard-pro'),
            __('Vendor Dashboard', 'vendor-dashboard-pro'),
            'manage_options',
            'vendor-dashboard-pro',
            array($this, 'render_settings_page'),
            'dashicons-store',
            30
        );
        
        add_submenu_page(
            'vendor-dashboard-pro',
            __('Settings', 'vendor-dashboard-pro'),
            __('Settings', 'vendor-dashboard-pro'),
            'manage_options',
            'vendor-dashboard-pro',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting('vdp_settings', 'vdp_settings', array($this, 'sanitize_settings'));
        
        // General settings section
        add_settings_section(
            'vdp_general_settings',
            __('General Settings', 'vendor-dashboard-pro'),
            array($this, 'render_general_settings_section'),
            'vendor-dashboard-pro'
        );
        
        add_settings_field(
            'vdp_enable_dashboard',
            __('Enable Dashboard', 'vendor-dashboard-pro'),
            array($this, 'render_enable_dashboard_field'),
            'vendor-dashboard-pro',
            'vdp_general_settings'
        );
        
        add_settings_field(
            'vdp_dashboard_url',
            __('Dashboard URL', 'vendor-dashboard-pro'),
            array($this, 'render_dashboard_url_field'),
            'vendor-dashboard-pro',
            'vdp_general_settings'
        );
        
        add_settings_field(
            'vdp_page_title',
            __('Page Title', 'vendor-dashboard-pro'),
            array($this, 'render_page_title_field'),
            'vendor-dashboard-pro',
            'vdp_general_settings'
        );
        
        // Appearance settings section
        add_settings_section(
            'vdp_appearance_settings',
            __('Appearance Settings', 'vendor-dashboard-pro'),
            array($this, 'render_appearance_settings_section'),
            'vendor-dashboard-pro'
        );
        
        add_settings_field(
            'vdp_primary_color',
            __('Primary Color', 'vendor-dashboard-pro'),
            array($this, 'render_primary_color_field'),
            'vendor-dashboard-pro',
            'vdp_appearance_settings'
        );
        
        add_settings_field(
            'vdp_secondary_color',
            __('Secondary Color', 'vendor-dashboard-pro'),
            array($this, 'render_secondary_color_field'),
            'vendor-dashboard-pro',
            'vdp_appearance_settings'
        );
        
        add_settings_field(
            'vdp_accent_color',
            __('Accent Color', 'vendor-dashboard-pro'),
            array($this, 'render_accent_color_field'),
            'vendor-dashboard-pro',
            'vdp_appearance_settings'
        );
        
        // Modules settings section
        add_settings_section(
            'vdp_modules_settings',
            __('Modules Settings', 'vendor-dashboard-pro'),
            array($this, 'render_modules_settings_section'),
            'vendor-dashboard-pro'
        );
        
        add_settings_field(
            'vdp_enable_analytics',
            __('Enable Analytics', 'vendor-dashboard-pro'),
            array($this, 'render_enable_analytics_field'),
            'vendor-dashboard-pro',
            'vdp_modules_settings'
        );
        
        add_settings_field(
            'vdp_enable_messages',
            __('Enable Messages', 'vendor-dashboard-pro'),
            array($this, 'render_enable_messages_field'),
            'vendor-dashboard-pro',
            'vdp_modules_settings'
        );
        
        add_settings_field(
            'vdp_enable_orders',
            __('Enable Orders', 'vendor-dashboard-pro'),
            array($this, 'render_enable_orders_field'),
            'vendor-dashboard-pro',
            'vdp_modules_settings'
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input Settings input.
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // General settings
        $sanitized['enable_dashboard'] = isset($input['enable_dashboard']) ? 1 : 0;
        $sanitized['dashboard_url'] = sanitize_text_field($input['dashboard_url']);
        $sanitized['page_title'] = sanitize_text_field($input['page_title']);
        
        // Appearance settings
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']);
        $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color']);
        $sanitized['accent_color'] = sanitize_hex_color($input['accent_color']);
        
        // Modules settings
        $sanitized['enable_analytics'] = isset($input['enable_analytics']) ? 1 : 0;
        $sanitized['enable_messages'] = isset($input['enable_messages']) ? 1 : 0;
        $sanitized['enable_orders'] = isset($input['enable_orders']) ? 1 : 0;
        
        return $sanitized;
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('vdp_settings');
                do_settings_sections('vendor-dashboard-pro');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general settings section.
     */
    public function render_general_settings_section() {
        echo '<p>' . esc_html__('Configure general settings for the Vendor Dashboard Pro plugin.', 'vendor-dashboard-pro') . '</p>';
    }

    /**
     * Render appearance settings section.
     */
    public function render_appearance_settings_section() {
        echo '<p>' . esc_html__('Customize the appearance of the vendor dashboard.', 'vendor-dashboard-pro') . '</p>';
    }

    /**
     * Render modules settings section.
     */
    public function render_modules_settings_section() {
        echo '<p>' . esc_html__('Enable or disable specific modules in the vendor dashboard.', 'vendor-dashboard-pro') . '</p>';
    }

    /**
     * Render enable dashboard field.
     */
    public function render_enable_dashboard_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['enable_dashboard']) ? $options['enable_dashboard'] : 1;
        ?>
        <label>
            <input type="checkbox" name="vdp_settings[enable_dashboard]" value="1" <?php checked($value, 1); ?>>
            <?php esc_html_e('Enable the enhanced vendor dashboard', 'vendor-dashboard-pro'); ?>
        </label>
        <p class="description"><?php esc_html_e('If disabled, vendors will see the default HivePress dashboard.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render dashboard URL field.
     */
    public function render_dashboard_url_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['dashboard_url']) ? $options['dashboard_url'] : 'my-store';
        ?>
        <input type="text" name="vdp_settings[dashboard_url]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('The URL slug for the vendor dashboard (e.g., "my-store" will make the dashboard available at yourdomain.com/my-store/).', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render page title field.
     */
    public function render_page_title_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['page_title']) ? $options['page_title'] : __('My Store', 'vendor-dashboard-pro');
        ?>
        <input type="text" name="vdp_settings[page_title]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('The title displayed in the browser tab for the vendor dashboard.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render primary color field.
     */
    public function render_primary_color_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['primary_color']) ? $options['primary_color'] : '#3483fa';
        ?>
        <input type="color" name="vdp_settings[primary_color]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php esc_html_e('The primary color used throughout the dashboard.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render secondary color field.
     */
    public function render_secondary_color_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['secondary_color']) ? $options['secondary_color'] : '#39b54a';
        ?>
        <input type="color" name="vdp_settings[secondary_color]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php esc_html_e('The secondary color used throughout the dashboard.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render accent color field.
     */
    public function render_accent_color_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['accent_color']) ? $options['accent_color'] : '#f5a623';
        ?>
        <input type="color" name="vdp_settings[accent_color]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php esc_html_e('The accent color used for highlighting elements.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render enable analytics field.
     */
    public function render_enable_analytics_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['enable_analytics']) ? $options['enable_analytics'] : 1;
        ?>
        <label>
            <input type="checkbox" name="vdp_settings[enable_analytics]" value="1" <?php checked($value, 1); ?>>
            <?php esc_html_e('Enable the analytics module', 'vendor-dashboard-pro'); ?>
        </label>
        <p class="description"><?php esc_html_e('If enabled, vendors will see analytics and statistics in their dashboard.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render enable messages field.
     */
    public function render_enable_messages_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['enable_messages']) ? $options['enable_messages'] : 1;
        ?>
        <label>
            <input type="checkbox" name="vdp_settings[enable_messages]" value="1" <?php checked($value, 1); ?>>
            <?php esc_html_e('Enable the messages module', 'vendor-dashboard-pro'); ?>
        </label>
        <p class="description"><?php esc_html_e('If enabled, vendors will be able to manage customer messages.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Render enable orders field.
     */
    public function render_enable_orders_field() {
        $options = get_option('vdp_settings');
        $value = isset($options['enable_orders']) ? $options['enable_orders'] : 1;
        ?>
        <label>
            <input type="checkbox" name="vdp_settings[enable_orders]" value="1" <?php checked($value, 1); ?>>
            <?php esc_html_e('Enable the orders module', 'vendor-dashboard-pro'); ?>
        </label>
        <p class="description"><?php esc_html_e('If enabled, vendors will be able to manage orders.', 'vendor-dashboard-pro'); ?></p>
        <?php
    }

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_vendor-dashboard-pro' !== $hook) {
            return;
        }
        
        wp_enqueue_style('vdp-admin', VDP_PLUGIN_URL . 'assets/css/admin.css', array(), VDP_VERSION);
        wp_enqueue_script('vdp-admin', VDP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), VDP_VERSION, true);
    }

    /**
     * Add plugin action links.
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=vendor-dashboard-pro') . '">' . __('Settings', 'vendor-dashboard-pro') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize Admin
VDP_Admin::instance();