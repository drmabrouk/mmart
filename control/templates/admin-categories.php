<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}matjar_categories ORDER BY id ASC" );
?>

<div class="control-header-flex" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-weight:800; font-size:1.3rem; margin:0; color:var(--control-text-dark);"><?php _e('إدارة التصنيفات', 'control'); ?></h2>
    <button id="add-category-btn" class="control-btn" style="background:var(--control-primary); border:none;">
        <span class="dashicons dashicons-plus-alt" style="margin-left:5px;"></span><?php _e('إضافة تصنيف جديد', 'control'); ?>
    </button>
</div>

<div class="control-card" style="padding: 0; overflow: hidden;">
    <table class="control-table" style="width:100%; border-collapse: collapse;">
        <thead style="background:#f8fafc;">
            <tr>
                <th style="padding:15px; text-align:right;"><?php _e('الاسم', 'control'); ?></th>
                <th style="padding:15px; text-align:right;"><?php _e('التصنيف الأب', 'control'); ?></th>
                <th style="padding:15px; text-align:left;"><?php _e('الإجراءات', 'control'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($categories): foreach($categories as $cat): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding:15px; font-weight:700;"><?php echo esc_html($cat->name); ?></td>
                    <td style="padding:15px; color:var(--control-muted);">
                        <?php
                        if ($cat->parent_id) {
                            $parent = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}matjar_categories WHERE id = %d", $cat->parent_id));
                            echo esc_html($parent ?: '-');
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td style="padding:15px; text-align:left;">
                        <button class="audit-action-btn edit-category" data-cat='<?php echo json_encode($cat); ?>'><span class="dashicons dashicons-edit"></span></button>
                        <button class="audit-action-btn delete-category" data-id="<?php echo $cat->id; ?>" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" style="padding:40px; text-align:center; color:var(--control-muted);"><?php _e('لا توجد تصنيفات حالياً.', 'control'); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Category Modal -->
<div id="matjar-category-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000000; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
    <div class="control-card" style="width: 100%; max-width: 400px; padding: 30px;">
        <h3 id="category-modal-title"><?php _e('إضافة تصنيف', 'control'); ?></h3>
        <form id="matjar-category-form">
            <input type="hidden" name="id" id="category-id">
            <div class="control-form-group">
                <label><?php _e('اسم التصنيف', 'control'); ?></label>
                <input type="text" name="name" id="category-name" required>
            </div>
            <div class="control-form-group">
                <label><?php _e('التصنيف الأب', 'control'); ?></label>
                <select name="parent_id" id="category-parent-id">
                    <option value="0"><?php _e('بدون (رئيسي)', 'control'); ?></option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap: 10px; margin-top:20px;">
                <button type="submit" class="control-btn" style="flex:1; background:var(--control-primary); border:none;"><?php _e('حفظ', 'control'); ?></button>
                <button type="button" class="control-btn matjar-close-cat-modal" style="flex:1; background:var(--control-bg); color:var(--control-text-dark) !important; border:none;"><?php _e('إلغاء', 'control'); ?></button>
            </div>
        </form>
    </div>
</div>
