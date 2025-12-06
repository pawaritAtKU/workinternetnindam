# Database Scripts สำหรับระบบ E-Commerce

ไฟล์ SQL สำหรับสร้าง Views, Triggers, Functions และข้อมูลตัวอย่าง

## ไฟล์ที่สร้าง

### 1. views.sql
สร้าง 3 Views:
- **vw_products_with_details** - สินค้าพร้อมข้อมูลหมวดหมู่และร้านค้า
- **vw_order_summary** - สรุปคำสั่งซื้อพร้อมข้อมูลผู้ใช้
- **vw_user_products_stats** - สินค้าของผู้ใช้พร้อมสถิติการขาย

### 2. triggers.sql
สร้าง 3 Triggers:
- **trg_products_updated_at** - อัปเดต updated_at อัตโนมัติเมื่อแก้ไขสินค้า
- **trg_order_items_update_stock** - ลด stock อัตโนมัติเมื่อเพิ่ม order_items และตรวจสอบ stock
- **trg_products_audit** - บันทึก audit log เมื่อมีการแก้ไขสินค้า (สร้างตาราง product_audit_log ด้วย)

### 3. functions.sql
สร้าง 3 Functions:
- **fn_get_product_stock(@product_id)** - ตรวจสอบ stock ของสินค้า
- **fn_calculate_order_total(@order_id)** - คำนวณยอดรวมของคำสั่งซื้อ
- **fn_get_user_order_count(@user_id, @order_status)** - นับจำนวนคำสั่งซื้อของผู้ใช้

### 4. stored_procedures.sql
สร้าง 3 Stored Procedures:
- **sp_add_product** - เพิ่มสินค้าใหม่พร้อมตรวจสอบข้อมูล
- **sp_create_order** - สร้างคำสั่งซื้อพร้อม order items
- **sp_add_order_item** - เพิ่ม order item และอัปเดต total_amount

### 5. sample_products.sql
สร้าง 3 สินค้าตัวอย่าง:
- iPhone 15 Pro Max 256GB (electronics)
- เสื้อเชิ้ตแขนยาว Premium (clothing)
- หนังสือ Clean Code (books)

## วิธีการใช้งาน

### วิธีที่ 1: รันทีละไฟล์
```sql
-- รัน views
:r api/database/views.sql

-- รัน triggers
:r api/database/triggers.sql

-- รัน functions
:r api/database/functions.sql

-- รัน stored procedures
:r api/database/stored_procedures.sql

-- รัน sample products
:r api/database/sample_products.sql
```

### วิธีที่ 2: รันผ่าน SQL Server Management Studio
1. เปิดไฟล์ SQL แต่ละไฟล์
2. กด F5 หรือ Execute เพื่อรัน

### วิธีที่ 3: รันผ่าน Command Line
```bash
sqlcmd -S server_name -d database_name -i api/database/views.sql
sqlcmd -S server_name -d database_name -i api/database/triggers.sql
sqlcmd -S server_name -d database_name -i api/database/functions.sql
sqlcmd -S server_name -d database_name -i api/database/stored_procedures.sql
sqlcmd -S server_name -d database_name -i api/database/sample_products.sql
```

## ตัวอย่างการใช้งาน Views

```sql
-- ดูสินค้าพร้อมรายละเอียด
SELECT * FROM vw_products_with_details;

-- ดูสรุปคำสั่งซื้อ
SELECT * FROM vw_order_summary WHERE order_status = 'completed';

-- ดูสถิติสินค้าของผู้ใช้
SELECT * FROM vw_user_products_stats WHERE owner_id = 1;
```

## ตัวอย่างการใช้งาน Functions

```sql
-- ตรวจสอบ stock
SELECT dbo.fn_get_product_stock(1) as stock;

-- คำนวณยอดรวมคำสั่งซื้อ
SELECT dbo.fn_calculate_order_total(1) as total;

-- นับจำนวนคำสั่งซื้อ
SELECT dbo.fn_get_user_order_count(1, 'completed') as completed_orders;
```

## ตัวอย่างการใช้งาน Stored Procedures

```sql
-- 1. เพิ่มสินค้า
DECLARE @product_id INT, @error_msg NVARCHAR(500);
EXEC sp_add_product 
    @name = 'สินค้าตัวอย่าง',
    @price = 1000.00,
    @category_id = 'electronics',
    @description = 'รายละเอียดสินค้า',
    @stock_quantity = 50,
    @image_url = 'https://example.com/image.jpg',
    @store_id = 1,
    @product_id = @product_id OUTPUT,
    @error_message = @error_msg OUTPUT;
SELECT @product_id as ProductID, @error_msg as Message;

-- 2. สร้างคำสั่งซื้อ
DECLARE @order_id INT, @order_num NVARCHAR(50), @error_msg NVARCHAR(500);
EXEC sp_create_order
    @user_id = 1,
    @shipping_name = 'ชื่อผู้รับ',
    @shipping_phone = '0812345678',
    @shipping_address = 'ที่อยู่',
    @shipping_province = 'กรุงเทพฯ',
    @shipping_postcode = '10110',
    @payment_method = 'cod',
    @order_id = @order_id OUTPUT,
    @order_number = @order_num OUTPUT,
    @error_message = @error_msg OUTPUT;
SELECT @order_id as OrderID, @order_num as OrderNumber, @error_msg as Message;

-- 3. เพิ่ม order item
DECLARE @item_id INT, @error_msg NVARCHAR(500);
EXEC sp_add_order_item
    @order_id = 1,
    @product_id = 1,
    @quantity = 2,
    @item_id = @item_id OUTPUT,
    @error_message = @error_msg OUTPUT;
SELECT @item_id as ItemID, @error_msg as Message;
```

## หมายเหตุ

- ไฟล์ทั้งหมดใช้ SQL Server syntax
- ควรรัน views.sql, triggers.sql, functions.sql, stored_procedures.sql ก่อน sample_products.sql
- Triggers จะทำงานอัตโนมัติเมื่อมีการ INSERT, UPDATE, DELETE
- Functions ต้องเรียกใช้ด้วย schema name (dbo.fn_...)

