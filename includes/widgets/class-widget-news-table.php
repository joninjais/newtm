<?php
/**
 * Elementor Widget: แสดงข่าวแบบ List/ตาราง
 * 
 * @package NewTM
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

/**
 * Widget สำหรับแสดงข่าวในรูปแบบ List/Table
 */
class NEWTM_Widget_News_Table extends Widget_Base {
    
    public function get_name() {
        return 'newtm-news-table';
    }
    
    public function get_title() {
        return __('ข่าว Table', 'newtm');
    }
    
    public function get_icon() {
        return 'eicon-table';
    }
    
    public function get_categories() {
        return ['newtm'];
    }
    
    public function get_keywords() {
        return ['news', 'ข่าว', 'table', 'list', 'ตาราง', 'newtm'];
    }
    
    /**
     * Script dependencies
     */
    public function get_script_depends() {
        return [];
    }
    
    /**
     * Style dependencies
     */
    public function get_style_depends() {
        return [];
    }
    
    /**
     * Show in panel
     */
    public function show_in_panel() {
        return true;
    }
    
    protected function register_controls() {
        
        // ตั้งค่าเนื้อหา
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('ตั้งค่าเนื้อหา', 'newtm'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'posts_per_page',
            [
                'label' => __('จำนวนข่าวที่แสดง', 'newtm'),
                'type' => Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'max' => 100,
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => __('เรียงลำดับตาม', 'newtm'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('วันที่', 'newtm'),
                    'title' => __('ชื่อเรื่อง', 'newtm'),
                    'menu_order' => __('ลำดับที่กำหนด', 'newtm'),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => __('ลำดับ', 'newtm'),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => __('เก่า → ใหม่', 'newtm'),
                    'DESC' => __('ใหม่ → เก่า', 'newtm'),
                ],
            ]
        );
        
        // เลือกหมวดหมู่
        $categories = get_terms([
            'taxonomy' => 'newtm_category',
            'hide_empty' => false,
        ]);
        
        $category_options = ['' => __('ทุกหมวดหมู่', 'newtm')];
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat) {
                $category_options[$cat->term_id] = $cat->name;
            }
        }
        
        $this->add_control(
            'category',
            [
                'label' => __('กรองตามหมวดหมู่', 'newtm'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $category_options,
            ]
        );
        
        $this->add_control(
            'show_thumbnail',
            [
                'label' => __('แสดงรูปภาพ', 'newtm'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('แสดง', 'newtm'),
                'label_off' => __('ซ่อน', 'newtm'),
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_category',
            [
                'label' => __('แสดงหมวดหมู่', 'newtm'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('แสดง', 'newtm'),
                'label_off' => __('ซ่อน', 'newtm'),
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_new_badge',
            [
                'label' => __('แสดงป้าย NEW', 'newtm'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('แสดง', 'newtm'),
                'label_off' => __('ซ่อน', 'newtm'),
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'new_badge_days',
            [
                'label' => __('ป้าย NEW แสดงกี่วัน', 'newtm'),
                'type' => Controls_Manager::NUMBER,
                'default' => 7,
                'min' => 1,
                'max' => 30,
                'condition' => [
                    'show_new_badge' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // ตั้งค่ารูปแบบตาราง
        $this->start_controls_section(
            'table_style_section',
            [
                'label' => __('รูปแบบตาราง', 'newtm'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'table_header_bg',
            [
                'label' => __('สีพื้นหลังหัวตาราง', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f5f5f5',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'table_header_color',
            [
                'label' => __('สีข้อความหัวตาราง', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'table_row_bg',
            [
                'label' => __('สีพื้นหลังแถว', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table tbody tr' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'table_row_hover_bg',
            [
                'label' => __('สีพื้นหลังเมื่อ hover', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f9f9f9',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table tbody tr:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'selector' => '{{WRAPPER}} .newtm-table, {{WRAPPER}} .newtm-table th, {{WRAPPER}} .newtm-table td',
            ]
        );
        
        $this->end_controls_section();
        
        // ตั้งค่าข้อความ
        $this->start_controls_section(
            'typography_section',
            [
                'label' => __('รูปแบบข้อความ', 'newtm'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __('สีหัวข้อ', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#0066cc',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table .newtm-news-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'title_hover_color',
            [
                'label' => __('สีหัวข้อเมื่อ hover', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#004499',
                'selectors' => [
                    '{{WRAPPER}} .newtm-table .newtm-news-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('ฟอนต์หัวข้อ', 'newtm'),
                'selector' => '{{WRAPPER}} .newtm-table .newtm-news-title',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $args = [
            'post_type' => 'newtm_news',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];
        
        if (!empty($settings['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'newtm_category',
                    'field' => 'term_id',
                    'terms' => $settings['category'],
                ],
            ];
        }
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) :
            ?>
            <div class="newtm-table-wrapper">
                <table class="newtm-table newtm-news-table">
                    <thead>
                        <tr>
                            <?php if ('yes' === $settings['show_thumbnail']) : ?>
                                <th class="newtm-col-thumbnail"><?php _e('รูปภาพ', 'newtm'); ?></th>
                            <?php endif; ?>
                            <th class="newtm-col-title"><?php _e('หัวข้อข่าว', 'newtm'); ?></th>
                            <?php if ('yes' === $settings['show_category']) : ?>
                                <th class="newtm-col-category"><?php _e('หมวดหมู่', 'newtm'); ?></th>
                            <?php endif; ?>
                            <th class="newtm-col-date"><?php _e('วันที่', 'newtm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($query->have_posts()) : 
                            $query->the_post();
                            
                            // ตรวจสอบว่าเป็นข่าวใหม่หรือไม่
                            $is_new = false;
                            if ('yes' === $settings['show_new_badge']) {
                                $post_date = get_the_date('U');
                                $days_old = floor((time() - $post_date) / (60 * 60 * 24));
                                $is_new = ($days_old <= $settings['new_badge_days']);
                            }
                            ?>
                            <tr>
                                <?php if ('yes' === $settings['show_thumbnail']) : ?>
                                    <td class="newtm-col-thumbnail">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail('thumbnail'); ?>
                                            </a>
                                        <?php else : ?>
                                            <div class="newtm-no-image">
                                                <i class="fa fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                
                                <td class="newtm-col-title">
                                    <div class="newtm-news-title">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                            <?php if ($is_new) : ?>
                                                <span class="newtm-new-badge">NEW</span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </td>
                                
                                <?php if ('yes' === $settings['show_category']) : ?>
                                    <td class="newtm-col-category">
                                        <?php
                                        $terms = get_the_terms(get_the_ID(), 'newtm_category');
                                        if ($terms && !is_wp_error($terms)) {
                                            $term_names = array();
                                            foreach ($terms as $term) {
                                                $term_names[] = $term->name;
                                            }
                                            echo esc_html(implode(', ', $term_names));
                                        }
                                        ?>
                                    </td>
                                <?php endif; ?>
                                
                                <td class="newtm-col-date">
                                    <?php echo get_the_date(); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php
            wp_reset_postdata();
        else :
            ?>
            <div class="newtm-no-news">
                <p><?php _e('ไม่มีข่าวสารในขณะนี้', 'newtm'); ?></p>
            </div>
            <?php
        endif;
    }
}
