<?php
/**
 * Helper Functions สำหรับ NewTM Plugin
 * 
 * @package NewTM
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Debug: แสดงข้อมูล WP_Query Arguments
 * 
 * @param array $args
 */
function newtm_debug_query_args($args = array()) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NEWTM Query Args: ' . print_r($args, true));
    }
}

/**
 * ตรวจสอบจำนวนข่าวทั้งหมดของแต่ละ post_status
 * 
 * @return array
 */
function newtm_count_posts_by_status() {
    global $wpdb;
    
    $query = "
        SELECT 
            p.post_status,
            COUNT(p.ID) as count
        FROM {$wpdb->posts} p
        WHERE p.post_type = 'newtm_news'
        GROUP BY p.post_status
    ";
    
    $results = $wpdb->get_results($query);
    
    $counts = array();
    foreach ($results as $result) {
        $counts[$result->post_status] = $result->count;
    }
    
    return $counts;
}

/**
 * ตรวจสอบข่าวที่ไม่ได้กำหนดหมวดหมู่
 * 
 * @return int
 */
function newtm_count_unassigned_news() {
    global $wpdb;
    
    $query = "
        SELECT COUNT(DISTINCT p.ID) as count
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
        LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE p.post_type = 'newtm_news'
        AND p.post_status = 'publish'
        AND (tt.taxonomy != 'newtm_category' OR tr.term_taxonomy_id IS NULL)
    ";
    
    $result = $wpdb->get_var($query);
    return intval($result);
}

/**
 * ตรวจสอบข่าวปัญหา (missing meta/data)
 * 
 * @return array
 */
function newtm_get_problematic_posts() {
    global $wpdb;
    
    $query = "
        SELECT 
            p.ID,
            p.post_title,
            p.post_status,
            COUNT(tr.term_taxonomy_id) as category_count,
            CASE WHEN pm.meta_id IS NULL THEN 'missing_featured_image' ELSE 'ok' END as featured_image_status
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
        LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'newtm_category')
        LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id')
        WHERE p.post_type = 'newtm_news'
        AND p.post_status = 'publish'
        GROUP BY p.ID
        HAVING category_count = 0
    ";
    
    return $wpdb->get_results($query);
}

/**
 * ตรวจสอบสถานะของ Widget/Shortcodes
 * 
 * @return array
 */
function newtm_get_widget_status() {
    $status = array();
    
    // ตรวจสอบ post status
    $status['post_counts'] = newtm_count_posts_by_status();
    $status['unassigned_news'] = newtm_count_unassigned_news();
    $status['problematic_posts'] = count(newtm_get_problematic_posts());
    
    // ตรวจสอบ cache
    $status['cache_enabled'] = defined('WP_CACHE') && WP_CACHE;
    $status['redis_enabled'] = defined('WP_REDIS_HOST') && WP_REDIS_HOST;
    
    return $status;
}

/**
 * แสดงสถานะ Widget ใน Admin Notice
 */
function newtm_display_widget_status() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (!isset($_GET['page']) || 'newtm-settings' !== $_GET['page']) {
        return;
    }
    
    $status = newtm_get_widget_status();
    
    ?>
    <div class="notice notice-info">
        <p><strong>NewTM Widget Status:</strong></p>
        <ul style="margin: 10px 0 10px 20px;">
            <li>Published News: <?php echo esc_html($status['post_counts']['publish'] ?? 0); ?></li>
            <li>Draft News: <?php echo esc_html($status['post_counts']['draft'] ?? 0); ?></li>
            <li>Unassigned Categories: <?php echo esc_html($status['unassigned_news']); ?></li>
            <li>Problematic Posts: <?php echo esc_html($status['problematic_posts']); ?></li>
            <li>Cache Enabled: <?php echo $status['cache_enabled'] ? '✓' : '✗'; ?></li>
            <li>Redis Enabled: <?php echo $status['redis_enabled'] ? '✓' : '✗'; ?></li>
        </ul>
    </div>
    <?php
}

/**
 * ตรวจสอบและแก้ไขปัญหา Widget โดยอัตโนมัติ
 */
function newtm_auto_fix_widget_issues() {
    global $wpdb;
    
    $problems = array();
    
    // 1. ตรวจสอบข่าวที่ draft แต่ควรจะ publish
    $draft_count = $wpdb->get_var(
        "SELECT COUNT(ID) FROM {$wpdb->posts} 
        WHERE post_type = 'newtm_news' AND post_status = 'draft'"
    );
    
    if ($draft_count > 0) {
        $problems[] = sprintf(
            'พบข่าว %d ฉบับที่ยังเป็น Draft (ควรเป็น Published)',
            $draft_count
        );
    }
    
    // 2. ตรวจสอบข่าวที่ไม่มีหมวดหมู่
    $uncategorized = newtm_count_unassigned_news();
    if ($uncategorized > 0) {
        $problems[] = sprintf(
            'พบข่าว %d ฉบับที่ไม่มีการกำหนดหมวดหมู่',
            $uncategorized
        );
    }
    
    return $problems;
}

/**
 * ทำการ Refresh News Query
 * ใช้สำหรับ fix issues
 * 
 * @return bool
 */
function newtm_refresh_all_queries() {
    // Clear all caches
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('newtm');
    }
    
    // Force recount posts
    wp_cache_delete('posts-newtm_news');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    return true;
}

/**
 * ดึงข่าว Published เฉพาะ (สำหรับ Widgets)
 * 
 * @param array $args
 * @return WP_Query
 */
function newtm_get_published_news($args = array()) {
    $default_args = array(
        'post_type' => 'newtm_news',
        'post_status' => 'publish', // บังคับให้ใช้ publish เท่านั้น
        'posts_per_page' => -1,
    );
    
    $args = wp_parse_args($args, $default_args);
    
    // ตรวจสอบว่า post_status ถูกตั้งค่าเป็น 'publish'
    if (!isset($args['post_status']) || 'publish' !== $args['post_status']) {
        $args['post_status'] = 'publish';
    }
    
    return new WP_Query($args);
}

/**
 * Log errors สำหรับ Troubleshooting
 * 
 * @param string $message
 * @param array  $data
 */
function newtm_log_error($message, $data = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('NEWTM ERROR: ' . $message);
        if (!empty($data)) {
            error_log('NEWTM DATA: ' . print_r($data, true));
        }
    }
}

// Display status on settings page
add_action('admin_init', 'newtm_display_widget_status');
