<?php
/**
 * ระบบการตั้งค่า Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * เพิ่มหน้าตั้งค่า
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=newtm_news',
            'ตั้งค่า NewTM',
            'ตั้งค่า',
            'manage_options',
            'newtm-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * ลงทะเบียนตั้งค่า
     */
    public function register_settings() {
        register_setting('newtm_settings_group', 'newtm_title_max_length', array(
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'absint',
        ));
        
        register_setting('newtm_settings_group', 'newtm_excerpt_max_length', array(
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'absint',
        ));
        
        register_setting('newtm_settings_group', 'newtm_items_per_page', array(
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => 'absint',
        ));
        
        // เพิ่ม section
        add_settings_section(
            'newtm_display_section',
            'ตั้งค่าการแสดงผล',
            array($this, 'display_section_callback'),
            'newtm_settings_group'
        );
        
        // เพิ่ม fields
        add_settings_field(
            'newtm_title_max_length',
            'จำนวนตัวอักษรสูงสุดของหัวข้อ',
            array($this, 'title_max_length_callback'),
            'newtm_settings_group',
            'newtm_display_section'
        );
        
        add_settings_field(
            'newtm_excerpt_max_length',
            'จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ',
            array($this, 'excerpt_max_length_callback'),
            'newtm_settings_group',
            'newtm_display_section'
        );
        
        add_settings_field(
            'newtm_items_per_page',
            'จำนวนข่าวต่อหน้า',
            array($this, 'items_per_page_callback'),
            'newtm_settings_group',
            'newtm_display_section'
        );
    }
    
    /**
     * Callback สำหรับ section
     */
    public function display_section_callback() {
        echo '<p>ตั้งค่าการแสดงผลของข่าวในหน้าเว็บ (ใส่ 0 = ไม่จำกัด)</p>';
    }
    
    /**
     * Callback สำหรับ title max length
     */
    public function title_max_length_callback() {
        $value = get_option('newtm_title_max_length', 0);
        echo '<input type="number" name="newtm_title_max_length" value="' . intval($value) . '" min="0" /> ตัวอักษร';
        echo '<p class="description">จำกัดความยาวของหัวข้อข่าวเมื่อแสดงในรายการ (0 = ไม่จำกัด)</p>';
    }
    
    /**
     * Callback สำหรับ excerpt max length
     */
    public function excerpt_max_length_callback() {
        $value = get_option('newtm_excerpt_max_length', 0);
        echo '<input type="number" name="newtm_excerpt_max_length" value="' . intval($value) . '" min="0" /> ตัวอักษร';
        echo '<p class="description">จำกัดความยาวของเนื้อหาย่อ (0 = ไม่จำกัด)</p>';
    }
    
    /**
     * Callback สำหรับ items per page
     */
    public function items_per_page_callback() {
        $value = get_option('newtm_items_per_page', 10);
        echo '<input type="number" name="newtm_items_per_page" value="' . intval($value) . '" min="1" /> รายการ';
        echo '<p class="description">จำนวนข่าวที่แสดงต่อหน้า</p>';
    }
    
    /**
     * แสดงหน้าตั้งค่า
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>ตั้งค่า NewTM - News Table System</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('newtm_settings_group');
                do_settings_sections('newtm_settings_group');
                submit_button('บันทึกการตั้งค่า');
                ?>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h2>วิธีการใช้ Shortcode</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>คำอธิบาย</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[newtm_table category="ประกาศ" limit="5"]</code></td>
                        <td>แสดงข่าวตามหมวดหมู่ (limit = จำนวนข่าว)</td>
                    </tr>
                    <tr>
                        <td><code>[newtm_table category="ประกาศ" limit="5" title_length="50"]</code></td>
                        <td>แสดงข่าวและจำกัดหัวข้อ 50 ตัวอักษร</td>
                    </tr>
                    <tr>
                        <td><code>[newtm_all_categories limit="5"]</code></td>
                        <td>แสดงข่าวทั้งหมดจัดกลุ่มตามหมวดหมู่</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Helper function เพื่อรับค่าตั้งค่า
function newtm_get_option($option, $default = false) {
    return get_option('newtm_' . $option, $default);
}

// Helper function เพื่อตัดข้อความ (รองรับภาษาไทย)
function newtm_truncate_text($text, $length = 0) {
    if ($length <= 0) {
        return $text;
    }
    
    if (mb_strlen($text, 'UTF-8') > $length) {
        return mb_strimwidth($text, 0, $length, '...', 'UTF-8');
    }
    
    return $text;
}
