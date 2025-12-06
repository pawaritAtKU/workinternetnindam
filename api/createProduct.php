<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require "base_url.php";

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imageUrl = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imageUrl = $uploadsUrl . $fileName;
    }
}

require 'db.php';
require_once 'jwt_utils.php';
require_once 'config.php';

// ตรวจสอบ JWT token
$decoded = verify_jwt_or_die();
$userId = $decoded->data->id;
$userRole = isset($decoded->data->role) ? $decoded->data->role : 'user';

$name = $_POST['name'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$category_id = $_POST['category_id'] ?? '';
$description = $_POST['description'] ?? null;
$stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
$store_id = isset($_POST['store_id']) ? intval($_POST['store_id']) : null;

// Validate required fields
if (empty($name) || $price <= 0 || empty($category_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
    exit;
}

// ถ้ามี store_id ต้องตรวจสอบว่า user เป็นเจ้าของ store หรือเป็น admin
if ($store_id) {
    if (!checkStoreOwnership($conn, $store_id, $userId, $userRole)) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "คุณไม่มีสิทธิ์เพิ่มสินค้าในร้านนี้"]);
        exit;
    }
} else {
    // ถ้าไม่มี store_id แต่ user ไม่ใช่ admin ให้หา store ของ user
    if ($userRole !== 'admin') {
        $storeSql = "SELECT id FROM stores WHERE user_id = ?";
        $storeParams = array($userId);
        $storeStmt = sqlsrv_prepare($conn, $storeSql, $storeParams);
        if ($storeStmt && sqlsrv_execute($storeStmt)) {
            $storeRow = sqlsrv_fetch_array($storeStmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($storeStmt);
            if ($storeRow) {
                $store_id = $storeRow['id'];
            }
        }
    }
}

$insertSql = "INSERT INTO products (name, price, category_id, image_url, description, stock_quantity, store_id) OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?, ?, ?)";
$insertParams = array($name, $price, $category_id, $imageUrl, $description, $stock_quantity, $store_id);
$stmt = sqlsrv_prepare($conn, $insertSql, $insertParams);

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
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $newId = $row ? $row['id'] : null;
    sqlsrv_free_stmt($stmt);
    echo json_encode([
        "success" => true,
        "id" => $newId,
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
        "error" => $errorMsg
    ]);
}
