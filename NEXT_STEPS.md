# ✅ ขั้นตอนต่อไป: ติดตั้ง PHP Driver

## 🎉 ข่าวดี!
คุณมี **ODBC Driver 18** ติดตั้งอยู่แล้ว (เวอร์ชันสูงกว่า Driver 17)  
**ไม่ต้องติดตั้ง ODBC Driver 17** - ข้ามขั้นตอนนี้ไปได้เลย!

---

## 📦 ขั้นตอนต่อไป: ติดตั้ง PHP Driver

### 1. ดาวน์โหลด Microsoft Drivers for PHP for SQL Server

**ลิงก์ดาวน์โหลด:**
- **GitHub Releases:** https://github.com/Microsoft/msphpsql/releases
- **Microsoft Docs:** https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

**เลือกไฟล์:**
- สำหรับ PHP 8.3 Thread Safe (TS) 64-bit
- ดาวน์โหลด: **SQLSRV83.exe** หรือ **SQLSRV83.zip**

---

### 2. ติดตั้ง/แตกไฟล์

- ถ้าเป็นไฟล์ `.exe` → ดับเบิลคลิกเพื่อติดตั้ง
- ถ้าเป็นไฟล์ `.zip` → คลิกขวา → Extract All

---

### 3. คัดลอกไฟล์ DLL

1. **หาไฟล์ในโฟลเดอร์ที่แตกไฟล์:**
   - ไปที่โฟลเดอร์ `x64` (สำหรับ 64-bit)
   - หาไฟล์ 2 ไฟล์:
     - `php_sqlsrv_83_ts_x64.dll`
     - `php_pdo_sqlsrv_83_ts_x64.dll`

2. **คัดลอกไฟล์:**
   - เลือกไฟล์ทั้ง 2 ไฟล์
   - กด `Ctrl + C` (คัดลอก)

3. **วางไฟล์:**
   - ไปที่โฟลเดอร์:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\
     ```
   - กด `Ctrl + V` (วาง)

---

### 4. แก้ไข php.ini

1. **เปิดไฟล์ php.ini:**
   - ไปที่: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`
   - คลิกขวา → **Open with** → **Notepad**

2. **เพิ่ม extension:**
   - เลื่อนไปท้ายไฟล์
   - เพิ่มบรรทัด 2 บรรทัดนี้:
     ```ini
     extension=php_sqlsrv_83_ts_x64
     extension=php_pdo_sqlsrv_83_ts_x64
     ```
   - **สำคัญ:** ตรวจสอบว่าไม่มี `;` นำหน้า

3. **บันทึก:**
   - กด `Ctrl + S`
   - ปิด Notepad

---

### 5. รีสตาร์ท Laragon

1. **หยุด Laragon:**
   - คลิกขวาที่ไอคอน Laragon (มุมล่างขวา)
   - เลือก **Stop All**

2. **เริ่ม Laragon ใหม่:**
   - คลิกขวาที่ Laragon อีกครั้ง
   - เลือก **Start All**

---

### 6. ทดสอบการติดตั้ง

เปิดเบราว์เซอร์ไปที่:
```
http://workinternetnindam.test/api/check_sqlsrv.php
```

**ผลลัพธ์ที่ควรเห็น:**
- ✅ ✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว
- ✅ ✓ PDO SQL Server Driver ติดตั้งแล้ว
- ✅ ✓ เชื่อมต่อ SQL Server สำเร็จ!

---

## 📋 Checklist

- [x] ODBC Driver 18 ติดตั้งแล้ว (มีอยู่แล้ว)
- [ ] ดาวน์โหลด Microsoft Drivers for PHP for SQL Server
- [ ] คัดลอก `php_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] คัดลอก `php_pdo_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] เพิ่ม extension ใน php.ini
- [ ] รีสตาร์ท Laragon
- [ ] ทดสอบด้วย check_sqlsrv.php

---

## 🎯 สรุป

คุณมี ODBC Driver 18 อยู่แล้ว (ดี!)  
ตอนนี้ต้องทำแค่:
1. ดาวน์โหลด PHP Driver
2. คัดลอกไฟล์ DLL
3. แก้ไข php.ini
4. รีสตาร์ท Laragon

**เสร็จแล้ว!** 🎉

