-- =============================================
-- สร้าง Tables สำหรับระบบ E-Commerce
-- =============================================

-- 1. เพิ่ม column stock_quantity ใน products table (ถ้ายังไม่มี)
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'products' AND COLUMN_NAME = 'stock_quantity'
)
BEGIN
    ALTER TABLE products ADD stock_quantity INT DEFAULT 0;
    PRINT 'Added stock_quantity column to products table';
END
GO

-- 2. เพิ่ม column store_id ใน products table (ถ้ายังไม่มี)
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'products' AND COLUMN_NAME = 'store_id'
)
BEGIN
    ALTER TABLE products ADD store_id INT NULL;
    PRINT 'Added store_id column to products table';
END
GO

-- 3. สร้าง stores table (ร้านค้า)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[stores]') AND type in (N'U'))
BEGIN
    CREATE TABLE stores (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        store_name NVARCHAR(255) NOT NULL,
        store_description NVARCHAR(MAX) NULL,
        store_address NVARCHAR(500) NULL,
        store_phone NVARCHAR(50) NULL,
        store_email NVARCHAR(255) NULL,
        is_active BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    PRINT 'Created stores table';
END
GO

-- 4. สร้าง orders table (คำสั่งซื้อ)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[orders]') AND type in (N'U'))
BEGIN
    CREATE TABLE orders (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        order_number NVARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_fee DECIMAL(10,2) DEFAULT 50.00,
        shipping_name NVARCHAR(255) NOT NULL,
        shipping_phone NVARCHAR(50) NOT NULL,
        shipping_address NVARCHAR(500) NOT NULL,
        shipping_province NVARCHAR(100) NOT NULL,
        shipping_postcode NVARCHAR(10) NOT NULL,
        payment_method NVARCHAR(50) DEFAULT 'cod',
        payment_status NVARCHAR(50) DEFAULT 'pending',
        order_status NVARCHAR(50) DEFAULT 'pending',
        notes NVARCHAR(MAX) NULL,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    PRINT 'Created orders table';
END
GO

-- 5. สร้าง order_items table (รายการสินค้าในคำสั่งซื้อ)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[order_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE order_items (
        id INT IDENTITY(1,1) PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name NVARCHAR(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE NO ACTION
    );
    PRINT 'Created order_items table';
END
GO

-- 6. สร้าง indexes สำหรับ performance
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_products_store_id' AND object_id = OBJECT_ID('products'))
BEGIN
    CREATE INDEX IX_products_store_id ON products(store_id);
    PRINT 'Created index IX_products_store_id';
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_orders_user_id' AND object_id = OBJECT_ID('orders'))
BEGIN
    CREATE INDEX IX_orders_user_id ON orders(user_id);
    PRINT 'Created index IX_orders_user_id';
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_orders_order_status' AND object_id = OBJECT_ID('orders'))
BEGIN
    CREATE INDEX IX_orders_order_status ON orders(order_status);
    PRINT 'Created index IX_orders_order_status';
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_order_items_order_id' AND object_id = OBJECT_ID('order_items'))
BEGIN
    CREATE INDEX IX_order_items_order_id ON order_items(order_id);
    PRINT 'Created index IX_order_items_order_id';
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_stores_user_id' AND object_id = OBJECT_ID('stores'))
BEGIN
    CREATE INDEX IX_stores_user_id ON stores(user_id);
    PRINT 'Created index IX_stores_user_id';
END
GO

PRINT 'All tables and indexes created successfully!';
GO

