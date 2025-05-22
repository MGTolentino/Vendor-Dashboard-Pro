<?php
/**
 * Lead view template
 *
 * @package Vendor Dashboard Pro
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get the page title
$page_title = __('Ver Lead', 'vendor-dashboard-pro');
?>

<div class="vdp-dashboard">
    <div class="vdp-dashboard-wrapper">
        <div class="vdp-dashboard-main">
            <div class="vdp-dashboard-header">
                <h1 class="vdp-dashboard-title"><?php echo esc_html($page_title); ?></h1>
                <div class="vdp-dashboard-breadcrumb">
                    <a href="<?php echo esc_url(vdp_get_dashboard_url()); ?>" class="vdp-breadcrumb-link">
                        <?php esc_html_e('Dashboard', 'vendor-dashboard-pro'); ?>
                    </a>
                    <span class="vdp-breadcrumb-separator">/</span>
                    <a href="<?php echo esc_url(vdp_get_dashboard_url('leads')); ?>" class="vdp-breadcrumb-link">
                        <?php esc_html_e('Leads', 'vendor-dashboard-pro'); ?>
                    </a>
                    <span class="vdp-breadcrumb-separator">/</span>
                    <span class="vdp-breadcrumb-current"><?php echo esc_html($page_title); ?></span>
                </div>
            </div>
            
            <div class="vdp-dashboard-content">
                <?php include VDP_PLUGIN_PATH . 'templates/lead-view-content.php'; ?>
            </div>
        </div>
    </div>
</div>