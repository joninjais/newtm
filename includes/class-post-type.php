<?php
/**
 * Custom Post Type สำหรับข่าวสาร
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Post_Type {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
    }
    
    public function register_post_type() {
        $args = array(
            'label' => 'ข่าวสาร',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,  // รองรับ Gutenberg และ REST API
            'rest_base' => 'news',
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array(
                'title', 
                'editor', 
                'thumbnail', 
                'excerpt',
                'custom-fields',  // รองรับ custom fields
                'revisions',      // รองรับประวัติการแก้ไข
                'author',         // แสดงผู้เขียน
            ),
            'taxonomies' => array('newtm_category', 'newtm_tag'),
            'menu_icon' => 'dashicons-megaphone',
            'rewrite' => array(
                'slug' => 'news',
                'with_front' => false,
            ),
            'labels' => array(
                'name' => 'ข่าวสาร',
                'singular_name' => 'ข่าว',
                'add_new' => 'เพิ่มข่าวใหม่',
                'add_new_item' => 'เพิ่มข่าวใหม่',
                'edit_item' => 'แก้ไขข่าว',
                'new_item' => 'ข่าวใหม่',
                'view_item' => 'ดูข่าว',
                'search_items' => 'ค้นหาข่าว',
                'not_found' => 'ไม่พบข่าว',
                'not_found_in_trash' => 'ไม่พบข่าวในถังขยะ',
                'all_items' => 'ข่าวทั้งหมด',
            ),
        );
        
        register_post_type('newtm_news', $args);
    }
}
