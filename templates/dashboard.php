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

// Get current action and item
$current_action = vdp_get_current_action();
$current_item = vdp_get_current_item();

// Get vendor
$vendor = vdp_get_current_vendor();

if (!$vendor) {
    echo '<div class="vdp-notice vdp-notice-error">';
    echo '<p>' . esc_html__('You must be a registered vendor to access this dashboard.', 'vendor-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get page title based on current action
$page_titles = array(
    'dashboard' => __('Dashboard', 'vendor-dashboard-pro'),
    'products' => __('Products', 'vendor-dashboard-pro'),
    'orders' => __('Orders', 'vendor-dashboard-pro'),
    'messages' => __('Messages', 'vendor-dashboard-pro'),
    'analytics' => __('Analytics', 'vendor-dashboard-pro'),
    'settings' => __('Settings', 'vendor-dashboard-pro'),
);

// If we have a specific action like 'edit' or 'view'
if ($current_action === 'products' && isset($_GET['edit'])) {
    $page_title = __('Edit Product', 'vendor-dashboard-pro');
} elseif ($current_action === 'products' && isset($_GET['add'])) {
    $page_title = __('Add Product', 'vendor-dashboard-pro');
} elseif ($current_action === 'orders' && $current_item) {
    $page_title = __('Order Details', 'vendor-dashboard-pro');
} elseif ($current_action === 'messages' && $current_item) {
    $page_title = __('Message Details', 'vendor-dashboard-pro');
} else {
    $page_title = isset($page_titles[$current_action]) ? $page_titles[$current_action] : $page_titles['dashboard'];
}
?>

<div class="vdp-wrapper">
    <div class="vdp-container">
        <div class="vdp-dashboard">
            <!-- Mobile Menu Toggle -->
            <div class="vdp-mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            
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
                <nav class="vdp-sidebar-nav">
                    <ul class="vdp-nav-list">
                        <li class="vdp-nav-item <?php echo $current_action === 'dashboard' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url()); ?>" class="vdp-nav-link vdp-ajax-link" data-action="dashboard">
                                <i class="fas fa-home"></i>
                                <span><?php esc_html_e('Dashboard', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_action === 'products' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="products">
                                <i class="fas fa-box"></i>
                                <span><?php esc_html_e('Products', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_action === 'orders' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('orders')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="orders">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?php esc_html_e('Orders', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_action === 'messages' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="messages">
                                <i class="fas fa-envelope"></i>
                                <span><?php esc_html_e('Messages', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_action === 'analytics' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('analytics')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="analytics">
                                <i class="fas fa-chart-line"></i>
                                <span><?php esc_html_e('Analytics', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $current_action === 'settings' ? 'vdp-active' : ''; ?>">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('settings')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="settings">
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
                        <?php if ($current_action === 'products') : ?>
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products', 'add')); ?>" class="vdp-btn vdp-btn-primary vdp-ajax-link" data-action="products" data-item="add">
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
                
                <!-- Content Area -->
                <div class="vdp-content-area">
                    <?php
                    // Display content based on current action
                    switch ($current_action) {
                        case 'products':
                            if (isset($_GET['add']) || isset($_GET['edit'])) {
                                include(VDP_PLUGIN_DIR . 'templates/products-edit-content.php');
                            } else {
                                include(VDP_PLUGIN_DIR . 'templates/products-content.php');
                            }
                            break;
                            
                        case 'orders':
                            if ($current_item) {
                                include(VDP_PLUGIN_DIR . 'templates/order-view-content.php');
                            } else {
                                include(VDP_PLUGIN_DIR . 'templates/orders-content.php');
                            }
                            break;
                            
                        case 'messages':
                            if ($current_item) {
                                include(VDP_PLUGIN_DIR . 'templates/message-view-content.php');
                            } else {
                                include(VDP_PLUGIN_DIR . 'templates/messages-content.php');
                            }
                            break;
                            
                        case 'analytics':
                            include(VDP_PLUGIN_DIR . 'templates/analytics-content.php');
                            break;
                            
                        case 'settings':
                            include(VDP_PLUGIN_DIR . 'templates/settings-content.php');
                            break;
                            
                        case 'dashboard':
                        default:
                            include(VDP_PLUGIN_DIR . 'templates/dashboard-content.php');
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>