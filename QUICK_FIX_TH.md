# NewTM - Quick Fix Guide

## 🚀 เริ่มต้นด่วน

### ปัญหา 1️⃣: ข่าวไม่แสดง

```
⏱️ เวลาดำเนินการ: 2 นาที

✅ ขั้นตอน:
1. เข้า WordPress Admin
2. คลิก "ข่าวสาร" → "Debug & Fix"
3. ดูส่วน "สถานะปัจจุบัน"
4. ถ้า "ไม่มีหมวดหมู่" > 0 → คลิก "กำหนดหมวดหมู่"
5. ถ้า "ข่าว Draft" > 0 → คลิก "Publish ข่าว Draft"
6. คลิก "Clear All Cache"
7. รีเฟรช browser (Ctrl+F5)
```

---

### ปัญหา 2️⃣: ข่าวแสดงผลไม่ถูกต้อง

```
⏱️ เวลาดำเนินการ: 1 นาที

✅ ขั้นตอน:
1. เข้า WordPress Admin
2. คลิก "ข่าวสาร" → "Debug & Fix"
3. คลิก "Clear All Cache"
4. รีเฟรช browser (Ctrl+F5)
5. ดู Widget ใหม่
```

---

### ปัญหา 3️⃣: Widget ไม่แสดงตัวเลือก

```
⏱️ เวลาดำเนินการ: 3 นาที

✅ ขั้นตอน:
1. เข้า WordPress Admin
2. คลิก "ข่าวสาร" → "Debug & Fix"
3. คลิก "Clear All Cache"
4. ปิด Elementor (กด Escape)
5. โหลด Elementor ใหม่
6. ลาก Widget "ข่าว Grid" หรือ "ข่าว Table" ใหม่
```

---

## 📋 Checklist สำหรับการตั้งค่า

```
□ ติดตั้ง Plugin NewTM แล้ว
□ เข้า "Debug & Fix" ตรวจสอบสถานษ
□ สร้างหมวดหมู่อย่างน้อย 1 หมวด
□ สร้างข่าว Published อย่างน้อย 3 อัน
□ แต่ละข่าวกำหนดหมวดหมู่ให้ครบ
□ Clear Cache ทั้งหมด
□ ทดสอบ Widget ใน Elementor
□ ทดสอบ Shortcode ด้วย [newtm_table category="..."]
```

---

## 📝 Status Check

**ถ้าคุณเห็นสถานะนี้ = ปกติดี✓**
```
Published News:        > 0
Draft News:            = 0
Unassigned Categories: = 0
Problematic Posts:     = 0
Cache Status:          ✓ Enabled
```

---

## 🔴 Red Flags (ต้องแก้ไข)

| สัญญาณ | ปัญหา | วิธีแก้ |
|-------|-------|--------|
| Draft News > 0 | มีข่าว Draft | Click "Publish Draft" |
| Unassigned > 0 | ข่าวไม่มีหมวด | Click "Assign Categories" |
| Problematic > 0 | ข่าวบกพร่อง | Clear Cache, Check posts |

---

## 🎯 วิธีใช้ Widget

### Widget: ข่าว Grid
```
เลือก:
- จำนวน: 6 อัน
- คอลัมน์: 3
- เรียงลำดับ: วันที่
- ลำดับ: ใหม่ → เก่า
- แสดง: รูป, หมวด, วันที่
```

### Widget: ข่าว Table
```
เลือก:
- จำนวน: 10 อัน
- เรียงลำดับ: วันที่
- ลำดับ: ใหม่ → เก่า
- แสดง: รูป, หมวด, ป้าย NEW
```

---

## 💡 Shortcodes ที่ได้ใช้บ่อย

### แสดงข่าว 5 อัน (หมวด "ข่าวสำคัญ")
```
[newtm_table category="khaosam-khauy" limit="5"]
```

### แสดงข่าวทั้งหมด
```
[newtm_all_categories columns="3" limit="5"]
```

> 💡 **Tip**: ดูชื่อ Slug ได้จาก Dashboard > ข่าวสาร > หมวดหมู่

---

## 📞 ต้องการความช่วยเหลือ?

1. **ตรวจสอบ Debug Page** → ข่าวสาร → Debug & Fix
2. **อ่านคู่มือเต็ม** → [MANUAL_TH.md](./MANUAL_TH.md)
3. **ดูเปลี่ยนแปลงใหม่** → [CHANGELOG_v1.0.1.md](./CHANGELOG_v1.0.1.md)

---

**หวังว่า Quick Guide นี้จะช่วยคุณได้!** 🎉
