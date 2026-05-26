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
                <th style="padding:15px; text-align:right;"><?php _e('الطلب', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('العميل والوجهة', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('الملخص المالي', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('الحالة', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('التاريخ', 'control'); ?></th>
                <th style="padding:15px; text-align:left;"><?php _e('الإجراءات', 'control'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders): foreach($orders as $order): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding:15px;">
                        <div style="font-weight:700;">#<?php echo $order->id; ?></div>
                        <div style="font-size:0.7rem; color:var(--control-muted);"><?php echo count(json_decode($order->items, true)); ?> <?php _e('منتجات', 'control'); ?></div>
                    </td>
                    <td style="padding:15px;">
                        <div style="font-weight:600;"><?php echo esc_html($order->user_id); ?></div>
                        <div style="font-size:0.75rem; color:var(--control-muted);"><?php echo esc_html($order->governorate); ?>، <?php echo esc_html($order->city); ?></div>
                    </td>
                    <td style="padding:15px;">
                        <div style="font-size:0.75rem; color:var(--control-muted);"><?php _e('الشحن:', 'control'); ?> <?php echo $order->shipping_cost; ?></div>
                        <div style="font-weight:800; color:var(--control-accent); font-size:1rem;"><?php echo $order->total; ?> EGP</div>
                    </td>
                    <td style="padding:15px;">
                        <span class="control-status-indicator <?php echo $order->status === 'pending' ? 'indicator-warning' : 'indicator-success'; ?>">
                            <?php echo esc_html($order->status); ?>
                        </span>
                    </td>
                    <td style="padding:15px; color:var(--control-muted); font-size:0.8rem;"><?php echo date('Y/m/d H:i', strtotime($order->created_at)); ?></td>
                    <td style="padding:15px; text-align:left;">
                        <button class="audit-action-btn view-order-details" data-order='<?php echo json_encode($order, JSON_UNESCAPED_UNICODE); ?>'><span class="dashicons dashicons-visibility"></span></button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="padding:40px; text-align:center; color:var(--control-muted);"><?php _e('لا توجد طلبات واردة حالياً.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Order Details Modal -->
<div id="matjar-order-details-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000000; align-items:center; justify-content:center; backdrop-filter: blur(5px); direction:rtl;">
    <div class="control-card" style="width: 100%; max-width: 650px; padding: 40px; border-radius:24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; border-bottom:1px solid var(--control-bg); padding-bottom:20px;">
            <div>
                <h3 id="order-modal-id" style="margin:0; font-size:1.4rem;"></h3>
                <p id="order-modal-date" style="margin:5px 0 0 0; color:var(--control-muted); font-size:0.85rem;"></p>
            </div>
            <span id="order-modal-status" class="control-status-indicator"></span>
        </div>

        <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:30px;">
            <div>
                <h4 style="margin:0 0 12px 0; font-size:0.9rem; color:var(--control-primary);"><?php _e('بيانات العميل والشحن', 'control'); ?></h4>
                <div style="background:var(--control-bg); padding:15px; border-radius:12px; font-size:0.85rem; line-height:1.6;">
                    <div id="order-modal-client" style="font-weight:700; margin-bottom:8px;"></div>
                    <div id="order-modal-location" style="color:var(--control-muted); margin-bottom:8px;"></div>
                    <div id="order-modal-address" style="font-style:italic;"></div>
                    <div id="order-modal-gps" style="margin-top:10px;"></div>
                </div>
            </div>
            <div>
                <h4 style="margin:0 0 12px 0; font-size:0.9rem; color:var(--control-primary);"><?php _e('ملاحظات الطلب', 'control'); ?></h4>
                <div id="order-modal-notes" style="background:#fffbeb; padding:15px; border-radius:12px; font-size:0.85rem; color:#92400e; border:1px solid #fef3c7; height:100%;"></div>
            </div>
        </div>

        <div style="margin-bottom:30px;">
            <h4 style="margin:0 0 15px 0; font-size:0.9rem;"><?php _e('المنتجات المطلوبة', 'control'); ?></h4>
            <div id="order-modal-items" style="max-height:200px; overflow-y:auto; border:1px solid var(--control-border); border-radius:12px;"></div>
        </div>

        <div style="background:var(--control-primary); color:#fff; padding:20px; border-radius:16px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:0.8rem; opacity:0.8;"><?php _e('إجمالي المبلغ المستحق:', 'control'); ?></div>
                <div id="order-modal-total" style="font-size:1.5rem; font-weight:900; color:var(--control-accent);"></div>
            </div>
            <button type="button" class="control-btn matjar-close-order-modal" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);"><?php _e('إغلاق النافذة', 'control'); ?></button>
        </div>
    </div>
</div>
