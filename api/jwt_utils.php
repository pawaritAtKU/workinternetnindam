<?php
require_once __DIR__ . "/php-jwt/JWTExceptionWithPayloadInterface.php";
require_once __DIR__ . "/php-jwt/JWT.php";
require_once __DIR__ . "/php-jwt/Key.php";
require_once __DIR__ . "/php-jwt/ExpiredException.php";
require_once __DIR__ . "/php-jwt/SignatureInvalidException.php";
require_once __DIR__ . "/php-jwt/BeforeValidException.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verify_jwt_or_die() {
    $headers = getallheaders();
    if (!isset($headers["Authorization"])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Missing Authorization header"]);
        exit;
    }

    $authHeader = $headers["Authorization"];
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid Authorization header"]);
        exit;
    }

    $jwt = $matches[1];
    require_once __DIR__ . "/config.php";
    global $secret_key;

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token invalid or expired"]);
        exit;
    }
}

// ตรวจสอบว่า user เป็นเจ้าของ product หรือเป็น admin
function checkProductOwnership($conn, $productId, $userId, $userRole) {
    // ถ้าเป็น admin ให้ผ่านเลย
    if ($userRole === 'admin') {
        return true;
    }
    
    // ตรวจสอบว่า product นี้เป็นของ store ที่ user เป็นเจ้าของ
    $sql = "SELECT p.store_id, s.user_id 
            FROM products p
            LEFT JOIN stores s ON p.store_id = s.id
            WHERE p.id = ?";
    $params = array($productId);
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if ($stmt === false || !sqlsrv_execute($stmt)) {
        return false;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    
    if (!$row) {
        return false;
    }
    
    // ถ้า product ไม่มี store_id หรือ store ไม่มี user_id ให้ปฏิเสธ
    if (empty($row['store_id']) || empty($row['user_id'])) {
        return false;
    }
    
    // ตรวจสอบว่า user เป็นเจ้าของ store
    return intval($row['user_id']) === intval($userId);
}

// ตรวจสอบว่า user เป็นเจ้าของ store หรือเป็น admin
function checkStoreOwnership($conn, $storeId, $userId, $userRole) {
    // ถ้าเป็น admin ให้ผ่านเลย
    if ($userRole === 'admin') {
        return true;
    }
    
    // ตรวจสอบว่า store นี้เป็นของ user
    $sql = "SELECT user_id FROM stores WHERE id = ?";
    $params = array($storeId);
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if ($stmt === false || !sqlsrv_execute($stmt)) {
        return false;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    
    if (!$row) {
        return false;
    }
    
    return intval($row['user_id']) === intval($userId);
}
