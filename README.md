# 🛍️ Modern E-Commerce System

ระบบ E-Commerce สมัยใหม่ที่พัฒนาด้วย PHP และ JavaScript

## 📁 โครงสร้างโปรเจกต์

```
workinternetnindam/
├── index.html          # หน้าหลักของเว็บไซต์
├── assets/             # ไฟล์ Frontend
│   ├── css/
│   │   └── style.css   # สไตล์ CSS
│   └── js/             # JavaScript Files
│       ├── config.js   # การตั้งค่า API
│       ├── app.js      # ควบคุมแอปพลิเคชันหลัก
│       ├── auth.js     # ระบบ Login/Register
│       ├── products.js # จัดการสินค้า
│       ├── cart.js     # จัดการตะกร้าสินค้า
│       ├── admin.js    # จัดการสินค้า (Admin)
│       └── profile.js  # โปรไฟล์ผู้ใช้
├── api/                # Backend API
│   ├── db.php          # การเชื่อมต่อฐานข้อมูล
│   ├── config.php      # การตั้งค่าฐานข้อมูล
│   ├── base_url.php    # Base URL Configuration
│   ├── login.php       # API Login
│   ├── register.php    # API Register
│   ├── getProducts.php # API ดึงสินค้า
│   ├── cartApi.php     # API ตะกร้าสินค้า
│   └── ...             # API อื่นๆ
├── uploads/           # ไฟล์ที่อัปโหลด
└── README.md           # คู่มือนี้
```

## 🚀 วิธีติดตั้งและใช้งาน

### วิธีที่ 1: ใช้ Laragon (แนะนำ)

1. **ดาวน์โหลด Laragon**
   - ไปที่: https://laragon.org/
   - ดาวน์โหลดและติดตั้ง

2. **คัดลอกไฟล์**
   - คัดลอกโฟลเดอร์ `workinternetnindam` ไปยัง:
   ```
   C:\laragon\www\workinternetnindam
   ```

3. **เริ่ม Laragon**
   - เปิด Laragon
   - Laragon จะเริ่ม Apache อัตโนมัติ

4. **เปิดเว็บไซต์**
   - ไปที่: `http://localhost/workinternetnindam`
   - หรือ: `http://workinternetnindam.test`

### วิธีที่ 2: ใช้ XAMPP

1. **ดาวน์โหลด XAMPP**
   - ไปที่: https://www.apachefriends.org/

2. **คัดลอกไฟล์**
   - คัดลอกโฟลเดอร์ `workinternetnindam` ไปยัง:
   ```
   C:\xampp\htdocs\workinternetnindam
   ```

3. **เริ่ม Apache**
   - เปิด XAMPP Control Panel
   - คลิก Start ที่ Apache

4. **เปิดเว็บไซต์**
   - ไปที่: `http://localhost/workinternetnindam`

## ⚙️ การตั้งค่า

### 1. ตั้งค่า Frontend (assets/js/config.js)

```javascript
const API_CONFIG = {
    baseUrl: 'http://localhost/workinternetnindam/api/',
    // หรือ
    // baseUrl: 'http://workinternetnindam.test/api/', // สำหรับ Laragon
};
```

### 2. ตั้งค่าฐานข้อมูล (api/config.php)

```php
$serverName = "DESKTOP-S0OP3KB"; // ชื่อ SQL Server
$connectionInfo = array(
    "Database" => "pawarit",
    "UID" => "sa",
    "PWD" => "1234",
    "CharacterSet" => "UTF-8"
);
```

### 3. ตั้งค่า Base URL (api/base_url.php)

ไฟล์นี้จะตรวจสอบอัตโนมัติว่าเป็น localhost หรือ production
- Localhost: `http://localhost/workinternetnindam/`
- Production: `http://nindam.sytes.net/...`

## 📋 ฟีเจอร์

- ✅ ระบบ Login/Register
- ✅ แสดงสินค้า
- ✅ ค้นหาสินค้า
- ✅ ตะกร้าสินค้า
- ✅ จัดการสินค้า (Admin)
- ✅ โปรไฟล์ผู้ใช้
- ✅ อัปโหลดรูปภาพ
- ✅ Responsive Design

## 🔧 ข้อกำหนดระบบ

- PHP 7.4 ขึ้นไป
- SQL Server (SQL Server Management Studio)
- SQL Server Driver for PHP (sqlsrv)
- Web Server (Apache/Nginx) หรือ Laragon/XAMPP

## 📖 เอกสารเพิ่มเติม

- **LARAGON_SETUP.md** - คู่มือการใช้งาน Laragon แบบละเอียด
- **api/check_sqlsrv.php** - ตรวจสอบการติดตั้ง SQL Server Driver

## 🐛 แก้ไขปัญหา

### ปัญหา: "Database connection failed"
- ตรวจสอบว่า SQL Server ทำงานอยู่
- ตรวจสอบ username/password ใน `api/config.php`
- ตรวจสอบว่า SQL Server Driver ติดตั้งแล้ว

### ปัญหา: "CORS Error"
- ตรวจสอบว่า API มี CORS headers
- ตรวจสอบว่า API URL ถูกต้อง

### ปัญหา: "404 Not Found"
- ตรวจสอบว่าไฟล์อยู่ในตำแหน่งที่ถูกต้อง
- ตรวจสอบว่า Apache ทำงานอยู่

## 📞 ติดต่อ

สำหรับคำถามหรือปัญหา กรุณาตรวจสอบเอกสารหรือดูในโค้ด

---

**พัฒนาโดย:** Modern E-Commerce Team  
**เวอร์ชัน:** 1.0.0

