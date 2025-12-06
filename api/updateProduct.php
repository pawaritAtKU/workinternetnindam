<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php';
require 'base_url.php';
require_once 'jwt_utils.php';
require_once 'config.php';

// ตรวจสอบ JWT token
$decoded = verify_jwt_or_die();
$userId = $decoded->data->id;
$userRole = isset($decoded->data->role) ? $decoded->data->role : 'user';

$id             = $_POST['id'] ?? '';
$name           = $_POST['name'] ?? '';
$price          = floatval($_POST['price'] ?? 0);
$category_id    = $_POST['category_id'] ?? '';
$description    = $_POST['description'] ?? null;
$stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;

if (!$id) {
    echo json_encode(["success" => false, "error" => "Missing product ID"]);
    exit;
}

// ตรวจสอบสิทธิ์ - user ต้องเป็นเจ้าของ product หรือเป็น admin
if (!checkProductOwnership($conn, $id, $userId, $userRole)) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "คุณไม่มีสิทธิ์แก้ไขสินค้านี้"]);
    exit;
}

$selectSql = "SELECT image_url FROM products WHERE id=?";
$selectParams = array($id);
$stmtOld = sqlsrv_prepare($conn, $selectSql, $selectParams);

if ($stmtOld === false) {
    echo json_encode(["success" => false, "error" => "Database query failed"]);
    exit;
}

if (!sqlsrv_execute($stmtOld)) {
    echo json_encode(["success" => false, "error" => "Database query execution failed"]);
    exit;
}

$oldRow = sqlsrv_fetch_array($stmtOld, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmtOld);

$imageUrl = $oldRow['image_url'] ?? null; // ตั้งค่าเริ่มต้นเป็นรูปเดิม

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imageUrl = $uploadsUrl . $fileName;
    }
}

$updateSql = "UPDATE products SET name=?, price=?, category_id=?, image_url=?, description=?, stock_quantity=? WHERE id=?";
$updateParams = array($name, $price, $category_id, $imageUrl, $description, $stock_quantity, $id);
$stmt = sqlsrv_prepare($conn, $updateSql, $updateParams);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    echo json_encode([
        "success" => false,
        "error" => $errorMsg
    ]);
    exit;
}

if (sqlsrv_execute($stmt)) {
    sqlsrv_free_stmt($stmt);
    echo json_encode([
        "success"   => true,
        "id"        => $id,
        "image_url" => $imageUrl
    ]);
} else {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query execution failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    sqlsrv_free_stmt($stmt);
    echo json_encode([
        "success" => false,
        "error"   => $errorMsg
    ]);
}
