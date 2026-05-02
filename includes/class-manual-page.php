<?php
/**
 * หน้าคู่มือการใช้งาน NewTM Plugin
 *
 * @package NewTM
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NEWTM_Manual_Page {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_manual_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    public function add_manual_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        add_submenu_page(
            'edit.php?post_type=newtm_news',
            'คู่มือการใช้งาน NewTM',
            'คู่มือการใช้งาน',
            'edit_posts',
            'newtm-manual',
            array( $this, 'render_manual_page' )
        );
    }

    public function enqueue_styles( $hook ) {
        if ( false === strpos( $hook, 'newtm-manual' ) ) {
            return;
        }
        // Inline styles สำหรับหน้าคู่มือ
        $css = '
        .newtm-manual-wrap { max-width: 900px; }
        .newtm-manual-wrap h1 { font-size: 24px; margin-bottom: 8px; }
        .newtm-manual-wrap .newtm-version { color: #666; font-size: 13px; margin-bottom: 24px; }
        .newtm-tabs { display: flex; gap: 4px; margin-bottom: 0; border-bottom: 2px solid #7C3AED; flex-wrap: wrap; }
        .newtm-tab-btn {
            padding: 9px 18px; cursor: pointer; background: #f0f0f1; border: 1px solid #c3c4c7;
            border-bottom: none; border-radius: 4px 4px 0 0; font-size: 13px; font-weight: 500;
            color: #50575e; transition: background .15s;
        }
        .newtm-tab-btn:hover { background: #e0d0ff; color: #3b1478; }
        .newtm-tab-btn.active { background: #7C3AED; color: #fff; border-color: #7C3AED; }
        .newtm-tab-content { display: none; background: #fff; border: 1px solid #c3c4c7; border-top: none; padding: 24px; border-radius: 0 0 4px 4px; }
        .newtm-tab-content.active { display: block; }
        .newtm-manual-wrap h2 { font-size: 18px; color: #3b1478; margin-top: 24px; margin-bottom: 10px; border-bottom: 1px solid #e0d0ff; padding-bottom: 6px; }
        .newtm-manual-wrap h3 { font-size: 15px; color: #1E0842; margin-top: 18px; margin-bottom: 8px; }
        .newtm-manual-wrap table { border-collapse: collapse; width: 100%; margin-bottom: 16px; font-size: 13px; }
        .newtm-manual-wrap table th { background: #7C3AED; color: #fff; padding: 8px 12px; text-align: left; }
        .newtm-manual-wrap table td { padding: 7px 12px; border-bottom: 1px solid #f0f0f1; vertical-align: top; }
        .newtm-manual-wrap table tr:nth-child(even) td { background: #faf7ff; }
        .newtm-code { background: #1E0842; color: #C4B5FD; padding: 12px 16px; border-radius: 4px; font-family: monospace; font-size: 13px; overflow-x: auto; margin: 8px 0 16px; white-space: pre; }
        .newtm-inline-code { background: #f0ebff; color: #5b21b6; padding: 1px 5px; border-radius: 3px; font-family: monospace; font-size: 12px; }
        .newtm-note { background: #fef3c7; border-left: 4px solid #d97706; padding: 10px 14px; border-radius: 0 4px 4px 0; margin: 12px 0; font-size: 13px; }
        .newtm-tip { background: #ecfdf5; border-left: 4px solid #10b981; padding: 10px 14px; border-radius: 0 4px 4px 0; margin: 12px 0; font-size: 13px; }
        .newtm-steps { counter-reset: step; list-style: none; padding: 0; }
        .newtm-steps li { counter-increment: step; display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px; font-size: 13px; }
        .newtm-steps li::before { content: counter(step); background: #7C3AED; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0; margin-top: 1px; }
        .newtm-badge { display: inline-block; background: #7C3AED; color: #fff; font-size: 11px; padding: 2px 7px; border-radius: 10px; margin-left: 4px; vertical-align: middle; }
        .newtm-badge.green { background: #10b981; }
        .newtm-badge.orange { background: #d97706; }
        ';
        wp_add_inline_style( 'wp-admin', $css );
    }

    public function render_manual_page() {
        $categories = get_terms( array(
            'taxonomy'   => 'newtm_category',
            'hide_empty' => false,
            'number'     => 10,
        ) );
        $first_cat_slug = ( ! is_wp_error( $categories ) && ! empty( $categories ) ) ? urldecode( $categories[0]->slug ) : 'ข่าวประชาสัมพันธ์';

        $settings_url = admin_url( 'edit.php?post_type=newtm_news&page=newtm-settings' );
        $add_news_url = admin_url( 'post-new.php?post_type=newtm_news' );
        $category_url = admin_url( 'edit-tags.php?taxonomy=newtm_category&post_type=newtm_news' );
        $debug_url    = admin_url( 'edit.php?post_type=newtm_news&page=newtm-debug' );
        ?>
        <div class="wrap newtm-manual-wrap">
            <h1>📖 คู่มือการใช้งาน NewTM</h1>
            <p class="newtm-version">News Table System | Version <?php echo esc_html( NEWTM_VERSION ); ?> | ระบบจัดการข่าวสำหรับหน่วยงานราชการ</p>

            <div class="newtm-tabs" id="newtm-tabs">
                <button class="newtm-tab-btn active" data-tab="quickstart">🚀 เริ่มต้นใช้งาน</button>
                <button class="newtm-tab-btn" data-tab="shortcode">📋 Shortcode</button>
                <button class="newtm-tab-btn" data-tab="category">🗂️ หมวดหมู่</button>
                <button class="newtm-tab-btn" data-tab="settings">⚙️ ตั้งค่า</button>
                <button class="newtm-tab-btn" data-tab="elementor">🧩 Elementor</button>
                <button class="newtm-tab-btn" data-tab="faq">❓ FAQ</button>
            </div>

            <!-- TAB: เริ่มต้นใช้งาน -->
            <div class="newtm-tab-content active" id="tab-quickstart">
                <h2>ขั้นตอนการเริ่มต้น</h2>
                <ol class="newtm-steps">
                    <li>
                        <div>
                            <strong>สร้างหมวดหมู่ข่าว</strong><br>
                            ไปที่ <a href="<?php echo esc_url( $category_url ); ?>">ข่าวสาร → หมวดหมู่</a> แล้วเพิ่มหมวดหมู่ที่ต้องการ เช่น "ข่าวประชาสัมพันธ์", "จัดซื้อจัดจ้าง", "รับสมัครงาน"<br>
                            <span class="newtm-note">จด <strong>Slug</strong> ของแต่ละหมวดหมู่ไว้ เพราะต้องใช้ใน Shortcode</span>
                        </div>
                    </li>
                    <li>
                        <div>
                            <strong>เพิ่มข่าว</strong><br>
                            ไปที่ <a href="<?php echo esc_url( $add_news_url ); ?>">ข่าวสาร → เพิ่มข่าวใหม่</a> กรอกหัวข้อ เนื้อหา เลือกหมวดหมู่ แล้ว Publish
                        </div>
                    </li>
                    <li>
                        <div>
                            <strong>นำ Shortcode ไปวางในหน้า</strong><br>
                            เปิดหน้าที่ต้องการแสดงข่าว แล้ววาง Shortcode:
                            <div class="newtm-code">[newtm_table category="<?php echo esc_html( $first_cat_slug ); ?>"]</div>
                            หรือแสดงทุกหมวดพร้อมกัน:
                            <div class="newtm-code">[newtm_all_categories limit="5"]</div>
                        </div>
                    </li>
                    <li>
                        <div>
                            <strong>ปรับการตั้งค่า (ถ้าต้องการ)</strong><br>
                            ไปที่ <a href="<?php echo esc_url( $settings_url ); ?>">ข่าวสาร → ตั้งค่า</a> เพื่อกำหนดจำนวนข่าวและความยาวหัวข้อแบบ Global
                        </div>
                    </li>
                </ol>

                <h2>โครงสร้างเมนู Admin</h2>
                <table>
                    <tr><th>เมนู</th><th>หน้าที่</th></tr>
                    <tr><td>ข่าวสารทั้งหมด</td><td>รายการข่าวทั้งหมด (กรองตามหมวดหมู่ได้จาก dropdown)</td></tr>
                    <tr><td>เพิ่มข่าวใหม่</td><td>เพิ่มข่าวสาร</td></tr>
                    <tr><td>หมวดหมู่</td><td>จัดการหมวดหมู่ + ตั้งค่าต่อหมวด</td></tr>
                    <tr><td>แท็ก</td><td>จัดการแท็กข่าว</td></tr>
                    <tr><td>ตั้งค่า</td><td>ตั้งค่าทั่วไป (Global Settings)</td></tr>
                    <tr><td>คู่มือการใช้งาน</td><td>เอกสารนี้</td></tr>
                    <tr><td>Debug &amp; Fix</td><td>ตรวจสอบและแก้ไขปัญหา / เคลียร์ Cache</td></tr>
                </table>
            </div>

            <!-- TAB: Shortcode -->
            <div class="newtm-tab-content" id="tab-shortcode">
                <h2>[newtm_table] — แสดงข่าวตามหมวดหมู่</h2>
                <div class="newtm-code">[newtm_table category="slug-หมวดหมู่" limit="5" show_title="true" title_length="80"]</div>

                <table>
                    <tr><th>พารามิเตอร์</th><th>ค่าเริ่มต้น</th><th>คำอธิบาย</th></tr>
                    <tr>
                        <td><span class="newtm-inline-code">category</span> <span class="newtm-badge orange">จำเป็น</span></td>
                        <td>—</td>
                        <td>Slug ของหมวดหมู่ (ดูได้จากหน้าหมวดหมู่)</td>
                    </tr>
                    <tr>
                        <td><span class="newtm-inline-code">limit</span></td>
                        <td>ค่าจากหมวด หรือ Settings</td>
                        <td>จำนวนข่าวที่แสดง (0 = ใช้ค่า default)</td>
                    </tr>
                    <tr>
                        <td><span class="newtm-inline-code">show_title</span></td>
                        <td><span class="newtm-inline-code">true</span></td>
                        <td>แสดงชื่อหมวดหมู่เหนือตาราง (true / false)</td>
                    </tr>
                    <tr>
                        <td><span class="newtm-inline-code">title_length</span></td>
                        <td>ค่าจากหมวด หรือ Settings</td>
                        <td>ตัดหัวข้อที่ n ตัวอักษร (0 = แสดงเต็ม)</td>
                    </tr>
                    <tr>
                        <td><span class="newtm-inline-code">excerpt_length</span></td>
                        <td>0</td>
                        <td>ตัดเนื้อหาย่อที่ n ตัวอักษร (0 = แสดงเต็ม)</td>
                    </tr>
                </table>

                <h3>ตัวอย่าง</h3>
                <div class="newtm-code"><?php
$examples = array(
    '[newtm_table category="ข่าวประชาสัมพันธ์"]',
    '[newtm_table category="จัดซื้อจัดจ้าง" limit="10"]',
    '[newtm_table category="รับสมัครงาน" show_title="false"]',
    '[newtm_table category="คำสั่งแต่งตั้ง" title_length="80"]',
);
echo esc_html( implode( "\n", $examples ) );
                ?></div>

                <div class="newtm-note">
                    <strong>ลำดับความสำคัญของค่า:</strong><br>
                    พารามิเตอร์ใน Shortcode &gt; ค่าตั้งค่าต่อหมวดหมู่ &gt; Global Settings
                </div>

                <h2>[newtm_all_categories] — แสดงทุกหมวดหมู่</h2>
                <div class="newtm-code">[newtm_all_categories limit="5" title_length="80"]</div>

                <table>
                    <tr><th>พารามิเตอร์</th><th>ค่าเริ่มต้น</th><th>คำอธิบาย</th></tr>
                    <tr>
                        <td><span class="newtm-inline-code">limit</span></td>
                        <td>5</td>
                        <td>จำนวนข่าวต่อหมวดหมู่</td>
                    </tr>
                    <tr>
                        <td><span class="newtm-inline-code">title_length</span></td>
                        <td>0</td>
                        <td>ตัดหัวข้อที่ n ตัวอักษร</td>
                    </tr>
                </table>

                <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                <h2>หมวดหมู่ที่มีอยู่ในระบบ</h2>
                <table>
                    <tr><th>ชื่อหมวดหมู่</th><th>Slug (ใช้ใน Shortcode)</th><th>จำนวนข่าว</th><th>Shortcode พร้อมใช้</th></tr>
                    <?php foreach ( $categories as $cat ) : ?>
                    <tr>
                        <td><?php echo esc_html( $cat->name ); ?></td>
                        <td><span class="newtm-inline-code"><?php echo esc_html( urldecode( $cat->slug ) ); ?></span></td>
                        <td><?php echo esc_html( $cat->count ); ?></td>
                        <td><span class="newtm-inline-code">[newtm_table category="<?php echo esc_attr( urldecode( $cat->slug ) ); ?>"]</span></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>

            <!-- TAB: หมวดหมู่ -->
            <div class="newtm-tab-content" id="tab-category">
                <h2>การจัดการหมวดหมู่</h2>
                <p>ไปที่ <a href="<?php echo esc_url( $category_url ); ?>">ข่าวสาร → หมวดหมู่</a></p>

                <h3>ฟิลด์พิเศษต่อหมวดหมู่</h3>
                <p>เมื่อแก้ไขหมวดหมู่ จะมีฟิลด์เพิ่มเติมสำหรับตั้งค่าเฉพาะหมวดนั้น:</p>
                <table>
                    <tr><th>ฟิลด์</th><th>คำอธิบาย</th></tr>
                    <tr>
                        <td>จำนวนข่าวต่อหน้า</td>
                        <td>ใส่ 0 = ใช้ Global Settings | ใส่ตัวเลข = override เฉพาะหมวดนี้</td>
                    </tr>
                    <tr>
                        <td>ความยาวสูงสุดของหัวข้อ</td>
                        <td>ตัดหัวข้อเฉพาะหมวดนี้ที่ n ตัวอักษร (0 = ไม่ตัด)</td>
                    </tr>
                </table>

                <h3>Slug คืออะไร?</h3>
                <p>Slug คือชื่อย่อสำหรับ URL ที่ WordPress สร้างให้อัตโนมัติ เช่น:</p>
                <table>
                    <tr><th>ชื่อหมวดหมู่</th><th>Slug (ตัวอย่าง)</th></tr>
                    <tr><td>ข่าวประชาสัมพันธ์</td><td><span class="newtm-inline-code">ข่าวประชาสัมพันธ์</span> หรือ <span class="newtm-inline-code">news</span></td></tr>
                    <tr><td>จัดซื้อจัดจ้าง</td><td><span class="newtm-inline-code">จัดซื้อจัดจ้าง</span></td></tr>
                </table>
                <div class="newtm-tip">
                    ดู Slug จริงได้จากหน้า <a href="<?php echo esc_url( $category_url ); ?>">หมวดหมู่</a> คอลัมน์ "Slug" หรือเมื่อแก้ไขหมวดหมู่
                </div>

                <h3>กรองข่าวตามหมวดหมู่ใน Admin</h3>
                <p>ที่หน้ารายการข่าว (<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=newtm_news' ) ); ?>">ข่าวสารทั้งหมด</a>) มี dropdown กรองหมวดหมู่ให้เลือกที่ด้านบนตาราง</p>
            </div>

            <!-- TAB: ตั้งค่า -->
            <div class="newtm-tab-content" id="tab-settings">
                <h2>Global Settings</h2>
                <p>ไปที่ <a href="<?php echo esc_url( $settings_url ); ?>">ข่าวสาร → ตั้งค่า</a></p>

                <table>
                    <tr><th>ตัวเลือก</th><th>ค่าเริ่มต้น</th><th>คำอธิบาย</th></tr>
                    <tr>
                        <td>จำนวนตัวอักษรสูงสุดของหัวข้อ</td>
                        <td>0 (ไม่จำกัด)</td>
                        <td>ตัดหัวข้อข่าวในรายการ ใส่ 0 = แสดงเต็ม</td>
                    </tr>
                    <tr>
                        <td>จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ</td>
                        <td>0 (ไม่จำกัด)</td>
                        <td>ตัดเนื้อหาย่อ ใส่ 0 = แสดงเต็ม</td>
                    </tr>
                    <tr>
                        <td>จำนวนข่าวต่อหน้า</td>
                        <td>10</td>
                        <td>จำนวนข่าวที่แสดงในแต่ละหมวดหมู่ (ถ้าไม่ได้ตั้งค่าต่อหมวด)</td>
                    </tr>
                </table>

                <div class="newtm-note">
                    ค่าเหล่านี้เป็น "ค่าเริ่มต้นทั่วไป" หากตั้งค่าต่อหมวดหมู่หรือใส่พารามิเตอร์ใน Shortcode จะมีความสำคัญกว่า
                </div>

                <h2>ระบบ Cache</h2>
                <p>Plugin จะเคลียร์ Cache อัตโนมัติเมื่อ:</p>
                <table>
                    <tr><th>เหตุการณ์</th><th>Cache ที่เคลียร์</th></tr>
                    <tr><td>บันทึก/อัปเดตข่าว</td><td>Cache ข่าวนั้น + หมวดหมู่ที่เกี่ยวข้อง + ทั้งหมด</td></tr>
                    <tr><td>ลบข่าว</td><td>Cache ข่าวนั้น + หมวดหมู่ + ทั้งหมด</td></tr>
                    <tr><td>เปลี่ยนหมวดหมู่ของข่าว</td><td>Cache หมวดหมู่ที่เกี่ยวข้อง</td></tr>
                    <tr><td>แก้ไข Settings</td><td>Cache ทั้งหมด</td></tr>
                </table>
                <p>เคลียร์ Cache ด้วยตนเอง: <a href="<?php echo esc_url( $debug_url ); ?>">Debug &amp; Fix → Clear Cache</a></p>
            </div>

            <!-- TAB: Elementor -->
            <div class="newtm-tab-content" id="tab-elementor">
                <h2>Elementor Widgets</h2>
                <?php if ( defined( 'ELEMENTOR_VERSION' ) ) : ?>
                    <div class="newtm-tip">✅ Elementor <?php echo esc_html( ELEMENTOR_VERSION ); ?> พบและเปิดใช้งานแล้ว</div>
                <?php else : ?>
                    <div class="newtm-note">⚠️ ไม่พบ Elementor — Widgets จะปรากฏเมื่อ activate Elementor</div>
                <?php endif; ?>

                <p>เมื่อ Elementor ถูก activate จะมี Widget category <strong>"NewTM - ข่าวสาร"</strong> ใน Elementor Editor</p>

                <table>
                    <tr><th>Widget</th><th>คำอธิบาย</th></tr>
                    <tr><td>News Grid</td><td>แสดงข่าวแบบ Grid (หลายคอลัมน์)</td></tr>
                    <tr><td>News Table</td><td>แสดงข่าวแบบตาราง (วันที่ | หัวข้อ)</td></tr>
                    <tr><td>Category Tabs</td><td>แสดงข่าวแบบ Tab แยกตามหมวดหมู่</td></tr>
                </table>

                <h3>วิธีใช้งาน Widget</h3>
                <ol class="newtm-steps">
                    <li><div>เปิด Elementor Editor ในหน้าที่ต้องการ</div></li>
                    <li><div>ค้นหา "NewTM" หรือเลือก Category "NewTM - ข่าวสาร" ในแผง Widgets</div></li>
                    <li><div>ลาก Widget ที่ต้องการไปวางในหน้า</div></li>
                    <li><div>ตั้งค่า Widget — เลือกหมวดหมู่ จำนวนข่าว ฯลฯ ในแผงซ้าย</div></li>
                    <li><div>คลิก Publish / Update</div></li>
                </ol>

                <div class="newtm-tip">
                    Shortcode ก็ใช้งานได้ใน Elementor โดยใช้ Widget "Shortcode" ของ Elementor แล้ววาง <span class="newtm-inline-code">[newtm_table category="..."]</span>
                </div>
            </div>

            <!-- TAB: FAQ -->
            <div class="newtm-tab-content" id="tab-faq">
                <h2>คำถามที่พบบ่อย</h2>

                <h3>❓ Shortcode แสดงว่า "ไม่พบหมวดหมู่"</h3>
                <p>ตรวจสอบ Slug ของหมวดหมู่ให้ถูกต้อง ไปที่ <a href="<?php echo esc_url( $category_url ); ?>">ข่าวสาร → หมวดหมู่</a> ดูคอลัมน์ "Slug" แล้วนำมาใส่ใน <span class="newtm-inline-code">category="..."</span></p>

                <h3>❓ ข่าวแสดงน้อยกว่าที่ต้องการ</h3>
                <p>ตรวจสอบ:</p>
                <ul>
                    <li>ข่าวมีสถานะ <strong>Published</strong> หรือไม่</li>
                    <li>ข่าวอยู่ในหมวดหมู่ที่ถูกต้องหรือไม่</li>
                    <li>ค่า <span class="newtm-inline-code">limit</span> ใน Shortcode หรือค่าตั้งค่าต่อหมวด</li>
                    <li>ไปที่ <a href="<?php echo esc_url( $debug_url ); ?>">Debug &amp; Fix</a> เพื่อตรวจสอบ</li>
                </ul>

                <h3>❓ ข่าวอัปเดตแล้วแต่หน้าเว็บยังแสดงข้อมูลเก่า</h3>
                <p>ไปที่ <a href="<?php echo esc_url( $debug_url ); ?>">Debug &amp; Fix → Clear Cache</a> เพื่อเคลียร์ Cache ด้วยตนเอง</p>

                <h3>❓ หัวข้อข่าวถูกตัดทั้งที่ไม่ได้ตั้งค่า title_length</h3>
                <p>ตรวจสอบ Global Settings (<a href="<?php echo esc_url( $settings_url ); ?>">ข่าวสาร → ตั้งค่า</a>) ว่า "จำนวนตัวอักษรสูงสุดของหัวข้อ" เป็น 0 หรือไม่ หรือตรวจสอบค่าต่อหมวดหมู่ที่หน้าแก้ไขหมวดหมู่</p>

                <h3>❓ Elementor Widget ไม่ปรากฏ</h3>
                <p>ตรวจสอบว่า Elementor ถูก activate แล้ว หากยังไม่ปรากฏให้ลอง Deactivate แล้ว Activate Plugin NewTM ใหม่</p>

                <h3>❓ URL ของหน้าข่าวไม่ทำงาน (404)</h3>
                <p>ไปที่ WordPress Admin → Settings → Permalinks แล้วคลิก <strong>Save Changes</strong> เพื่อ flush rewrite rules</p>

                <h3>❓ ต้องการ Debug เพิ่มเติม</h3>
                <p>ไปที่ <a href="<?php echo esc_url( $debug_url ); ?>">ข่าวสาร → Debug &amp; Fix</a> มีข้อมูลระบบและเครื่องมือช่วยแก้ปัญหา</p>
            </div>
        </div>

        <script>
        (function() {
            var tabs = document.querySelectorAll('.newtm-tab-btn');
            var contents = document.querySelectorAll('.newtm-tab-content');
            tabs.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = this.getAttribute('data-tab');
                    tabs.forEach(function(b) { b.classList.remove('active'); });
                    contents.forEach(function(c) { c.classList.remove('active'); });
                    this.classList.add('active');
                    var el = document.getElementById('tab-' + target);
                    if (el) el.classList.add('active');
                }.bind(btn));
            });
        })();
        </script>
        <?php
    }
}
