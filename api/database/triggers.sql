-- =============================================
-- Triggers สำหรับระบบ E-Commerce
-- =============================================

-- 1. Trigger: อัปเดต updated_at อัตโนมัติเมื่อแก้ไขสินค้า (ถ้ามี column updated_at)
IF EXISTS (SELECT * FROM sys.triggers WHERE name = 'trg_products_updated_at')
    DROP TRIGGER trg_products_updated_at;
GO

CREATE TRIGGER trg_products_updated_at
ON products
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- ตรวจสอบว่ามี column updated_at หรือไม่
    IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_NAME = 'products' AND COLUMN_NAME = 'updated_at')
    BEGIN
        UPDATE p
        SET updated_at = GETDATE()
        FROM products p
        INNER JOIN inserted i ON p.id = i.id;
    END
END;
GO

PRINT 'Trigger trg_products_updated_at created successfully';
GO

-- 2. Trigger: ลด stock อัตโนมัติเมื่อเพิ่ม order_items และตรวจสอบ stock
IF EXISTS (SELECT * FROM sys.triggers WHERE name = 'trg_order_items_update_stock')
    DROP TRIGGER trg_order_items_update_stock;
GO

CREATE TRIGGER trg_order_items_update_stock
ON order_items
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @product_id INT;
    DECLARE @quantity INT;
    DECLARE @current_stock INT;
    
    DECLARE stock_cursor CURSOR FOR
    SELECT product_id, quantity
    FROM inserted;
    
    OPEN stock_cursor;
    FETCH NEXT FROM stock_cursor INTO @product_id, @quantity;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- ตรวจสอบ stock ปัจจุบัน
        SELECT @current_stock = stock_quantity
        FROM products
        WHERE id = @product_id;
        
        -- ตรวจสอบว่า stock เพียงพอหรือไม่
        IF @current_stock < @quantity
        BEGIN
            RAISERROR('สินค้า ID %d มีจำนวนไม่พอ (คงเหลือ: %d, ต้องการ: %d)', 16, 1, @product_id, @current_stock, @quantity);
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        -- ลด stock
        UPDATE products
        SET stock_quantity = stock_quantity - @quantity
        WHERE id = @product_id;
        
        FETCH NEXT FROM stock_cursor INTO @product_id, @quantity;
    END
    
    CLOSE stock_cursor;
    DEALLOCATE stock_cursor;
END;
GO

PRINT 'Trigger trg_order_items_update_stock created successfully';
GO

-- 3. Trigger: บันทึก audit log เมื่อมีการแก้ไขสินค้า
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'product_audit_log' AND type = 'U')
    DROP TABLE product_audit_log;
GO

-- สร้างตาราง audit log
-- หมายเหตุ: ไม่ใช้ foreign key constraint เพื่อให้สามารถบันทึก audit log ได้แม้สินค้าถูกลบไปแล้ว
CREATE TABLE product_audit_log (
    id INT IDENTITY(1,1) PRIMARY KEY,
    product_id INT NOT NULL, -- ไม่ใส่ foreign key เพื่อให้บันทึกได้แม้สินค้าถูกลบ
    action_type NVARCHAR(50) NOT NULL, -- 'INSERT', 'UPDATE', 'DELETE'
    old_name NVARCHAR(255) NULL,
    new_name NVARCHAR(255) NULL,
    old_price DECIMAL(10,2) NULL,
    new_price DECIMAL(10,2) NULL,
    old_stock_quantity INT NULL,
    new_stock_quantity INT NULL,
    changed_by INT NULL,
    changed_at DATETIME DEFAULT GETDATE()
);
GO

IF EXISTS (SELECT * FROM sys.triggers WHERE name = 'trg_products_audit')
    DROP TRIGGER trg_products_audit;
GO

CREATE TRIGGER trg_products_audit
ON products
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- สำหรับ INSERT
    IF EXISTS (SELECT * FROM inserted) AND NOT EXISTS (SELECT * FROM deleted)
    BEGIN
        INSERT INTO product_audit_log (product_id, action_type, new_name, new_price, new_stock_quantity)
        SELECT 
            id, 
            'INSERT',
            name,
            price,
            stock_quantity
        FROM inserted;
    END
    
    -- สำหรับ UPDATE
    IF EXISTS (SELECT * FROM inserted) AND EXISTS (SELECT * FROM deleted)
    BEGIN
        INSERT INTO product_audit_log (product_id, action_type, old_name, new_name, old_price, new_price, old_stock_quantity, new_stock_quantity)
        SELECT 
            i.id,
            'UPDATE',
            d.name,
            i.name,
            d.price,
            i.price,
            d.stock_quantity,
            i.stock_quantity
        FROM inserted i
        INNER JOIN deleted d ON i.id = d.id
        WHERE i.name != d.name 
           OR i.price != d.price 
           OR i.stock_quantity != d.stock_quantity;
    END
    
    -- สำหรับ DELETE
    IF EXISTS (SELECT * FROM deleted) AND NOT EXISTS (SELECT * FROM inserted)
    BEGIN
        INSERT INTO product_audit_log (product_id, action_type, old_name, old_price, old_stock_quantity)
        SELECT 
            id,
            'DELETE',
            name,
            price,
            stock_quantity
        FROM deleted;
    END
END;
GO

PRINT 'Trigger trg_products_audit created successfully';
GO

PRINT 'All triggers created successfully!';
GO

