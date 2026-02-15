<?php
/**
 * Template สำหรับหน้ารวมข่าวและหมวดหมู่
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <header class="page-header">
            <?php
            if (is_post_type_archive('newtm_news')) {
                echo '<h1 class="page-title">ข่าวสารทั้งหมด</h1>';
            } elseif (is_tax('newtm_category')) {
                $term = get_queried_object();
                echo '<h1 class="page-title">' . esc_html($term->name) . '</h1>';
                if ($term->description) {
                    echo '<div class="archive-description">' . esc_html($term->description) . '</div>';
                }
            }
            ?>
        </header>
        
        <?php if (have_posts()) : ?>
            
            <div class="newtm-archive-list">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('newtm-archive-list-item'); ?>>
                        
                        <div class="list-item-wrapper">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="list-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="list-thumbnail-placeholder">
                                    <a href="<?php the_permalink(); ?>">
                                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="100" height="100" fill="#e5e7eb"/>
                                            <path d="M30 40 L50 20 L70 40 M50 20 L50 80" stroke="#9ca3af" stroke-width="2" fill="none"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="list-content">
                                <div class="list-meta">
                                    <?php
                                    $categories = get_the_terms(get_the_ID(), 'newtm_category');
                                    if ($categories && !is_wp_error($categories)) {
                                        foreach ($categories as $cat) {
                                            echo '<span class="list-category">' . esc_html($cat->name) . '</span>';
                                        }
                                    }
                                    ?>
                                    <span class="list-date"><?php 
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
                                    echo ' | โดย ';
                                    the_author();
                                    //
                                    // echo get_the_date('j M Y');
                                     ?></span>
                                </div>
                                
                                <h2 class="list-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <?php if (has_excerpt()) : ?>
                                    <div class="list-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="list-excerpt">
                                        <?php echo wp_trim_words(get_the_content(), 25); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="list-read-more">
                                    อ่านเพิ่มเติม →
                                </a>
                            </div>
                        </div>
                        
                    </article>
                    
                <?php endwhile; ?>
            </div>
            
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => '← ก่อนหน้า',
                'next_text' => 'ถัดไป →',
            ));
            ?>
            
        <?php else : ?>
            
            <p class="no-posts-found">ไม่พบข่าวสาร</p>
            
        <?php endif; ?>
        
    </main>
</div>

<style>
.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 10px 0;
}

.archive-description {
    color: #6b7280;
    font-size: 16px;
}

.newtm-archive-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin: 40px 0;
}

.newtm-archive-list-item {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #311B92;
    transition: all 0.3s ease;
}

.newtm-archive-list-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-left-color: #311B92;
}

.list-item-wrapper {
    display: flex;
    gap: 20px;
    padding: 20px;
    align-items: stretch;
}

.list-thumbnail {
    flex-shrink: 0;
    width: 120px;
    height: 120px;
    border-radius: 6px;
    overflow: hidden;
    background: #f3f4f6;
}

.list-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.list-thumbnail-placeholder {
    flex-shrink: 0;
    width: 120px;
    height: 120px;
    border-radius: 6px;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
}

.list-thumbnail-placeholder svg {
    width: 60%;
    height: 60%;
    opacity: 0.5;
}

.list-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.list-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 10px;
    font-size: 12px;
    flex-wrap: wrap;
}

.list-category {
    background: #dbeafe;
    color: #311B92;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
}

.list-date {
    color: #9ca3af;
    font-weight: 500;
}

.list-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 8px 0;
    line-height: 1.4;
}

.list-title a {
    color: #1f2937;
    text-decoration: none;
}

.list-title a:hover {
    color: #311B92;
}

.list-excerpt {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.list-read-more {
    color: #311B92;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    align-self: flex-start;
}

.list-read-more:hover {
    color: #311B92;
    text-decoration: underline;
}

.no-posts-found {
    text-align: center;
    color: #6b7280;
    font-style: italic;
    padding: 40px;
}

@media (max-width: 768px) {
    .list-item-wrapper {
        flex-direction: column;
        gap: 15px;
    }
    
    .list-thumbnail,
    .list-thumbnail-placeholder {
        width: 100%;
        height: 180px;
    }
    
    .page-title {
        font-size: 24px;
    }
    
    .list-title {
        font-size: 16px;
    }
}
</style>

<?php get_footer(); ?>
