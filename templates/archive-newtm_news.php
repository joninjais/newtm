<?php
/**
 * Template สำหรับหน้ารวมข่าวและหมวดหมู่
 */

get_header();

// ---- ตัวแปรพื้นฐาน ----
$is_archive   = is_post_type_archive( 'newtm_news' );
$is_tax       = is_tax( 'newtm_category' );
$queried_term = $is_tax ? get_queried_object() : null;

// ---- ค่าการค้นหา / กรอง ----
$search_query = isset( $_GET['newtm_s'] ) ? sanitize_text_field( wp_unslash( $_GET['newtm_s'] ) ) : '';
$filter_cat   = isset( $_GET['newtm_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['newtm_cat'] ) ) : '';

// ---- หมวดหมู่ทั้งหมดสำหรับ dropdown filter ----
$all_categories = get_terms( array(
    'taxonomy'   => 'newtm_category',
    'hide_empty' => true,
) );

// ---- ตัดสินใจว่าใช้ query แบบไหน ----
// search/filter → custom query + param ?pg=N (หลีกเลี่ยง WordPress canonical redirect)
// ปกติ         → main $wp_query + get_pagenum_link() (ถูกต้องตาม permalink structure)
$use_custom_query = ( $is_archive && ( $search_query !== '' || $filter_cat !== '' ) );

if ( $use_custom_query ) {
    // ใช้ ?pg=N เพื่อหลีกเลี่ยง canonical redirect ของ WordPress
    $paged     = isset( $_GET['pg'] ) ? max( 1, intval( $_GET['pg'] ) ) : 1;
    $tax_query = array();
    if ( $filter_cat !== '' ) {
        $tax_query[] = array(
            'taxonomy' => 'newtm_category',
            'field'    => 'slug',
            'terms'    => array( $filter_cat ),
        );
    }
    $custom_query = new WP_Query( array(
        'post_type'      => 'newtm_news',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'paged'          => $paged,
        's'              => $search_query,
        'tax_query'      => $tax_query,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    $the_query = $custom_query;
} else {
    global $wp_query;
    $the_query = $wp_query;
    // get_query_var('paged') รองรับทั้ง pretty permalink (/page/2/) และ ?paged=2
    $paged = max( 1, get_query_var( 'paged' ) );
}

// ---- URL ฐานสำหรับ pagination ----
$base_url = ( $is_archive ) ? get_post_type_archive_link( 'newtm_news' ) : get_term_link( $queried_term );
$base_url = is_wp_error( $base_url ) ? home_url( '/index.php/news/' ) : $base_url;

// ---- ฟังก์ชัน generate URL ที่ถูกต้องตาม permalink structure ----
function newtm_get_page_url( $page, $base_url, $use_custom, $search, $cat ) {
    if ( $use_custom ) {
        // custom query: ใช้ query param ?pg=N พร้อม search/filter params
        $params = array();
        if ( $search !== '' ) $params['newtm_s'] = $search;
        if ( $cat !== '' )    $params['newtm_cat'] = $cat;
        if ( $page > 1 )      $params['pg'] = $page;
        return add_query_arg( $params, $base_url );
    } else {
        // main query: ใช้ get_pagenum_link() ซึ่ง generate URL ถูกต้องตาม permalink structure
        return get_pagenum_link( $page );
    }
}

// ---- ฟังก์ชัน pagination ----
function newtm_pagination( $current, $total, $base_url, $search, $cat, $use_custom ) {
    if ( $total <= 1 ) return;

    echo '<nav class="newtm-pagination" aria-label="หน้า">';
    echo '<ul class="newtm-page-list">';

    // ปุ่ม prev
    if ( $current > 1 ) {
        $url = newtm_get_page_url( $current - 1, $base_url, $use_custom, $search, $cat );
        echo '<li><a href="' . esc_url( $url ) . '" class="newtm-page-btn">‹ ก่อนหน้า</a></li>';
    }

    // ปุ่มเลขหน้า
    $range = 2;
    for ( $i = 1; $i <= $total; $i++ ) {
        if ( $i === 1 || $i === $total || abs( $i - $current ) <= $range ) {
            $url    = newtm_get_page_url( $i, $base_url, $use_custom, $search, $cat );
            $active = ( $i === $current ) ? ' active' : '';
            echo '<li><a href="' . esc_url( $url ) . '" class="newtm-page-btn' . $active . '">' . $i . '</a></li>';
        } elseif ( abs( $i - $current ) === $range + 1 ) {
            echo '<li><span class="newtm-page-dots">…</span></li>';
        }
    }

    // ปุ่ม next
    if ( $current < $total ) {
        $url = newtm_get_page_url( $current + 1, $base_url, $use_custom, $search, $cat );
        echo '<li><a href="' . esc_url( $url ) . '" class="newtm-page-btn">ถัดไป ›</a></li>';
    }

    echo '</ul></nav>';
}
?>

<style>
/* ─── NEWTM Archive Page ──────────────────────────────── */
:root {
    --na-primary:  #1E0842;
    --na-mid:      #3B1478;
    --na-accent:   #7C3AED;
    --na-pale:     #C4B5FD;
    --na-section:  #FAF7FF;
    --na-gold:     #D97706;
    --na-text:     #1C0A2E;
    --na-border:   #E8DEF8;
    --na-radius:   8px;
    --na-shadow:   0 2px 16px rgba(30,8,66,.08);
}

.newtm-archive-page {
    max-width: 860px;
    margin: 0 auto;
    padding: 32px 16px 60px;
}

/* ─── Header ─────────────────────────────────────── */
.newtm-archive-header {
    margin-bottom: 28px;
}

.newtm-archive-header h1 {
    font-size: 26px;
    font-weight: 700;
    color: var(--na-primary);
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.newtm-archive-header h1::before {
    content: '';
    display: inline-block;
    width: 5px;
    height: 28px;
    background: var(--na-accent);
    border-radius: 3px;
    flex-shrink: 0;
}

.newtm-archive-header .newtm-sub {
    color: #7c5cbf;
    font-size: 13px;
    margin-left: 15px;
}

.newtm-archive-desc {
    color: #6b5b8e;
    font-size: 14px;
    margin: 8px 0 0 15px;
}

/* ─── Search + Filter Bar ─────────────────────────── */
.newtm-filter-bar {
    background: var(--na-section);
    border: 1px solid var(--na-border);
    border-radius: var(--na-radius);
    padding: 14px 16px;
    margin-bottom: 24px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.newtm-filter-bar form {
    display: contents;
}

.newtm-search-input {
    flex: 1;
    min-width: 200px;
    padding: 8px 14px;
    border: 1px solid var(--na-border);
    border-radius: 6px;
    font-size: 14px;
    color: var(--na-text);
    background: #fff;
    outline: none;
    transition: border-color .2s;
}

.newtm-search-input:focus {
    border-color: var(--na-accent);
    box-shadow: 0 0 0 2px rgba(124,58,237,.15);
}

.newtm-filter-select {
    padding: 8px 14px;
    border: 1px solid var(--na-border);
    border-radius: 6px;
    font-size: 14px;
    color: var(--na-text);
    background: #fff;
    min-width: 160px;
    cursor: pointer;
    outline: none;
}

.newtm-filter-select:focus {
    border-color: var(--na-accent);
}

.newtm-search-btn {
    padding: 8px 20px;
    background: var(--na-accent);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}

.newtm-search-btn:hover {
    background: var(--na-mid);
}

.newtm-filter-clear {
    color: #999;
    font-size: 12px;
    text-decoration: none;
    padding: 4px 8px;
    border-radius: 4px;
    transition: color .2s;
    white-space: nowrap;
}

.newtm-filter-clear:hover {
    color: var(--na-accent);
}

/* ─── Result Info ─────────────────────────────────── */
.newtm-result-info {
    font-size: 13px;
    color: #7c5cbf;
    margin-bottom: 16px;
    padding: 0 2px;
}

.newtm-result-info strong {
    color: var(--na-accent);
}

/* ─── News List ───────────────────────────────────── */
.newtm-archive-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.newtm-archive-item {
    background: #fff;
    border-radius: var(--na-radius);
    border: 1px solid var(--na-border);
    border-left: 4px solid var(--na-accent);
    box-shadow: var(--na-shadow);
    transition: box-shadow .2s, border-left-color .2s;
    overflow: hidden;
}

.newtm-archive-item:hover {
    box-shadow: 0 6px 24px rgba(124,58,237,.14);
    border-left-color: var(--na-gold);
}

.newtm-archive-item-inner {
    display: flex;
    gap: 16px;
    padding: 16px 20px;
    align-items: flex-start;
}

/* Thumbnail */
.newtm-item-thumb {
    flex-shrink: 0;
    width: 96px;
    height: 96px;
    border-radius: 6px;
    overflow: hidden;
    background: var(--na-section);
}

.newtm-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.newtm-item-thumb-placeholder {
    flex-shrink: 0;
    width: 96px;
    height: 96px;
    border-radius: 6px;
    background: var(--na-section);
    display: flex;
    align-items: center;
    justify-content: center;
}

.newtm-item-thumb-placeholder svg {
    width: 40px;
    height: 40px;
    opacity: .35;
}

/* Content */
.newtm-item-content {
    flex: 1;
    min-width: 0;
}

.newtm-item-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}

.newtm-item-cat {
    background: #ede9fe;
    color: var(--na-mid);
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .3px;
    text-decoration: none;
    transition: background .15s;
}

.newtm-item-cat:hover {
    background: var(--na-pale);
}

.newtm-item-date {
    color: #a090c0;
    font-size: 12px;
}

.newtm-item-title {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.45;
    margin: 0 0 6px;
}

.newtm-item-title a {
    color: var(--na-primary);
    text-decoration: none;
    transition: color .15s;
}

.newtm-item-title a:hover {
    color: var(--na-accent);
}

.newtm-item-excerpt {
    color: #6b5b8e;
    font-size: 13px;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0 0 8px;
}

.newtm-item-more {
    font-size: 12px;
    color: var(--na-accent);
    font-weight: 600;
    text-decoration: none;
}

.newtm-item-more:hover {
    color: var(--na-mid);
    text-decoration: underline;
}

/* ─── No Posts ───────────────────────────────────── */
.newtm-no-posts {
    text-align: center;
    padding: 60px 20px;
    color: #9880c0;
    background: var(--na-section);
    border-radius: var(--na-radius);
    border: 1px dashed var(--na-border);
}

.newtm-no-posts p { font-size: 15px; margin: 8px 0; }
.newtm-no-posts a { color: var(--na-accent); }

/* ─── Pagination ─────────────────────────────────── */
.newtm-pagination {
    margin-top: 36px;
    display: flex;
    justify-content: center;
}

.newtm-page-list {
    display: flex;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
    justify-content: center;
}

.newtm-page-btn {
    display: inline-block;
    padding: 7px 14px;
    border: 1px solid var(--na-border);
    border-radius: 6px;
    color: var(--na-mid);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    background: #fff;
    transition: all .15s;
    min-width: 38px;
    text-align: center;
}

.newtm-page-btn:hover {
    background: var(--na-section);
    border-color: var(--na-accent);
    color: var(--na-accent);
}

.newtm-page-btn.active {
    background: var(--na-accent);
    border-color: var(--na-accent);
    color: #fff;
    pointer-events: none;
}

.newtm-page-dots {
    display: inline-block;
    padding: 7px 6px;
    color: #bbb;
    font-size: 13px;
}

/* ─── Responsive ─────────────────────────────────── */
@media (max-width: 600px) {
    .newtm-archive-item-inner { flex-direction: column; gap: 12px; }
    .newtm-item-thumb,
    .newtm-item-thumb-placeholder { width: 100%; height: 160px; }
    .newtm-archive-header h1 { font-size: 20px; }
    .newtm-filter-bar { flex-direction: column; }
    .newtm-search-input,
    .newtm-filter-select { min-width: 0; width: 100%; }
}
</style>

<div class="newtm-archive-page">

    <!-- Header -->
    <header class="newtm-archive-header">
        <?php if ( $is_archive ) : ?>
            <h1>ข่าวสารทั้งหมด</h1>
            <p class="newtm-sub">รวมข่าวสารจากทุกหมวดหมู่</p>
        <?php elseif ( $is_tax && $queried_term ) : ?>
            <h1><?php echo esc_html( $queried_term->name ); ?></h1>
            <?php if ( $queried_term->description ) : ?>
                <p class="newtm-archive-desc"><?php echo esc_html( $queried_term->description ); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </header>

    <!-- Search & Filter Bar (แสดงเฉพาะหน้า archive หลัก) -->
    <?php if ( $is_archive ) : ?>
    <div class="newtm-filter-bar">
        <form method="GET" action="<?php echo esc_url( $base_url ); ?>" style="display:contents;">
            <input
                type="text"
                name="newtm_s"
                class="newtm-search-input"
                placeholder="ค้นหาข่าว..."
                value="<?php echo esc_attr( $search_query ); ?>"
                autocomplete="off"
            >
            <?php if ( ! is_wp_error( $all_categories ) && ! empty( $all_categories ) ) : ?>
            <select name="newtm_cat" class="newtm-filter-select">
                <option value="">หมวดหมู่ทั้งหมด</option>
                <?php foreach ( $all_categories as $cat ) : ?>
                    <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $filter_cat, $cat->slug ); ?>>
                        <?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button type="submit" class="newtm-search-btn">🔍 ค้นหา</button>
            <?php if ( $search_query !== '' || $filter_cat !== '' ) : ?>
                <a href="<?php echo esc_url( $base_url ); ?>" class="newtm-filter-clear">✕ ล้างตัวกรอง</a>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>

    <!-- Result Info -->
    <?php
    $total_posts = $the_query->found_posts;
    $total_pages = $the_query->max_num_pages;
    if ( $search_query !== '' || $filter_cat !== '' ) :
    ?>
    <p class="newtm-result-info">
        พบ <strong><?php echo esc_html( $total_posts ); ?></strong> รายการ
        <?php if ( $search_query !== '' ) : ?>
            สำหรับ "<strong><?php echo esc_html( $search_query ); ?></strong>"
        <?php endif; ?>
        <?php if ( $filter_cat !== '' && ! is_wp_error( $all_categories ) ) :
            foreach ( $all_categories as $c ) {
                if ( $c->slug === $filter_cat ) {
                    echo ' ในหมวด <strong>' . esc_html( $c->name ) . '</strong>';
                    break;
                }
            }
        endif; ?>
    </p>
    <?php endif; ?>

    <!-- News List -->
    <?php if ( $the_query->have_posts() ) : ?>

        <div class="newtm-archive-list">
        <?php
        $thai_months = [ 1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.' ];
        while ( $the_query->have_posts() ) :
            $the_query->the_post();
            $post_id   = get_the_ID();
            $day       = (int) get_the_date( 'j', $post_id );
            $month     = (int) get_the_date( 'n', $post_id );
            $year      = (int) get_the_date( 'Y', $post_id );
            $thai_date = $day . ' ' . ( $thai_months[ $month ] ?? '' ) . ' ' . ( $year + 543 );
            $post_cats = get_the_terms( $post_id, 'newtm_category' );
        ?>
            <article class="newtm-archive-item">
                <div class="newtm-archive-item-inner">
                    <!-- Thumbnail -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="newtm-item-thumb">
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
                        </div>
                    <?php else : ?>
                        <div class="newtm-item-thumb-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <!-- Content -->
                    <div class="newtm-item-content">
                        <div class="newtm-item-meta">
                            <?php if ( $post_cats && ! is_wp_error( $post_cats ) ) : ?>
                                <?php foreach ( $post_cats as $pc ) : ?>
                                    <a href="<?php echo esc_url( get_term_link( $pc ) ); ?>" class="newtm-item-cat"><?php echo esc_html( $pc->name ); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <span class="newtm-item-date"><?php echo esc_html( $thai_date ); ?></span>
                        </div>

                        <h2 class="newtm-item-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <p class="newtm-item-excerpt">
                            <?php
                            if ( has_excerpt() ) {
                                echo esc_html( get_the_excerpt() );
                            } else {
                                echo esc_html( wp_trim_words( get_the_content(), 28, '...' ) );
                            }
                            ?>
                        </p>

                        <a href="<?php the_permalink(); ?>" class="newtm-item-more">อ่านเพิ่มเติม →</a>
                    </div>
                </div>
            </article>
        <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Pagination -->
        <?php newtm_pagination( $paged, $total_pages, $base_url, $search_query, $filter_cat, $use_custom_query ); ?>

    <?php else : ?>

        <div class="newtm-no-posts">
            <?php if ( $search_query !== '' || $filter_cat !== '' ) : ?>
                <p>🔍 ไม่พบข่าวที่ตรงกับเงื่อนไขที่ค้นหา</p>
                <p><a href="<?php echo esc_url( $base_url ); ?>">← ดูข่าวทั้งหมด</a></p>
            <?php else : ?>
                <p>ยังไม่มีข่าวสารในขณะนี้</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
