<?php
/**
 * Plugin Name: NewTM - News Table System
 * Plugin URI: https://example.com/newtm
 * Description: ระบบจัดการข่าวแบบตารางสำหรับหน่วยงานราชการ
 * Version: 1.0.0
 * Author: NewTM
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: newtm
 * Domain Path: /languages
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง
if (!defined('ABSPATH')) {
    exit;
}

// กำหนดค่าคงที่ของ plugin
define('NEWTM_VERSION', '1.0.0');
define('NEWTM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEWTM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NEWTM_PLUGIN_FILE', __FILE__);

// โหลดไฟล์ที่จำเป็น
require_once NEWTM_PLUGIN_DIR . 'includes/class-post-type.php';
require_once NEWTM_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once NEWTM_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once NEWTM_PLUGIN_DIR . 'includes/class-settings.php';
require_once NEWTM_PLUGIN_DIR . 'includes/class-elementor-checker.php';

/**
 * คลาสหลักของ plugin
 */
class NEWTM_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
        $this->init_elementor();
        $this->enable_elementor_support();
    }
    
    private function init() {
        // เริ่มต้น post type
        new NEWTM_Post_Type();
        
        // เริ่มต้น taxonomy
        new NEWTM_Taxonomy();
        
        // เริ่มต้น shortcodes
        new NEWTM_Shortcodes();
        
        // เริ่มต้น settings
        new NEWTM_Settings();
        
        // โหลด assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // โหลด template
        add_filter('single_template', array($this, 'load_single_template'));
        add_filter('archive_template', array($this, 'load_archive_template'));

        // Admin filters for news list
        add_action('restrict_manage_posts', array($this, 'add_news_category_filter'));
        add_action('pre_get_posts', array($this, 'apply_news_category_filter'));
    }
    
    public function load_single_template($template) {
        global $post;
        
        if ($post->post_type === 'newtm_news') {
            $plugin_template = NEWTM_PLUGIN_DIR . 'templates/single-newtm_news.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    public function load_archive_template($template) {
        if (is_post_type_archive('newtm_news') || is_tax('newtm_category')) {
            $plugin_template = NEWTM_PLUGIN_DIR . 'templates/archive-newtm_news.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    public function enqueue_public_assets() {
        wp_enqueue_style('newtm-public', NEWTM_PLUGIN_URL . 'assets/public.css', array(), NEWTM_VERSION);
        wp_enqueue_script('newtm-public', NEWTM_PLUGIN_URL . 'assets/public.js', array('jquery'), NEWTM_VERSION, true);
    }
    
    public function enqueue_admin_assets() {
        wp_enqueue_style('newtm-admin', NEWTM_PLUGIN_URL . 'assets/admin.css', array(), NEWTM_VERSION);
        wp_enqueue_script('newtm-admin', NEWTM_PLUGIN_URL . 'assets/admin.js', array('jquery'), NEWTM_VERSION, true);
    }

    /**
     * Add category filter dropdown on admin news list.
     */
    public function add_news_category_filter() {
        global $typenow;

        if ( 'newtm_news' !== $typenow ) {
            return;
        }

        $selected = isset( $_GET['newtm_category'] ) ? sanitize_text_field( wp_unslash( $_GET['newtm_category'] ) ) : '';

        wp_dropdown_categories( array(
            'show_option_all' => __( 'หมวดหมู่ทั้งหมด', 'newtm' ),
            'taxonomy'        => 'newtm_category',
            'name'            => 'newtm_category',
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'show_count'      => true,
            'hide_empty'      => false,
            'value_field'     => 'slug',
        ) );
    }

    /**
     * Apply category filter to admin news list query.
     *
     * @param WP_Query $query
     */
    public function apply_news_category_filter( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $post_type = $query->get( 'post_type' );
        if ( is_array( $post_type ) ) {
            if ( ! in_array( 'newtm_news', $post_type, true ) ) {
                return;
            }
        } elseif ( 'newtm_news' !== $post_type ) {
            return;
        }

        if ( empty( $_GET['newtm_category'] ) ) {
            return;
        }

        $term_slug = sanitize_text_field( wp_unslash( $_GET['newtm_category'] ) );
        if ( '' === $term_slug ) {
            return;
        }

        $tax_query = (array) $query->get( 'tax_query' );
        $tax_query[] = array(
            'taxonomy' => 'newtm_category',
            'field'    => 'slug',
            'terms'    => array( $term_slug ),
        );

        $query->set( 'tax_query', $tax_query );

        // Also set query var for compatibility.
        $query->set( 'newtm_category', $term_slug );
    }
    
    /**
     * เริ่มต้น Elementor Integration
     * ใช้ wp_enqueue_scripts และ admin_enqueue_scripts เพื่อหลีกเลี่ยง fatal error ตอน activate
     */
    private function init_elementor() {
        // Frontend Elementor integration
        add_action('wp_enqueue_scripts', array($this, 'register_elementor_widgets'), 999);
        
        // Backend Elementor integration (Elementor editor)
        add_action('admin_enqueue_scripts', array($this, 'register_elementor_widgets'), 999);
    }
    
    /**
     * ลงทะเบียน Elementor widgets
     */
    public function register_elementor_widgets() {
        // ตรวจสอบว่า Elementor โหลดแล้วหรือยัง
        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }
        
        // ตรวจสอบว่า Widgets_Manager มีหรือไม่
        if (!class_exists('\Elementor\Widgets_Manager')) {
            return;
        }
        
        // Hook เข้า elementor/widgets/register
        add_action('elementor/widgets/register', array($this, 'load_elementor_widgets'), 20);
        
        // ลงทะเบียนหมวดหมู่ widget
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_category'));
    }
    
    /**
     * โหลดไฟล์ widget ของ Elementor
     */
    public function load_elementor_widgets($widgets_manager) {
        // ตรวจสอบว่า widgets_manager พร้อมใช้งาน
        if (!$widgets_manager || !method_exists($widgets_manager, 'register')) {
            return;
        }
        
        // รายการ widget files
        $widget_files = array(
            'includes/widgets/class-widget-news-grid.php' => 'NEWTM_Widget_News_Grid',
            'includes/widgets/class-widget-news-table.php' => 'NEWTM_Widget_News_Table',
            'includes/widgets/class-widget-category-tabs.php' => 'NEWTM_Widget_Category_Tabs',
        );
        
        // โหลดและลงทะเบียน widget ทีละตัว
        foreach ($widget_files as $file => $class_name) {
            $filepath = NEWTM_PLUGIN_DIR . $file;
            
            // ตรวจสอบว่าไฟล์มีอยู่
            if (!file_exists($filepath)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('NewTM Widget file not found: ' . $filepath);
                }
                continue;
            }
            
            // โหลดไฟล์ widget
            if (!class_exists($class_name)) {
                try {
                    require_once $filepath;
                } catch (Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('NewTM Widget require error: ' . $e->getMessage());
                    }
                    continue;
                }
            }
            
            // ลงทะเบียน widget
            if (class_exists($class_name)) {
                try {
                    $widget_instance = new $class_name();
                    
                    // ตรวจสอบว่า widget instance ถูกต้อง
                    if ($widget_instance instanceof \Elementor\Widget_Base) {
                        $widgets_manager->register($widget_instance);
                    }
                } catch (Exception $e) {
                    // บันทึก error log ถ้า WP_DEBUG เปิดอยู่
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('NewTM Widget Error: ' . $e->getMessage());
                    }
                }
            }
        }
    }
    
    /**
     * เพิ่มหมวดหมู่ widget ใน Elementor
     */
    public function add_elementor_widget_category($elements_manager) {
        $elements_manager->add_category(
            'newtm',
            array(
                'title' => __('NewTM - ข่าวสาร', 'newtm'),
                'icon' => 'fa fa-newspaper-o',
            )
        );
    }
    
    /**
     * เปิดใช้งาน Elementor สำหรับ Custom Post Type
     */
    private function enable_elementor_support() {
        // เพิ่ม support สำหรับ Elementor ใน custom post type
        add_action('init', function() {
            // ตรวจสอบว่า Elementor เปิดใช้งาน
            if (defined('ELEMENTOR_VERSION')) {
                // เพิ่ม post type ให้ Elementor รองรับ
                add_post_type_support('newtm_news', 'elementor');
            }
        }, 11); // Priority 11 เพื่อให้ทำงานหลังจาก post type ถูกลงทะเบียน
        
        // เพิ่ม newtm_news เข้าไปใน Elementor CPT Support
        add_filter('elementor/utils/get_public_post_types', array($this, 'add_cpt_to_elementor'));
        
        // ปิด Loop Builder สำหรับ widgets ของเรา (แก้ไข JavaScript errors)
        add_action('elementor/documents/register_controls', array($this, 'disable_loop_builder_for_newtm'), 10);
    }
    
    /**
     * เพิ่ม Custom Post Type ให้ Elementor รู้จัก
     */
    public function add_cpt_to_elementor($post_types) {
        $post_types['newtm_news'] = 'newtm_news';
        return $post_types;
    }
    
    /**
     * ปิด Loop Builder สำหรับป้องกัน JavaScript errors
     */
    public function disable_loop_builder_for_newtm($document) {
        // ป้องกัน Loop Builder errors โดยไม่เปิดใช้งาน Loop สำหรับ newtm_news
        if ($document && method_exists($document, 'get_post') && $document->get_post()) {
            $post = $document->get_post();
            if ($post && $post->post_type === 'newtm_news') {
                // ปิด Loop Builder controls สำหรับ newtm_news
                remove_action('elementor/documents/register_controls', 'elementor_pro_register_loop_builder_controls', 10);
            }
        }
    }
}

// เริ่มต้น plugin
function newtm_init() {
    NEWTM_Plugin::get_instance();
}
add_action('plugins_loaded', 'newtm_init');

// เมื่อเปิดใช้งาน plugin
register_activation_hook(NEWTM_PLUGIN_FILE, function() {
    // เริ่มต้น post types และ taxonomies โดยตรง
    // ห้ามใช้ do_action('init') เพราะจะทำให้ Elementor โหลดซ้ำ
    $post_type = new NEWTM_Post_Type();
    $taxonomy = new NEWTM_Taxonomy();
    
    // รีเฟรช permalink rules
    flush_rewrite_rules();
});

// เมื่อปิดใช้งาน plugin
register_deactivation_hook(NEWTM_PLUGIN_FILE, function() {
    // รีเฟรช permalink rules
    flush_rewrite_rules();
});
