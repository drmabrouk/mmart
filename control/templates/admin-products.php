<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_products = $wpdb->prefix . 'matjar_products';
$products = $wpdb->get_results( "SELECT * FROM $table_products ORDER BY id DESC" );
?>

<div class="wrap" style="direction: rtl;">
    <h1 class="wp-heading-inline"><?php _e('إدارة المنتجات', 'control'); ?></h1>
    <a href="#" class="page-title-action add-new-product-btn"><?php _e('إضافة منتج جديد', 'control'); ?></a>
    <hr class="wp-header-end">

    <div class="matjar-admin-actions" style="margin: 20px 0; display: flex; gap: 10px;">
        <button id="matjar-export-products" class="button button-secondary">
            <span class="dashicons dashicons-download"></span> <?php _e('تصدير CSV', 'control'); ?>
        </button>
        <button id="matjar-import-trigger" class="button button-secondary">
            <span class="dashicons dashicons-upload"></span> <?php _e('استيراد CSV', 'control'); ?>
        </button>
        <input type="file" id="matjar-import-file" style="display: none;" accept=".csv">
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list products">
        <thead>
            <tr>
                <th scope="col" class="manage-column"><?php _e('الصورة', 'control'); ?></th>
                <th scope="col" class="manage-column"><?php _e('الاسم', 'control'); ?></th>
                <th scope="col" class="manage-column"><?php _e('السعر', 'control'); ?></th>
                <th scope="col" class="manage-column"><?php _e('المخزون', 'control'); ?></th>
                <th scope="col" class="manage-column"><?php _e('التصنيف', 'control'); ?></th>
                <th scope="col" class="manage-column"><?php _e('الإجراءات', 'control'); ?></th>
            </tr>
        </thead>
        <tbody id="matjar-admin-products-body">
            <?php if ( $products ) : foreach ( $products as $product ) : ?>
                <tr data-product='<?php echo json_encode($product); ?>'>
                    <td>
                        <img src="<?php echo esc_url($product->image_url ?: 'https://via.placeholder.com/50'); ?>" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                    </td>
                    <td><strong><?php echo esc_html($product->name); ?></strong></td>
                    <td><?php echo esc_html($product->price); ?> SAR</td>
                    <td><?php echo esc_html($product->stock); ?></td>
                    <td><?php echo esc_html($product->category); ?></td>
                    <td>
                        <a href="#" class="edit-product" data-id="<?php echo $product->id; ?>"><?php _e('تعديل', 'control'); ?></a> |
                        <a href="#" class="delete-product" data-id="<?php echo $product->id; ?>" style="color: #ef4444;"><?php _e('حذف', 'control'); ?></a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;"><?php _e('لم يتم العثور على منتجات.', 'control'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Product Modal -->
<div id="matjar-product-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
    <div style="background:#fff; width: 100%; max-width: 500px; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 id="product-modal-title"><?php _e('إضافة منتج', 'control'); ?></h3>
        <form id="matjar-admin-product-form">
            <input type="hidden" name="id" id="product-id">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;"><?php _e('اسم المنتج', 'control'); ?></label>
                <input type="text" name="name" id="product-name" style="width:100%;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;"><?php _e('الوصف', 'control'); ?></label>
                <textarea name="description" id="product-description" style="width:100%;" rows="3"></textarea>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; margin-bottom:5px;"><?php _e('السعر', 'control'); ?></label>
                    <input type="number" name="price" id="product-price" step="0.01" style="width:100%;" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px;"><?php _e('المخزون', 'control'); ?></label>
                    <input type="number" name="stock" id="product-stock" style="width:100%;" required>
                </div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;"><?php _e('رابط الصورة', 'control'); ?></label>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="image_url" id="product-image-url" style="flex:1;">
                    <button type="button" class="button matjar-upload-btn"><?php _e('رفع', 'control'); ?></button>
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px;"><?php _e('التصنيف', 'control'); ?></label>
                <input type="text" name="category" id="product-category" style="width:100%;">
            </div>
            <div style="display:flex; gap: 10px;">
                <button type="submit" class="button button-primary" style="flex:1; height:40px;"><?php _e('حفظ المنتج', 'control'); ?></button>
                <button type="button" class="button matjar-close-modal" style="flex:1; height:40px;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
