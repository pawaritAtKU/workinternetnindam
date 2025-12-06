# 🚀 คู่มือการใช้งาน Laragon (วิธีที่ 3)

## 📥 ขั้นตอนที่ 1: ดาวน์โหลดและติดตั้ง Laragon

### 1.1 ดาวน์โหลด Laragon
- ไปที่: **https://laragon.org/**
- คลิก **Download** (เลือกเวอร์ชัน Full หรือ Basic)
- **Full** = มี PHP, MySQL, Node.js, Git (แนะนำ)
- **Basic** = มีแค่ PHP และ MySQL

### 1.2 ติดตั้ง Laragon
1. ดับเบิลคลิกไฟล์ที่ดาวน์โหลดมา
2. เลือก **Install Location** (แนะนำ: `C:\laragon`)
3. คลิก **Install**
4. รอให้ติดตั้งเสร็จ

---

## 📁 ขั้นตอนที่ 2: คัดลอกไฟล์โปรเจกต์

### 2.1 หาโฟลเดอร์ www ของ Laragon
- ไปที่: `C:\laragon\www\`
- (หรือโฟลเดอร์ที่คุณติดตั้ง Laragon)

### 2.2 คัดลอกไฟล์
1. **คัดลอกโฟลเดอร์ `workinternetnindam`** ทั้งหมด
2. **วางใน:** `C:\laragon\www\workinternetnindam`

**โครงสร้างควรเป็น:**
```
C:\laragon\www\workinternetnindam\
├── index.html
├── assets\
│   ├── css\
│   └── js\
├── api\
└── uploads\
```

---

## ⚙️ ขั้นตอนที่ 3: เริ่ม Laragon

### 3.1 เปิด Laragon
1. เปิด **Laragon** จาก Desktop หรือ Start Menu
2. Laragon จะเริ่ม **Apache** และ **MySQL** อัตโนมัติ
3. ดูที่ไอคอนใน System Tray (มุมล่างขวา)
   - 🟢 = ทำงาน
   - 🔴 = ไม่ทำงาน

### 3.2 ตรวจสอบว่า Laragon ทำงาน
- ดูที่ Laragon window:
  - **Apache** = ควรเป็นสีเขียว (Running)
  - **MySQL** = ควรเป็นสีเขียว (Running)

---

## 🌐 ขั้นตอนที่ 4: เปิดเว็บไซต์

### 4.1 วิธีที่ 1: ใช้ Auto Virtual Host (แนะนำ)
1. **คลิกขวาที่ Laragon** ใน System Tray
2. เลือก **Web** → **workinternetnindam.test**
3. หรือเปิดเบราว์เซอร์ไปที่: **http://workinternetnindam.test**

### 4.2 วิธีที่ 2: ใช้ localhost
- เปิดเบราว์เซอร์ไปที่: **http://localhost/workinternetnindam**

---

## 🔧 ขั้นตอนที่ 5: แก้ไข Config

### 5.1 แก้ไข Frontend Config
1. เปิดไฟล์: `C:\laragon\www\workinternetnindam\assets\js\config.js`
2. แก้ไขเป็น:
```javascript
const API_CONFIG = {
    // baseUrl: 'http://nindam.sytes.net/std6530251065/workinternetnindam/api/',
    baseUrl: 'http://localhost/workinternetnindam/api/', // สำหรับ Laragon
    // หรือ
    // baseUrl: 'http://workinternetnindam.test/api/', // ถ้าใช้ .test domain
```

### 5.2 แก้ไข Base URL ใน PHP (ถ้าจำเป็น)
- ไฟล์ `api/base_url.php` จะตรวจสอบอัตโนมัติ
- ถ้าใช้ localhost จะใช้ localhost อัตโนมัติ

---

## ✅ ขั้นตอนที่ 6: ทดสอบ

### 6.1 ทดสอบหน้าแรก
- เปิด: **http://localhost/workinternetnindam**
- หรือ: **http://workinternetnindam.test**

### 6.2 ทดสอบ API
- เปิด: **http://localhost/workinternetnindam/api/getProducts.php**
- ควรเห็น JSON response

### 6.3 ทดสอบ SQL Server Driver
- เปิด: **http://localhost/workinternetnindam/api/check_sqlsrv.php**
- ตรวจสอบว่า SQL Server Driver ติดตั้งแล้ว
- **หากยังไม่ได้ติดตั้ง:** ดูคำแนะนำในไฟล์ **INSTALL_SQLSERVER.md**

---

## 🔧 ขั้นตอนที่ 7: ติดตั้ง SQL Server Driver (ถ้ายังไม่ได้ติดตั้ง)

### 7.1 ตรวจสอบสถานะ
- เปิด: **http://workinternetnindam.test/api/check_sqlsrv.php**
- หากเห็น ✗ SQL Server Driver ยังไม่ได้ติดตั้ง ให้ทำตามขั้นตอนต่อไป

### 7.2 ติดตั้งตามคู่มือ
1. **อ่านคู่มือ:** เปิดไฟล์ `INSTALL_SQLSERVER.md`
2. **รันสคริปต์ช่วยตรวจสอบ:** ดับเบิลคลิก `INSTALL_SQLSERVER.bat`
3. **ทำตามขั้นตอนในคู่มือ**

### 7.3 สรุปขั้นตอนหลัก
1. ดาวน์โหลด **Microsoft Drivers for PHP for SQL Server** (PHP 8.3 TS)
2. ติดตั้ง **ODBC Driver 17 หรือ 18 for SQL Server**
3. คัดลอกไฟล์ DLL ไปยังโฟลเดอร์ `ext` ของ PHP
4. แก้ไข `php.ini` เพื่อเพิ่ม extension
5. รีสตาร์ท Laragon

**ดูรายละเอียดเพิ่มเติม:** `INSTALL_SQLSERVER.md`

---

## 🎯 ฟีเจอร์พิเศษของ Laragon

### 1. Auto Virtual Host
- Laragon จะสร้าง `.test` domain อัตโนมัติ
- เช่น: `workinternetnindam.test`
- ไม่ต้องแก้ไข hosts file

### 2. SSL Certificate
- Laragon มี SSL certificate อัตโนมัติ
- ใช้ `https://workinternetnindam.test` ได้เลย

### 3. Database Management
- มี **HeidiSQL** สำหรับจัดการฐานข้อมูล
- คลิกขวาที่ Laragon → **Database** → **HeidiSQL**

### 4. Terminal
- คลิกขวาที่ Laragon → **Terminal**
- เปิด Terminal ในโฟลเดอร์โปรเจกต์

---

## 🔍 แก้ไขปัญหา

### ปัญหา: "Apache ไม่ทำงาน"
**วิธีแก้:**
1. คลิกขวาที่ Laragon → **Stop All**
2. คลิกขวาที่ Laragon → **Start All**
3. หรือ Restart Laragon

### ปัญหา: "Port 80 ถูกใช้งาน"
**วิธีแก้:**
1. คลิกขวาที่ Laragon → **Settings**
2. เปลี่ยน Apache Port เป็น 8080
3. ใช้: `http://localhost:8080/workinternetnindam`

### ปัญหา: "ไม่เห็น .test domain"
**วิธีแก้:**
1. คลิกขวาที่ Laragon → **Menu** → **Tools** → **Quick add**
2. เลือกโฟลเดอร์โปรเจกต์
3. หรือใช้ `http://localhost/workinternetnindam` แทน

### ปัญหา: "404 Not Found"
**วิธีแก้:**
1. ตรวจสอบว่าไฟล์อยู่ใน `C:\laragon\www\workinternetnindam`
2. ตรวจสอบว่า Apache ทำงานอยู่
3. ลองใช้: `http://localhost/workinternetnindam/index.html`

---

## 📝 สรุป URL

### สำหรับ Laragon:
- **หน้าแรก:** `http://localhost/workinternetnindam`
- **หรือ:** `http://workinternetnindam.test`
- **API:** `http://localhost/workinternetnindam/api/`
- **หรือ:** `http://workinternetnindam.test/api/`

---

## 🎉 พร้อมใช้งาน!

หลังจากทำตามขั้นตอนแล้ว คุณสามารถ:
- ✅ เปิดเว็บไซต์ได้ทันที
- ✅ แก้ไขโค้ดแล้วเห็นผลทันที (Auto Reload)
- ✅ ใช้ Database Tools ได้
- ✅ ใช้ Terminal ได้

---

## 💡 เคล็ดลับ

1. **Quick Add:**
   - คลิกขวาที่ Laragon → **Quick add**
   - เลือกโฟลเดอร์โปรเจกต์
   - Laragon จะสร้าง virtual host อัตโนมัติ

2. **Database:**
   - ใช้ HeidiSQL สำหรับจัดการ SQL Server
   - หรือใช้ SQL Server Management Studio

3. **Terminal:**
   - ใช้ Terminal ใน Laragon แทน Command Prompt
   - ทำงานได้เหมือน Linux/Mac

4. **Auto Reload:**
   - Laragon จะ reload อัตโนมัติเมื่อแก้ไขไฟล์
   - ไม่ต้อง refresh หน้าเว็บ

