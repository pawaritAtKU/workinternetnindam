-- =============================================
-- ข้อมูลสินค้าตัวอย่าง (Sample Products)
-- =============================================

-- ตรวจสอบว่ามี category_id และ store_id หรือไม่
-- ถ้าไม่มีให้สร้างหรือใช้ค่า default

-- สินค้าตัวอย่างที่ 1: สมาร์ทโฟน
IF NOT EXISTS (SELECT * FROM products WHERE name = 'iPhone 15 Pro Max 256GB')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'iPhone 15 Pro Max 256GB',
        45900.00,
        'electronics',
        'สมาร์ทโฟนรุ่นล่าสุดจาก Apple พร้อมชิป A17 Pro, กล้อง 48MP, หน้าจอ 6.7 นิ้ว Super Retina XDR',
        50,
        'https://via.placeholder.com/400x400?text=iPhone+15+Pro+Max'
    );
    PRINT 'Product 1: iPhone 15 Pro Max inserted';
END
GO

-- สินค้าตัวอย่างที่ 2: เสื้อผ้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'เสื้อเชิ้ตแขนยาว Premium')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'เสื้อเชิ้ตแขนยาว Premium',
        1290.00,
        'clothing',
        'เสื้อเชิ้ตคุณภาพสูง ผ้าคอตตอน 100% ใส่สบาย เหมาะสำหรับงานและชีวิตประจำวัน มีหลายสีให้เลือก',
        100,
        'https://via.placeholder.com/400x400?text=Premium+Shirt'
    );
    PRINT 'Product 2: Premium Shirt inserted';
END
GO

-- สินค้าตัวอย่างที่ 3: หนังสือ
IF NOT EXISTS (SELECT * FROM products WHERE name = 'หนังสือ Clean Code')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'หนังสือ Clean Code',
        890.00,
        'books',
        'หนังสือสำหรับนักพัฒนาซอฟต์แวร์ที่ต้องการเขียนโค้ดที่ดีและอ่านง่าย เขียนโดย Robert C. Martin (Uncle Bob)',
        75,
        'https://via.placeholder.com/400x400?text=Clean+Code+Book'
    );
    PRINT 'Product 3: Clean Code Book inserted';
END
GO

-- อัปเดต store_id ให้สินค้า (ถ้ามี store ในระบบ)
-- ตัวอย่าง: อัปเดตให้สินค้าทั้ง 3 ชิ้นเป็นของ store แรก
DECLARE @first_store_id INT;
SELECT TOP 1 @first_store_id = id FROM stores ORDER BY id;

IF @first_store_id IS NOT NULL
BEGIN
    UPDATE products 
    SET store_id = @first_store_id
    WHERE store_id IS NULL 
      AND name IN ('iPhone 15 Pro Max 256GB', 'เสื้อเชิ้ตแขนยาว Premium', 'หนังสือ Clean Code');
    
    PRINT 'Updated store_id for sample products';
END
GO

PRINT 'All sample products created successfully!';
GO

-- ตรวจสอบข้อมูลสินค้าที่สร้าง
SELECT 
    id,
    name,
    price,
    category_id,
    stock_quantity,
    store_id
FROM products
WHERE name IN ('iPhone 15 Pro Max 256GB', 'เสื้อเชิ้ตแขนยาว Premium', 'หนังสือ Clean Code')
ORDER BY id;
GO

