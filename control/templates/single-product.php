<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
global $wpdb;
$product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}matjar_products WHERE id = %d", $product_id ) );

if ( ! $product ) {
    echo '<div class="control-card" style="text-align:center; padding:50px;"><h3>' . __('المنتج غير موجود', 'control') . '</h3><a href="' . get_permalink(get_option('matjar_store_page_id')) . '" class="control-btn">' . __('العودة للمتجر', 'control') . '</a></div>';
    return;
}
?>

<div class="matjar-single-product">
    <div class="control-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
        <div class="product-gallery">
            <div class="control-card" style="padding:0; overflow:hidden; border-radius:24px;">
                <img src="<?php echo esc_url($product->image_url ?: 'https://via.placeholder.com/600'); ?>" style="width:100%; display:block;">
            </div>
        </div>
        <div class="product-details">
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--control-text-dark); margin-bottom: 10px;"><?php echo esc_html($product->name); ?></h1>
            <div class="price-tag" style="font-size: 1.8rem; color: var(--control-accent); font-weight: 900; margin-bottom: 25px;">
                <?php echo esc_html($product->price); ?> <small style="font-size:1rem;">EGP</small>
            </div>

            <div class="product-meta" style="margin-bottom: 30px; display: flex; gap: 15px;">
                <span class="control-status-indicator <?php echo $product->stock > 0 ? 'indicator-success' : 'indicator-danger'; ?>">
                    <?php echo $product->stock > 0 ? __('متوفر في المخزون', 'control') : __('غير متوفر', 'control'); ?>
                </span>
                <span class="control-status-indicator indicator-accent"><?php echo esc_html($product->category); ?></span>
            </div>

            <div class="product-description" style="line-height: 1.8; color: var(--control-text); margin-bottom: 40px; font-size: 1.1rem;">
                <?php echo wpautop(esc_html($product->description)); ?>
            </div>

            <button class="add-to-cart-btn" data-id="<?php echo $product->id; ?>" style="width: 100%; height: 60px; font-size: 1.1rem;">
                <span class="dashicons dashicons-cart" style="margin-left: 10px; font-size: 20px; width: 20px; height: 20px;"></span>
                <?php _e('إضافة إلى سلة التسوق', 'control'); ?>
            </button>
        </div>
    </div>
</div>
