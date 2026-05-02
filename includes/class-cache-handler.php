<?php
/**
 * ระบบจัดการ Cache สำหรับ NewTM Plugin
 * 
 * @package NewTM
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Cache_Handler {
    
    private static $instance = null;
    private static $cache_prefix = 'newtm_';
    private static $cache_group = 'newtm';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hook เข้า save/update/delete post เพื่อ clear cache
        add_action('save_post_newtm_news', array($this, 'clear_news_cache'));
        add_action('delete_post', array($this, 'clear_cache_on_delete'));
        add_action('set_object_terms', array($this, 'clear_cache_on_taxonomy_change'), 10, 4);
        
        // Hook สำหรับเคลียร์ cache เมื่อเปลี่ยนตัวเลือกการแสดงผล
        add_action('update_option_newtm_title_max_length', array($this, 'clear_all_news_cache'));
        add_action('update_option_newtm_excerpt_max_length', array($this, 'clear_all_news_cache'));
        add_action('update_option_newtm_items_per_page', array($this, 'clear_all_news_cache'));
    }
    
    /**
     * เคลียร์ cache ของข่าวเมื่อมีการบันทึก/อัพเดต
     * 
     * @param int $post_id
     */
    public function clear_news_cache($post_id) {
        // ตรวจสอบว่าเป็น autosave หรือไม่
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // ตรวจสอบ nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
            // ถ้า nonce ไม่ถูกต้อง ยังคงเคลียร์ cache เพื่อให้ปลอดภัย
        }
        
        // เคลียร์ cache ของข่าวนี้
        $this->clear_single_news_cache($post_id);
        
        // เคลียร์ cache ของหมวดหมู่ที่เกี่ยวข้อง
        $terms = wp_get_post_terms($post_id, 'newtm_category', array('fields' => 'ids'));
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term_id) {
                $this->clear_category_cache($term_id);
            }
        }
        
        // เคลียร์ cache ทั้งหมด
        $this->clear_all_news_cache();
    }
    
    /**
     * เคลียร์ cache เมื่อมีการลบข่าว
     * 
     * @param int $post_id
     */
    public function clear_cache_on_delete($post_id) {
        $post = get_post($post_id);
        
        // ตรวจสอบว่าเป็น post type newtm_news หรือไม่
        if (!$post || 'newtm_news' !== $post->post_type) {
            return;
        }
        
        // เคลียร์ cache ของข่าวนี้
        $this->clear_single_news_cache($post_id);
        
        // เคลียร์ cache ของหมวดหมู่ที่เกี่ยวข้อง
        $terms = wp_get_post_terms($post_id, 'newtm_category', array('fields' => 'ids'));
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term_id) {
                $this->clear_category_cache($term_id);
            }
        }
        
        // เคลียร์ cache ทั้งหมด
        $this->clear_all_news_cache();
    }
    
    /**
     * เคลียร์ cache เมื่อเปลี่ยนแปลง taxonomy
     * 
     * @param int    $object_id
     * @param array  $terms
     * @param array  $tt_ids
     * @param string $taxonomy
     */
    public function clear_cache_on_taxonomy_change($object_id, $terms, $tt_ids, $taxonomy) {
        if ('newtm_category' !== $taxonomy) {
            return;
        }
        
        // ตรวจสอบว่าเป็น post type newtm_news หรือไม่
        $post = get_post($object_id);
        if (!$post || 'newtm_news' !== $post->post_type) {
            return;
        }
        
        // เคลียร์ cache ของข่าวนี้
        $this->clear_single_news_cache($object_id);
        
        // เคลียร์ cache ของทุกหมวดหมู่
        foreach ($terms as $term_id) {
            $this->clear_category_cache($term_id);
        }
        
        // เคลียร์ cache ทั้งหมด
        $this->clear_all_news_cache();
    }
    
    /**
     * เคลียร์ cache ของข่าวเดี่ยว
     * 
     * @param int $post_id
     */
    public function clear_single_news_cache($post_id) {
        wp_cache_delete(self::$cache_prefix . 'post_' . $post_id, self::$cache_group);
    }
    
    /**
     * เคลียร์ cache ของหมวดหมู่
     * 
     * @param int $category_id
     */
    public function clear_category_cache($category_id) {
        wp_cache_delete(self::$cache_prefix . 'category_' . $category_id, self::$cache_group);
    }
    
    /**
     * เคลียร์ cache ทั้งหมดของข่าว
     */
    public function clear_all_news_cache() {
        wp_cache_delete(self::$cache_prefix . 'all_news', self::$cache_group);
        wp_cache_delete(self::$cache_prefix . 'all_categories', self::$cache_group);
        
        // ถ้าใช้ Redis cache ให้ clear patterns
        if (function_exists('wp_cache_flush')) {
            // Clear widget caches
            wp_cache_delete(self::$cache_prefix . 'widget_grid', self::$cache_group);
            wp_cache_delete(self::$cache_prefix . 'widget_table', self::$cache_group);
        }
    }
    
    /**
     * ดึง cache
     * 
     * @param string $key
     * @param bool   $found
     * @return mixed
     */
    public static function get($key, &$found = null) {
        return wp_cache_get(self::$cache_prefix . $key, self::$cache_group, false, $found);
    }
    
    /**
     * ตั้งค่า cache
     * 
     * @param string $key
     * @param mixed  $data
     * @param int    $expire
     * @return bool
     */
    public static function set($key, $data, $expire = 3600) {
        return wp_cache_set(self::$cache_prefix . $key, $data, self::$cache_group, $expire);
    }
    
    /**
     * ลบ cache
     * 
     * @param string $key
     * @return bool
     */
    public static function delete($key) {
        return wp_cache_delete(self::$cache_prefix . $key, self::$cache_group);
    }
}

// Initialize cache handler
NEWTM_Cache_Handler::get_instance();
