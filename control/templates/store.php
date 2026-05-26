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
        // Dummy products for initial setup
        $products = array(
            array('id' => 1, 'name' => 'Product 1', 'price' => '100 SAR', 'img' => 'https://via.placeholder.com/300'),
            array('id' => 2, 'name' => 'Product 2', 'price' => '150 SAR', 'img' => 'https://via.placeholder.com/300'),
            array('id' => 3, 'name' => 'Product 3', 'price' => '200 SAR', 'img' => 'https://via.placeholder.com/300'),
            array('id' => 4, 'name' => 'Product 4', 'price' => '250 SAR', 'img' => 'https://via.placeholder.com/300'),
            array('id' => 5, 'name' => 'Product 5', 'price' => '300 SAR', 'img' => 'https://via.placeholder.com/300'),
        );

        foreach ($products as $product): ?>
            <div class="matjar-product-card">
                <img src="<?php echo $product['img']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-info">
                    <h4 style="margin: 0 0 10px 0; font-weight: 700;"><?php echo $product['name']; ?></h4>
                    <p style="color: var(--control-accent); font-weight: 800; margin-bottom: 15px;"><?php echo $product['price']; ?></p>
                    <button class="control-btn add-to-cart-btn" data-id="<?php echo $product['id']; ?>" style="width: 100%;">
                        <span class="dashicons dashicons-plus" style="margin-left: 5px;"></span>
                        <?php _e('أضف للسلة', 'control'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
