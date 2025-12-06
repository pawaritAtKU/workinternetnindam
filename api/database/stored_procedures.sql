-- =============================================
-- Stored Procedures สำหรับระบบ E-Commerce
-- =============================================

-- 1. Stored Procedure: เพิ่มสินค้าใหม่พร้อมตรวจสอบข้อมูล
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'sp_add_product' AND type = 'P')
    DROP PROCEDURE sp_add_product;
GO

CREATE PROCEDURE sp_add_product
    @name NVARCHAR(255),
    @price DECIMAL(10,2),
    @category_id NVARCHAR(50),
    @description NVARCHAR(MAX) = NULL,
    @stock_quantity INT = 0,
    @image_url NVARCHAR(500) = NULL,
    @store_id INT = NULL,
    @product_id INT OUTPUT,
    @error_message NVARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        -- ตรวจสอบข้อมูลที่จำเป็น
        IF @name IS NULL OR LEN(LTRIM(RTRIM(@name))) = 0
        BEGIN
            SET @error_message = 'กรุณากรอกชื่อสินค้า';
            SET @product_id = -1;
            RETURN;
        END
        
        IF @price IS NULL OR @price <= 0
        BEGIN
            SET @error_message = 'ราคาต้องมากกว่า 0';
            SET @product_id = -1;
            RETURN;
        END
        
        IF @stock_quantity < 0
        BEGIN
            SET @error_message = 'จำนวนสินค้าต้องมากกว่าหรือเท่ากับ 0';
            SET @product_id = -1;
            RETURN;
        END
        
        -- เพิ่มสินค้า
        INSERT INTO products (name, price, category_id, description, stock_quantity, image_url, store_id)
        VALUES (@name, @price, @category_id, @description, @stock_quantity, @image_url, @store_id);
        
        -- ดึง ID ที่สร้างขึ้น
        SET @product_id = SCOPE_IDENTITY();
        SET @error_message = 'เพิ่มสินค้าสำเร็จ';
        
    END TRY
    BEGIN CATCH
        SET @error_message = ERROR_MESSAGE();
        SET @product_id = -1;
    END CATCH
END;
GO

PRINT 'Stored Procedure sp_add_product created successfully';
GO

-- 2. Stored Procedure: สร้างคำสั่งซื้อพร้อม order items
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'sp_create_order' AND type = 'P')
    DROP PROCEDURE sp_create_order;
GO

CREATE PROCEDURE sp_create_order
    @user_id INT,
    @shipping_name NVARCHAR(255),
    @shipping_phone NVARCHAR(50),
    @shipping_address NVARCHAR(500),
    @shipping_province NVARCHAR(100),
    @shipping_postcode NVARCHAR(10),
    @payment_method NVARCHAR(50) = 'cod',
    @notes NVARCHAR(MAX) = NULL,
    @order_id INT OUTPUT,
    @order_number NVARCHAR(50) OUTPUT,
    @error_message NVARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- สร้าง order number
        DECLARE @order_count INT;
        SELECT @order_count = COUNT(*) FROM orders WHERE user_id = @user_id;
        SET @order_number = 'ORD' + FORMAT(GETDATE(), 'yyyyMMdd') + '-' + RIGHT('0000' + CAST((@order_count + 1) AS NVARCHAR), 4);
        
        -- สร้าง order
        INSERT INTO orders (
            user_id, order_number, total_amount, shipping_fee,
            shipping_name, shipping_phone, shipping_address,
            shipping_province, shipping_postcode, payment_method, notes
        )
        VALUES (
            @user_id, @order_number, 0, 50.00,
            @shipping_name, @shipping_phone, @shipping_address,
            @shipping_province, @shipping_postcode, @payment_method, @notes
        );
        
        SET @order_id = SCOPE_IDENTITY();
        
        -- คำนวณ total_amount จาก order_items (จะถูกอัปเดตหลังจากเพิ่ม items)
        -- สำหรับตอนนี้ตั้งเป็น 0 ก่อน
        
        COMMIT TRANSACTION;
        SET @error_message = 'สร้างคำสั่งซื้อสำเร็จ';
        
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        SET @error_message = ERROR_MESSAGE();
        SET @order_id = -1;
        SET @order_number = NULL;
    END CATCH
END;
GO

PRINT 'Stored Procedure sp_create_order created successfully';
GO

-- 3. Stored Procedure: เพิ่ม order item และอัปเดต total_amount
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'sp_add_order_item' AND type = 'P')
    DROP PROCEDURE sp_add_order_item;
GO

CREATE PROCEDURE sp_add_order_item
    @order_id INT,
    @product_id INT,
    @quantity INT,
    @item_id INT OUTPUT,
    @error_message NVARCHAR(500) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- ตรวจสอบ stock
        DECLARE @current_stock INT;
        DECLARE @product_name NVARCHAR(255);
        DECLARE @product_price DECIMAL(10,2);
        
        SELECT 
            @current_stock = stock_quantity,
            @product_name = name,
            @product_price = price
        FROM products
        WHERE id = @product_id;
        
        IF @current_stock IS NULL
        BEGIN
            SET @error_message = 'ไม่พบสินค้า';
            SET @item_id = -1;
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        IF @current_stock < @quantity
        BEGIN
            SET @error_message = 'สินค้า ' + @product_name + ' มีจำนวนไม่พอ (คงเหลือ: ' + CAST(@current_stock AS NVARCHAR) + ')';
            SET @item_id = -1;
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        -- คำนวณ subtotal
        DECLARE @subtotal DECIMAL(10,2);
        SET @subtotal = @product_price * @quantity;
        
        -- เพิ่ม order item
        INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
        VALUES (@order_id, @product_id, @product_name, @product_price, @quantity, @subtotal);
        
        SET @item_id = SCOPE_IDENTITY();
        
        -- ลด stock (trigger จะทำอัตโนมัติ แต่เราทำซ้ำเพื่อความแน่ใจ)
        UPDATE products
        SET stock_quantity = stock_quantity - @quantity
        WHERE id = @product_id;
        
        -- อัปเดต total_amount ใน orders
        DECLARE @new_total DECIMAL(10,2);
        SELECT @new_total = SUM(subtotal)
        FROM order_items
        WHERE order_id = @order_id;
        
        UPDATE orders
        SET total_amount = @new_total,
            updated_at = GETDATE()
        WHERE id = @order_id;
        
        COMMIT TRANSACTION;
        SET @error_message = 'เพิ่มรายการสินค้าสำเร็จ';
        
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        SET @error_message = ERROR_MESSAGE();
        SET @item_id = -1;
    END CATCH
END;
GO

PRINT 'Stored Procedure sp_add_order_item created successfully';
GO

-- ตัวอย่างการใช้งาน Stored Procedures:

-- 1. เพิ่มสินค้า
/*
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
*/

-- 2. สร้างคำสั่งซื้อ
/*
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
*/

-- 3. เพิ่ม order item
/*
DECLARE @item_id INT, @error_msg NVARCHAR(500);
EXEC sp_add_order_item
    @order_id = 1,
    @product_id = 1,
    @quantity = 2,
    @item_id = @item_id OUTPUT,
    @error_message = @error_msg OUTPUT;
SELECT @item_id as ItemID, @error_msg as Message;
*/

PRINT 'All stored procedures created successfully!';
GO

