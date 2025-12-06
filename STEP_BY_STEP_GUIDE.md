# 📝 คู่มือทำทีละขั้นตอน: ติดตั้ง PHP Driver

## 🎯 ขั้นตอนที่ 1: คัดลอกไฟล์ DLL ไปยังโฟลเดอร์ ext

### วิธีที่ 1: ใช้ File Explorer (แนะนำ)

1. **เปิด File Explorer:**
   - กด `Win + E` หรือคลิกที่ไอคอน File Explorer

2. **ไปที่โฟลเดอร์ที่แตกไฟล์:**
   - ไปที่โฟลเดอร์ที่คุณดาวน์โหลดและแตกไฟล์ SQLSRV83
   - เข้าไปในโฟลเดอร์ `x64` (สำหรับ 64-bit)
   - คุณจะเห็นไฟล์หลายไฟล์ รวมถึง:
     - `php_sqlsrv_83_ts_x64.dll`
     - `php_pdo_sqlsrv_83_ts_x64.dll`

3. **เลือกไฟล์ทั้ง 2 ไฟล์:**
   - คลิกที่ `php_sqlsrv_83_ts_x64.dll`
   - กด `Ctrl` ค้างไว้ แล้วคลิกที่ `php_pdo_sqlsrv_83_ts_x64.dll`
   - (ตอนนี้เลือกไฟล์ 2 ไฟล์แล้ว)

4. **คัดลอกไฟล์:**
   - กด `Ctrl + C` (หรือคลิกขวา → Copy)

5. **ไปที่โฟลเดอร์ ext:**
   - เปิด File Explorer ใหม่ (กด `Win + E` อีกครั้ง)
   - ไปที่แถบ Address bar (ด้านบน)
   - พิมพ์หรือวาง:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext
     ```
   - กด Enter

6. **วางไฟล์:**
   - กด `Ctrl + V` (หรือคลิกขวา → Paste)
   - ไฟล์ทั้ง 2 ไฟล์จะถูกคัดลอกมาที่นี่

---

### วิธีที่ 2: ใช้ Run Dialog

1. **เปิด Run Dialog:**
   - กด `Win + R`

2. **พิมพ์ path:**
   - พิมพ์:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext
     ```
   - กด Enter
   - จะเปิดโฟลเดอร์ ext ขึ้นมา

3. **คัดลอกไฟล์:**
   - ไปที่โฟลเดอร์ที่แตกไฟล์ SQLSRV83
   - คัดลอกไฟล์ 2 ไฟล์ (ตามวิธีที่ 1)
   - กลับมาที่โฟลเดอร์ ext
   - วางไฟล์ (`Ctrl + V`)

---

## 🎯 ขั้นตอนที่ 2: แก้ไข php.ini

### วิธีที่ 1: ใช้ Notepad (แนะนำ)

1. **เปิด File Explorer:**
   - กด `Win + E`

2. **ไปที่โฟลเดอร์ PHP:**
   - ไปที่แถบ Address bar
   - พิมพ์:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64
     ```
   - กด Enter

3. **หาไฟล์ php.ini:**
   - ในโฟลเดอร์นี้จะมีไฟล์ `php.ini`
   - คลิกขวาที่ไฟล์ `php.ini`
   - เลือก **Open with** → **Notepad**
   - (หรือเลือก **Choose another app** → **Notepad**)

4. **เพิ่ม extension:**
   - กด `Ctrl + End` เพื่อไปท้ายไฟล์
   - หรือเลื่อน scroll bar ลงไปท้ายสุด
   - เพิ่มบรรทัด 2 บรรทัดนี้:
     ```ini
     extension=php_sqlsrv_83_ts_x64
     extension=php_pdo_sqlsrv_83_ts_x64
     ```
   - **สำคัญ:** ตรวจสอบว่าไม่มี `;` นำหน้า

5. **บันทึกไฟล์:**
   - กด `Ctrl + S`
   - หรือคลิก **File** → **Save**
   - ปิด Notepad

---

### วิธีที่ 2: ใช้ Laragon

1. **เปิด Laragon:**
   - คลิกขวาที่ไอคอน Laragon (มุมล่างขวา)

2. **เปิด php.ini:**
   - คลิก **Menu** → **PHP** → **php.ini**
   - จะเปิด php.ini ด้วยโปรแกรมที่ตั้งค่าไว้

3. **เพิ่ม extension:**
   - ไปท้ายไฟล์
   - เพิ่มบรรทัด 2 บรรทัด:
     ```ini
     extension=php_sqlsrv_83_ts_x64
     extension=php_pdo_sqlsrv_83_ts_x64
     ```

4. **บันทึก:**
   - กด `Ctrl + S`

---

## 🎯 ขั้นตอนที่ 3: รีสตาร์ท Laragon

1. **หยุด Laragon:**
   - คลิกขวาที่ไอคอน Laragon (มุมล่างขวา)
   - เลือก **Stop All**
   - รอสักครู่จน Laragon หยุดทำงาน

2. **เริ่ม Laragon ใหม่:**
   - คลิกขวาที่ไอคอน Laragon อีกครั้ง
   - เลือก **Start All**
   - รอจน Laragon เริ่มทำงาน (ไอคอนจะเป็นสีเขียว)

---

## ✅ ตรวจสอบผลลัพธ์

1. **เปิดเบราว์เซอร์:**
   - เปิด Chrome, Edge, หรือ Firefox

2. **ไปที่:**
   ```
   http://workinternetnindam.test/api/check_sqlsrv.php
   ```

3. **ตรวจสอบ:**
   - ควรเห็น: ✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว
   - ควรเห็น: ✓ PDO SQL Server Driver ติดตั้งแล้ว
   - ควรเห็น: ✓ เชื่อมต่อ SQL Server สำเร็จ!

---

## 🖼️ ภาพตัวอย่าง

### ตัวอย่างโฟลเดอร์ ext:
```
C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\
├── php_bz2.dll
├── php_curl.dll
├── php_mbstring.dll
├── php_sqlsrv_83_ts_x64.dll      ← ไฟล์นี้ (ต้องมี)
├── php_pdo_sqlsrv_83_ts_x64.dll  ← ไฟล์นี้ (ต้องมี)
└── ... (ไฟล์อื่นๆ)
```

### ตัวอย่าง php.ini (ท้ายไฟล์):
```ini
; ... (โค้ดอื่นๆ)

; SQL Server Driver
extension=php_sqlsrv_83_ts_x64
extension=php_pdo_sqlsrv_83_ts_x64
```

---

## ⚠️ ข้อควรระวัง

1. **ชื่อไฟล์ต้องถูกต้อง:**
   - ต้องเป็น `php_sqlsrv_83_ts_x64.dll` (ไม่ใช่ `83_nts` หรือ `82_ts`)
   - ต้องเป็น `php_pdo_sqlsrv_83_ts_x64.dll`

2. **ไม่มี `;` นำหน้า:**
   - ❌ ผิด: `;extension=php_sqlsrv_83_ts_x64`
   - ✅ ถูก: `extension=php_sqlsrv_83_ts_x64`

3. **ต้องรีสตาร์ท Laragon:**
   - หลังจากแก้ไข php.ini ต้องรีสตาร์ท Laragon เสมอ

---

## 🐛 ถ้ายังไม่ได้

1. **ตรวจสอบว่าไฟล์อยู่ในโฟลเดอร์ ext:**
   - ไปที่ `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\`
   - ตรวจสอบว่ามีไฟล์ 2 ไฟล์อยู่

2. **ตรวจสอบ php.ini:**
   - เปิด php.ini อีกครั้ง
   - ตรวจสอบว่ามี extension 2 บรรทัด (ไม่มี `;` นำหน้า)

3. **รีสตาร์ท Laragon อีกครั้ง:**
   - Stop All → Start All

4. **ตรวจสอบ error:**
   - เปิด `http://workinternetnindam.test/api/check_sqlsrv.php`
   - ดู error message ที่แสดง

---

**พร้อมแล้ว!** ทำตามขั้นตอนนี้ทีละขั้นตอนนะครับ 🚀

