<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$current_user = Control_Auth::current_user();

// Vendor-specific sales metrics
$stats = $wpdb->get_row( $wpdb->prepare( "
    SELECT
        SUM(price * quantity) as total_revenue,
        COUNT(DISTINCT order_id) as total_orders,
        COUNT(id) as total_items_sold
    FROM {$wpdb->prefix}matjar_order_items
    WHERE vendor_id = %s
", $current_user->id ) );

$revenue = $stats->total_revenue ?: 0;
$orders_count = $stats->total_orders ?: 0;
$items_count = $stats->total_items_sold ?: 0;
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="font-weight:800; font-size:1.4rem; margin:0; color:#1e293b;"><?php _e('التحليلات المالية والنشاط', 'control'); ?></h2>
</div>

<div class="control-metrics-grid" style="margin-bottom:30px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
    <div class="control-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; border:none; padding:25px;">
        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.7); font-weight: 600; margin-bottom:5px;"><?php _e('إجمالي المبيعات', 'control'); ?></div>
        <div style="font-size: 1.8rem; font-weight: 900;"><?php echo number_format($revenue, 2); ?> <small style="font-size:1rem;">EGP</small></div>
    </div>
    <div class="control-card" style="padding:25px;">
        <div style="font-size: 0.8rem; color: var(--control-muted); font-weight: 600; margin-bottom:5px;"><?php _e('عدد الطلبات', 'control'); ?></div>
        <div style="font-size: 1.8rem; font-weight: 900; color:var(--control-text-dark);"><?php echo number_format($orders_count); ?></div>
    </div>
    <div class="control-card" style="padding:25px;">
        <div style="font-size: 0.8rem; color: var(--control-muted); font-weight: 600; margin-bottom:5px;"><?php _e('القطع المباعة', 'control'); ?></div>
        <div style="font-size: 1.8rem; font-weight: 900; color:var(--control-text-dark);"><?php echo number_format($items_count); ?></div>
    </div>
</div>

<div class="control-card">
    <h3 style="margin-bottom:20px;"><?php _e('تاريخ المبيعات التفصيلي', 'control'); ?></h3>
    <p style="text-align:center; padding:40px; color:var(--control-muted);"><?php _e('سيظهر الرسم البياني للمبيعات هنا قريباً.', 'control'); ?></p>
</div>
