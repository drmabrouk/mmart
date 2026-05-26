<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$rules = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_shipping_rules ORDER BY tier ASC, governorate ASC" );
?>

<div class="wrap" style="direction: rtl;">
    <h1><?php _e('إعدادات الشحن والمناطق الجغرافية', 'control'); ?></h1>
    <p><?php _e('إدارة تكاليف الشحن بناءً على المحافظات المصرية.', 'control'); ?></p>

    <div class="control-card" style="margin-top: 20px; padding: 0; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('المحافظة', 'control'); ?></th>
                    <th><?php _e('التصنيف الجغرافي', 'control'); ?></th>
                    <th><?php _e('سعر الشحن الأساسي (EGP)', 'control'); ?></th>
                    <th><?php _e('الإجراءات', 'control'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $rule): ?>
                    <tr>
                        <td><strong><?php echo esc_html($rule->governorate); ?></strong></td>
                        <td>
                            <?php
                            $tier_labels = array('central' => 'المركز الرئيسي', 'delta' => 'محافظات الدلتا', 'upper' => 'الصعيد والمحافظات النائية');
                            echo $tier_labels[$rule->tier] ?? $rule->tier;
                            ?>
                        </td>
                        <td><?php echo esc_html($rule->base_rate); ?> EGP</td>
                        <td>
                            <button class="button edit-shipping-rule" data-rule='<?php echo json_encode($rule); ?>'><?php _e('تعديل', 'control'); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Shipping Modal -->
<div id="matjar-shipping-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
    <div class="control-card" style="width: 100%; max-width: 400px; padding: 30px;">
        <h3><?php _e('تعديل سعر الشحن', 'control'); ?></h3>
        <form id="matjar-shipping-form">
            <input type="hidden" name="id" id="shipping-rule-id">
            <div class="control-form-group">
                <label><?php _e('المحافظة', 'control'); ?></label>
                <input type="text" id="shipping-gov-name" disabled>
            </div>
            <div class="control-form-group">
                <label><?php _e('سعر الشحن (EGP)', 'control'); ?></label>
                <input type="number" name="base_rate" id="shipping-base-rate" step="0.01" required>
            </div>
            <div style="display:flex; gap: 10px; margin-top:20px;">
                <button type="submit" class="button button-primary" style="flex:1; height:40px;"><?php _e('حفظ التغييرات', 'control'); ?></button>
                <button type="button" class="button matjar-close-shipping-modal" style="flex:1; height:40px;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
