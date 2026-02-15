<?php
/**
 * Template สำหรับหน้าข่าวเดี่ยว
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('newtm-single-news'); ?>>
                
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    
                    <div class="entry-meta">
                        <?php
                        $categories = get_the_terms(get_the_ID(), 'newtm_category');
                        if ($categories && !is_wp_error($categories)) {
                            echo '<span class="newtm-category-badge">';
                            foreach ($categories as $category) {
                                echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a> ';
                            }
                            echo '</span>';
                        }
                        ?>
                        <span class="posted-on">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php 
                                $day = get_the_date('j');
                                $month = get_the_date('n');
                                $year = get_the_date('Y');
                                $thai_year = $year + 543;
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
                                echo esc_html($day . ' ' . $thai_months[$month] . ' ' . $thai_year);
                                //  
                                // echo get_the_date('j M Y'); 
                                //ผู้เขียน: 
                                echo ' | โดย ';
                                the_author(); 
                                ?>
                            </time>
                        </span>
                    </div>
                </header>
                
                <!-- <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?> -->
                
                <div class="entry-content">
                    <?php
                    the_content();
                    
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'newtm'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>
                
                <?php
                $tags = get_the_terms(get_the_ID(), 'newtm_tag');
                if ($tags && !is_wp_error($tags)) :
                ?>
                    <footer class="entry-footer">
                        <div class="tags-links">
                            <strong>แท็ก:</strong>
                            <?php
                            foreach ($tags as $tag) {
                                echo '<a href="' . esc_url(get_term_link($tag)) . '" class="newtm-tag">' . esc_html($tag->name) . '</a> ';
                            }
                            ?>
                        </div>
                    </footer>
                <?php endif; ?>
                
            </article>
            
        <?php endwhile; ?>
        
    </main>
</div>

<style>
.newtm-single-news {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.newtm-single-news .entry-header {
    margin-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.newtm-single-news .entry-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 15px 0;
    line-height: 1.3;
}

.newtm-single-news .entry-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 14px;
    color: #6b7280;
}

.newtm-category-badge a {
    background: #311B92;
    color: #fff;
    padding: 4px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.newtm-category-badge a:hover {
    background: #311B92;
}

.newtm-single-news .post-thumbnail {
    margin: 20px 0;
    border-radius: 8px;
    overflow: hidden;
}

.newtm-single-news .post-thumbnail img {
    width: 100%;
    height: auto;
    display: block;
}

.newtm-single-news .entry-content {
    font-size: 18px;
    line-height: 1.8;
    color: #374151;
    margin: 30px 0;
}

.newtm-single-news .entry-content p {
    margin-bottom: 20px;
}

.newtm-single-news .entry-content h2,
.newtm-single-news .entry-content h3 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #1f2937;
}

.newtm-single-news .entry-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.newtm-tag {
    display: inline-block;
    background: #f3f4f6;
    color: #374151;
    padding: 4px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    margin: 5px 5px 5px 0;
}

.newtm-tag:hover {
    background: #e5e7eb;
}

@media (max-width: 768px) {
    .newtm-single-news {
        margin: 20px;
        padding: 15px;
    }
    
    .newtm-single-news .entry-title {
        font-size: 24px;
    }
    
    .newtm-single-news .entry-content {
        font-size: 16px;
    }
}
</style>

<?php get_footer(); ?>
