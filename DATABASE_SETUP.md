# Database Setup Guide

## Tables ที่ต้องสร้าง

### 1. Tables ที่มีอยู่แล้ว
- `users` - ข้อมูลผู้ใช้
- `products` - ข้อมูลสินค้า
- `cart` - ตะกร้าสินค้า

### 2. Tables ใหม่ที่ต้องสร้าง

#### stores (ร้านค้า)
- `id` (INT, PRIMARY KEY, IDENTITY)
- `user_id` (INT, FOREIGN KEY -> users.id)
- `store_name` (NVARCHAR(255))
- `store_description` (NVARCHAR(MAX))
- `store_address` (NVARCHAR(500))
- `store_phone` (NVARCHAR(50))
- `store_email` (NVARCHAR(255))
- `is_active` (BIT, DEFAULT 1)
- `created_at` (DATETIME, DEFAULT GETDATE())
- `updated_at` (DATETIME, DEFAULT GETDATE())

#### orders (คำสั่งซื้อ)
- `id` (INT, PRIMARY KEY, IDENTITY)
- `user_id` (INT, FOREIGN KEY -> users.id)
- `order_number` (NVARCHAR(50), UNIQUE)
- `total_amount` (DECIMAL(10,2))
- `shipping_fee` (DECIMAL(10,2), DEFAULT 50.00)
- `shipping_name` (NVARCHAR(255))
- `shipping_phone` (NVARCHAR(50))
- `shipping_address` (NVARCHAR(500))
- `shipping_province` (NVARCHAR(100))
- `shipping_postcode` (NVARCHAR(10))
- `payment_method` (NVARCHAR(50), DEFAULT 'cod')
- `payment_status` (NVARCHAR(50), DEFAULT 'pending')
- `order_status` (NVARCHAR(50), DEFAULT 'pending')
- `notes` (NVARCHAR(MAX))
- `created_at` (DATETIME, DEFAULT GETDATE())
- `updated_at` (DATETIME, DEFAULT GETDATE())

#### order_items (รายการสินค้าในคำสั่งซื้อ)
- `id` (INT, PRIMARY KEY, IDENTITY)
- `order_id` (INT, FOREIGN KEY -> orders.id)
- `product_id` (INT, FOREIGN KEY -> products.id)
- `product_name` (NVARCHAR(255))
- `product_price` (DECIMAL(10,2))
- `quantity` (INT)
- `subtotal` (DECIMAL(10,2))
- `created_at` (DATETIME, DEFAULT GETDATE())

### 3. Columns ที่ต้องเพิ่มใน products table
- `stock_quantity` (INT, DEFAULT 0) - จำนวนสินค้าคงเหลือ
- `store_id` (INT, NULL, FOREIGN KEY -> stores.id) - ร้านค้าที่เป็นเจ้าของสินค้า

## วิธีสร้าง Tables

### วิธีที่ 1: ใช้ SQL Script
1. เปิดไฟล์ `api/create_new_tables.sql` ใน SQL Server Management Studio
2. รัน script ทั้งหมด

### วิธีที่ 2: ใช้ setup_database.php
1. เปิด `http://localhost/workinternetnindam/api/setup_database.php` ใน browser
2. ระบบจะสร้าง tables อัตโนมัติ

## สถานะคำสั่งซื้อ (Order Status)
- `pending` - รอการตรวจสอบ
- `processing` - กำลังจัดเตรียม
- `shipping` - กำลังจัดส่ง
- `completed` - สำเร็จ
- `cancelled` - ยกเลิก

## สถานะการชำระเงิน (Payment Status)
- `pending` - รอการชำระเงิน
- `paid` - ชำระเงินแล้ว
- `failed` - ชำระเงินไม่สำเร็จ

## หมายเหตุ
- หลังจากสร้าง tables แล้ว ให้ตรวจสอบว่า columns ทั้งหมดถูกสร้างครบ
- ตรวจสอบ foreign keys ว่าถูกต้อง
- ตรวจสอบ indexes สำหรับ performance

