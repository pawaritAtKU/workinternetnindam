-- =============================================
-- Views สำหรับระบบ E-Commerce
-- =============================================

-- 1. View: สินค้าพร้อมข้อมูลหมวดหมู่และร้านค้า
IF EXISTS (SELECT * FROM sys.views WHERE name = 'vw_products_with_details')
    DROP VIEW vw_products_with_details;
GO

CREATE VIEW vw_products_with_details
AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.image_url,
    p.description,
    p.stock_quantity,
    p.category_id,
    p.store_id,
    s.store_name,
    s.user_id as store_owner_id,
    CASE 
        WHEN p.stock_quantity > 0 THEN 'in_stock'
        ELSE 'out_of_stock'
    END as stock_status
FROM products p
LEFT JOIN stores s ON p.store_id = s.id;
GO

PRINT 'View vw_products_with_details created successfully';
GO

-- 2. View: สรุปคำสั่งซื้อพร้อมข้อมูลผู้ใช้
IF EXISTS (SELECT * FROM sys.views WHERE name = 'vw_order_summary')
    DROP VIEW vw_order_summary;
GO

CREATE VIEW vw_order_summary
AS
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    u.username,
    u.email,
    o.total_amount,
    o.shipping_fee,
    (o.total_amount + o.shipping_fee) as grand_total,
    o.payment_status,
    o.order_status,
    o.payment_method,
    o.shipping_name,
    o.shipping_address,
    o.shipping_province,
    o.shipping_postcode,
    COUNT(oi.id) as item_count,
    o.created_at,
    o.updated_at
FROM orders o
INNER JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY 
    o.id, o.order_number, o.user_id, u.username, u.email,
    o.total_amount, o.shipping_fee, o.payment_status, o.order_status,
    o.payment_method, o.shipping_name, o.shipping_address,
    o.shipping_province, o.shipping_postcode, o.created_at, o.updated_at;
GO

PRINT 'View vw_order_summary created successfully';
GO

-- 3. View: สินค้าของผู้ใช้พร้อมสถิติการขาย
IF EXISTS (SELECT * FROM sys.views WHERE name = 'vw_user_products_stats')
    DROP VIEW vw_user_products_stats;
GO

CREATE VIEW vw_user_products_stats
AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    p.store_id,
    s.store_name,
    s.user_id as owner_id,
    u.username as owner_username,
    COUNT(DISTINCT oi.order_id) as total_orders,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as total_revenue,
    AVG(oi.quantity) as avg_quantity_per_order
FROM products p
INNER JOIN stores s ON p.store_id = s.id
INNER JOIN users u ON s.user_id = u.id
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY 
    p.id, p.name, p.price, p.stock_quantity, p.store_id,
    s.store_name, s.user_id, u.username;
GO

PRINT 'View vw_user_products_stats created successfully';
GO

PRINT 'All views created successfully!';
GO

