-- =============================================
-- INSERT สินค้า 20 รายการ แบ่งตาม 6 หมวดหมู่
-- =============================================

-- หมวดหมู่: Electronics (อิเล็กทรอนิกส์) - 4 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'iPhone 15 Pro Max 256GB')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'iPhone 15 Pro Max 256GB',
        45900.00,
        'electronics',
        'สมาร์ทโฟนรุ่นล่าสุดจาก Apple พร้อมชิป A17 Pro, กล้อง 48MP, หน้าจอ 6.7 นิ้ว Super Retina XDR, ระบบป้องกันน้ำ IP68',
        50,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: iPhone 15 Pro Max 256GB';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'Samsung Galaxy S24 Ultra')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'Samsung Galaxy S24 Ultra',
        42900.00,
        'electronics',
        'สมาร์ทโฟน Android รุ่นพรีเมียม หน้าจอ Dynamic AMOLED 6.8 นิ้ว, กล้อง 200MP, S Pen, ชิป Snapdragon 8 Gen 3',
        45,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: Samsung Galaxy S24 Ultra';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'MacBook Pro 14 นิ้ว M3')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'MacBook Pro 14 นิ้ว M3',
        69900.00,
        'electronics',
        'โน้ตบุ๊กสำหรับงานระดับมืออาชีพ พร้อมชิป Apple M3, RAM 16GB, SSD 512GB, หน้าจอ Liquid Retina XDR 14.2 นิ้ว',
        30,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: MacBook Pro 14 นิ้ว M3';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'AirPods Pro 2')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'AirPods Pro 2',
        8990.00,
        'electronics',
        'หูฟังไร้สายพร้อม Active Noise Cancellation, Spatial Audio, ระบบชาร์จ MagSafe, ใช้งานได้ 6 ชั่วโมงต่อครั้ง',
        100,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: AirPods Pro 2';
END
GO

-- หมวดหมู่: Clothing (เสื้อผ้า) - 4 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'เสื้อเชิ้ตแขนยาว Premium')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'เสื้อเชิ้ตแขนยาว Premium',
        1290.00,
        'clothing',
        'เสื้อเชิ้ตคุณภาพสูง ผ้าคอตตอน 100% ใส่สบาย เหมาะสำหรับงานและชีวิตประจำวัน มีหลายสีให้เลือก (ขาว, น้ำเงิน, เทา)',
        100,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: เสื้อเชิ้ตแขนยาว Premium';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'กางเกงยีนส์ Slim Fit')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'กางเกงยีนส์ Slim Fit',
        1890.00,
        'clothing',
        'กางเกงยีนส์สไตล์ Slim Fit ผ้าเดนิมคุณภาพดี ใส่สบาย มีหลายไซส์ (28-36) และหลายสี (น้ำเงิน, ดำ)',
        80,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: กางเกงยีนส์ Slim Fit';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'รองเท้าผ้าใบ Nike Air Max')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'รองเท้าผ้าใบ Nike Air Max',
        4590.00,
        'clothing',
        'รองเท้าผ้าใบสปอร์ต Nike Air Max เทคโนโลยี Air Cushioning ใส่สบาย เหมาะสำหรับวิ่งและออกกำลังกาย มีหลายไซส์ (EU 38-45)',
        60,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: รองเท้าผ้าใบ Nike Air Max';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'เสื้อโปโล Polo Shirt')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'เสื้อโปโล Polo Shirt',
        890.00,
        'clothing',
        'เสื้อโปโลสไตล์คลาสสิก ผ้าคอตตอนผสมโพลีเอสเตอร์ ใส่สบาย เหมาะสำหรับทุกโอกาส มีหลายสี (ขาว, น้ำเงิน, แดง, เขียว)',
        120,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: เสื้อโปโล Polo Shirt';
END
GO

-- หมวดหมู่: Books (หนังสือ) - 3 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'หนังสือ Clean Code')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'หนังสือ Clean Code',
        890.00,
        'books',
        'หนังสือสำหรับนักพัฒนาซอฟต์แวร์ที่ต้องการเขียนโค้ดที่ดีและอ่านง่าย เขียนโดย Robert C. Martin (Uncle Bob) ภาษาไทย',
        75,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: หนังสือ Clean Code';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'หนังสือ The Pragmatic Programmer')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'หนังสือ The Pragmatic Programmer',
        950.00,
        'books',
        'หนังสือคลาสสิกสำหรับนักพัฒนาโปรแกรม เรียนรู้วิธีคิดและทำงานอย่างมืออาชีพ เขียนโดย Andrew Hunt และ David Thomas',
        65,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: หนังสือ The Pragmatic Programmer';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'หนังสือ Design Patterns')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'หนังสือ Design Patterns',
        1200.00,
        'books',
        'หนังสือเกี่ยวกับ Design Patterns ใน Object-Oriented Programming เขียนโดย Gang of Four (GoF) เหมาะสำหรับนักพัฒนาระดับกลาง-สูง',
        55,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: หนังสือ Design Patterns';
END
GO

-- หมวดหมู่: Home (ของใช้ในบ้าน) - 3 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'หมอนหนุน Memory Foam')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'หมอนหนุน Memory Foam',
        1290.00,
        'home',
        'หมอนหนุน Memory Foam รองรับสรีระ ระบายอากาศได้ดี ช่วยลดอาการปวดคอและไหล่ เหมาะสำหรับคนนอนตะแคงและนอนหงาย',
        90,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: หมอนหนุน Memory Foam';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'ชุดเครื่องครัวสแตนเลส')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'ชุดเครื่องครัวสแตนเลส',
        2590.00,
        'home',
        'ชุดเครื่องครัวสแตนเลสคุณภาพดี ประกอบด้วย หม้อ 3 ใบ, กระทะ 2 ใบ, และช้อนส้อม 6 ชุด ทำจากสแตนเลส 304 ปลอดภัยต่อสุขภาพ',
        50,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: ชุดเครื่องครัวสแตนเลส';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'โคมไฟตั้งโต๊ะ LED')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'โคมไฟตั้งโต๊ะ LED',
        890.00,
        'home',
        'โคมไฟตั้งโต๊ะ LED แบบปรับความสว่างได้ 3 ระดับ ไฟ LED หลอดยาว ใช้งานได้ 50,000 ชั่วโมง ดีไซน์โมเดิร์น เหมาะสำหรับอ่านหนังสือและทำงาน',
        70,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: โคมไฟตั้งโต๊ะ LED';
END
GO

-- หมวดหมู่: Sports (กีฬา) - 3 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'ลูกบาสเกตบอล Spalding')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'ลูกบาสเกตบอล Spalding',
        1290.00,
        'sports',
        'ลูกบาสเกตบอล Spalding Official Size 7 หนังแท้คุณภาพดี เหมาะสำหรับเล่นในร่มและกลางแจ้ง ใช้ในการแข่งขันได้',
        40,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: ลูกบาสเกตบอล Spalding';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'ชุดโยคะ Premium')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'ชุดโยคะ Premium',
        1890.00,
        'sports',
        'ชุดโยคะ Premium ประกอบด้วย เสื้อยืด, กางเกงโยคะ, และเสื่อโยคะ ผ้าคุณภาพดี ยืดหยุ่น ระบายอากาศได้ดี เหมาะสำหรับโยคะและพิลาทิส',
        60,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: ชุดโยคะ Premium';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'น้ำหนักดัมเบลล์ 5 กก.')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'น้ำหนักดัมเบลล์ 5 กก.',
        890.00,
        'sports',
        'ดัมเบลล์เหล็กชุบโครเมียม 5 กิโลกรัมต่อข้าง (รวม 10 กก.) ด้ามจับนุ่มสบาย เหมาะสำหรับออกกำลังกายที่บ้าน',
        50,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: น้ำหนักดัมเบลล์ 5 กก.';
END
GO

-- หมวดหมู่: General (ของใช้ทั่วไป) - 3 สินค้า
IF NOT EXISTS (SELECT * FROM products WHERE name = 'กระเป๋าเป้ Laptop Backpack')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'กระเป๋าเป้ Laptop Backpack',
        1290.00,
        'general',
        'กระเป๋าเป้สำหรับใส่ Laptop ขนาด 15.6 นิ้ว มีช่องใส่ของหลายช่อง กันน้ำเบื้องต้น ดีไซน์โมเดิร์น เหมาะสำหรับนักเรียนและพนักงานออฟฟิศ',
        80,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: กระเป๋าเป้ Laptop Backpack';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'กระบอกน้ำสแตนเลส')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'กระบอกน้ำสแตนเลส',
        590.00,
        'general',
        'กระบอกน้ำสแตนเลส 750ml กันความร้อนและเย็น เก็บอุณหภูมิได้นาน 12 ชั่วโมง ปลอดภัย BPA Free เหมาะสำหรับออกกำลังกายและใช้งานประจำวัน',
        150,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: กระบอกน้ำสแตนเลส';
END
GO

IF NOT EXISTS (SELECT * FROM products WHERE name = 'ปากกาลูกลื่น Pilot G2')
BEGIN
    INSERT INTO products (name, price, category_id, description, stock_quantity, image_url)
    VALUES (
        'ปากกาลูกลื่น Pilot G2',
        45.00,
        'general',
        'ปากกาลูกลื่น Pilot G2 หมึกน้ำ 0.7mm เขียนลื่น ไม่ซึม หมึกแห้งเร็ว มีหลายสี (ดำ, น้ำเงิน, แดง, เขียว) แพ็ค 12 ด้าม',
        200,
        'assets/images/placeholder.svg'
    );
    PRINT 'Inserted: ปากกาลูกลื่น Pilot G2';
END
GO

-- อัปเดต store_id ให้สินค้าทั้งหมด (ถ้ามี store ในระบบ)
DECLARE @first_store_id INT;
SELECT TOP 1 @first_store_id = id FROM stores ORDER BY id;

IF @first_store_id IS NOT NULL
BEGIN
    UPDATE products 
    SET store_id = @first_store_id
    WHERE store_id IS NULL;
    
    PRINT 'Updated store_id for all products';
END
GO

PRINT '=============================================';
PRINT 'สรุป: สร้างสินค้าทั้งหมด 20 รายการ';
PRINT '- Electronics (อิเล็กทรอนิกส์): 4 รายการ';
PRINT '- Clothing (เสื้อผ้า): 4 รายการ';
PRINT '- Books (หนังสือ): 3 รายการ';
PRINT '- Home (ของใช้ในบ้าน): 3 รายการ';
PRINT '- Sports (กีฬา): 3 รายการ';
PRINT '- General (ของใช้ทั่วไป): 3 รายการ';
PRINT '=============================================';
GO

-- ตรวจสอบข้อมูลสินค้าที่สร้าง
SELECT 
    category_id,
    COUNT(*) as product_count,
    SUM(stock_quantity) as total_stock,
    AVG(price) as avg_price
FROM products
GROUP BY category_id
ORDER BY category_id;
GO

