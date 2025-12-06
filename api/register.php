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

// Get and trim values
$username = isset($data["username"]) ? trim($data["username"]) : "";
$email    = isset($data["email"]) ? trim($data["email"]) : "";
$password = isset($data["password"]) ? trim($data["password"]) : "";

// Validate inputs
if (empty($username) || empty($password) || empty($email)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อผู้ใช้ อีเมล และรหัสผ่าน"]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "รูปแบบอีเมลไม่ถูกต้อง"]);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร"]);
    exit;
}

// ตรวจสอบ username และ email แยกกัน
$checkUsernameSql = "SELECT COUNT(*) AS count FROM users WHERE username=?";
$checkUsernameParams = array($username);
$checkUsername = sqlsrv_prepare($conn, $checkUsernameSql, $checkUsernameParams);

if ($checkUsername === false) {
    $errors = sqlsrv_errors();
    echo json_encode(["success" => false, "message" => "Database query failed", "error" => $errors ? $errors[0]['message'] : ""]);
    exit;
}

if (!sqlsrv_execute($checkUsername)) {
    $errors = sqlsrv_errors();
    echo json_encode(["success" => false, "message" => "Database query execution failed", "error" => $errors ? $errors[0]['message'] : ""]);
    exit;
}

$checkUsernameRow = sqlsrv_fetch_array($checkUsername, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($checkUsername);

if ($checkUsernameRow && isset($checkUsernameRow['count']) && $checkUsernameRow['count'] > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Username ถูกใช้แล้ว"]);
    exit;
}

// ตรวจสอบ email
$checkEmailSql = "SELECT COUNT(*) AS count FROM users WHERE email=?";
$checkEmailParams = array($email);
$checkEmail = sqlsrv_prepare($conn, $checkEmailSql, $checkEmailParams);

if ($checkEmail === false) {
    $errors = sqlsrv_errors();
    echo json_encode(["success" => false, "message" => "Database query failed", "error" => $errors ? $errors[0]['message'] : ""]);
    exit;
}

if (!sqlsrv_execute($checkEmail)) {
    $errors = sqlsrv_errors();
    echo json_encode(["success" => false, "message" => "Database query execution failed", "error" => $errors ? $errors[0]['message'] : ""]);
    exit;
}

$checkEmailRow = sqlsrv_fetch_array($checkEmail, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($checkEmail);

if ($checkEmailRow && isset($checkEmailRow['count']) && $checkEmailRow['count'] > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Email ถูกใช้แล้ว"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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

// INSERT user data - รวม role ถ้ามี
if ($hasRoleColumn) {
    $insertSql = "INSERT INTO users (username, email, password, role) OUTPUT INSERTED.id VALUES (?, ?, ?, ?)";
    $insertParams = array($username, $email, $hashedPassword, 'user');
} else {
    $insertSql = "INSERT INTO users (username, email, password) OUTPUT INSERTED.id VALUES (?, ?, ?)";
    $insertParams = array($username, $email, $hashedPassword);
}
$stmt = sqlsrv_prepare($conn, $insertSql, $insertParams);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database query failed"]);
    exit;
}

$ok = sqlsrv_execute($stmt);
$newUserId = null;

if ($ok) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row) {
        $newUserId = $row['id'];
    }
}
sqlsrv_free_stmt($stmt);

if ($ok && $newUserId) {
    // Query user data - รวม role ถ้ามี
    if ($hasRoleColumn) {
        $selectSql = "SELECT id, username, email, role FROM users WHERE id=?";
    } else {
        $selectSql = "SELECT id, username, email FROM users WHERE id=?";
    }
    $selectParams = array($newUserId);
    $stmt2 = sqlsrv_prepare($conn, $selectSql, $selectParams);

    if ($stmt2 === false) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database query failed"]);
        exit;
    }

    if (!sqlsrv_execute($stmt2)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database query execution failed"]);
        exit;
    }

    $newUser = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt2);

    if ($newUser) {
        // กำหนด role (default 'user' ถ้าไม่มี column หรือไม่มีค่า)
        $userRole = 'user';
        if ($hasRoleColumn && isset($newUser["role"]) && !empty($newUser["role"])) {
            $userRole = $newUser["role"];
        }
        $newUser['role'] = $userRole;

        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "สมัครสมาชิกสำเร็จ",
            "user"    => $newUser
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "ไม่สามารถดึงข้อมูลผู้ใช้ใหม่ได้"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "ไม่สามารถสร้างผู้ใช้ใหม่ได้"]);
}
