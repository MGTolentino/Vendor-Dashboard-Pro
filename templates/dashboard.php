<?php
/**
 * Main dashboard template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get current section
$current_section = vdp_get_current_section();

// Get vendor
$vendor = vdp_get_current_vendor();

if (!$vendor) {
    wp_redirect(home_url());
    exit;
}

// Get page title
$page_title = get_the_title();

// Set custom page title based on section
switch ($current_section) {
    case 'products':
        $action = get_query_var('vdp_action', '');
        if ($action === 'add') {
            $page_title = __('Add New Product', 'vendor-dashboard-pro');
        } elseif ($action === 'edit') {
            $page_title = __('Edit Product', 'vendor-dashboard-pro');
        } else {
            $page_title = __('Products', 'vendor-dashboard-pro');
        }
        break;
        
    case 'orders':
        $action = get_query_var('vdp_action', '');
        if ($action === 'view') {
            $page_title = __('Order Details', 'vendor-dashboard-pro');
        } else {
            $page_title = __('Orders', 'vendor-dashboard-pro');
        }
        break;
        
    case 'messages':
        $action = get_query_var('vdp_action', '');
        if ($action === 'view') {
            $page_title = __('Message Details', 'vendor-dashboard-pro');
        } else {
            $page_title = __('Messages', 'vendor-dashboard-pro');
        }
        break;
        
    case 'analytics':
        $page_title = __('Analytics', 'vendor-dashboard-pro');
        break;
        
    case 'settings':
        $page_title = __('Settings', 'vendor-dashboard-pro');
        break;
        
    case 'dashboard':
    default:
        $page_title = __('Dashboard', 'vendor-dashboard-pro');
        break;
}

// Get header and footer
get_header();
?>

<div class="vdp-wrapper">
    <div class="vdp-container">
        <div class="vdp-dashboard">
            <!-- Sidebar -->
            <div class="vdp-sidebar">
                <!-- Vendor Profile -->
                <div class="vdp-vendor-profile">
                    <div class="vdp-vendor-avatar">
                        <?php if ($vendor->get_image__url('thumbnail')) : ?>
                            <img src="<?php echo esc_url($vendor->get_image__url('thumbnail')); ?>" alt="<?php echo esc_attr($vendor->get_name()); ?>">
                        <?php else : ?>
                            <div class="vdp-avatar-placeholder">
                                <i class="fas fa-store"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="vdp-vendor-info">
                        <h3 class="vdp-vendor-name">
                            <?php echo esc_html($vendor->get_name()); ?>
                            <?php if ($vendor->is_verified()) : ?>
                                <span class="vdp-verified-badge" title="<?php esc_attr_e('Verified Seller', 'vendor-dashboard-pro'); ?>">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            <?php endif; ?>
                        </h3>
                        <div class="vdp-vendor-status">
                            <span class="vdp-status-indicator vdp-status-active"></span>
                            <?php esc_html_e('Active Seller', 'vendor-dashboard-pro'); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="vdp-navigation">
                    <ul class="vdp-nav-list">
                        <li class="vdp-nav-item <?php echo $current_section === 'dashboard' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url()); ?>" class="vdp-nav-link">
                                <i class="fas fa-home"></i>
                                <span><?php esc_html_e('Dashboard', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_section === 'products' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-nav-link">
                                <i class="fas fa-box"></i>
                                <span><?php esc_html_e('Products', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_section === 'orders' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('orders')); ?>" class="vdp-nav-link">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?php esc_html_e('Orders', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_section === 'messages' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>" class="vdp-nav-link">
                                <i class="fas fa-envelope"></i>
                                <span><?php esc_html_e('Messages', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_section === 'analytics' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('analytics')); ?>" class="vdp-nav-link">
                                <i class="fas fa-chart-line"></i>
                                <span><?php esc_html_e('Analytics', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_section === 'settings' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('settings')); ?>" class="vdp-nav-link">
                                <i class="fas fa-cog"></i>
                                <span><?php esc_html_e('Settings', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Original HivePress Link -->
                <div class="vdp-sidebar-footer">
                    <a href="<?php echo esc_url(get_permalink($vendor->get_id())); ?>" class="vdp-hivepress-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <?php esc_html_e('View Original Profile', 'vendor-dashboard-pro'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="vdp-main">
                <!-- Header -->
                <header class="vdp-header">
                    <div class="vdp-header-title">
                        <h1><?php echo esc_html($page_title); ?></h1>
                    </div>
                    
                    <div class="vdp-header-actions">
                        <?php if ($current_section === 'products' && empty(get_query_var('vdp_action'))) : ?>
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products/add')); ?>" class="vdp-btn vdp-btn-primary">
                                <i class="fas fa-plus"></i>
                                <?php esc_html_e('Add New Product', 'vendor-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <div class="vdp-notifications">
                            <a href="#" class="vdp-notification-toggle">
                                <i class="fas fa-bell"></i>
                                <span class="vdp-notification-count">3</span>
                            </a>
                            <div class="vdp-notification-dropdown">
                                <div class="vdp-notification-header">
                                    <h3><?php esc_html_e('Notifications', 'vendor-dashboard-pro'); ?></h3>
                                </div>
                                <div class="vdp-notification-list">
                                    <a href="#" class="vdp-notification-item vdp-unread">
                                        <div class="vdp-notification-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="vdp-notification-content">
                                            <p class="vdp-notification-text"><?php esc_html_e('You have a new order!', 'vendor-dashboard-pro'); ?></p>
                                            <p class="vdp-notification-time"><?php esc_html_e('2 hours ago', 'vendor-dashboard-pro'); ?></p>
                                        </div>
                                    </a>
                                    <a href="#" class="vdp-notification-item vdp-unread">
                                        <div class="vdp-notification-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="vdp-notification-content">
                                            <p class="vdp-notification-text"><?php esc_html_e('New message from John Doe', 'vendor-dashboard-pro'); ?></p>
                                            <p class="vdp-notification-time"><?php esc_html_e('3 hours ago', 'vendor-dashboard-pro'); ?></p>
                                        </div>
                                    </a>
                                    <a href="#" class="vdp-notification-item vdp-unread">
                                        <div class="vdp-notification-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="vdp-notification-content">
                                            <p class="vdp-notification-text"><?php esc_html_e('You received a new review!', 'vendor-dashboard-pro'); ?></p>
                                            <p class="vdp-notification-time"><?php esc_html_e('Yesterday', 'vendor-dashboard-pro'); ?></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="vdp-notification-footer">
                                    <a href="#"><?php esc_html_e('View All Notifications', 'vendor-dashboard-pro'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Content -->
                <div class="vdp-content">
                    <?php
                    // Display content based on current section
                    switch ($current_section) {
                        case 'products':
                            $action = get_query_var('vdp_action', '');
                            if ($action === 'add' || $action === 'edit') {
                                do_action('vdp_product_edit_content');
                            } else {
                                do_action('vdp_products_content');
                            }
                            break;
                            
                        case 'orders':
                            $action = get_query_var('vdp_action', '');
                            if ($action === 'view') {
                                do_action('vdp_order_view_content');
                            } else {
                                do_action('vdp_orders_content');
                            }
                            break;
                            
                        case 'messages':
                            $action = get_query_var('vdp_action', '');
                            if ($action === 'view') {
                                do_action('vdp_message_view_content');
                            } else {
                                do_action('vdp_messages_content');
                            }
                            break;
                            
                        case 'analytics':
                            do_action('vdp_analytics_content');
                            break;
                            
                        case 'settings':
                            do_action('vdp_settings_content');
                            break;
                            
                        case 'dashboard':
                        default:
                            do_action('vdp_dashboard_content');
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();