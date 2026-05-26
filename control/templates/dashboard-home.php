<?php
global $wpdb;
$current_user = Control_Auth::current_user();
if ( Control_Auth::is_admin() ) {
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}control_staff");
    ?>
    <div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="font-weight:800; font-size:1.4rem; margin:0; color:#1e293b;"><?php _e('نظرة عامة على المتجر', 'control'); ?></h2>
        <div style="display:flex; gap:10px;">
            <a href="<?php echo admin_url('admin.php?page=matjar-products'); ?>" class="control-btn" style="background:#0f172a; border:none; height:40px; font-size:0.8rem;"><span class="dashicons dashicons-cart" style="margin-left:5px;"></span><?php _e('إدارة المنتجات', 'control'); ?></a>
            <a href="<?php echo add_query_arg('control_view', 'users'); ?>" class="control-btn" style="background:var(--control-accent); color:#000 !important; border:none; height:40px; font-size:0.8rem;"><span class="dashicons dashicons-groups" style="margin-left:5px;"></span><?php _e('العملاء والشركاء', 'control'); ?></a>
        </div>
    </div>

    <?php
    // Fetch E-commerce Metrics
    $revenue = $wpdb->get_var("SELECT SUM(total) FROM {$wpdb->prefix}matjar_orders WHERE status != 'cancelled'");
    $order_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}matjar_orders");
    $pending_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}matjar_orders WHERE status = 'pending'");
    $low_stock_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}matjar_products WHERE stock < 10");
    ?>

    <!-- E-commerce Critical Metrics -->
    <div class="control-metrics-grid" style="margin-bottom:30px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <div class="control-card" style="border:none; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6); font-weight: 600; margin-bottom:5px;"><?php _e('إجمالي الإيرادات', 'control'); ?></div>
                    <div style="font-size: 1.8rem; font-weight: 800; color:var(--control-accent);"><?php echo number_format($revenue, 2); ?> <small style="font-size:0.9rem;">SAR</small></div>
                </div>
                <div style="width: 45px; height: 45px; background: rgba(212,175,55,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--control-accent);">
                    <span class="dashicons dashicons-chart-area" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div style="margin-top:15px; font-size:0.7rem; color:rgba(255,255,255,0.5);"><?php echo sprintf(__('من %d عملية بيع ناجحة', 'control'), $order_count); ?></div>
        </div>

        <div class="control-card" style="border:1px solid var(--control-border); padding: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--control-muted); font-weight: 600; margin-bottom:5px;"><?php _e('طلبات بانتظار الإجراء', 'control'); ?></div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #ef4444;"><?php echo number_format($pending_orders); ?></div>
                </div>
                <div style="width: 45px; height: 45px; background: #fef2f2; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                    <span class="dashicons dashicons-warning" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div style="margin-top:15px; font-size:0.7rem; color:var(--control-muted);"><?php _e('تطلب معالجة فورية (شحن/دفع)', 'control'); ?></div>
        </div>

        <div class="control-card" style="border:1px solid var(--control-border); padding: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--control-muted); font-weight: 600; margin-bottom:5px;"><?php _e('تنبيهات المخزون', 'control'); ?></div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #f59e0b;"><?php echo number_format($low_stock_count); ?></div>
                </div>
                <div style="width: 45px; height: 45px; background: #fffbeb; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d97706;">
                    <span class="dashicons dashicons-database-export" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div style="margin-top:15px; font-size:0.7rem; color:var(--control-muted);"><?php _e('منتجات قاربت على النفاد (أقل من 10)', 'control'); ?></div>
        </div>
    </div>

    <div class="control-grid main-dashboard-grid" style="grid-template-columns: 1fr; gap: 25px;">
        <div class="control-dashboard-main-column">
            <!-- Sales & Orders Detailed Table -->
            <div class="control-card" style="padding: 0; overflow: hidden; border:1px solid var(--control-border);">
                <div style="padding: 20px 30px; background: #f8fafc; border-bottom: 1px solid var(--control-border); display:flex; justify-content: space-between; align-items: center;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="dashicons dashicons-cart" style="color:var(--control-accent);"></span>
                        <h3 style="margin:0; font-size:1rem;"><?php _e('آخر الطلبات الواردة', 'control'); ?></h3>
                    </div>
                    <button class="control-btn" style="height:32px; padding:0 12px; font-size:0.7rem; background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border);"><?php _e('تصدير التقرير', 'control'); ?></button>
                </div>
                <div style="padding: 0;">
                    <table class="control-table" style="width:100%; font-size:0.85rem; border-collapse: collapse;">
                        <thead style="background:#f1f5f9; color:var(--control-muted); font-size:0.7rem; text-transform:uppercase;">
                            <tr>
                                <th style="padding:15px 30px; text-align:right;"><?php _e('رقم الطلب', 'control'); ?></th>
                                <th style="padding:15px; text-align:right;"><?php _e('العميل', 'control'); ?></th>
                                <th style="padding:15px; text-align:right;"><?php _e('المبلغ', 'control'); ?></th>
                                <th style="padding:15px; text-align:right;"><?php _e('الحالة', 'control'); ?></th>
                                <th style="padding:15px 30px; text-align:left;"><?php _e('التاريخ', 'control'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_orders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}matjar_orders ORDER BY created_at DESC LIMIT 5");
                            if (empty($recent_orders)): ?>
                                <tr><td colspan="5" style="padding:40px; text-align:center; color:var(--control-muted);"><?php _e('لا توجد طلبات مسجلة بعد.', 'control'); ?></td></tr>
                            <?php else:
                                foreach($recent_orders as $order): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding:15px 30px; font-weight:700;">#<?php echo $order->id; ?></td>
                                        <td style="padding:15px;">User ID: <?php echo esc_html($order->user_id); ?></td>
                                        <td style="padding:15px; font-weight:800; color:var(--control-text-dark);"><?php echo number_format($order->total, 2); ?> <small>SAR</small></td>
                                        <td style="padding:15px;">
                                            <span class="control-status-indicator <?php echo $order->status === 'pending' ? 'indicator-warning' : 'indicator-success'; ?>" style="font-size:0.65rem;">
                                                <?php echo esc_html($order->status); ?>
                                            </span>
                                        </td>
                                        <td style="padding:15px 30px; text-align:left; color:var(--control-muted); font-size:0.75rem;"><?php echo date('Y/m/d H:i', strtotime($order->created_at)); ?></td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="control-card" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px 30px; background: #f8fafc; border-bottom: 1px solid var(--control-border); display:flex; justify-content: space-between; align-items: center;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="dashicons dashicons-list-view" style="color:var(--control-accent);"></span>
                        <h3 style="margin:0; font-size:1rem;"><?php _e('آخر 5 نشاطات في النظام', 'control'); ?></h3>
                    </div>
                    <a href="<?php echo add_query_arg('control_view', 'settings'); ?>#tab-audit" style="font-size:0.75rem; color:var(--control-accent); font-weight:800; text-decoration:none;"><?php _e('سجل النشاطات الكامل', 'control'); ?></a>
                </div>
                <div style="padding: 10px 30px 30px 30px;">
                    <?php
                    $recent_logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}control_activity_logs ORDER BY action_date DESC LIMIT 5");
                    if (empty($recent_logs)): ?>
                        <div style="text-align:center; padding:40px 20px;">
                            <span class="dashicons dashicons-info" style="font-size:30px; color:var(--control-border); width:30px; height:30px;"></span>
                            <p style="font-size:0.8rem; color:#94a3b8; margin-top:10px;"><?php _e('لا توجد نشاطات مسجلة حالياً.', 'control'); ?></p>
                        </div>
                    <?php else:
                        foreach($recent_logs as $log): ?>
                            <div style="padding: 15px 0; border-bottom: 1px solid #f1f5f9; display:flex; gap:12px;">
                                <div style="width:8px; height:8px; border-radius:50%; background:var(--control-accent); margin-top:5px; flex-shrink:0;"></div>
                                <div style="flex:1;">
                                    <div style="font-size:0.8rem; font-weight:600; color:var(--control-text-dark); line-height:1.4;"><?php echo esc_html($log->description); ?></div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                                        <small style="color:var(--control-muted); font-size:0.65rem;"><?php echo date('H:i - Y/m/d', strtotime($log->action_date)); ?></small>
                                        <small style="background:var(--control-bg); padding:2px 6px; border-radius:4px; font-size:0.6rem; color:var(--control-muted);"><?php echo esc_html($log->user_id); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-weight:800; font-size:1.3rem; margin:0; color:#1e293b;"><?php _e('لوحة المعلومات', 'control'); ?></h2>
    </div>
    <div class="control-card" style="border-radius: 12px; padding: 20px;">
        <p><?php _e('أهلاً بك في نظام متجر الإداري.', 'control'); ?></p>
        <p style="color:#64748b; font-size:0.9rem;"><?php _e('برجاء التواصل مع الإدارة إذا كنت بحاجة إلى صلاحيات إضافية للوصول إلى لوحة التحكم.', 'control'); ?></p>
    </div>
    <?php
}
?>
