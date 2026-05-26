<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="matjar-store-container">
    <div class="control-card store-header" style="margin-bottom: 30px;">
        <div class="search-bar-wrapper" style="position: relative;">
            <span class="dashicons dashicons-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--control-muted);"></span>
            <input type="text" id="matjar-search" placeholder="<?php _e('ابحث عن المنتجات...', 'control'); ?>" style="padding-right: 45px; height: 50px; border-radius: 15px; background: var(--control-bg);">
        </div>
    </div>

    <div class="matjar-product-grid">
        <?php
        global $wpdb;
        $db_products = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_products ORDER BY id DESC" );

        if ( $db_products ) :
            $product_page_url = get_permalink(get_option('matjar_product_page_id'));
            foreach ($db_products as $product):
                $view_url = add_query_arg('product_id', $product->id, $product_page_url);
                ?>
                <div class="matjar-product-card">
                    <a href="<?php echo esc_url($view_url); ?>" style="text-decoration: none; color: inherit; display: block;">
                        <img src="<?php echo esc_url($product->image_url ?: 'https://via.placeholder.com/300'); ?>" alt="<?php echo esc_attr($product->name); ?>" class="product-image">
                        <div class="product-info">
                            <h4><?php echo esc_html($product->name); ?></h4>
                            <div class="product-snippet" style="font-size: 0.75rem; color: var(--control-muted); margin-bottom: 10px; height: 32px; overflow: hidden;">
                                <?php echo esc_html(wp_trim_words($product->description, 10)); ?>
                            </div>
                            <div class="price-tag"><?php echo esc_html($product->price); ?> <small>EGP</small></div>
                        </div>
                    </a>
                    <div style="padding: 0 20px 20px 20px;">
                        <button class="add-to-cart-btn" data-id="<?php echo esc_attr($product->id); ?>" style="width: 100%;">
                            <span class="dashicons dashicons-plus" style="margin-left: 5px; font-size: 14px;"></span>
                            <?php _e('أضف للسلة', 'control'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach;
        else : ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--control-muted);">
                <?php _e('لا توجد منتجات متوفرة حالياً.', 'control'); ?>
            </p>
        <?php endif; ?>
    </div>
</div>
