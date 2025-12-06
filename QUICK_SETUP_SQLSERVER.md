# 🚀 คู่มือเชื่อมต่อ SQL Server Management Studio กับ PHP (แบบง่าย)

## ✅ สิ่งที่คุณมีแล้ว
- ✅ SQL Server Management Studio (SSMS)
- ✅ Laragon พร้อม PHP 8.3.26
- ✅ เว็บไซต์ทำงานที่ `http://workinternetnindam.test`

## 🎯 สิ่งที่ต้องทำ (3 ขั้นตอน)

---

### 📥 ขั้นตอนที่ 1: ดาวน์โหลดและติดตั้ง ODBC Driver

**ทำไมต้องติดตั้ง:** PHP ต้องใช้ ODBC Driver เพื่อเชื่อมต่อกับ SQL Server

1. **ดาวน์โหลด ODBC Driver 18 for SQL Server:**
   - ไปที่: https://go.microsoft.com/fwlink/?linkid=2249004
   - หรือ: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
   - เลือก: **ODBC Driver 18 for SQL Server** (Windows)

2. **ติดตั้ง:**
   - ดับเบิลคลิกไฟล์ที่ดาวน์โหลดมา
   - ทำตามขั้นตอนการติดตั้ง (Next → Next → Install)

3. **ตรวจสอบ:**
   - เปิด Command Prompt
   - พิมพ์: `odbcinst -q -d`
   - ควรเห็น "ODBC Driver 18 for SQL Server"

---

### 📦 ขั้นตอนที่ 2: ดาวน์โหลดและติดตั้ง PHP Driver

**ทำไมต้องติดตั้ง:** PHP ต้องมี extension เพื่อใช้ SQL Server

1. **ดาวน์โหลด Microsoft Drivers for PHP for SQL Server:**
   - ไปที่: https://github.com/Microsoft/msphpsql/releases
   - หรือ: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
   - เลือก: **SQLSRV83.exe** หรือ **SQLSRV83.zip** (สำหรับ PHP 8.3)

2. **ติดตั้ง/แตกไฟล์:**
   - ถ้าเป็นไฟล์ `.exe` → ดับเบิลคลิกเพื่อติดตั้ง
   - ถ้าเป็นไฟล์ `.zip` → แตกไฟล์

3. **คัดลอกไฟล์ DLL:**
   - ไปที่โฟลเดอร์ที่แตกไฟล์
   - เข้าไปในโฟลเดอร์ `x64` (สำหรับ 64-bit)
   - หาไฟล์ 2 ไฟล์:
     - `php_sqlsrv_83_ts_x64.dll`
     - `php_pdo_sqlsrv_83_ts_x64.dll`
   - **คัดลอก** ไฟล์ทั้ง 2 ไฟล์
   - **วาง** ในโฟลเดอร์:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\
     ```

---

### ⚙️ ขั้นตอนที่ 3: แก้ไข php.ini และรีสตาร์ท

1. **เปิดไฟล์ php.ini:**
   - ไปที่: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`
   - คลิกขวา → **Open with** → **Notepad**

2. **เพิ่ม extension:**
   - เลื่อนไปท้ายไฟล์ หรือหาส่วน `[Extension]`
   - เพิ่มบรรทัด 2 บรรทัดนี้:
     ```ini
     extension=php_sqlsrv_83_ts_x64
     extension=php_pdo_sqlsrv_83_ts_x64
     ```
   - **สำคัญ:** ตรวจสอบว่าไม่มี `;` นำหน้า

3. **บันทึกไฟล์:**
   - กด `Ctrl + S`
   - ปิด Notepad

4. **รีสตาร์ท Laragon:**
   - คลิกขวาที่ไอคอน Laragon (มุมล่างขวา)
   - เลือก **Stop All**
   - รอสักครู่
   - คลิกขวาที่ Laragon อีกครั้ง
   - เลือก **Start All**

---

## ✅ ตรวจสอบการติดตั้ง

1. **เปิดเบราว์เซอร์:**
   - ไปที่: `http://workinternetnindam.test/api/check_sqlsrv.php`

2. **ตรวจสอบผลลัพธ์:**
   - ควรเห็น: ✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว
   - ควรเห็น: ✓ PDO SQL Server Driver ติดตั้งแล้ว
   - ควรเห็น: ✓ เชื่อมต่อ SQL Server สำเร็จ!

---

## 🔍 ตรวจสอบ SQL Server ทำงานอยู่หรือไม่

1. **เปิด SQL Server Management Studio:**
   - เปิด SSMS
   - พยายามเชื่อมต่อกับ Server: `DESKTOP-S0OP3KB`
   - ถ้าเชื่อมต่อได้ แสดงว่า SQL Server ทำงานอยู่ ✅

2. **ตรวจสอบ Service:**
   - กด `Win + R`
   - พิมพ์: `services.msc`
   - หา: **SQL Server (MSSQLSERVER)** หรือ **SQL Server (SQLEXPRESS)**
   - ตรวจสอบว่า Status = **Running**

---

## 🐛 แก้ไขปัญหา

### ❌ "SQL Server Driver ยังไม่ได้ติดตั้ง"
**วิธีแก้:**
- ตรวจสอบว่าไฟล์ DLL อยู่ในโฟลเดอร์ `ext` แล้ว
- ตรวจสอบว่าเพิ่ม extension ใน php.ini แล้ว
- รีสตาร์ท Laragon

### ❌ "Unable to load dynamic library"
**วิธีแก้:**
- ตรวจสอบว่า ODBC Driver ติดตั้งแล้ว
- ตรวจสอบชื่อไฟล์ DLL ว่าถูกต้อง (ต้องมี `83_ts_x64`)

### ❌ "DB Connection failed"
**วิธีแก้:**
- ตรวจสอบว่า SQL Server ทำงานอยู่
- ตรวจสอบการตั้งค่าใน `api/db.php`:
  - Server name: `DESKTOP-S0OP3KB`
  - Database: `pawarit`
  - Username: `sa`
  - Password: `1234`
- ตรวจสอบว่า SQL Server อนุญาตให้เชื่อมต่อได้ (SQL Server Authentication)

---

## 📋 Checklist

ทำตามขั้นตอนนี้ทีละข้อ:

- [ ] ติดตั้ง ODBC Driver 18 for SQL Server
- [ ] ดาวน์โหลด Microsoft Drivers for PHP for SQL Server
- [ ] คัดลอก `php_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] คัดลอก `php_pdo_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] เพิ่ม extension ใน php.ini
- [ ] รีสตาร์ท Laragon
- [ ] ตรวจสอบด้วย `check_sqlsrv.php`
- [ ] ทดสอบเชื่อมต่อฐานข้อมูล

---

## 🎉 เสร็จสิ้น!

หลังจากทำตามขั้นตอนทั้งหมดแล้ว:
- ✅ PHP จะเชื่อมต่อกับ SQL Server ได้
- ✅ เว็บไซต์จะทำงานกับฐานข้อมูลได้ปกติ
- ✅ API ทั้งหมดจะทำงานได้

---

## 📞 ต้องการความช่วยเหลือ?

1. **รันสคริปต์ตรวจสอบ:**
   - ดับเบิลคลิก `CHECK_SQL_SERVER.bat`
   - จะแสดงสถานะและขั้นตอนที่ต้องทำ

2. **อ่านคู่มือแบบละเอียด:**
   - เปิดไฟล์ `INSTALL_SQLSERVER.md`

3. **ตรวจสอบ error:**
   - เปิด `http://workinternetnindam.test/api/check_sqlsrv.php`
   - ดู error message ที่แสดง

---

**หมายเหตุ:** หาก SQL Server ใช้ชื่อ server หรือ instance อื่น ให้แก้ไขใน `api/db.php`


