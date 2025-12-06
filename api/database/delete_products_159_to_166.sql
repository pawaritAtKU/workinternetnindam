-- =============================================
-- ลบข้อมูลสินค้าที่มี ID ตั้งแต่ 159 ถึง 166
-- =============================================

-- ตรวจสอบข้อมูลก่อนลบ
PRINT '=============================================';
PRINT 'ข้อมูลสินค้าที่จะถูกลบ (ID 159-166):';
PRINT '=============================================';

SELECT 
    id,
    name,
    price,
    category_id,
    stock_quantity,
    image_url
FROM products
WHERE id BETWEEN 159 AND 166
ORDER BY id;
GO

-- ตรวจสอบว่ามี order_items ที่อ้างอิงสินค้าเหล่านี้หรือไม่
PRINT '';
PRINT '=============================================';
PRINT 'ตรวจสอบ order_items ที่อ้างอิงสินค้าเหล่านี้:';
PRINT '=============================================';

SELECT 
    oi.product_id,
    p.name as product_name,
    COUNT(oi.id) as order_item_count
FROM order_items oi
INNER JOIN products p ON oi.product_id = p.id
WHERE oi.product_id BETWEEN 159 AND 166
GROUP BY oi.product_id, p.name;
GO

-- ถ้ามี order_items ที่อ้างอิง จะไม่สามารถลบได้
-- ให้แสดงข้อความเตือน
DECLARE @order_item_count INT;
SELECT @order_item_count = COUNT(*)
FROM order_items
WHERE product_id BETWEEN 159 AND 166;

IF @order_item_count > 0
BEGIN
    PRINT '';
    PRINT '=============================================';
    PRINT 'คำเตือน: พบ order_items ที่อ้างอิงสินค้าเหล่านี้';
    PRINT 'ไม่สามารถลบสินค้าได้ เนื่องจากมีคำสั่งซื้อที่เกี่ยวข้อง';
    PRINT 'จำนวน order_items: ' + CAST(@order_item_count AS NVARCHAR);
    PRINT '=============================================';
    PRINT '';
    PRINT 'หากต้องการลบ ต้องลบ order_items ที่เกี่ยวข้องก่อน';
    PRINT 'หรือใช้วิธีอื่น เช่น soft delete (อัปเดต status)';
END
ELSE
BEGIN
    PRINT '';
    PRINT '=============================================';
    PRINT 'ไม่พบ order_items ที่อ้างอิงสินค้าเหล่านี้';
    PRINT 'สามารถลบได้';
    PRINT '=============================================';
    PRINT '';
    
    -- ลบข้อมูลสินค้า
    BEGIN TRY
        BEGIN TRANSACTION;
        
        DELETE FROM products
        WHERE id BETWEEN 159 AND 166;
        
        DECLARE @deleted_count INT;
        SET @deleted_count = @@ROWCOUNT;
        
        COMMIT TRANSACTION;
        
        PRINT 'ลบสินค้าสำเร็จ: ' + CAST(@deleted_count AS NVARCHAR) + ' รายการ';
        PRINT 'ID ที่ถูกลบ: 159-166';
        
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        PRINT 'เกิดข้อผิดพลาด: ' + ERROR_MESSAGE();
        PRINT 'ไม่สามารถลบสินค้าได้';
    END CATCH
END
GO

-- ตรวจสอบผลลัพธ์หลังลบ
PRINT '';
PRINT '=============================================';
PRINT 'ตรวจสอบข้อมูลหลังลบ:';
PRINT '=============================================';

SELECT 
    COUNT(*) as remaining_products_with_id_159_166
FROM products
WHERE id BETWEEN 159 AND 166;
GO

PRINT '';
PRINT '=============================================';
PRINT 'เสร็จสิ้น';
PRINT '=============================================';
GO

