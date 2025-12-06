# 📦 คู่มือการติดตั้ง SQL Server Driver for PHP ใน Laragon

## 📋 ข้อมูลระบบของคุณ

- **PHP Version:** 8.3.26
- **Architecture:** 64-bit
- **Thread Safety:** Yes (TS)
- **php.ini location:** `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`
- **Extensions folder:** `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext`

---

## 🚀 ขั้นตอนการติดตั้ง

### ขั้นตอนที่ 1: ดาวน์โหลด Microsoft Drivers for PHP for SQL Server

1. **ไปที่เว็บไซต์ Microsoft:**
   - https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
   - หรือดาวน์โหลดโดยตรง: https://github.com/Microsoft/msphpsql/releases

2. **เลือกเวอร์ชันที่ถูกต้อง:**
   - สำหรับ PHP 8.3 Thread Safe (TS) 64-bit
   - ดาวน์โหลดไฟล์: `SQLSRV83.exe` หรือ `SQLSRV83.zip`
   - หรือใช้ direct link: https://github.com/Microsoft/msphpsql/releases/latest

---

### ขั้นตอนที่ 2: ติดตั้ง ODBC Driver for SQL Server

**สำคัญ:** ต้องติดตั้ง ODBC Driver ก่อนติดตั้ง PHP Driver

1. **ดาวน์โหลด ODBC Driver:**
   - ไปที่: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
   - ดาวน์โหลด: **ODBC Driver 18 for SQL Server** (แนะนำ)
   - หรือ **ODBC Driver 17 for SQL Server**

2. **ติดตั้ง ODBC Driver:**
   - ดับเบิลคลิกไฟล์ที่ดาวน์โหลดมา
   - ทำตามขั้นตอนการติดตั้ง
   - ตรวจสอบการติดตั้งด้วยคำสั่ง:
     ```cmd
     odbcinst -q -d
     ```
   - ควรเห็น "ODBC Driver 17 for SQL Server" หรือ "ODBC Driver 18 for SQL Server"

---

### ขั้นตอนที่ 3: แตกไฟล์และคัดลอก PHP Extensions

1. **แตกไฟล์ SQLSRV83:**
   - แตกไฟล์ ZIP หรือรันไฟล์ EXE ที่ดาวน์โหลดมา
   - จะได้โฟลเดอร์ที่มีไฟล์ DLL อยู่ข้างใน

2. **หาไฟล์ที่ต้องการ:**
   - ไปที่โฟลเดอร์ `x64` (สำหรับ 64-bit)
   - หาไฟล์:
     - `php_sqlsrv_83_ts_x64.dll` (สำหรับ sqlsrv)
     - `php_pdo_sqlsrv_83_ts_x64.dll` (สำหรับ pdo_sqlsrv)

3. **คัดลอกไฟล์ไปยังโฟลเดอร์ ext:**
   - คัดลอกไฟล์ทั้ง 2 ไฟล์ไปยัง:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\ext\
     ```
   - **หมายเหตุ:** ตรวจสอบว่าไฟล์ชื่อถูกต้อง (ต้องมี `83_ts_x64`)

---

### ขั้นตอนที่ 4: แก้ไขไฟล์ php.ini

1. **เปิดไฟล์ php.ini:**
   - ไปที่: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`
   - เปิดด้วย Notepad หรือ Text Editor อื่นๆ

2. **เพิ่มบรรทัดต่อไปนี้:**
   - หาส่วน `[Extension]` หรือส่วนท้ายของไฟล์
   - เพิ่มบรรทัด:
     ```ini
     extension=php_sqlsrv_83_ts_x64
     extension=php_pdo_sqlsrv_83_ts_x64
     ```
   - **สำคัญ:** ตรวจสอบว่าไม่มี `;` นำหน้า (ถ้ามีให้ลบออก)

3. **บันทึกไฟล์:**
   - กด `Ctrl + S` เพื่อบันทึก
   - ปิดไฟล์

---

### ขั้นตอนที่ 5: รีสตาร์ท Laragon

1. **หยุด Laragon:**
   - คลิกขวาที่ Laragon ใน System Tray
   - เลือก **Stop All**

2. **เริ่ม Laragon ใหม่:**
   - คลิกขวาที่ Laragon
   - เลือก **Start All**

---

### ขั้นตอนที่ 6: ตรวจสอบการติดตั้ง

1. **เปิดไฟล์ตรวจสอบ:**
   - เปิดเบราว์เซอร์ไปที่: `http://workinternetnindam.test/api/check_sqlsrv.php`
   - หรือ: `http://localhost/workinternetnindam/api/check_sqlsrv.php`

2. **ตรวจสอบผลลัพธ์:**
   - ควรเห็น ✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว
   - ควรเห็น ✓ PDO SQL Server Driver ติดตั้งแล้ว
   - ควรเห็น ✓ เชื่อมต่อ SQL Server สำเร็จ!

---

## 🔍 วิธีตรวจสอบด้วย Command Line

### ตรวจสอบ ODBC Driver:
```cmd
odbcinst -q -d
```

### ตรวจสอบ PHP Extensions:
```cmd
php -m | findstr sqlsrv
```

ควรเห็น:
- `sqlsrv`
- `pdo_sqlsrv`

---

## 🐛 แก้ไขปัญหา

### ปัญหา: "The specified module could not be found"

**สาเหตุ:** ไฟล์ DLL ไม่ถูกต้องหรือชื่อไฟล์ผิด

**วิธีแก้:**
1. ตรวจสอบว่าไฟล์อยู่ในโฟลเดอร์ `ext` แล้ว
2. ตรวจสอบชื่อไฟล์ว่าเป็น `php_sqlsrv_83_ts_x64.dll` (สำหรับ PHP 8.3 TS)
3. ตรวจสอบว่าใช้ Thread Safe (TS) ไม่ใช่ Non-Thread Safe (NTS)

---

### ปัญหา: "Unable to load dynamic library"

**สาเหตุ:** ODBC Driver ยังไม่ได้ติดตั้ง

**วิธีแก้:**
1. ติดตั้ง ODBC Driver 17 หรือ 18 for SQL Server
2. ตรวจสอบด้วยคำสั่ง `odbcinst -q -d`
3. รีสตาร์ท Laragon

---

### ปัญหา: "SQLSTATE[08001] [Microsoft][ODBC Driver 17 for SQL Server]"

**สาเหตุ:** SQL Server ไม่ทำงานหรือเชื่อมต่อไม่ได้

**วิธีแก้:**
1. ตรวจสอบว่า SQL Server Service ทำงานอยู่
2. เปิด SQL Server Configuration Manager
3. ตรวจสอบว่า SQL Server (MSSQLSERVER) หรือ SQL Server Express ทำงานอยู่
4. ตรวจสอบการตั้งค่าใน `api/db.php`:
   - Server name: `DESKTOP-S0OP3KB`
   - Database: `pawarit`
   - Username: `sa`
   - Password: `1234`

---

### ปัญหา: "Extension not found in php.ini"

**วิธีแก้:**
1. ตรวจสอบว่าเพิ่ม extension ใน php.ini แล้ว
2. ตรวจสอบว่าไม่มี `;` นำหน้า extension
3. ตรวจสอบว่าใช้ชื่อไฟล์ที่ถูกต้อง (ต้องตรงกับชื่อไฟล์ DLL)
4. รีสตาร์ท Laragon

---

## 📝 ตรวจสอบ php.ini ที่ถูกต้อง

บางครั้ง PHP อาจใช้ php.ini หลายไฟล์ ตรวจสอบด้วย:

```cmd
php --ini
```

หรือสร้างไฟล์ PHP:
```php
<?php
echo php_ini_loaded_file();
?>
```

---

## 🔗 ลิงก์ดาวน์โหลด

### Microsoft Drivers for PHP for SQL Server:
- **PHP 8.3:** https://github.com/Microsoft/msphpsql/releases
- **Documentation:** https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

### ODBC Driver for SQL Server:
- **ODBC Driver 18:** https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
- **ODBC Driver 17:** https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server

---

## ✅ Checklist การติดตั้ง

- [ ] ดาวน์โหลด Microsoft Drivers for PHP for SQL Server
- [ ] ติดตั้ง ODBC Driver 17 หรือ 18 for SQL Server
- [ ] ตรวจสอบ ODBC Driver ด้วย `odbcinst -q -d`
- [ ] คัดลอก `php_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] คัดลอก `php_pdo_sqlsrv_83_ts_x64.dll` ไปยังโฟลเดอร์ ext
- [ ] เพิ่ม extension ใน php.ini
- [ ] รีสตาร์ท Laragon
- [ ] ตรวจสอบด้วย `http://workinternetnindam.test/api/check_sqlsrv.php`
- [ ] ทดสอบการเชื่อมต่อฐานข้อมูล

---

## 🎉 เสร็จสิ้น!

หลังจากทำตามขั้นตอนทั้งหมดแล้ว คุณควรจะสามารถ:
- ✅ ใช้ SQL Server Driver ใน PHP ได้
- ✅ เชื่อมต่อกับ SQL Server Management Studio ได้
- ✅ ใช้งาน API ทั้งหมดได้ปกติ

---

**หมายเหตุ:** หากยังมีปัญหา กรุณาตรวจสอบ error log ใน Laragon หรือดู error message ใน `check_sqlsrv.php`


