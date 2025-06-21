<?php
/**
 * Products content template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vdp-products-content">
    <!-- Filters -->
    <div class="vdp-filters">
        <div class="vdp-filter-group">
            <div class="vdp-search-box">
                <input type="text" class="vdp-search-input vdp-search-products" placeholder="<?php esc_attr_e('Search listings...', 'vendor-dashboard-pro'); ?>">
                <button type="button" class="vdp-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <div class="vdp-filter-group">
            <select class="vdp-filter-select" name="category">
                <option value=""><?php esc_html_e('All Categories', 'vendor-dashboard-pro'); ?></option>
                <?php if (!empty($categories)) : ?>
                    <?php foreach ($categories as $id => $name) : ?>
                        <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <select class="vdp-filter-select" name="status">
                <option value=""><?php esc_html_e('All Statuses', 'vendor-dashboard-pro'); ?></option>
                <option value="publish"><?php esc_html_e('Published', 'vendor-dashboard-pro'); ?></option>
                <option value="draft"><?php esc_html_e('Draft', 'vendor-dashboard-pro'); ?></option>
                <option value="pending"><?php esc_html_e('Pending', 'vendor-dashboard-pro'); ?></option>
            </select>
        </div>
        
        <div class="vdp-filter-actions">
            <a href="<?php echo esc_url(vdp_get_dashboard_url('products', 'add')); ?>" class="vdp-btn vdp-btn-primary vdp-add-listing-btn">
                <i class="fas fa-plus"></i>
                <?php esc_html_e('Add New Listing', 'vendor-dashboard-pro'); ?>
            </a>
        </div>
    </div>
    
    <!-- System information for debugging -->
    <?php 
    global $wpdb;
    $user_id = get_current_user_id(); 
    // Usar el vendor_id que ahora está garantizado que existe en el scope
    
    // Solo mostrar en modo depuración o para administradores
    if (current_user_can('administrator') || (defined('WP_DEBUG') && WP_DEBUG)) : 
    ?>
    <div class="vdp-debug-info" style="background: #f5f5f5; padding: 10px; margin-bottom: 20px; border-left: 4px solid #0073aa;">
        <p><strong>Información de diagnóstico (solo visible para administradores):</strong></p>
        <ul>
            <li>User ID: <?php echo esc_html($user_id); ?></li>
            <li>Vendor ID: <?php echo isset($vendor_id) ? esc_html($vendor_id) : 'N/A'; ?></li>
            <li>Listings encontrados: <?php echo isset($listings) && is_array($listings) ? count($listings) : 'N/A'; ?></li>
            <li>Total listings: <?php echo isset($total_listings) ? esc_html($total_listings) : 'N/A'; ?></li>
        </ul>
        <?php
        if (isset($vendor_id)) {
            // Consulta directa para verificar listings
            $direct_check = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'hp_listing' AND post_parent = %d AND post_status IN ('publish', 'draft', 'pending')",
                $vendor_id
            ));
            echo '<p>Verificación directa de BD: ' . esc_html($direct_check) . ' listings con post_parent = ' . esc_html($vendor_id) . '</p>';
            
            // Mostrar ejemplos de listings si hay alguno
            if ($direct_check > 0) {
                $example_listings = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID, post_title, post_status FROM {$wpdb->posts} 
                    WHERE post_type = 'hp_listing' AND post_parent = %d 
                    AND post_status IN ('publish', 'draft', 'pending')
                    LIMIT 5",
                    $vendor_id
                ));
                
                if (!empty($example_listings)) {
                    echo '<p><strong>Ejemplos de listings encontrados:</strong></p>';
                    echo '<ul>';
                    foreach ($example_listings as $ex) {
                        echo '<li>ID: ' . $ex->ID . ', Título: ' . $ex->post_title . ', Estado: ' . $ex->post_status . '</li>';
                    }
                    echo '</ul>';
                }
            }
        }
        ?>
    </div>
    <?php endif; ?>
    
    <!-- Listings Grid -->
    <div class="vdp-listings-grid">
        <?php if (empty($listings)) : ?>
            <div class="vdp-empty-state">
                <div class="vdp-empty-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3><?php esc_html_e('No listings found', 'vendor-dashboard-pro'); ?></h3>
                <p><?php esc_html_e('You haven\'t created any listings yet. Create your first listing to get started!', 'vendor-dashboard-pro'); ?></p>
                <a href="<?php echo esc_url(vdp_get_dashboard_url('products', 'add')); ?>" class="vdp-btn vdp-btn-primary vdp-add-listing-btn">
                    <i class="fas fa-plus"></i>
                    <?php esc_html_e('Add Your First Listing', 'vendor-dashboard-pro'); ?>
                </a>
            </div>
        <?php else : ?>
            <?php foreach ($listings as $listing) : ?>
                <div class="vdp-listing-card" data-status="<?php echo esc_attr($listing['status']); ?>" data-id="<?php echo esc_attr($listing['id']); ?>">
                    <div class="vdp-listing-thumbnail">
                        <?php if (!empty($listing['thumbnail'])) : ?>
                            <img src="<?php echo esc_url($listing['thumbnail']); ?>" alt="<?php echo esc_attr($listing['title']); ?>">
                        <?php else : ?>
                            <div class="vdp-listing-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="vdp-listing-status">
                            <span class="vdp-status-badge vdp-status-<?php echo esc_attr($listing['status']); ?>">
                                <?php echo esc_html(ucfirst($listing['status'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="vdp-listing-content">
                        <h3 class="vdp-listing-title">
                            <?php echo esc_html($listing['title']); ?>
                        </h3>
                        
                        <div class="vdp-listing-price">
                            <?php if ($listing['price'] > 0) : ?>
                                <span class="vdp-price"><?php echo esc_html(vdp_format_price($listing['price'])); ?></span>
                            <?php else : ?>
                                <span class="vdp-price vdp-price-free"><?php esc_html_e('Free', 'vendor-dashboard-pro'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="vdp-listing-meta">
                            <span class="vdp-listing-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo esc_html(vdp_format_date($listing['date'])); ?>
                            </span>
                        </div>
                        
                        <div class="vdp-listing-actions">
                            <a href="<?php echo esc_url($listing['edit_url']); ?>" class="vdp-btn vdp-btn-secondary vdp-btn-sm">
                                <i class="fas fa-edit"></i>
                                <?php esc_html_e('Edit', 'vendor-dashboard-pro'); ?>
                            </a>
                            
                            <a href="<?php echo esc_url(get_permalink($listing['id'])); ?>" class="vdp-btn vdp-btn-outline vdp-btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                                <?php esc_html_e('View', 'vendor-dashboard-pro'); ?>
                            </a>
                            
                            <button class="vdp-btn vdp-btn-danger vdp-btn-sm vdp-delete-listing" data-listing-id="<?php echo esc_attr($listing['id']); ?>">
                                <i class="fas fa-trash"></i>
                                <?php esc_html_e('Delete', 'vendor-dashboard-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (!empty($listings) && isset($total_pages) && $total_pages > 1) : ?>
        <div class="vdp-pagination">
            <?php
            $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
            $current_url = vdp_get_dashboard_url('products');
            
            // Previous page
            if ($current_page > 1) {
                $prev_url = add_query_arg('paged', $current_page - 1, $current_url);
                echo '<a href="' . esc_url($prev_url) . '" class="vdp-pagination-item vdp-pagination-prev">';
                echo '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'vendor-dashboard-pro');
                echo '</a>';
            }
            
            // Page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                if ($i == $current_page) {
                    echo '<span class="vdp-pagination-item vdp-pagination-current">' . esc_html($i) . '</span>';
                } else {
                    $page_url = add_query_arg('paged', $i, $current_url);
                    echo '<a href="' . esc_url($page_url) . '" class="vdp-pagination-item">' . esc_html($i) . '</a>';
                }
            }
            
            // Next page
            if ($current_page < $total_pages) {
                $next_url = add_query_arg('paged', $current_page + 1, $current_url);
                echo '<a href="' . esc_url($next_url) . '" class="vdp-pagination-item vdp-pagination-next">';
                echo esc_html__('Next', 'vendor-dashboard-pro') . ' <i class="fas fa-chevron-right"></i>';
                echo '</a>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<style>
.vdp-listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.vdp-listing-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.vdp-listing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.vdp-listing-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.vdp-listing-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.vdp-listing-placeholder {
    width: 100%;
    height: 100%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 48px;
}

.vdp-listing-status {
    position: absolute;
    top: 10px;
    right: 10px;
}

.vdp-status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.vdp-status-publish {
    background: #d4edda;
    color: #155724;
}

.vdp-status-draft {
    background: #fff3cd;
    color: #856404;
}

.vdp-status-pending {
    background: #d1ecf1;
    color: #0c5460;
}

.vdp-listing-content {
    padding: 15px;
}

.vdp-listing-title {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
}

.vdp-listing-price {
    margin-bottom: 10px;
}

.vdp-price {
    font-size: 18px;
    font-weight: 700;
    color: #3483fa;
}

.vdp-price-free {
    color: #28a745;
}

.vdp-listing-meta {
    margin-bottom: 15px;
    color: #666;
    font-size: 14px;
}

.vdp-listing-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.vdp-btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}
</style>