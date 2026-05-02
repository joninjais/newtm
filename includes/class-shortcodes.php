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
        // AJAX handlers for shortcode pagination
        add_action('wp_ajax_newtm_load_page',        array($this, 'ajax_load_page'));
        add_action('wp_ajax_nopriv_newtm_load_page', array($this, 'ajax_load_page'));
    }

    /**
     * AJAX: โหลดหน้าข่าวสำหรับ [newtm_table]
     */
    public function ajax_load_page() {
        check_ajax_referer('newtm_ajax_nonce', 'nonce');
        $category       = isset($_POST['category'])   ? sanitize_title(wp_unslash($_POST['category']))   : '';
        $paged          = isset($_POST['paged'])       ? max(1, intval($_POST['paged']))                  : 1;
        $limit          = isset($_POST['limit'])       ? max(1, intval($_POST['limit']))                  : 5;
        $title_length   = isset($_POST['title_length']) ? intval($_POST['title_length'])                  : 0;
        if (empty($category)) { wp_send_json_error('no category'); return; }
        $html = $this->render_news_table(array(
            'category'     => $category,
            'limit'        => $limit,
            'paged'        => $paged,
            'show_title'   => 'false',
            'title_length' => $title_length,
        ), '', 'newtm_table');
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * แสดงข่าวในรูปแบบตารางตามหมวดหมู่
     */
    public function render_news_table($atts, $content = '', $tag = '') {
        $atts = shortcode_atts(array(
            'category'     => '',
            'limit'        => 0,
            'show_title'   => 'true',
            'title_length' => 0,
            'excerpt_length' => 0,
            'paged'        => 1,
        ), $atts);
        $atts['paged'] = max(1, intval($atts['paged']));
        
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
            'post_type'      => 'newtm_news',
            'posts_per_page' => intval($atts['limit']),
            'paged'          => $atts['paged'],
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'newtm_category',
                    'field'    => 'term_id',
                    'terms'    => $category->term_id,
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
                try {
                    $post_id = get_the_ID();
                    $link = get_permalink($post_id);
                    $title = get_the_title($post_id);

                    // จำกัดความยาวของหัวข้อ
                    $title = newtm_truncate_text($title, intval($atts['title_length']));

                    $day   = get_the_date('j', $post_id);   // วัน
                    $month = (int) get_the_date('n', $post_id);   // เดือนเป็นตัวเลข (1-12)
                    $year  = (int) get_the_date('Y', $post_id);   // ปี ค.ศ.

                    // แปลงเป็น พ.ศ.
                    $thai_year = $year > 0 ? ($year + 543) : '';

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

                    $month_text = $thai_months[$month] ?? '';
                    $safe_link = $link ? $link : '#';

                    echo '<tr class="content-table">';
                    echo '<td class="content-table-date">' . esc_html(trim($day . ' ' . $month_text . ' ' . $thai_year)) . '</td>';
                    echo '<td class="content-table-title"><a href="' . esc_url($safe_link) . '" rel="bookmark">' . esc_html($title) . '</a></td>';
                    echo '</tr>';
                } catch (Throwable $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('NewTM render_news_table error (post ID ' . (int) get_the_ID() . '): ' . $e->getMessage());
                    }
                    continue;
                }
            }
            
            echo '</tbody></table></div>';
            
            // ---- Pagination ----
            $total_pages  = $query->max_num_pages;
            $current_page = $atts['paged'];
            $cat_link     = get_term_link($category);
            $limit_val    = intval($atts['limit']);
            $tl_val       = intval($atts['title_length']);

            if ($total_pages > 1) {
                // Unique ID สำหรับ block นี้ (ใช้ term_id + ตำแหน่ง)
                $block_id = 'newtm-block-' . $category->term_id;
                echo '<div class="newtm-shortcode-pagination" ';
                echo 'data-block="' . esc_attr($block_id) . '" ';
                echo 'data-category="' . esc_attr($category->slug) . '" ';
                echo 'data-limit="' . esc_attr($limit_val) . '" ';
                echo 'data-title-length="' . esc_attr($tl_val) . '" ';
                echo 'data-total="' . esc_attr($total_pages) . '" ';
                echo 'data-current="' . esc_attr($current_page) . '">';

                if ($current_page > 1) {
                    echo '<button class="newtm-pg-btn newtm-pg-prev" data-page="' . ($current_page - 1) . '">‹</button>';
                }
                for ($p = 1; $p <= $total_pages; $p++) {
                    $active = ($p === $current_page) ? ' active' : '';
                    echo '<button class="newtm-pg-btn' . $active . '" data-page="' . $p . '">' . $p . '</button>';
                }
                if ($current_page < $total_pages) {
                    echo '<button class="newtm-pg-btn newtm-pg-next" data-page="' . ($current_page + 1) . '">›</button>';
                }
                echo '</div>';
            } elseif ($query->found_posts > $limit_val && $cat_link && !is_wp_error($cat_link)) {
                // ไม่มี pagination: แสดงลิงก์ "ดูทั้งหมด"
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
                    try {
                        echo do_shortcode('[newtm_table category="' . esc_attr($category->slug) . '" limit="' . $limit . '" show_title="false"]');
                    } catch (Throwable $e) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            error_log('NewTM render_all_categories error (category ' . (int) $category->term_id . '): ' . $e->getMessage());
                        }
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
