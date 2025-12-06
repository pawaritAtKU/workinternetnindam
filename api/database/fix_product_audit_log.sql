-- =============================================
-- แก้ไขตาราง product_audit_log
-- ลบ foreign key constraint เพื่อให้สามารถบันทึก audit log ได้แม้สินค้าถูกลบ
-- =============================================

-- ตรวจสอบว่ามี foreign key constraint หรือไม่
DECLARE @fk_name NVARCHAR(255);
SELECT @fk_name = name
FROM sys.foreign_keys
WHERE parent_object_id = OBJECT_ID('product_audit_log')
  AND referenced_object_id = OBJECT_ID('products');

IF @fk_name IS NOT NULL
BEGIN
    -- ลบ foreign key constraint
    DECLARE @sql NVARCHAR(MAX);
    SET @sql = 'ALTER TABLE product_audit_log DROP CONSTRAINT ' + @fk_name;
    EXEC sp_executesql @sql;
    
    PRINT 'ลบ foreign key constraint: ' + @fk_name;
    PRINT 'ตาราง product_audit_log สามารถบันทึก audit log ได้แม้สินค้าถูกลบแล้ว';
END
ELSE
BEGIN
    PRINT 'ไม่พบ foreign key constraint ในตาราง product_audit_log';
    PRINT 'ตารางพร้อมใช้งานแล้ว';
END
GO

-- ตรวจสอบโครงสร้างตาราง
PRINT '';
PRINT '=============================================';
PRINT 'โครงสร้างตาราง product_audit_log:';
PRINT '=============================================';

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'product_audit_log'
ORDER BY ORDINAL_POSITION;
GO

PRINT '';
PRINT '=============================================';
PRINT 'เสร็จสิ้น';
PRINT '=============================================';
GO

