<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="matjar-cart-container">
    <div class="control-card">
        <h3><?php _e('سلة التسوق', 'control'); ?></h3>
        <div id="matjar-cart-items">
            <p style="text-align: center; padding: 40px; color: var(--control-muted);">
                <?php _e('السلة فارغة حالياً', 'control'); ?>
            </p>
        </div>
        <div class="cart-footer" style="border-top: 1px solid var(--control-border); padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div class="total-amount">
                <span style="font-weight: 600;"><?php _e('الإجمالي:', 'control'); ?></span>
                <span id="cart-total" style="font-weight: 800; color: var(--control-accent); margin-right: 10px;">0 EGP</span>
            </div>
            <button id="confirm-order-btn" class="control-btn" disabled>
                <?php _e('إتمام الطلب', 'control'); ?>
            </button>
        </div>
    </div>
</div>
