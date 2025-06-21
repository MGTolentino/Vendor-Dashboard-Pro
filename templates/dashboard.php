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
    echo '<p>' . esc_html__('Please register as a vendor to continue.', 'vendor-dashboard-pro') . '</p>';
    if (function_exists('hivepress') && hivepress()->router->get_url('vendor_register_page')) {
        echo '<a href="' . esc_url(hivepress()->router->get_url('vendor_register_page')) . '" class="vdp-btn vdp-btn-primary">';
        echo esc_html__('Register as Vendor', 'vendor-dashboard-pro');
        echo '</a>';
    }
    echo '</div>';
    return;
}

// Get page title based on current action
$page_titles = array(
    'dashboard' => __('Dashboard', 'vendor-dashboard-pro'),
    'products' => __('Listings', 'vendor-dashboard-pro'),
    'orders' => __('Orders', 'vendor-dashboard-pro'),
    'messages' => __('Messages', 'vendor-dashboard-pro'),
    'analytics' => __('Analytics', 'vendor-dashboard-pro'),
    'settings' => __('Settings', 'vendor-dashboard-pro'),
);

// If we have a specific action like 'edit' or 'view'
if ($current_action === 'products' && isset($_GET['edit'])) {
    $page_title = __('Edit Listing', 'vendor-dashboard-pro');
} elseif ($current_action === 'products' && isset($_GET['add'])) {
    $page_title = __('Add Listing', 'vendor-dashboard-pro');
} elseif ($current_action === 'orders' && $current_item) {
    $page_title = __('Order Details', 'vendor-dashboard-pro');
} elseif ($current_action === 'messages' && $current_item) {
    $page_title = __('Message Details', 'vendor-dashboard-pro');
} else {
    $page_title = isset($page_titles[$current_action]) ? $page_titles[$current_action] : $page_titles['dashboard'];
}

// Use safe vendor access functions from functions.php
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
                        <?php if (is_object($vendor) && method_exists($vendor, 'get_image__url') && $vendor->get_image__url('thumbnail')) : ?>
                            <img src="<?php echo esc_url($vendor->get_image__url('thumbnail')); ?>" alt="<?php echo esc_attr((is_object($vendor) && method_exists($vendor, 'get_name')) ? $vendor->get_name() : 'Vendor'); ?>">
                        <?php elseif (is_object($vendor) && isset($vendor->get_image__url) && is_callable($vendor->get_image__url) && ($vendor->get_image__url)('thumbnail')) : ?>
                            <img src="<?php echo esc_url(($vendor->get_image__url)('thumbnail')); ?>" alt="<?php echo esc_attr(is_callable($vendor->get_name) ? ($vendor->get_name)() : 'Vendor'); ?>">
                        <?php else : ?>
                            <div class="vdp-avatar-placeholder">
                                <i class="fas fa-store"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="vdp-vendor-info">
                        <h3 class="vdp-vendor-name">
                            <?php 
                            if (is_object($vendor) && method_exists($vendor, 'get_name')) {
                                echo esc_html($vendor->get_name());
                            } elseif (is_object($vendor) && isset($vendor->get_name) && is_callable($vendor->get_name)) {
                                echo esc_html(($vendor->get_name)());
                            } else {
                                echo esc_html('Test Vendor');
                            }
                            ?>
                            <?php 
                            $is_verified = false;
                            if (is_object($vendor) && method_exists($vendor, 'is_verified')) {
                                $is_verified = $vendor->is_verified();
                            } elseif (is_object($vendor) && isset($vendor->is_verified) && is_callable($vendor->is_verified)) {
                                $is_verified = ($vendor->is_verified)();
                            }
                            
                            if ($is_verified) : 
                            ?>
                                <span class="vdp-verified-badge" title="<?php esc_attr_e('Verified Seller', 'vendor-dashboard-pro'); ?>">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            <?php endif; ?>
                        </h3>
                        <div class="vdp-vendor-status">
                            <?php 
                            $is_active = false;
                            if (is_object($vendor) && method_exists($vendor, 'is_active_seller')) {
                                $is_active = $vendor->is_active_seller();
                            } elseif (is_object($vendor) && isset($vendor->is_active_seller) && is_callable($vendor->is_active_seller)) {
                                $is_active = ($vendor->is_active_seller)();
                            }
                            
                            $status_class = $is_active ? 'vdp-status-active' : 'vdp-status-inactive';
                            $status_text = $is_active ? __('Active Seller', 'vendor-dashboard-pro') : __('Inactive Seller', 'vendor-dashboard-pro');
                            ?>
                            <span class="vdp-status-indicator <?php echo esc_attr($status_class); ?>"></span>
                            <?php echo esc_html($status_text); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="vdp-sidebar-nav">
                    <ul class="vdp-nav-list">
                        <?php 
                        // Asegurar que la acción actual esté correctamente identificada
                        // Usar vdp_get_current_action() que obtendrá el valor de la variable global $vdp_current_action
                        $active_action = vdp_get_current_action();
                        vdp_debug_log("Acción activa en navegación: " . $active_action);
                        ?>
                        <li class="vdp-nav-item <?php echo $active_action === 'dashboard' ? 'vdp-active' : ''; ?>" id="vdp-nav-dashboard">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url()); ?>" class="vdp-nav-link vdp-ajax-link" data-action="dashboard">
                                <i class="fas fa-home"></i>
                                <span><?php esc_html_e('Dashboard', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'products' ? 'vdp-active' : ''; ?>" id="vdp-nav-products">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('products')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="products">
                                <i class="fas fa-box"></i>
                                <span><?php esc_html_e('Listings', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'orders' ? 'vdp-active' : ''; ?>" id="vdp-nav-orders">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('orders')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="orders">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?php esc_html_e('Orders', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'leads' ? 'vdp-active' : ''; ?>" id="vdp-nav-leads">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('leads')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="leads">
                                <i class="fas fa-user-plus"></i>
                                <span><?php esc_html_e('Leads', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'messages' ? 'vdp-active' : ''; ?>" id="vdp-nav-messages">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('messages')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="messages">
                                <i class="fas fa-envelope"></i>
                                <span><?php esc_html_e('Messages', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'analytics' ? 'vdp-active' : ''; ?>" id="vdp-nav-analytics">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('analytics')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="analytics">
                                <i class="fas fa-chart-line"></i>
                                <span><?php esc_html_e('Analytics', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                        <li class="vdp-nav-item <?php echo $active_action === 'settings' ? 'vdp-active' : ''; ?>" id="vdp-nav-settings">
                            <a href="<?php echo esc_url(vdp_get_dashboard_url('settings')); ?>" class="vdp-nav-link vdp-ajax-link" data-action="settings">
                                <i class="fas fa-cog"></i>
                                <span><?php esc_html_e('Settings', 'vendor-dashboard-pro'); ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Original HivePress Link -->
                <div class="vdp-sidebar-footer">
                    <?php
                    $vendor_id = null;
                    $vendor_slug = '';
                    
                    if (is_object($vendor) && method_exists($vendor, 'get_id')) {
                        $vendor_id = $vendor->get_id();
                    } elseif (is_object($vendor) && isset($vendor->get_id) && is_callable($vendor->get_id)) {
                        $vendor_id = ($vendor->get_id)();
                    }
                    
                    if (is_object($vendor) && method_exists($vendor, 'get_slug')) {
                        $vendor_slug = $vendor->get_slug();
                    } elseif (is_object($vendor) && isset($vendor->get_slug) && is_callable($vendor->get_slug)) {
                        $vendor_slug = ($vendor->get_slug)();
                    }
                    
                    // Determine vendor profile URL
                    $profile_url = '#';
                    if (class_exists('\HivePress\Models\Vendor') && function_exists('hivepress') && $vendor_id) {
                        // Try using HivePress URL
                        try {
                            $profile_url = hivepress()->router->get_url('vendor_view_page', ['id' => $vendor_id]);
                        } catch (Exception $e) {
                            $profile_url = get_permalink($vendor_id);
                        }
                    } elseif ($vendor_id) {
                        // Fallback to post permalink
                        $profile_url = get_permalink($vendor_id);
                    } elseif (!empty($vendor_slug)) {
                        // Try to construct URL from slug - check both Spanish and English patterns
                        $base_url = home_url('/');
                        $possible_urls = array(
                            $base_url . 'official-store-for/' . $vendor_slug . '/',
                            $base_url . 'tienda-oficial-de/' . $vendor_slug . '/',
                        );
                        
                        // Check which URL exists by making a quick HEAD request or checking pages
                        foreach ($possible_urls as $test_url) {
                            // For now, we'll use the first one as default
                            $profile_url = $test_url;
                            break;
                        }
                    }
                    ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="vdp-hivepress-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <?php esc_html_e('View Public Profile', 'vendor-dashboard-pro'); ?>
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
                                <?php esc_html_e('Add New Listing', 'vendor-dashboard-pro'); ?>
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
                                    <a href="<?php echo esc_url(vdp_get_dashboard_url('notifications')); ?>"><?php esc_html_e('View All Notifications', 'vendor-dashboard-pro'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Content Area -->
                <div class="vdp-content-area">
                    <?php
                    // Para evitar duplicaciones, siempre usamos el router para renderizar el contenido
                    // El router ya maneja las acciones específicas para cada sección
                    
                    // Renderizar el contenido basado en la acción actual
                    vdp_debug_log("Dashboard renderizando contenido para acción: " . $current_action);
                    
                    // Usamos el método del router que maneja las acciones y hooks adecuados
                    VDP_Router::render_content($current_action, $current_item);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>