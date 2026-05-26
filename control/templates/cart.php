<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="matjar-cart-container">
    <!-- Step 1: Summary -->
    <div id="checkout-step-1" class="checkout-step">
        <div class="control-card">
            <h3 style="display:flex; justify-content:space-between; align-items:center;">
                <?php _e('سلة التسوق', 'control'); ?>
                <span class="step-indicator" style="font-size:0.7rem; background:var(--control-bg); padding:4px 10px; border-radius:10px; color:var(--control-muted);">1 / 3</span>
            </h3>
            <div id="matjar-cart-items">
                <p style="text-align: center; padding: 40px; color: var(--control-muted);">
                    <?php _e('السلة فارغة حالياً', 'control'); ?>
                </p>
            </div>
            <div class="cart-footer" style="border-top: 1px solid var(--control-border); padding-top: 20px; margin-top:20px;">
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span><?php _e('المجموع الفرعي:', 'control'); ?></span>
                        <span id="cart-subtotal">0 EGP</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span><?php _e('الشحن:', 'control'); ?></span>
                        <span id="cart-shipping">50.00 EGP</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.1rem; border-top:1px dashed var(--control-border); padding-top:10px;">
                        <span><?php _e('الإجمالي:', 'control'); ?></span>
                        <span id="cart-total" style="color: var(--control-accent);">0 EGP</span>
                    </div>
                </div>
                <button id="cart-next-to-shipping" class="control-btn" style="width:100%; height:50px; font-weight:800;" disabled>
                    <?php _e('الاستمرار للشحن', 'control'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Shipping & Details -->
    <div id="checkout-step-2" class="checkout-step" style="display:none;">
        <div class="control-card">
            <h3 style="display:flex; justify-content:space-between; align-items:center;">
                <?php _e('بيانات الشحن', 'control'); ?>
                <span class="step-indicator" style="font-size:0.7rem; background:var(--control-bg); padding:4px 10px; border-radius:10px; color:var(--control-muted);">2 / 3</span>
            </h3>
            <form id="checkout-shipping-form">
                <?php if ( ! Control_Auth::is_logged_in() ) : ?>
                    <div style="background:#fffbeb; border:1px solid #fef3c7; padding:15px; border-radius:12px; margin-bottom:20px; color:#92400e; font-size:0.85rem; line-height:1.5;">
                        <?php _e('يجب عليك تسجيل الدخول أو إنشاء حساب لإتمام عملية الشحن.', 'control'); ?>
                        <div style="margin-top:10px;">
                            <a href="<?php echo get_permalink(get_option('matjar_dashboard_page_id')); ?>?redirect_to=checkout" class="control-btn" style="background:#d97706; border:none; height:32px; font-size:0.75rem;"><?php _e('تسجيل الدخول الآن', 'control'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="control-form-group">
                        <label><?php _e('المحافظة', 'control'); ?></label>
                        <select name="governorate" id="checkout-governorate" required>
                            <option value=""><?php _e('اختر المحافظة', 'control'); ?></option>
                            <?php
                            $govs = $wpdb->get_results( "SELECT governorate FROM {$wpdb->prefix}matjar_shipping_rules ORDER BY governorate ASC" );
                            foreach($govs as $g) echo '<option value="'.esc_attr($g->governorate).'">'.esc_html($g->governorate).'</option>';
                            ?>
                        </select>
                    </div>
                    <div class="control-form-group">
                        <label><?php _e('المدينة / المنطقة', 'control'); ?></label>
                        <input type="text" name="city" id="checkout-city" required placeholder="<?php _e('اسم المدينة', 'control'); ?>">
                    </div>
                </div>

                <div class="control-form-group">
                    <label><?php _e('عنوان التوصيل بالتفصيل', 'control'); ?></label>
                    <textarea name="shipping_address" id="checkout-address" rows="3" required placeholder="<?php _e('رقم الشارع، المبنى، الشقة...', 'control'); ?>"><?php echo esc_textarea(Control_Auth::current_user()->address ?? ''); ?></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <button type="button" id="get-gps-location" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); width:100%; height:45px; font-size:0.85rem;">
                        <span class="dashicons dashicons-location" style="margin-left:8px;"></span><?php _e('تحديد الموقع عبر GPS (خرائط جوجل)', 'control'); ?>
                    </button>
                    <input type="hidden" name="gps_coords" id="checkout-gps">
                </div>
                <div class="control-form-group">
                    <label><?php _e('ملاحظات إضافية', 'control'); ?></label>
                    <textarea name="order_notes" id="checkout-notes" rows="2"></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:25px;">
                    <button type="button" class="control-btn checkout-prev" style="background:var(--control-bg); color:var(--control-text-dark) !important; border:none; flex:1;"><?php _e('السابق', 'control'); ?></button>
                    <button type="button" id="cart-next-to-confirm" class="control-btn" style="flex:2; font-weight:800;" <?php echo !Control_Auth::is_logged_in() ? 'disabled' : ''; ?>><?php _e('الاستمرار للمراجعة', 'control'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 3: Final Confirmation -->
    <div id="checkout-step-3" class="checkout-step" style="display:none;">
        <div class="control-card">
            <h3 style="display:flex; justify-content:space-between; align-items:center;">
                <?php _e('تأكيد الطلب', 'control'); ?>
                <span class="step-indicator" style="font-size:0.7rem; background:var(--control-bg); padding:4px 10px; border-radius:10px; color:var(--control-muted);">3 / 3</span>
            </h3>
            <div style="margin-bottom:25px; padding:20px; background:var(--control-bg); border-radius:16px;">
                <h4 style="margin:0 0 10px 0; font-size:0.9rem;"><?php _e('سيتم الشحن إلى:', 'control'); ?></h4>
                <p id="confirm-address-display" style="margin:0; font-size:0.85rem; color:var(--control-muted); line-height:1.5;"></p>
            </div>

            <div style="margin-bottom:25px;">
                <h4 style="margin:0 0 15px 0; font-size:0.9rem;"><?php _e('طريقة الدفع:', 'control'); ?></h4>
                <div style="display:flex; align-items:center; gap:12px; padding:15px; border:2px solid var(--control-accent); border-radius:12px; background:var(--control-accent-soft);">
                    <span class="dashicons dashicons-money-alt" style="color:var(--control-accent);"></span>
                    <span style="font-weight:700; font-size:0.9rem;"><?php _e('الدفع عند الاستلام (COD)', 'control'); ?></span>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:30px;">
                <button type="button" class="control-btn checkout-prev" style="background:var(--control-bg); color:var(--control-text-dark) !important; border:none; flex:1;"><?php _e('السابق', 'control'); ?></button>
                <button id="confirm-order-btn" class="control-btn" style="flex:2; font-weight:900; background:var(--control-accent); color:#000 !important; border:none;"><?php _e('تأكيد وإرسال الطلب', 'control'); ?></button>
            </div>
        </div>
    </div>
</div>
