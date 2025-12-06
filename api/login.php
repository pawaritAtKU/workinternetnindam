<?php
// Remove any existing CORS headers to avoid duplicates
header_remove('Access-Control-Allow-Origin');
header_remove('Access-Control-Allow-Methods');
header_remove('Access-Control-Allow-Headers');

// Get origin from request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
$allowedOrigins = [
    'http://workinternetnindam.test',
    'http://localhost',
    'http://127.0.0.1',
    'http://localhost:80',
    'http://127.0.0.1:80'
];

// Set CORS headers
if (in_array($origin, $allowedOrigins) || $origin === '*') {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";
require_once "config.php";
require_once __DIR__ . '/php-jwt/JWTExceptionWithPayloadInterface.php';
require_once __DIR__ . '/php-jwt/JWT.php';
require_once __DIR__ . '/php-jwt/Key.php';
require_once __DIR__ . '/php-jwt/ExpiredException.php';
require_once __DIR__ . '/php-jwt/SignatureInvalidException.php';
require_once __DIR__ . '/php-jwt/BeforeValidException.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Check if JSON decode failed
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
    exit;
}

// Get identifier from email or username field
$identifier = "";
if (isset($data["email"]) && !empty(trim($data["email"]))) {
    $identifier = trim($data["email"]);
} elseif (isset($data["username"]) && !empty(trim($data["username"]))) {
    $identifier = trim($data["username"]);
}

$password = isset($data["password"]) ? trim($data["password"]) : "";

if (!$identifier || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่าน"]);
    exit;
}

// ตรวจสอบว่า role column มีอยู่หรือไม่ และเพิ่มถ้ายังไม่มี
$checkRoleSql = "SELECT COUNT(*) AS count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'role'";
$checkRoleStmt = sqlsrv_query($conn, $checkRoleSql);
$hasRoleColumn = false;
if ($checkRoleStmt) {
    $checkRoleRow = sqlsrv_fetch_array($checkRoleStmt, SQLSRV_FETCH_ASSOC);
    if ($checkRoleRow && $checkRoleRow['count'] > 0) {
        $hasRoleColumn = true;
    } else {
        // เพิ่ม role column ถ้ายังไม่มี
        $addRoleSql = "ALTER TABLE users ADD role NVARCHAR(50) DEFAULT 'user'";
        $addRoleStmt = sqlsrv_query($conn, $addRoleSql);
        if ($addRoleStmt) {
            $hasRoleColumn = true;
            sqlsrv_free_stmt($addRoleStmt);
        }
    }
    sqlsrv_free_stmt($checkRoleStmt);
}

// Query user data - รวม role ถ้ามี
if ($hasRoleColumn) {
    $sql = "SELECT TOP 1 id, username, email, password, role FROM users WHERE email=? OR username=?";
} else {
    $sql = "SELECT TOP 1 id, username, email, password FROM users WHERE email=? OR username=?";
}
$params = array($identifier, $identifier);
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
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "ไม่พบผู้ใช้งาน"]);
    exit;
}

sqlsrv_free_stmt($stmt);

if (!password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "รหัสผ่านไม่ถูกต้อง"]);
    exit;
}

$issuedAt   = time();
$expiration = $issuedAt + (60 * 60 * 24); // 24 ชั่วโมง

// กำหนด role (default 'user' ถ้าไม่มี column หรือไม่มีค่า)
$userRole = 'user';
if ($hasRoleColumn && isset($user["role"]) && !empty($user["role"])) {
    $userRole = $user["role"];
}

$payload = [
    "iss"  => "http://yourdomain.com",
    "aud"  => "http://yourdomain.com",
    "iat"  => $issuedAt,
    "exp"  => $expiration,
    "data" => [
        "id"       => $user["id"],
        "username" => $user["username"],
        "email"    => $user["email"],
        "role"     => $userRole
    ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo json_encode([
    "success" => true,
    "message" => "เข้าสู่ระบบสำเร็จ",
    "token"   => $jwt,
    "user"    => [
        "id"       => $user["id"],
        "username" => $user["username"],
        "email"    => $user["email"],
        "role"     => $userRole
    ]
]);
