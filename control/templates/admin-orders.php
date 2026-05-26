<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$current_user = Control_Auth::current_user();
$is_admin = Control_Auth::is_admin();

if ($is_admin) {
    $orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_orders ORDER BY created_at DESC" );
} else {
    // For Vendors, find orders containing their items
    $order_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT order_id FROM {$wpdb->prefix}matjar_order_items WHERE vendor_id = %s", $current_user->id ) );
    if ($order_ids) {
        $ids_str = implode(',', array_map('intval', $order_ids));
        $orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_orders WHERE id IN ($ids_str) ORDER BY created_at DESC" );
    } else {
        $orders = array();
    }
}
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-weight:800; font-size:1.3rem; margin:0; color:var(--control-text-dark);"><?php echo $is_admin ? __('استقبال الطلبات', 'control') : __('إدارة طلبات المتجر', 'control'); ?></h2>
</div>

<div class="control-card" style="padding: 0; overflow: hidden;">
    <table class="control-table" style="width:100%; border-collapse: collapse;">
        <thead style="background:#f8fafc;">
            <tr>
                <th style="padding:15px; text-align:right;"><?php _e('رقم الطلب', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('العميل', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('الإجمالي', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('الحالة', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('التاريخ', 'control'); ?></th>
                <th style="padding:15px; text-align:left;"><?php _e('الإجراءات', 'control'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders): foreach($orders as $order): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding:15px; font-weight:700;">#<?php echo $order->id; ?></td>
                    <td style="padding:15px;"><?php echo esc_html($order->user_id); ?></td>
                    <td style="padding:15px; font-weight:800; color:var(--control-accent);"><?php echo $order->total; ?> EGP</td>
                    <td style="padding:15px;">
                        <span class="control-status-indicator <?php echo $order->status === 'pending' ? 'indicator-warning' : 'indicator-success'; ?>">
                            <?php echo esc_html($order->status); ?>
                        </span>
                    </td>
                    <td style="padding:15px; color:var(--control-muted); font-size:0.8rem;"><?php echo date('Y/m/d H:i', strtotime($order->created_at)); ?></td>
                    <td style="padding:15px; text-align:left;">
                        <button class="audit-action-btn view-order-details" data-order='<?php echo json_encode($order); ?>'><span class="dashicons dashicons-visibility"></span></button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="padding:40px; text-align:center; color:var(--control-muted);"><?php _e('لا توجد طلبات واردة حالياً.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
