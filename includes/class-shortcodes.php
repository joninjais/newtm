<?php
/**
 * Shortcodes สำหรับแสดงข่าวสาร
 */

if (!defined('ABSPATH')) {
    exit;
}

class NEWTM_Shortcodes {
    
    public function __construct() {
        add_shortcode('newtm_table', array($this, 'render_news_table'));
        add_shortcode('newtm_all_categories', array($this, 'render_all_categories'));
    }
    
    /**
     * แสดงข่าวในรูปแบบตารางตามหมวดหมู่
     */
    public function render_news_table($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => 0,
            'show_title' => 'true',
            'title_length' => 0,  // จำนวนตัวอักษรสูงสุดของหัวข้อ
            'excerpt_length' => 0, // จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ
        ), $atts);
        
        if (empty($atts['category'])) {
            return '<p>กรุณาระบุ category</p>';
        }
        
        // ดึงหมวดหมู่จาก slug
        $category = get_term_by('slug', sanitize_title($atts['category']), 'newtm_category');
        if (!$category) {
            return '<p>ไม่พบหมวดหมู่</p>';
        }
        
        // ดึงค่าจากการตั้งค่าของหมวดหมู่ (ถ้ามี)
        if ($atts['title_length'] == 0) {
            $cat_title_length = get_term_meta($category->term_id, 'category_title_length', true);
            if ($cat_title_length !== '') {
                $atts['title_length'] = intval($cat_title_length);
            } else {
                // ถ้าไม่มีการตั้งค่าหมวดหมู่ ให้ใช้ค่าทั่วไป
                $atts['title_length'] = newtm_get_option('title_max_length', 0);
            }
        }

        if (intval($atts['limit']) <= 0) {
            $cat_items = get_term_meta($category->term_id, 'category_items_per_page', true);
            if ($cat_items !== '') {
                $atts['limit'] = intval($cat_items);
            } else {
                // ถ้าไม่มีการตั้งค่าหมวดหมู่ ให้ใช้ค่าทั่วไป
                $atts['limit'] = newtm_get_option('items_per_page', 5);
            }
        }
        
        $args = array(
            'post_type' => 'newtm_news',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'newtm_category',
                    'field' => 'id',
                    'terms' => $category->term_id,
                ),
            ),
        );
        
        $query = new WP_Query($args);
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="newtm-news-section">';
            
            if ($atts['show_title'] === 'true' || $atts['show_title'] === '1') {
                echo '<h3 class="newtm-section-title">' . esc_html($category->name) . '</h3>';
            }
            
            echo '<div class="table-responsive"><table class="table"><tbody>';
            
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $link = get_permalink();
                $title = get_the_title();
                
                // จำกัดความยาวของหัวข้อ
                $title = newtm_truncate_text($title, intval($atts['title_length']));

                $day   = get_the_date('j');   // วัน
                $month = get_the_date('n');   // เดือนเป็นตัวเลข (1-12)
                $year  = get_the_date('Y');   // ปี ค.ศ.

                // แปลงเป็น พ.ศ.
                $thai_year = $year + 543;

                // กำหนดชื่อเดือนย่อภาษาไทย
                $thai_months = [
                    1 => 'ม.ค.',
                    2 => 'ก.พ.',
                    3 => 'มี.ค.',
                    4 => 'เม.ย.',
                    5 => 'พ.ค.',
                    6 => 'มิ.ย.',
                    7 => 'ก.ค.',
                    8 => 'ส.ค.',
                    9 => 'ก.ย.',
                    10 => 'ต.ค.',
                    11 => 'พ.ย.',
                    12 => 'ธ.ค.'
                ];

                echo '<tr class="content-table">';
                echo '<td class="content-table-date">' . esc_html($day . ' ' . $thai_months[$month] . ' ' . $thai_year)
 . '</td>';
                echo '<td class="content-table-title"><a href="' . esc_url($link) . '" rel="bookmark">' . esc_html($title) . '</a></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table></div>';
            
            // แสดงลิงก์ "ดูทั้งหมด" ถ้ามีข่าวมากกว่าที่กำหนด
            if ($query->found_posts > intval($atts['limit'])) {
                $cat_link = get_term_link($category);
                echo '<div class="newtm-view-all-wrap"><a href="' . esc_url($cat_link) . '" class="newtm-view-all-link">ดูทั้งหมด</a></div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="newtm-no-posts">ไม่พบข่าวสาร</p>';
        }
        
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    /**
     * แสดงข่าวทุกหมวดหมู่
     */
    public function render_all_categories($atts) {
        $atts = shortcode_atts(array(
            'columns' => 4,
            'limit' => 0,
        ), $atts);

        if (intval($atts['limit']) <= 0) {
            $atts['limit'] = newtm_get_option('items_per_page', 5);
        }
        
        $categories = get_terms(array(
            'taxonomy' => 'newtm_category',
            'hide_empty' => false,
        ));
        
        if (empty($categories) || is_wp_error($categories)) {
            return '<p>ไม่มีหมวดหมู่</p>';
        }
        
        ob_start();
        ?>
        <div class="newtm-categories-wrapper">
            <?php foreach ($categories as $category): ?>
                <div class="newtm-category-block">
                    <h4 class="newtm-category-title"><?php echo esc_html($category->name); ?></h4>
                    <?php
                    // ดึงค่า limit จากหมวดหมู่ (ถ้ามี)
                    $cat_limit = get_term_meta($category->term_id, 'category_items_per_page', true);
                    if ($cat_limit !== '' && intval($cat_limit) > 0) {
                        $limit = intval($cat_limit);
                    } else {
                        $limit = intval($atts['limit']);
                    }
                    echo do_shortcode('[newtm_table category="' . esc_attr($category->slug) . '" limit="' . $limit . '" show_title="false"]');
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
