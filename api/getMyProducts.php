<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require "db.php";
require "base_url.php";
require_once 'jwt_utils.php';
require_once 'config.php';

// ตรวจสอบ JWT token
$decoded = verify_jwt_or_die();
$userId = $decoded->data->id;
$userRole = isset($decoded->data->role) ? $decoded->data->role : 'user';

// ถ้าเป็น admin ให้ดึงสินค้าทั้งหมด
if ($userRole === 'admin') {
    $sql = "SELECT p.* FROM products p";
    $params = array();
} else {
    // ถ้าไม่ใช่ admin ให้ดึงเฉพาะสินค้าของร้านตัวเอง
    $sql = "SELECT p.* 
            FROM products p
            INNER JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = ?";
    $params = array($userId);
}

// Filter by category if provided
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];
    if (is_numeric($category_id)) {
        $category_id = intval($category_id);
        $sql .= $userRole === 'admin' ? " WHERE" : " AND";
        $sql .= " p.category_id = ?";
        $params[] = $category_id;
    }
}

$sql .= " ORDER BY p.id DESC";

$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["error" => "Database query failed"], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!sqlsrv_execute($stmt)) {
    echo json_encode(["error" => "Database query execution failed"], JSON_UNESCAPED_UNICODE);
    exit;
}

$products = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // แปลง DateTime objects เป็น string
    $product = [];
    foreach ($row as $key => $value) {
        if ($value instanceof DateTime) {
            $product[$key] = $value->format('Y-m-d H:i:s');
        } else {
            $product[$key] = $value;
        }
    }
    
    // แก้ไข image_url
    if (!empty($product['image_url'])) {
        if (strpos($product['image_url'], 'http') !== 0 && strpos($product['image_url'], 'assets/') !== 0) {
            $imagePath = ltrim($product['image_url'], '/');
            $fullPath = __DIR__ . '/../uploads/' . basename($imagePath);
            if (file_exists($fullPath)) {
                $product['image_url'] = $uploadsUrl . basename($imagePath);
            } else {
                $product['image_url'] = null;
            }
        }
    }
    
    // Ensure stock_quantity exists
    if (!isset($product['stock_quantity'])) {
        $product['stock_quantity'] = 0;
    }
    
    // Get store name if store_id exists
    if (!empty($product['store_id'])) {
        $storeSql = "SELECT store_name FROM stores WHERE id = ?";
        $storeParams = array($product['store_id']);
        $storeStmt = sqlsrv_prepare($conn, $storeSql, $storeParams);
        if ($storeStmt && sqlsrv_execute($storeStmt)) {
            $store = sqlsrv_fetch_array($storeStmt, SQLSRV_FETCH_ASSOC);
            if ($store) {
                $product['store_name'] = $store['store_name'];
            }
            sqlsrv_free_stmt($storeStmt);
        }
    }
    
    $products[] = $product;
}
sqlsrv_free_stmt($stmt);

// ถ้าไม่มีข้อมูล ให้ return array ว่าง
if (empty($products)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
}
?>

