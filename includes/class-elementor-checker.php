<?php
/**
 * ตรวจสอบความเข้ากันได้กับ Elementor
 * 
 * @package NewTM
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * คลาสสำหรับตรวจสอบว่า Elementor ติดตั้งและใช้งานได้หรือไม่
 */
class NEWTM_Elementor_Checker {
    
    /**
     * ตรวจสอบว่า Elementor เปิดใช้งานอยู่หรือไม่
     *
     * @return bool
     */
    public static function is_elementor_active() {
        return did_action('elementor/loaded');
    }
    
    /**
     * ดึงเวอร์ชันของ Elementor
     *
     * @return string|null
     */
    public static function get_elementor_version() {
        if (defined('ELEMENTOR_VERSION')) {
            return ELEMENTOR_VERSION;
        }
        return null;
    }
    
    /**
     * ตรวจสอบความเข้ากันได้ทั้งหมด
     *
     * @return bool
     */
    public static function is_compatible() {
        // ตรวจสอบว่า Elementor ติดตั้งแล้วหรือไม่
        if (!self::is_elementor_active()) {
            return false;
        }
        
        // ตรวจสอบเวอร์ชัน PHP (ต้องการ 7.4 ขึ้นไป)
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return false;
        }
        
        // ตรวจสอบเวอร์ชัน WordPress (ต้องการ 5.9 ขึ้นไป)
        if (version_compare(get_bloginfo('version'), '5.9', '<')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * แสดงข้อความแจ้งเตือนใน admin หาก Elementor ยังไม่ได้ติดตั้ง
     */
    public static function admin_notice() {
        // ตรวจสอบสิทธิ์ผู้ใช้
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // แสดงข้อความถ้า Elementor ยังไม่ติดตั้ง
        if (!self::is_elementor_active()) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php esc_html_e('NewTM - ฟีเจอร์ Elementor:', 'newtm'); ?></strong><br>
                    <?php esc_html_e('หากต้องการใช้งาน widgets ของ Elementor กับ NewTM กรุณาติดตั้งและเปิดใช้งาน Elementor Page Builder', 'newtm'); ?>
                    <a href="<?php echo esc_url(admin_url('plugin-install.php?tab=search&s=elementor')); ?>" target="_blank">
                        <?php esc_html_e('ติดตั้ง Elementor →', 'newtm'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}

// เพิ่ม admin notice
add_action('admin_notices', array('NEWTM_Elementor_Checker', 'admin_notice'));
