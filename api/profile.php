<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/php-jwt/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/php-jwt/JWT.php';
require_once __DIR__ . '/php-jwt/Key.php';
require_once __DIR__ . '/php-jwt/ExpiredException.php';
require_once __DIR__ . '/php-jwt/SignatureInvalidException.php';
require_once __DIR__ . '/php-jwt/BeforeValidException.php';

require_once "db.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "4d2a8f9f23c5e17c0e7a4f2c6d76a2b19123a7bbde8d5cf3f9f8c33f6ac5d89c";

$headers = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
} elseif (function_exists('apache_request_headers')) {
    $requestHeaders = apache_request_headers();
    if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
    }
}

if (!$headers) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authorization header not found"]);
    exit;
}

if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid Authorization format"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_data = (array) $decoded->data;

    // ตรวจสอบว่า role column มีอยู่หรือไม่
    $checkRoleSql = "SELECT COUNT(*) AS count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'role'";
    $checkRoleStmt = sqlsrv_query($conn, $checkRoleSql);
    $hasRoleColumn = false;
    if ($checkRoleStmt) {
        $checkRoleRow = sqlsrv_fetch_array($checkRoleStmt, SQLSRV_FETCH_ASSOC);
        if ($checkRoleRow && $checkRoleRow['count'] > 0) {
            $hasRoleColumn = true;
        }
        sqlsrv_free_stmt($checkRoleStmt);
    }

    // Query user data - รวม role ถ้ามี
    if ($hasRoleColumn) {
        $sql = "SELECT TOP 1 id, username, email, role FROM users WHERE id = ?";
    } else {
        $sql = "SELECT TOP 1 id, username, email FROM users WHERE id = ?";
    }
    $params = array($user_data['id']);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database query failed"]);
        exit;
    }

    if (!sqlsrv_execute($stmt)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database query execution failed"]);
        exit;
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($user === false) {
        sqlsrv_free_stmt($stmt);
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "ไม่พบผู้ใช้ในระบบ"]);
        exit;
    }

    sqlsrv_free_stmt($stmt);

    // กำหนด role (default 'user' ถ้าไม่มี column หรือไม่มีค่า)
    $userRole = 'user';
    if ($hasRoleColumn && isset($user["role"]) && !empty($user["role"])) {
        $userRole = $user["role"];
    } else if (isset($user_data['role'])) {
        // ถ้าไม่มี role ใน database แต่มีใน JWT token ให้ใช้จาก token
        $userRole = $user_data['role'];
    }

    // เพิ่ม role ใน user object
    $user['role'] = $userRole;

    echo json_encode([
        "success" => true,
        "message" => "โหลดโปรไฟล์สำเร็จ",
        "user"    => $user
    ]);

} catch (\Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token หมดอายุ"]);
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token ไม่ถูกต้อง", "error" => $e->getMessage()]);
}
