<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$current_user = Control_Auth::current_user();
$is_admin = Control_Auth::is_admin();

$query = "SELECT * FROM {$wpdb->prefix}matjar_products";
if ( ! $is_admin ) {
    $query .= $wpdb->prepare(" WHERE vendor_id = %s", $current_user->id);
}
$query .= " ORDER BY id DESC";

$products = $wpdb->get_results( $query );
$categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_categories ORDER BY name ASC" );
?>

<div class="matjar-admin-wrapper" style="direction: rtl;">
    <div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-weight:800; font-size:1.3rem; margin:0; color:var(--control-text-dark);"><?php echo $is_admin ? __('إدارة كافة المنتجات', 'control') : __('منتجاتي', 'control'); ?></h2>
        <button class="control-btn add-new-product-btn" style="background:var(--control-primary); border:none;">
            <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إضافة منتج جديد', 'control'); ?>
        </button>
    </div>

    <?php if ($is_admin): ?>
    <div class="matjar-admin-actions" style="margin: 20px 0; display: flex; gap: 10px;">
        <button id="matjar-export-products" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); font-size:0.8rem;">
            <span class="dashicons dashicons-download" style="margin-left:5px;"></span> <?php _e('تصدير CSV', 'control'); ?>
        </button>
        <button id="matjar-import-trigger" class="control-btn" style="background:#fff; color:var(--control-text-dark) !important; border:1px solid var(--control-border); font-size:0.8rem;">
            <span class="dashicons dashicons-upload" style="margin-left:5px;"></span> <?php _e('استيراد CSV', 'control'); ?>
        </button>
        <input type="file" id="matjar-import-file" style="display: none;" accept=".csv">
    </div>
    <?php endif; ?>

    <div class="control-card" style="padding: 0; overflow: hidden;">
    <table class="control-table" style="width:100%; border-collapse: collapse;">
        <thead style="background:#f8fafc;">
            <tr>
                <th style="padding:15px; text-align:right;"><?php _e('الصورة', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('الاسم', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('السعر', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('المخزون', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('التصنيف', 'control'); ?></th>
                <th style="padding:15px; text-align:left;"><?php _e('الإجراءات', 'control'); ?></th>
            </tr>
        </thead>
        <tbody id="matjar-admin-products-body">
            <?php if ( $products ) : foreach ( $products as $product ) : ?>
                <tr data-product='<?php echo json_encode($product); ?>' style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding:10px 15px;">
                        <img src="<?php echo esc_url($product->image_url ?: 'https://via.placeholder.com/50'); ?>" style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;">
                    </td>
                    <td style="padding:10px 15px;"><strong><?php echo esc_html($product->name); ?></strong></td>
                    <td style="padding:10px 15px; font-weight:700; color:var(--control-accent);"><?php echo esc_html($product->price); ?> EGP</td>
                    <td style="padding:10px 15px;"><?php echo esc_html($product->stock); ?></td>
                    <td style="padding:10px 15px;">
                        <?php
                        if ($product->category_id) {
                            $cat_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}matjar_categories WHERE id = %d", $product->category_id));
                            echo esc_html($cat_name ?: '-');
                        } else {
                            echo esc_html($product->category ?: '-');
                        }
                        ?>
                    </td>
                    <td style="padding:10px 15px; text-align:left;">
                        <button class="audit-action-btn edit-product"><span class="dashicons dashicons-edit"></span></button>
                        <button class="audit-action-btn delete-product" data-id="<?php echo $product->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="6" style="padding:40px; text-align:center; color:var(--control-muted);"><?php _e('لم يتم العثور على منتجات.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Product Modal -->
<div id="matjar-product-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000000; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
    <div class="control-card" style="width: 100%; max-width: 500px; padding: 30px;">
        <h3 id="product-modal-title"><?php _e('إضافة منتج', 'control'); ?></h3>
        <form id="matjar-admin-product-form">
            <input type="hidden" name="id" id="product-id">
            <div class="control-form-group">
                <label><?php _e('اسم المنتج', 'control'); ?></label>
                <input type="text" name="name" id="product-name" required>
            </div>
            <div class="control-form-group">
                <label><?php _e('الوصف', 'control'); ?></label>
                <textarea name="description" id="product-description" rows="3"></textarea>
            </div>
            <div class="control-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="control-form-group">
                    <label><?php _e('السعر (EGP)', 'control'); ?></label>
                    <input type="number" name="price" id="product-price" step="0.01" required>
                </div>
                <div class="control-form-group">
                    <label><?php _e('المخزون', 'control'); ?></label>
                    <input type="number" name="stock" id="product-stock" required>
                </div>
            </div>
            <div class="control-form-group">
                <label><?php _e('رابط الصورة', 'control'); ?></label>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="image_url" id="product-image-url" style="flex:1;">
                    <button type="button" class="control-btn matjar-upload-btn" style="height:45px; width:45px; padding:0;"><span class="dashicons dashicons-upload"></span></button>
                </div>
            </div>
            <div class="control-form-group">
                <label><?php _e('التصنيف', 'control'); ?></label>
                <select name="category_id" id="product-category-id">
                    <option value="0"><?php _e('عام', 'control'); ?></option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap: 10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none; height:45px;"><?php _e('حفظ المنتج', 'control'); ?></button>
                <button type="button" class="control-btn matjar-close-modal" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none; height:45px;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
