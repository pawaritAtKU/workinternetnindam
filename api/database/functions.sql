-- =============================================
-- Functions สำหรับระบบ E-Commerce
-- =============================================

-- 1. Function: ตรวจสอบ stock ของสินค้า
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'fn_get_product_stock' AND type = 'FN')
    DROP FUNCTION fn_get_product_stock;
GO

CREATE FUNCTION fn_get_product_stock(@product_id INT)
RETURNS INT
AS
BEGIN
    DECLARE @stock INT;
    
    SELECT @stock = stock_quantity
    FROM products
    WHERE id = @product_id;
    
    RETURN ISNULL(@stock, 0);
END;
GO

PRINT 'Function fn_get_product_stock created successfully';
GO

-- 2. Function: คำนวณยอดรวมของคำสั่งซื้อ
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'fn_calculate_order_total' AND type = 'FN')
    DROP FUNCTION fn_calculate_order_total;
GO

CREATE FUNCTION fn_calculate_order_total(@order_id INT)
RETURNS DECIMAL(10,2)
AS
BEGIN
    DECLARE @total DECIMAL(10,2);
    DECLARE @shipping_fee DECIMAL(10,2);
    DECLARE @grand_total DECIMAL(10,2);
    
    -- คำนวณยอดรวมจาก order_items
    SELECT @total = ISNULL(SUM(subtotal), 0)
    FROM order_items
    WHERE order_id = @order_id;
    
    -- ดึง shipping fee
    SELECT @shipping_fee = ISNULL(shipping_fee, 0)
    FROM orders
    WHERE id = @order_id;
    
    -- คำนวณยอดรวมทั้งหมด
    SET @grand_total = @total + @shipping_fee;
    
    RETURN @grand_total;
END;
GO

PRINT 'Function fn_calculate_order_total created successfully';
GO

-- 3. Function: นับจำนวนคำสั่งซื้อของผู้ใช้
IF EXISTS (SELECT * FROM sys.objects WHERE name = 'fn_get_user_order_count' AND type = 'FN')
    DROP FUNCTION fn_get_user_order_count;
GO

CREATE FUNCTION fn_get_user_order_count(@user_id INT, @order_status NVARCHAR(50) = NULL)
RETURNS INT
AS
BEGIN
    DECLARE @count INT;
    
    IF @order_status IS NULL
    BEGIN
        SELECT @count = COUNT(*)
        FROM orders
        WHERE user_id = @user_id;
    END
    ELSE
    BEGIN
        SELECT @count = COUNT(*)
        FROM orders
        WHERE user_id = @user_id 
          AND order_status = @order_status;
    END
    
    RETURN ISNULL(@count, 0);
END;
GO

PRINT 'Function fn_get_user_order_count created successfully';
GO

-- ตัวอย่างการใช้งาน Functions:
-- SELECT dbo.fn_get_product_stock(1) as stock;
-- SELECT dbo.fn_calculate_order_total(1) as total;
-- SELECT dbo.fn_get_user_order_count(1, 'completed') as completed_orders;

PRINT 'All functions created successfully!';
GO

