<?php
/**
 * Elementor Widget: แสดงข่าวตามหมวดหมู่แบบแท็บ
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

/**
 * Widget สำหรับแสดงข่าวแบบแยกตามหมวดหมู่
 */
class NEWTM_Widget_Category_Tabs extends Widget_Base {
    
    public function get_name() {
        return 'newtm-category-tabs';
    }
    
    public function get_title() {
        return __('ข่าวแยกหมวดหมู่ (Tabs)', 'newtm');
    }
    
    public function get_icon() {
        return 'eicon-tabs';
    }
    
    public function get_categories() {
        return ['newtm'];
    }
    
    public function get_keywords() {
        return ['news', 'ข่าว', 'category', 'หมวดหมู่', 'tabs', 'แท็บ', 'newtm'];
    }
    
    /**
     * Script dependencies
     */
    public function get_script_depends() {
        return ['jquery'];
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
        
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('ตั้งค่าเนื้อหา', 'newtm'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'posts_per_tab',
            [
                'label' => __('จำนวนข่าวต่อหมวดหมู่', 'newtm'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 20,
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
        
        $this->add_control(
            'show_all_tab',
            [
                'label' => __('แสดงแท็บ "ทั้งหมด"', 'newtm'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('แสดง', 'newtm'),
                'label_off' => __('ซ่อน', 'newtm'),
                'default' => 'yes',
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
        
        $this->end_controls_section();
        
        // ตั้งค่ารูปแบบแท็บ
        $this->start_controls_section(
            'tab_style_section',
            [
                'label' => __('รูปแบบแท็บ', 'newtm'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'tab_bg_color',
            [
                'label' => __('สีพื้นหลังแท็บ', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f5f5f5',
                'selectors' => [
                    '{{WRAPPER}} .newtm-tab-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'tab_active_bg_color',
            [
                'label' => __('สีพื้นหลังแท็บที่เลือก', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#0066cc',
                'selectors' => [
                    '{{WRAPPER}} .newtm-tab-button.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'tab_text_color',
            [
                'label' => __('สีข้อความแท็บ', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .newtm-tab-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'tab_active_text_color',
            [
                'label' => __('สีข้อความแท็บที่เลือก', 'newtm'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .newtm-tab-button.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tab_typography',
                'label' => __('ฟอนต์แท็บ', 'newtm'),
                'selector' => '{{WRAPPER}} .newtm-tab-button',
            ]
        );
        
        $this->end_controls_section();
        
        // ตั้งค่ารูปแบบเนื้อหา
        $this->start_controls_section(
            'content_style_section',
            [
                'label' => __('รูปแบบเนื้อหา', 'newtm'),
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
                    '{{WRAPPER}} .newtm-tab-content .newtm-news-item-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('ฟอนต์หัวข้อ', 'newtm'),
                'selector' => '{{WRAPPER}} .newtm-tab-content .newtm-news-item-title',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // ดึงหมวดหมู่ทั้งหมด
        $categories = get_terms([
            'taxonomy' => 'newtm_category',
            'hide_empty' => true,
        ]);
        
        if (empty($categories) || is_wp_error($categories)) {
            echo '<div class="newtm-no-categories"><p>' . __('ยังไม่มีหมวดหมู่ข่าว', 'newtm') . '</p></div>';
            return;
        }
        
        $tab_id = 'newtm-tabs-' . $this->get_id();
        ?>
        <div class="newtm-category-tabs" id="<?php echo esc_attr($tab_id); ?>">
            <div class="newtm-tabs-nav">
                <?php if ('yes' === $settings['show_all_tab']) : ?>
                    <button class="newtm-tab-button active" data-tab="all">
                        <?php _e('ทั้งหมด', 'newtm'); ?>
                    </button>
                <?php endif; ?>
                
                <?php 
                $first_category = true;
                foreach ($categories as $category) : 
                    $active_class = ('yes' !== $settings['show_all_tab'] && $first_category) ? ' active' : '';
                    $first_category = false;
                    ?>
                    <button class="newtm-tab-button<?php echo $active_class; ?>" data-tab="<?php echo esc_attr($category->term_id); ?>">
                        <?php echo esc_html($category->name); ?>
                        <span class="newtm-tab-count">(<?php echo $category->count; ?>)</span>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="newtm-tabs-content">
                <?php if ('yes' === $settings['show_all_tab']) : ?>
                    <div class="newtm-tab-content active" id="tab-all">
                        <?php $this->render_news_list(null, $settings); ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                $first_category = true;
                foreach ($categories as $category) : 
                    $active_class = ('yes' !== $settings['show_all_tab'] && $first_category) ? ' active' : '';
                    $first_category = false;
                    ?>
                    <div class="newtm-tab-content<?php echo $active_class; ?>" id="tab-<?php echo esc_attr($category->term_id); ?>">
                        <?php $this->render_news_list($category->term_id, $settings); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#<?php echo esc_js($tab_id); ?> .newtm-tab-button').on('click', function() {
                var tabId = $(this).data('tab');
                var $wrapper = $(this).closest('.newtm-category-tabs');
                
                // อัปเดตปุ่มแท็บ
                $wrapper.find('.newtm-tab-button').removeClass('active');
                $(this).addClass('active');
                
                // อัปเดตเนื้อหา
                $wrapper.find('.newtm-tab-content').removeClass('active');
                $wrapper.find('#tab-' + tabId).addClass('active');
            });
        });
        </script>
        <?php
    }
    
    /**
     * แสดงรายการข่าวตามหมวดหมู่
     */
    private function render_news_list($category_id, $settings) {
        $args = [
            'post_type' => 'newtm_news',
            'posts_per_page' => $settings['posts_per_tab'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];
        
        if ($category_id) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'newtm_category',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ],
            ];
        }
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) :
            ?>
            <ul class="newtm-news-list">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <li class="newtm-news-item">
                        <a href="<?php the_permalink(); ?>">
                            <span class="newtm-news-item-title">
                                <?php the_title(); ?>
                            </span>
                            <?php if ('yes' === $settings['show_date']) : ?>
                                <span class="newtm-news-item-date">
                                    <?php echo get_the_date(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
            <?php
            wp_reset_postdata();
        else :
            ?>
            <p class="newtm-no-news"><?php _e('ไม่มีข่าวในหมวดหมู่นี้', 'newtm'); ?></p>
            <?php
        endif;
    }
}
