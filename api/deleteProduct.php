<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "db.php";
require_once 'jwt_utils.php';
require_once 'config.php';

// ตรวจสอบ JWT token
$decoded = verify_jwt_or_die();
$userId = $decoded->data->id;
$userRole = isset($decoded->data->role) ? $decoded->data->role : 'user';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing required field (id)"]);
    exit;
}

// ตรวจสอบสิทธิ์ - user ต้องเป็นเจ้าของ product หรือเป็น admin
if (!checkProductOwnership($conn, $id, $userId, $userRole)) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "คุณไม่มีสิทธิ์ลบสินค้านี้"]);
    exit;
}

$oldSql = "SELECT image_url FROM products WHERE id = ?";
$oldParams = array($id);
$stmtOld = sqlsrv_prepare($conn, $oldSql, $oldParams);

if ($stmtOld === false) {
    echo json_encode(["success" => false, "message" => "Database query failed"]);
    exit;
}

if (!sqlsrv_execute($stmtOld)) {
    echo json_encode(["success" => false, "message" => "Database query execution failed"]);
    exit;
}

$oldProduct = sqlsrv_fetch_array($stmtOld, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmtOld);

// ตรวจสอบว่ามี order_items ที่อ้างอิงสินค้านี้หรือไม่
$checkOrderItemsSql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
$checkParams = array($id);
$checkStmt = sqlsrv_prepare($conn, $checkOrderItemsSql, $checkParams);

if ($checkStmt === false) {
    echo json_encode(["success" => false, "message" => "Database query failed"]);
    exit;
}

if (!sqlsrv_execute($checkStmt)) {
    echo json_encode(["success" => false, "message" => "Database query execution failed"]);
    exit;
}

$orderItemsResult = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($checkStmt);

if ($orderItemsResult && $orderItemsResult['count'] > 0) {
    echo json_encode([
        "success" => false, 
        "message" => "ไม่สามารถลบสินค้าได้ เนื่องจากมีคำสั่งซื้อที่เกี่ยวข้องกับสินค้านี้อยู่ (" . $orderItemsResult['count'] . " รายการ) กรุณาตรวจสอบประวัติการสั่งซื้อก่อน"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($oldProduct && $oldProduct['image_url'] && str_contains($oldProduct['image_url'], "/uploads/")) {
    $oldPath = __DIR__ . "/uploads/" . basename($oldProduct['image_url']);
    if (file_exists($oldPath)) {
        unlink($oldPath);
    }
}

$sql = "DELETE FROM products WHERE id = ?";
$params = array($id);
$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    echo json_encode(["success" => false, "message" => $errorMsg]);
    exit;
}

if (sqlsrv_execute($stmt)) {
    sqlsrv_free_stmt($stmt);
    echo json_encode(["success" => true, "message" => "Product deleted"]);
} else {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query execution failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    sqlsrv_free_stmt($stmt);
    echo json_encode(["success" => false, "message" => $errorMsg]);
}
