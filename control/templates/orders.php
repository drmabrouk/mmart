<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php
$current_user = Control_Auth::current_user();
global $wpdb;
$orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}matjar_orders WHERE user_id = %s ORDER BY created_at DESC", $current_user->id ) );
?>

<div class="matjar-orders-container">
    <div class="control-card">
        <h3><?php _e('تاريخ الطلبات', 'control'); ?></h3>
        <div class="order-list">
            <?php if ( $orders ) : ?>
                <table class="control-table" style="width: 100%; text-align: right; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--control-border);">
                            <th style="padding: 10px;"><?php _e('رقم الطلب', 'control'); ?></th>
                            <th style="padding: 10px;"><?php _e('التاريخ', 'control'); ?></th>
                            <th style="padding: 10px;"><?php _e('الإجمالي', 'control'); ?></th>
                            <th style="padding: 10px;"><?php _e('الحالة', 'control'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $orders as $order ) : ?>
                            <tr style="border-bottom: 1px solid var(--control-border);">
                                <td style="padding: 10px;">#<?php echo $order->id; ?></td>
                                <td style="padding: 10px;"><?php echo date('Y/m/d', strtotime($order->created_at)); ?></td>
                                <td style="padding: 10px; font-weight: 800; color: var(--control-accent);"><?php echo $order->total; ?> SAR</td>
                                <td style="padding: 10px;">
                                    <span class="control-status-indicator indicator-warning"><?php echo esc_html($order->status); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p style="text-align: center; padding: 40px; color: var(--control-muted);">
                    <?php _e('لا توجد طلبات سابقة', 'control'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
