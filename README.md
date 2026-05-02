# NewTM - News Table System

ระบบจัดการข่าวแบบตารางสำหรับหน่วยงานราชการ | Version 1.0.1

---

## คุณสมบัติ

- ✅ **Custom Post Type** `newtm_news` สำหรับจัดการข่าว
- ✅ **Taxonomy** หมวดหมู่ลำดับชั้น `newtm_category` + แท็ก `newtm_tag`
- ✅ **Shortcode** `[newtm_table]` — แสดงข่าวตารางตามหมวดหมู่
- ✅ **Shortcode** `[newtm_all_categories]` — แสดงทุกหมวดหมู่พร้อมกัน
- ✅ **Elementor Widgets** — News Grid, News Table, Category Tabs
- ✅ **ระบบ Cache** — เคลียร์อัตโนมัติเมื่อบันทึก/แก้ไข/ลบข่าว
- ✅ **ตั้งค่าต่อหมวดหมู่** — จำนวนข่าว / ความยาวหัวข้อ แยกแต่ละหมวด
- ✅ **Filter Admin** — กรองข่าวตามหมวดหมู่ในหน้าจัดการ
- ✅ **หน้าคู่มือ** — เปิดดูได้จากเมนู Admin ข่าว → คู่มือการใช้งาน
- ✅ **หน้า Debug & Fix** — ตรวจสอบและแก้ไขปัญหาจาก Admin
- ✅ **Responsive Design** รองรับทุกขนาดหน้าจอ

---

## ความต้องการระบบ

- WordPress 5.8 ขึ้นไป
- PHP 7.4 ขึ้นไป (รองรับ PHP 8.3)
- **หมายเหตุ:** ไม่จำเป็นต้องติดตั้ง `mbstring` — Plugin ใช้ `iconv` เป็น fallback อัตโนมัติ

---

## การติดตั้ง

1. อัปโหลดโฟลเดอร์ `newtm` ไปยัง `/wp-content/plugins/`
2. เข้า WordPress Admin → Plugins
3. ค้นหา "NewTM - News Table System" แล้วคลิก **Activate**
4. Permalink จะถูก flush อัตโนมัติ

---

## โครงสร้างเมนู Admin

เมื่อ activate plugin จะมีเมนูหลัก **"ข่าวสาร"** ปรากฏใน Admin sidebar ประกอบด้วย:

| เมนูย่อย | หน้าที่ |
|---|---|
| ข่าวสารทั้งหมด | รายการข่าวทั้งหมด (กรองตามหมวดหมู่ได้) |
| เพิ่มข่าวใหม่ | เพิ่มข่าว |
| หมวดหมู่ | จัดการหมวดหมู่ + ตั้งค่าต่อหมวด |
| แท็ก | จัดการแท็ก |
| ตั้งค่า | ตั้งค่าทั่วไป (Global Settings) |
| คู่มือการใช้งาน | เอกสารและตัวอย่างการใช้งาน |
| Debug & Fix | ตรวจสอบและแก้ไขปัญหา |

---

## Shortcodes

### `[newtm_table]` — แสดงข่าวตามหมวดหมู่

```
[newtm_table category="slug-ของหมวดหมู่"]
```

**พารามิเตอร์ทั้งหมด:**

| พารามิเตอร์ | ค่าเริ่มต้น | คำอธิบาย |
|---|---|---|
| `category` | (จำเป็น) | Slug ของหมวดหมู่ |
| `limit` | 0 (ใช้ค่าจากหมวด/Settings) | จำนวนข่าวที่แสดง |
| `show_title` | `true` | แสดงชื่อหมวดหมู่เหนือตาราง |
| `title_length` | 0 (ใช้ค่าจากหมวด/Settings) | ตัดหัวข้อที่ n ตัวอักษร (0 = เต็ม) |
| `excerpt_length` | 0 | ตัดเนื้อหาย่อที่ n ตัวอักษร (0 = เต็ม) |

**ตัวอย่าง:**
```
[newtm_table category="ข่าวประชาสัมพันธ์"]
[newtm_table category="จัดซื้อจัดจ้าง" limit="10"]
[newtm_table category="รับสมัครงาน" show_title="false" title_length="80"]
```

**ลำดับความสำคัญของค่า (Priority):**
1. พารามิเตอร์ใน Shortcode (สูงสุด)
2. ค่าที่ตั้งต่อหมวดหมู่ (แก้ไขที่หน้าหมวดหมู่)
3. ค่า Global Settings (ข่าวสาร → ตั้งค่า)

---

### `[newtm_all_categories]` — แสดงทุกหมวดหมู่

```
[newtm_all_categories limit="5"]
```

**พารามิเตอร์:**

| พารามิเตอร์ | ค่าเริ่มต้น | คำอธิบาย |
|---|---|---|
| `limit` | 5 | จำนวนข่าวต่อหมวดหมู่ |
| `title_length` | 0 | ตัดหัวข้อที่ n ตัวอักษร |

---

## การตั้งค่าต่อหมวดหมู่

ที่หน้า **ข่าวสาร → หมวดหมู่** → แก้ไขหมวดหมู่ จะมีฟิลด์เพิ่มเติม:

- **จำนวนข่าวต่อหน้า** — ค่านี้จะ override Global Settings สำหรับหมวดนั้น
- **ความยาวสูงสุดของหัวข้อ** — ตัดหัวข้อสำหรับหมวดนั้นโดยเฉพาะ

---

## Elementor Widgets

เมื่อ Elementor ถูก activate ไว้ จะมี Widget category **"NewTM - ข่าวสาร"** ใน Elementor Editor:

| Widget | คำอธิบาย |
|---|---|
| News Grid | แสดงข่าวแบบ Grid |
| News Table | แสดงข่าวแบบตาราง |
| Category Tabs | แสดงข่าวแบบแท็บแยกหมวด |

---

## ระบบ Cache

Plugin จะเคลียร์ cache อัตโนมัติเมื่อ:
- **บันทึก/อัปเดต** ข่าว
- **ลบ** ข่าว
- **เปลี่ยนหมวดหมู่** ของข่าว
- **แก้ไข Settings** (title_max_length, excerpt_max_length, items_per_page)

เคลียร์ cache ด้วยตนเอง: **ข่าวสาร → Debug & Fix → Clear Cache**

---

## โครงสร้างไฟล์

```
newtm/
├── newtm.php                          # ไฟล์หลัก Plugin
├── includes/
│   ├── class-post-type.php            # ลงทะเบียน Custom Post Type
│   ├── class-taxonomy.php             # ลงทะเบียน Taxonomy + Category meta
│   ├── class-shortcodes.php           # Shortcodes [newtm_table] [newtm_all_categories]
│   ├── class-settings.php             # หน้าตั้งค่า + helper functions
│   ├── class-cache-handler.php        # ระบบ Cache อัตโนมัติ
│   ├── class-helper-functions.php     # ฟังก์ชันช่วยเหลือทั่วไป
│   ├── class-elementor-checker.php    # ตรวจสอบ Elementor
│   ├── class-debug-page.php           # หน้า Debug & Fix
│   ├── class-manual-page.php          # หน้าคู่มือการใช้งาน
│   └── widgets/
│       ├── class-widget-news-grid.php
│       ├── class-widget-news-table.php
│       └── class-widget-category-tabs.php
├── templates/
│   ├── single-newtm_news.php          # Template หน้าข่าวเดี่ยว
│   └── archive-newtm_news.php         # Template หน้าข่าวทั้งหมด
└── assets/
    ├── public.css / public.js         # Frontend assets
    └── admin.css / admin.js           # Admin assets
```

---

## ข้อมูลทางเทคนิค

- **Post Type:** `newtm_news`
- **Taxonomy (หมวดหมู่):** `newtm_category` | Slug: `news-category`
- **Taxonomy (แท็ก):** `newtm_tag` | Slug: `news-tag`
- **Option keys:** `newtm_title_max_length`, `newtm_excerpt_max_length`, `newtm_items_per_page`
- **Term meta:** `category_title_length`, `category_items_per_page`

---

## Changelog

### v1.0.1
- แก้ไข `newtm_truncate_text()` รองรับ PHP ที่ไม่มี `mbstring` (ใช้ `iconv` fallback)
- แก้ไข `tax_query` field จาก `id` → `term_id`
- เพิ่ม Filter หมวดหมู่ในหน้า Admin
- เพิ่มหน้าคู่มือการใช้งาน
- เพิ่มหน้า Debug & Fix
- เพิ่ม Cache Handler อัตโนมัติ

### v1.0.0
- Release แรก

## คุณสมบัติ

- ✅ Custom Post Type สำหรับข่าว (newtm_news)
- ✅ Taxonomy หมวดหมู่ลำดับชั้น (newtm_category)
- ✅ Shortcode แสดงข่าวแบบตาราง 2 คอลัมน์ (วันที่ | หัวข้อ)
- ✅ แสดงข่าวจัดกลุ่มตามหมวดหมู่
- ✅ ปุ่ม "ดูทั้งหมด" สำหรับแต่ละหมวด
- ✅ Responsive Design
- ✅ UI/UX เรียบง่ายและใช้งานง่าย

## การติดตั้ง

1. อัปโหลดโฟลเดอร์ `newtm` ไปยัง `/wp-content/plugins/`
2. เข้า WordPress Admin → Plugins
3. ค้นหา "NewTM - News Table System"
4. คลิก "Activate"

## การใช้งาน Shortcode

### แสดงข่าวตามหมวดหมู่
```
[newtm_table category="ประกาศ" limit="5"]
```

**พารามิเตอร์:**
- `category` - Slug ของหมวดหมู่ (จำเป็น)
- `limit` - จำนวนข่าวที่แสดง (ค่าเริ่มต้น: ใช้ค่าจากหน้า ตั้งค่า)
- `title_length` - จำนวนตัวอักษรสูงสุดของหัวข้อ (ค่าเริ่มต้น: ใช้ค่าจากหน้า ตั้งค่า)
- `show_title` - แสดงชื่อหมวดหมู่ (true/false) ค่าเริ่มต้น: true

**ตัวอย่าง:**
```
[newtm_table category="ประกาศ" limit="10" show_title="true"]
[newtm_table category="กิจกรรม" limit="5"]
[newtm_table category="ประกาศ" title_length="80"]
[newtm_table category="ประกาศ" limit="0" title_length="0"]
```

### แสดงข่าวทั้งหมดจัดกลุ่มตามหมวดหมู่
```
[newtm_all_categories limit="5"]
```

## ตั้งค่าการแสดงผล (UI)

### ตั้งค่าทั่วไป (Global Settings)
ไปที่ WordPress Admin → ข่าวสาร → ตั้งค่า

- **จำนวนตัวอักษรสูงสุดของหัวข้อ** (ใช้เป็นค่าเริ่มต้นของ `title_length`)
- **จำนวนข่าวต่อหน้า** (ใช้เป็นค่าเริ่มต้นของ `limit`)

### ตั้งค่าแยกรายหมวดหมู่ (Per-Category Settings)
ไปที่ WordPress Admin → ข่าวสาร → หมวดหมู่ → แก้ไขหมวดหมู่

แต่ละหมวดหมู่สามารถตั้งค่าเป็นของตัวเองได้:
- **จำนวนตัวอักษรสูงสุดของหัวข้อ** - ใช้สำหรับหมวดหมู่นี้เท่านั้น
- **จำนวนข่าวต่อหน้า** - ใช้สำหรับหมวดหมู่นี้เท่านั้น
- **จำนวนตัวอักษรสูงสุดของเนื้อหาย่อ** - สำหรับการแสดงผลเนื้อหาย่อ

**ลำดับการใช้ค่า:**
1. ค่าจากตัวพารามิเตอร์ shortcode (เช่น `limit="10"`)
2. ค่าจากการตั้งค่าของหมวดหมู่ (ถ้าได้ตั้งค่าไว้)
3. ค่าจากตั้งค่าทั่วไป (Global Settings)

**หมายเหตุ:** 
- ใส่ `0` = ไม่จำกัด หรือใช้ค่าเริ่มต้นจากระดับต่ำกว่า
- หากตั้งค่าในหมวดหมู่ที่เป็นตัวเลขมากกว่า 0 จะใช้ค่านั้นแทน

## โครงสร้างไฟล์

```
newtm/
├── newtm.php                    # ไฟล์หลักของ Plugin
├── includes/
│   ├── class-post-type.php      # Custom Post Type
│   ├── class-taxonomy.php       # Taxonomy
│   └── class-shortcodes.php     # Shortcodes
└── assets/
    ├── public.css               # Frontend Styles
    ├── public.js                # Frontend Scripts
    ├── admin.css                # Admin Styles
    └── admin.js                 # Admin Scripts
```

## วิธีสร้างข่าว

1. ไปที่ WordPress Admin → ข่าวสาร
2. คลิก "Add New"
3. กรอก:
   - **Title** - หัวข้อข่าว
   - **Content** - เนื้อหาข่าว
   - **Category** - เลือกหมวดหมู่
   - **Featured Image** - รูปภาพประกอบ (ถ้ามี)
4. คลิก "Publish"

## การดูข่าว

### หน้ารวมข่าวทั้งหมด
- **URL:** `/news/` หรือ `/newtm_news/` (ขึ้นอยู่กับ permalink settings)
- **รูปแบบการแสดงผล:** รายการข่าวแบบ List
- **ข้อมูลที่แสดง:** รูปภาพประกอบ, หมวดหมู่, วันที่, หัวข้อ, บรรยายสั้น

### หน้าแสดงข่าวตามหมวดหมู่
- **URL:** `/news-category/[slug]/` เช่น `/news-category/ประกาศ/`
- **รูปแบบการแสดงผล:** รายการข่าวแบบ List ที่ได้กรองตามหมวดหมู่
- **Pagination:** แสดงการแบ่งหน้าอัตโนมัติ

### หน้าแสดงข่าวเดี่ยว
- **URL:** `/newtm_news/[post-name]/`
- **รูปแบบการแสดงผล:** เนื้อหาข่าวแบบเต็ม
- **ข้อมูลที่แสดง:** รูปภาพประกอบ, หมวดหมู่, แท็ก, วันที่, เนื้อหาเต็มด้วย

## Slug ของหมวดหมู่

หลังจากสร้างหมวดหมู่ ให้คัดลอก slug เพื่อใช้ใน shortcode:
- เช่น ชื่อหมวดหมู่: "ข่าวประชาสัมพันธ์" → slug: "ข่าวประชาสัมพันธ์" (หรือกำหนดเอง)

## ข้อแนะนำ

- สร้างหมวดหมู่ก่อนสร้างข่าว
- ใช้ slug ภาษาอังกฤษถ้าเป็นไปได้ (เพื่อประสิทธิภาพ)
- เพิ่มรูปภาพประกอบข่าวเพื่อให้น่าสนใจ
- ใส่ shortcode ลงในหน้า/บทความเพื่อแสดงข่าว

## License

GPL v2 or later
