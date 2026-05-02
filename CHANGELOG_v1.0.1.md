# NewTM Plugin - Version 1.0.1 Update

## 📋 สรุปการปรับปรุง

เวอร์ชัน 1.0.1 ได้เพิ่มเติมและแก้ไขปัญหาต่อไปนี้:

### 🐛 ปัญหาที่แก้ไข

1. **ข่าวไม่แสดงตรงกับที่มีอยู่จริง** 
   - ✓ เพิ่มระบบ Auto Cache Clear เมื่อแก้ไขข่าว
   - ✓ เพิ่ม Query Validation เพื่อแน่นอนว่าใช้เฉพาะ Published posts
   - ✓ เพิ่ม Debug Page เพื่อตรวจสอบและแก้ไขปัญหา

2. **ข่าวที่ไม่มีหมวดหมู่ไม่แสดง**
   - ✓ เพิ่ม Auto-fix feature ที่กำหนดหมวดหมู่เริ่มต้น
   - ✓ เพิ่มการแจ้งเตือนเมื่อพบปัญหา

3. **Cache ไม่ update เมื่อมีการเปลี่ยนแปลง**
   - ✓ เพิ่ม Hooks สำหรับ save_post, delete_post, set_object_terms
   - ✓ Automatic cache invalidation

### ✨ ฟีเจอร์ใหม่

1. **Smart Cache Handler** (`class-cache-handler.php`)
   - อัตโนมัติเคลียร์ cache เมื่อมีการแก้ไขข่าว
   - เคลียร์ cache ตามหมวดหมู่ที่เกี่ยวข้อง
   - รองรับ Redis Cache

2. **Helper Functions** (`class-helper-functions.php`)
   - `newtm_get_widget_status()` - ตรวจสอบสถานะ widgets
   - `newtm_count_posts_by_status()` - นับข่าวตามสถานะ
   - `newtm_get_unassigned_news()` - ดึงข่าวที่ไม่มีหมวดหมู่
   - `newtm_refresh_all_queries()` - Refresh ทั้งหมด
   - `newtm_get_published_news()` - ดึง published posts เท่านั้น
   - `newtm_log_error()` - Log errors สำหรับ debugging

3. **Admin Debug Page** (`class-debug-page.php`)
   - ✓ Dashboard สำหรับมองสถานะข่าว
   - ✓ One-click fix สำหรับปัญหาทั่วไป
   - ✓ Clear Cache button
   - ✓ Publish Draft posts
   - ✓ Assign categories to uncategorized posts
   - ✓ Widget Status indicator

4. **Comprehensive User Manual** (`MANUAL_TH.md`)
   - คู่มือแบบละเอียดในภาษาไทย
   - วิธีการใช้งาน Widgets และ Shortcodes
   - Troubleshooting guide
   - ตัวอย่างการใช้งาน

---

## 📦 ไฟล์ที่เพิ่มใหม่

```
newtm/
├── includes/
│   ├── class-cache-handler.php      (ใหม่) ระบบจัดการ Cache
│   ├── class-helper-functions.php   (ใหม่) Helper Functions
│   ├── class-debug-page.php         (ใหม่) Debug & Fix Admin Page
│   └── ... (ไฟล์เดิม)
├── MANUAL_TH.md                      (ใหม่) คู่มือการใช้งาน
└── ... (ไฟล์เดิม)
```

---

## 🔧 วิธีการใช้ Debug Page

### เข้าหน้า Debug
```
1. WordPress Admin
2. ข่าวสาร → Debug & Fix
3. ดูสถานะและทำการแก้ไขตามต้องการ
```

### ฟังก์ชัน Fix ที่มีให้

| ฟังก์ชัน | วัตถุประสงค์ | เหตุผลใช้ |
|---------|----------|--------|
| Clear All Cache | เคลียร์ cache ทั้งหมด | เมื่อข่าวไม่อัพเดต |
| Publish Draft | เปลี่ยน Draft → Published | เมื่อข่าวเป็น Draft โดยไม่ตั้งใจ |
| Assign Categories | กำหนดหมวดหมู่ | เมื่อข่าวไม่มีหมวดหมู่ |

---

## 📖 ตัวอย่างการใช้ Helper Functions

### ตรวจสอบสถานะ Widget
```php
$status = newtm_get_widget_status();

echo $status['post_counts']['publish'];  // จำนวน published posts
echo $status['unassigned_news'];         // จำนวนข่าวไม่มีหมวดหมู่
echo $status['problematic_posts'];       // จำนวนข่าวปัญหา
echo $status['cache_enabled'] ? 'Yes' : 'No';  // สถานะ cache
```

### ดึง Published News เท่านั้น
```php
$args = array(
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
    'tax_query' => array(
        array(
            'taxonomy' => 'newtm_category',
            'field' => 'term_id',
            'terms' => 5, // Category ID
        ),
    ),
);

$query = newtm_get_published_news($args);
```

### Log Errors
```php
newtm_log_error('Custom message', array(
    'post_id' => 123,
    'category' => 'test',
));
```

---

## 🚀 Installation

### Update Plugin
```
1. เข้า WordPress Admin
2. ข่าวสาร → ตั้งค่า
3. ดูหน้า Debug & Fix เพื่อตรวจสอบสถานะ
4. ทำการ Fix ที่จำเป็นตามปัญหา
```

### การตรวจสอบการติดตั้ง
```
1. ไปที่ ข่าวสาร → Debug & Fix
2. ตรวจสอบ:
   - Published News > 0
   - Unassigned Categories = 0
   - Draft = 0
3. ถ้ามีปัญหา ให้คลิก Fix buttons
```

---

## 🔍 Troubleshooting

### ข่าวยังไม่แสดง หลังจากแก้ไข
```
1. ไปที่ Debug & Fix page
2. คลิก "Clear All Cache"
3. รีเฟรช browser (Ctrl+F5)
4. ตรวจสอบสถานะ Widget Status
```

### Widget ไม่แสดงตัวเลือก
```
1. ไปที่ Debug & Fix
2. คลิก "Clear All Cache"
3. ปิด Elementor แล้วเปิดใหม่
4. Refresh page
```

### หมวดหมู่ไม่แสดง
```
1. ตรวจสอบว่ามีข่าว Published ที่กำหนดหมวดหมู่แล้ว
2. ไปที่ Debug & Fix
3. ใช้ "Assign Categories" เพื่ออัตโนมัติกำหนดหมวดหมู่
```

---

## 📝 Version History

### v1.0.1 (28/04/2567)
- ✓ เพิ่ม Smart Cache Handler
- ✓ เพิ่ม Debug & Fix Admin Page
- ✓ เพิ่ม Helper Functions
- ✓ เพิ่มคู่มือการใช้งาน (Thai)
- ✓ แก้ไขปัญหา Query Logic
- ✓ Automatic cache invalidation

### v1.0.0 (15/03/2567)
- เวอร์ชันแรกที่เผยแพร่

---

## 🔐 ความปลอดภัย

- ✓ ตรวจสอบ Capabilities ทั้งหมด
- ✓ Nonce verification สำหรับ form actions
- ✓ Data sanitization ด้วย `sanitize_text_field()`
- ✓ Output escaping ด้วย `esc_html()`, `esc_url()` เป็นต้น

---

## 📞 ติดต่อสนับสนุน

หากพบปัญหา:
1. ตรวจสอบ Debug & Fix page
2. ดูคู่มือการใช้งาน (MANUAL_TH.md)
3. ตรวจสอบ WordPress Debug Log

---

**เอกสารนี้อัพเดตล่าสุด: 28 เมษายน 2567**
