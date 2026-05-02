<?php
/**
 * Admin Page สำหรับ Debug และ Fix Issues
 * 
 * @package NewTM
 * @since 1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Debug_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_debug_page'));
        add_action('admin_init', array($this, 'handle_fix_actions'));
    }
    
    /**
     * เพิ่มหน้า Debug ใน Admin
     */
    public function add_debug_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_submenu_page(
            'edit.php?post_type=newtm_news',
            'Debug & Fix',
            'Debug & Fix',
            'manage_options',
            'newtm-debug',
            array($this, 'render_debug_page')
        );
    }
    
    /**
     * จัดการการกระทำ Fix
     */
    public function handle_fix_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_POST['newtm_action'])) {
            $action = sanitize_text_field($_POST['newtm_action']);
            
            if ('clear_cache' === $action) {
                check_admin_referer('newtm_debug_nonce');
                $this->clear_all_cache();
                wp_safe_remote_post(admin_url('admin-ajax.php'), array(
                    'blocking' => false,
                    'sslverify' => apply_filters('https_local_ssl_verify', false),
                    'args' => array(
                        'action' => 'newtm_clear_cache_ajax'
                    )
                ));
                wp_redirect(add_query_arg('newtm_message', 'cache_cleared', admin_url('edit.php?post_type=newtm_news&page=newtm-debug')));
                exit;
            }
            
            if ('fix_draft_posts' === $action) {
                check_admin_referer('newtm_debug_nonce');
                $fixed = $this->publish_draft_posts();
                wp_redirect(add_query_arg('newtm_message', 'posts_published&count=' . $fixed, admin_url('edit.php?post_type=newtm_news&page=newtm-debug')));
                exit;
            }
            
            if ('fix_uncat_posts' === $action) {
                check_admin_referer('newtm_debug_nonce');
                $fixed = $this->assign_default_category();
                wp_redirect(add_query_arg('newtm_message', 'posts_categorized&count=' . $fixed, admin_url('edit.php?post_type=newtm_news&page=newtm-debug')));
                exit;
            }
        }
    }
    
    /**
     * Render หน้า Debug
     */
    public function render_debug_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // ดึงข้อมูลสถานะ
        $status = $this->get_status();
        
        ?>
        <div class="wrap">
            <h1>NewTM - Debug & Fix</h1>
            
            <?php $this->display_messages(); ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Status Card -->
                <div style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background: #f9f9f9;">
                    <h2>📊 สถานะปัจจุบัน</h2>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 8px;"><strong>จำนวนข่าว Published</strong></td>
                            <td style="padding: 8px; text-align: right; color: #2271b1;">
                                <strong><?php echo esc_html($status['published']); ?></strong>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 8px;"><strong>จำนวนข่าว Draft</strong></td>
                            <td style="padding: 8px; text-align: right; color: #d63638;">
                                <strong><?php echo esc_html($status['draft']); ?></strong>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 8px;"><strong>ไม่มีหมวดหมู่</strong></td>
                            <td style="padding: 8px; text-align: right; color: #d63638;">
                                <strong><?php echo esc_html($status['uncat']); ?></strong>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 8px;"><strong>จำนวนหมวดหมู่</strong></td>
                            <td style="padding: 8px; text-align: right;">
                                <strong><?php echo esc_html($status['categories']); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px;"><strong>Cache Status</strong></td>
                            <td style="padding: 8px; text-align: right;">
                                <span style="<?php echo $status['cache_enabled'] ? 'color: #0a8c5d;' : 'color: #d63638;'; ?>">
                                    <strong><?php echo $status['cache_enabled'] ? '✓ Enabled' : '✗ Disabled'; ?></strong>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Issues Card -->
                <div style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background: #f9f9f9;">
                    <h2>⚠️ ปัญหาที่พบ</h2>
                    
                    <?php if (empty($status['issues'])) : ?>
                        <p style="color: #0a8c5d; font-size: 16px;"><strong>✓ ไม่พบปัญหา</strong></p>
                    <?php else : ?>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($status['issues'] as $issue) : ?>
                                <li style="color: #d63638; margin-bottom: 8px;">
                                    <?php echo esc_html($issue); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Fix Actions -->
            <div style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background: #fff; margin: 20px 0;">
                <h2>🔧 การแก้ไข</h2>
                
                <form method="post" style="display: grid; gap: 10px; max-width: 400px;">
                    <?php wp_nonce_field('newtm_debug_nonce'); ?>
                    
                    <button type="submit" name="newtm_action" value="clear_cache" 
                            class="button button-primary" 
                            style="padding: 10px 20px; font-size: 14px;">
                        🔄 Clear All Cache
                    </button>
                    
                    <?php if ($status['draft'] > 0) : ?>
                        <button type="submit" name="newtm_action" value="fix_draft_posts" 
                                class="button button-secondary" 
                                style="padding: 10px 20px; font-size: 14px;"
                                onclick="return confirm('นี่จะเปลี่ยนข่าว Draft ทั้งหมดเป็น Published\n\nต้องการทำต่อหรือไม่?');">
                            📝 Publish ข่าว Draft (<?php echo esc_html($status['draft']); ?> อัน)
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($status['uncat'] > 0) : ?>
                        <button type="submit" name="newtm_action" value="fix_uncat_posts" 
                                class="button button-secondary" 
                                style="padding: 10px 20px; font-size: 14px;"
                                onclick="return confirm('นี่จะกำหนดหมวดหมู่ให้กับข่าว <?php echo esc_html($status['uncat']); ?> อัน\n\nต้องการทำต่อหรือไม่?');">
                            🏷️ กำหนดหมวดหมู่ (<?php echo esc_html($status['uncat']); ?> อัน)
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Query Info -->
            <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #f0f0f0; margin: 20px 0;">
                <h2>ℹ️ ข้อมูลเพิ่มเติม</h2>
                
                <details style="cursor: pointer; margin: 10px 0;">
                    <summary style="font-weight: bold;">📋 ข่าว Published ที่ไม่มีหมวดหมู่</summary>
                    <div style="margin: 10px 0; padding: 10px; background: white; border-radius: 3px;">
                        <?php 
                        $uncat_posts = $this->get_uncat_posts();
                        if (empty($uncat_posts)) {
                            echo '<p>ไม่มี</p>';
                        } else {
                            echo '<ol style="margin: 0; padding-left: 20px;">';
                            foreach ($uncat_posts as $post) {
                                echo '<li>' . esc_html($post->post_title) . ' (ID: ' . esc_html($post->ID) . ')</li>';
                            }
                            echo '</ol>';
                        }
                        ?>
                    </div>
                </details>
                
                <details style="cursor: pointer; margin: 10px 0;">
                    <summary style="font-weight: bold;">📋 ข่าว Draft</summary>
                    <div style="margin: 10px 0; padding: 10px; background: white; border-radius: 3px;">
                        <?php 
                        $draft_posts = $this->get_draft_posts();
                        if (empty($draft_posts)) {
                            echo '<p>ไม่มี</p>';
                        } else {
                            echo '<ol style="margin: 0; padding-left: 20px;">';
                            foreach ($draft_posts as $post) {
                                echo '<li>' . esc_html($post->post_title) . ' (ID: ' . esc_html($post->ID) . ')</li>';
                            }
                            echo '</ol>';
                        }
                        ?>
                    </div>
                </details>
            </div>
        </div>
        <?php
    }
    
    /**
     * แสดงข้อความจากการกระทำ
     */
    private function display_messages() {
        if (isset($_GET['newtm_message'])) {
            $message = sanitize_text_field($_GET['newtm_message']);
            
            if ('cache_cleared' === $message) {
                echo '<div class="notice notice-success"><p>✓ Cache ถูกล้างแล้ว</p></div>';
            }
            
            if (strpos($message, 'posts_published') !== false) {
                $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
                echo '<div class="notice notice-success"><p>✓ เปลี่ยน ' . esc_html($count) . ' ข่าวเป็น Published</p></div>';
            }
            
            if (strpos($message, 'posts_categorized') !== false) {
                $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
                echo '<div class="notice notice-success"><p>✓ กำหนดหมวดหมู่ให้กับ ' . esc_html($count) . ' ข่าว</p></div>';
            }
        }
    }
    
    /**
     * ดึงข้อมูลสถานะ
     */
    private function get_status() {
        global $wpdb;
        
        $status = array();
        
        // นับจำนวนข่าวตามสถานะ
        $status['published'] = $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts} 
            WHERE post_type = 'newtm_news' AND post_status = 'publish'"
        );
        
        $status['draft'] = $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts} 
            WHERE post_type = 'newtm_news' AND post_status = 'draft'"
        );
        
        // นับข่าวที่ไม่มีหมวดหมู่
        $status['uncat'] = $wpdb->get_var(
            "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
            WHERE p.post_type = 'newtm_news' AND p.post_status = 'publish'
            AND (tt.taxonomy != 'newtm_category' OR tr.term_taxonomy_id IS NULL)"
        );
        
        // นับจำนวนหมวดหมู่
        $status['categories'] = $wpdb->get_var(
            "SELECT COUNT(term_id) FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON (t.term_id = tt.term_id)
            WHERE tt.taxonomy = 'newtm_category'"
        );
        
        // ตรวจสอบ Cache
        $status['cache_enabled'] = defined('WP_CACHE') && WP_CACHE;
        
        // ตรวจสอบปัญหา
        $status['issues'] = array();
        
        if ($status['published'] === 0) {
            $status['issues'][] = 'ไม่มีข่าว Published ที่จะแสดง';
        }
        
        if ($status['draft'] > 0) {
            $status['issues'][] = 'มีข่าว ' . intval($status['draft']) . ' ฉบับที่ยังเป็น Draft';
        }
        
        if ($status['uncat'] > 0) {
            $status['issues'][] = 'มีข่าว ' . intval($status['uncat']) . ' ฉบับที่ไม่มีหมวดหมู่';
        }
        
        if ($status['categories'] === 0) {
            $status['issues'][] = 'ยังไม่มีการสร้างหมวดหมู่';
        }
        
        return $status;
    }
    
    /**
     * ดึงข่าวที่ไม่มีหมวดหมู่
     */
    private function get_uncat_posts() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT DISTINCT p.ID, p.post_title FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
            WHERE p.post_type = 'newtm_news' AND p.post_status = 'publish'
            AND (tt.taxonomy != 'newtm_category' OR tr.term_taxonomy_id IS NULL)
            LIMIT 50"
        );
    }
    
    /**
     * ดึงข่าว Draft
     */
    private function get_draft_posts() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT ID, post_title FROM {$wpdb->posts} 
            WHERE post_type = 'newtm_news' AND post_status = 'draft'
            LIMIT 50"
        );
    }
    
    /**
     * เคลียร์ cache ทั้งหมด
     */
    private function clear_all_cache() {
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Publish ข่าว Draft ทั้งหมด
     */
    private function publish_draft_posts() {
        global $wpdb;
        
        $draft_posts = $wpdb->get_col(
            "SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'newtm_news' AND post_status = 'draft'"
        );
        
        $count = 0;
        foreach ($draft_posts as $post_id) {
            wp_update_post(array(
                'ID' => intval($post_id),
                'post_status' => 'publish'
            ));
            $count++;
        }
        
        return $count;
    }
    
    /**
     * กำหนดหมวดหมู่เริ่มต้นให้กับข่าวที่ไม่มีหมวดหมู่
     */
    private function assign_default_category() {
        global $wpdb;
        
        // ดึงหมวดหมู่แรก
        $default_cat = $wpdb->get_var(
            "SELECT term_id FROM {$wpdb->term_taxonomy} 
            WHERE taxonomy = 'newtm_category' LIMIT 1"
        );
        
        if (!$default_cat) {
            return 0;
        }
        
        $uncat_posts = $wpdb->get_col(
            "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
            WHERE p.post_type = 'newtm_news' AND p.post_status = 'publish'
            AND (tt.taxonomy != 'newtm_category' OR tr.term_taxonomy_id IS NULL)"
        );
        
        $count = 0;
        foreach ($uncat_posts as $post_id) {
            wp_set_object_terms(intval($post_id), intval($default_cat), 'newtm_category', false);
            $count++;
        }
        
        return $count;
    }
}

// Initialize debug page
new NEWTM_Debug_Page();
