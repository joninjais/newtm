# แก้ไขปัญหา: ปุ่ม "Edit with Elementor" ไม่แสดง

## ปัญหา
ปุ่ม "Edit with Elementor" ไม่แสดงในหน้าแก้ไขข่าวสาร (newtm_news)

## สาเหตุ
Custom Post Type `newtm_news` ยังไม่ได้ลงทะเบียนกับ Elementor

## วิธีแก้ไข

### 1. แก้ไขไฟล์ newtm.php ✅

เพิ่มฟังก์ชันเหล่านี้เข้าไป:

```php
/**
 * เปิดใช้งาน Elementor สำหรับ Custom Post Type
 */
private function enable_elementor_support() {
    // เพิ่ม support สำหรับ Elementor ใน custom post type
    add_action('init', function() {
        if (defined('ELEMENTOR_VERSION')) {
            add_post_type_support('newtm_news', 'elementor');
        }
    }, 11);
    
    // เพิ่ม newtm_news เข้าไปใน Elementor CPT Support
    add_filter('elementor/utils/get_public_post_types', array($this, 'add_cpt_to_elementor'));
}

/**
 * เพิ่ม Custom Post Type ให้ Elementor รู้จัก
 */
public function add_cpt_to_elementor($post_types) {
    $post_types['newtm_news'] = 'newtm_news';
    return $post_types;
}
```

### 2. รันสคริปต์เพื่ออัปเดตการตั้งค่า ✅

```bash
php /tmp/enable_elementor_newtm.php
```

**ผลลัพธ์:**
```
✅ เพิ่ม newtm_news เข้าไปใน Elementor CPT Support แล้ว

📋 Post Types ที่ Elementor รองรับ:
   - post
   - page
   - newtm_news
```

### 3. ทดสอบ

1. ไปที่ **WordPress Admin** → **ข่าวสาร** (NewTM)
2. คลิกแก้ไขข่าวใดข่าวหนึ่ง
3. **ตอนนี้คุณจะเห็นปุ่ม "Edit with Elementor"** ด้านบนแล้ว! 🎉

## การตรวจสอบเพิ่มเติม

### ผ่าน WordPress Admin

1. ไปที่ **Elementor** → **Settings** → **General**
2. ดูที่ **Post Types** 
3. ตรวจสอบว่า `newtm_news` มีอยู่ในรายการและถูกเลือกไว้

### ผ่าน Database

```sql
SELECT * FROM wp_options WHERE option_name = 'elementor_cpt_support';
```

ควรเห็น: `a:3:{i:0;s:4:"post";i:1;s:4:"page";i:2;s:10:"newtm_news";}`

## หมายเหตุ

- การเปลี่ยนแปลงนี้มีผลทันที ไม่ต้อง deactivate/activate plugin
- ถ้าปุ่มยังไม่แสดง ลอง:
  1. รีเฟรชหน้าเบราว์เซอร์ (Ctrl+F5)
  2. ล้าง Browser Cache
  3. ไปที่ Elementor → Tools → Regenerate CSS & Data

## ไฟล์ที่แก้ไข

- ✅ `/wp-content/plugins/newtm/newtm.php` - เพิ่ม Elementor support
- ✅ Database option `elementor_cpt_support` - อัปเดตรายการ post types

## สถานะ

✅ **แก้ไขเสร็จสมบูรณ์**  
วันที่: 11 กุมภาพันธ์ 2026
