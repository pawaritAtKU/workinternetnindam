# ✅ ขั้นตอนสุดท้าย: เชื่อมต่อ SQL Server

## ✅ สิ่งที่ทำเสร็จแล้ว:
- ✅ เพิ่ม extension ใน php.ini แล้ว
- ✅ ODBC Driver 18 ติดตั้งแล้ว

---

## 🔄 ขั้นตอนที่ 1: ตรวจสอบไฟล์ DLL

**ต้องมีไฟล์ 2 ไฟล์ในโฟลเดอร์ ext:**

1. เปิด File Explorer
2. ไปที่: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\`
3. ตรวจสอบว่ามีไฟล์:
   - `php_sqlsrv_83_ts_x64.dll` ✅
   - `php_pdo_sqlsrv_83_ts_x64.dll` ✅

**ถ้ายังไม่มี:**
- ต้องคัดลอกไฟล์จากโฟลเดอร์ที่แตกไฟล์ SQLSRV83
- วางในโฟลเดอร์ ext

---

## 🔄 ขั้นตอนที่ 2: รีสตาร์ท Laragon

**สำคัญมาก!** ต้องรีสตาร์ท Laragon หลังจากแก้ไข php.ini

1. **หยุด Laragon:**
   - คลิกขวาที่ไอคอน Laragon (มุมล่างขวา)
   - เลือก **Stop All**
   - รอจน Laragon หยุดทำงาน

2. **เริ่ม Laragon ใหม่:**
   - คลิกขวาที่ Laragon อีกครั้ง
   - เลือก **Start All**
   - รอจน Laragon เริ่มทำงาน (ไอคอนเป็นสีเขียว)

---

## ✅ ขั้นตอนที่ 3: ทดสอบการเชื่อมต่อ

1. **เปิดเบราว์เซอร์:**
   - เปิด Chrome, Edge, หรือ Firefox

2. **ไปที่:**
   ```
   http://workinternetnindam.test/api/check_sqlsrv.php
   ```

3. **ตรวจสอบผลลัพธ์:**

   **ถ้าเห็น:**
   - ✅ ✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว
   - ✅ ✓ PDO SQL Server Driver ติดตั้งแล้ว
   - ✅ ✓ เชื่อมต่อ SQL Server สำเร็จ!
   
   **= เชื่อมต่อได้แล้ว! 🎉**

   **ถ้ายังเห็น:**
   - ✗ SQL Server Driver ยังไม่ได้ติดตั้ง
   
   **= ยังเชื่อมต่อไม่ได้** (ดูข้อผิดพลาดด้านล่าง)

---

## 🐛 แก้ไขปัญหา

### ปัญหา: "SQL Server Driver ยังไม่ได้ติดตั้ง"

**ตรวจสอบ:**

1. **ไฟล์ DLL อยู่ใน ext แล้วหรือยัง?**
   - ไปที่: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\`
   - ตรวจสอบว่ามีไฟล์ 2 ไฟล์

2. **รีสตาร์ท Laragon แล้วหรือยัง?**
   - ต้อง Stop All แล้ว Start All

3. **ชื่อไฟล์ถูกต้องหรือไม่?**
   - ต้องเป็น: `php_sqlsrv_83_ts_x64.dll` (ไม่ใช่ `82` หรือ `nts`)

4. **extension ใน php.ini ถูกต้องหรือไม่?**
   - ต้องไม่มี `;` นำหน้า
   - ต้องเป็น: `extension=php_sqlsrv_83_ts_x64`

---

### ปัญหา: "DB Connection failed"

**ตรวจสอบ:**

1. **SQL Server ทำงานอยู่หรือไม่?**
   - เปิด SQL Server Management Studio
   - เชื่อมต่อกับ `DESKTOP-S0OP3KB`
   - ถ้าเชื่อมต่อได้ = SQL Server ทำงานอยู่

2. **การตั้งค่าใน db.php ถูกต้องหรือไม่?**
   - Server: `DESKTOP-S0OP3KB`
   - Database: `pawarit`
   - Username: `sa`
   - Password: `1234`

3. **SQL Server Authentication เปิดอยู่หรือไม่?**
   - เปิด SQL Server Management Studio
   - คลิกขวาที่ Server → Properties → Security
   - ตรวจสอบว่า "SQL Server and Windows Authentication mode" ถูกเลือก

---

## 📋 Checklist สุดท้าย

- [ ] ไฟล์ DLL อยู่ในโฟลเดอร์ ext แล้ว
- [ ] รีสตาร์ท Laragon แล้ว (Stop All → Start All)
- [ ] ทดสอบที่ check_sqlsrv.php
- [ ] SQL Server ทำงานอยู่
- [ ] การตั้งค่าใน db.php ถูกต้อง

---

## 🎉 เสร็จสิ้น!

หลังจากทำตามขั้นตอนทั้งหมดแล้ว:
- ✅ PHP จะเชื่อมต่อกับ SQL Server ได้
- ✅ เว็บไซต์จะทำงานกับฐานข้อมูลได้ปกติ
- ✅ API ทั้งหมดจะทำงานได้

---

**ถ้ายังมีปัญหา:** บอก error message ที่เห็นใน check_sqlsrv.php มาได้เลยครับ

