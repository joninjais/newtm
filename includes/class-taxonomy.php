<?php
/**
 * Custom Taxonomy สำหรับข่าวสาร
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Taxonomy {
    
    public function __construct() {
        add_action('init', array($this, 'register_taxonomies'));
        // สำหรับหมวดหมู่
        add_action('newtm_category_add_form_fields', array($this, 'add_category_fields'));
        add_action('newtm_category_edit_form_fields', array($this, 'edit_category_fields'));
        add_action('created_newtm_category', array($this, 'save_category_meta'));
        add_action('edited_newtm_category', array($this, 'save_category_meta'));
    }
    
    public function register_taxonomies() {
        // หมวดหมู่ (แบบลำดับชั้น)
        $category_args = array(
            'label' => 'หมวดหมู่',
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,  // รองรับ Gutenberg
            'rest_base' => 'news-categories',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'news-category',
                'with_front' => false,
                'hierarchical' => true,
            ),
            'labels' => array(
                'name' => 'หมวดหมู่',
                'singular_name' => 'หมวดหมู่',
                'search_items' => 'ค้นหาหมวดหมู่',
                'all_items' => 'หมวดหมู่ทั้งหมด',
                'parent_item' => 'หมวดหมู่แม่',
                'parent_item_colon' => 'หมวดหมู่แม่:',
                'edit_item' => 'แก้ไขหมวดหมู่',
                'update_item' => 'อัปเดตหมวดหมู่',
                'add_new_item' => 'เพิ่มหมวดหมู่ใหม่',
                'new_item_name' => 'ชื่อหมวดหมู่ใหม่',
                'menu_name' => 'หมวดหมู่',
            ),
        );
        
        register_taxonomy('newtm_category', 'newtm_news', $category_args);
        
        // แท็ก (ไม่มีลำดับชั้น)
        $tag_args = array(
            'label' => 'แท็ก',
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,  // รองรับ Gutenberg
            'rest_base' => 'news-tags',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'news-tag',
                'with_front' => false,
            ),
            'labels' => array(
                'name' => 'แท็ก',
                'singular_name' => 'แท็ก',
                'search_items' => 'ค้นหาแท็ก',
                'popular_items' => 'แท็กยอดนิยม',
                'all_items' => 'แท็กทั้งหมด',
                'edit_item' => 'แก้ไขแท็ก',
                'update_item' => 'อัปเดตแท็ก',
                'add_new_item' => 'เพิ่มแท็กใหม่',
                'new_item_name' => 'ชื่อแท็กใหม่',
                'menu_name' => 'แท็ก',
            ),
        );
        
        register_taxonomy('newtm_tag', 'newtm_news', $tag_args);
    }
    
    /**
     * เพิ่มฟิลด์ตั้งค่าเมื่อสร้างหมวดหมู่ใหม่
     */
    public function add_category_fields() {
        ?>
        <div class="form-field">
            <label for="category_title_length">จำนวนตัวอักษรสูงสุดของหัวข้อ</label>
            <input type="number" name="category_title_length" id="category_title_length" value="0" min="0" />
            <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
        </div>
        <div class="form-field">
            <label for="category_items_per_page">จำนวนข่าวต่อหน้า</label>
            <input type="number" name="category_items_per_page" id="category_items_per_page" value="0" min="0" />
            <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
        </div>
        <div class="form-field">
            <label for="category_excerpt_length">จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ</label>
            <input type="number" name="category_excerpt_length" id="category_excerpt_length" value="0" min="0" />
            <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
        </div>
        <?php
    }
    
    /**
     * แสดงฟิลด์ตั้งค่าเมื่อแก้ไขหมวดหมู่
     */
    public function edit_category_fields($term) {
        $title_length = get_term_meta($term->term_id, 'category_title_length', true);
        $items_per_page = get_term_meta($term->term_id, 'category_items_per_page', true);
        $excerpt_length = get_term_meta($term->term_id, 'category_excerpt_length', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="category_title_length">จำนวนตัวอักษรสูงสุดของหัวข้อ</label></th>
            <td>
                <input type="number" name="category_title_length" id="category_title_length" value="<?php echo intval($title_length); ?>" min="0" />
                <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="category_items_per_page">จำนวนข่าวต่อหน้า</label></th>
            <td>
                <input type="number" name="category_items_per_page" id="category_items_per_page" value="<?php echo intval($items_per_page); ?>" min="0" />
                <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="category_excerpt_length">จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ</label></th>
            <td>
                <input type="number" name="category_excerpt_length" id="category_excerpt_length" value="<?php echo intval($excerpt_length); ?>" min="0" />
                <p class="description">ใส่ 0 = ไม่จำกัด หรือจะใช้ค่าเริ่มต้นจากหน้าตั้งค่า</p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * บันทึกตั้งค่าหมวดหมู่
     */
    public function save_category_meta($term_id) {
        if (isset($_POST['category_title_length'])) {
            update_term_meta($term_id, 'category_title_length', intval($_POST['category_title_length']));
        }
        if (isset($_POST['category_items_per_page'])) {
            update_term_meta($term_id, 'category_items_per_page', intval($_POST['category_items_per_page']));
        }
        if (isset($_POST['category_excerpt_length'])) {
            update_term_meta($term_id, 'category_excerpt_length', intval($_POST['category_excerpt_length']));
        }
    }
}
