<?php
/**
 * Elementor Widget: แสดงข่าวแบบ Grid
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
use Elementor\Group_Control_Box_Shadow;

/**
 * Widget สำหรับแสดงข่าวในรูปแบบ Grid
 */
class NEWTM_Widget_News_Grid extends Widget_Base {
    
    /**
     * ชื่อของ widget
     */
    public function get_name() {
        return 'newtm-news-grid';
    }
    
    /**
     * ชื่อที่แสดงใน Elementor
     */
    public function get_title() {
        return __('ข่าว Grid', 'newtm');
    }
    
    /**
     * ไอคอนของ widget
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }
    
    /**
     * หมวดหมู่ของ widget
     */
    public function get_categories() {
        return ['newtm'];
    }
    
    /**
     * คำค้นหาสำหรับ widget
     */
    public function get_keywords() {
        return ['news', 'ข่าว', 'grid', 'กริด', 'newtm'];
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
    
    /**
     * ลงทะเบียน controls
     */
    protected function register_controls() {
        
        // ส่วนตั้งค่าเนื้อหา
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
                'default' => 6,
                'min' => 1,
                'max' => 50,
            ]
        );
        
        $this->add_control(
            'columns',
            [
                'label' => __('จำนวนคอลัมน์', 'newtm'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => __('1 คอลัมน์', 'newtm'),
                    '2' => __('2 คอลัมน์', 'newtm'),
                    '3' => __('3 คอลัมน์', 'newtm'),
                    '4' => __('4 คอลัมน์', 'newtm'),
                    '6' => __('6 คอลัมน์', 'newtm'),
                ],
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
                    'rand' => __('สุ่ม', 'newtm'),
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
                    'ASC' => __('จากเก่าไปใหม่', 'newtm'),
                    'DESC' => __('จากใหม่ไปเก่า', 'newtm'),
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
            'show_excerpt',
            [
                'label' => __('แสดงเนื้อหาย่อ', 'newtm'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('แสดง', 'newtm'),
                'label_off' => __('ซ่อน', 'newtm'),
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'excerpt_length',
            [
                'label' => __('ความยาวเนื้อหาย่อ', 'newtm'),
                'type' => Controls_Manager::NUMBER,
                'default' => 120,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'show_date',
            [
                'label' => __('แสดงวันที่', 'newtm'),
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
        
        $this->end_controls_section();
        
        // ส่วนตั้งค่ารูปแบบ
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('รูปแบบการแสดงผล', 'newtm'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'card_bg_color',
            [
                'label' => __('สีพื้นหลังการ์ด', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .newtm-news-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .newtm-news-card',
            ]
        );
        
        $this->add_control(
            'card_border_radius',
            [
                'label' => __('มุมโค้ง', 'newtm'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .newtm-news-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .newtm-news-card',
            ]
        );
        
        $this->add_control(
            'card_padding',
            [
                'label' => __('ระยะห่างภายใน', 'newtm'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .newtm-news-card-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // ส่วนตั้งค่าข้อความ
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
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .newtm-news-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('ฟอนต์หัวข้อ', 'newtm'),
                'selector' => '{{WRAPPER}} .newtm-news-title',
            ]
        );
        
        $this->add_control(
            'excerpt_color',
            [
                'label' => __('สีเนื้อหาย่อ', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .newtm-news-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'label' => __('ฟอนต์เนื้อหาย่อ', 'newtm'),
                'selector' => '{{WRAPPER}} .newtm-news-excerpt',
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * แสดงผล widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // สร้าง query arguments
        $args = [
            'post_type' => 'newtm_news',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];
        
        // กรองตามหมวดหมู่
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
            <div class="newtm-news-grid newtm-columns-<?php echo esc_attr($settings['columns']); ?>">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="newtm-news-item">
                        <div class="newtm-news-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="newtm-news-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="newtm-news-card-content">
                                <?php if ('yes' === $settings['show_category']) : ?>
                                    <div class="newtm-news-meta">
                                        <?php
                                        $terms = get_the_terms(get_the_ID(), 'newtm_category');
                                        if ($terms && !is_wp_error($terms)) {
                                            foreach ($terms as $term) {
                                                echo '<span class="newtm-category-badge">' . esc_html($term->name) . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <h3 class="newtm-news-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                
                                <?php if ('yes' === $settings['show_date']) : ?>
                                    <div class="newtm-news-date">
                                        <i class="fa fa-calendar"></i>
                                        <?php echo get_the_date(); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ('yes' === $settings['show_excerpt']) : ?>
                                    <div class="newtm-news-excerpt">
                                        <?php
                                        $excerpt = get_the_excerpt();
                                        $length = $settings['excerpt_length'];
                                        if (function_exists('mb_strimwidth')) {
                                            echo mb_strimwidth($excerpt, 0, $length, '...', 'UTF-8');
                                        } else {
                                            echo esc_html(wp_trim_words($excerpt, 20, '...'));
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="newtm-read-more">
                                    <?php _e('อ่านต่อ', 'newtm'); ?> →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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
