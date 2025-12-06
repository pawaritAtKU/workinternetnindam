<?php
// Disable error display and enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any errors
ob_start();

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
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Clear any output before starting
ob_clean();

require_once "db.php";
require_once "config.php";

// Require JWT files in correct order - interface must come first
try {
    if (!interface_exists('Firebase\JWT\JWTExceptionWithPayloadInterface')) {
        $interfacePath = __DIR__ . '/php-jwt/JWTExceptionWithPayloadInterface.php';
        if (!file_exists($interfacePath)) {
            throw new Exception("JWTExceptionWithPayloadInterface.php not found at: " . $interfacePath);
        }
        require_once $interfacePath;
    }
    
    if (!class_exists('Firebase\JWT\JWT')) {
        require_once __DIR__ . '/php-jwt/JWT.php';
    }
    if (!class_exists('Firebase\JWT\Key')) {
        require_once __DIR__ . '/php-jwt/Key.php';
    }
    if (!class_exists('Firebase\JWT\ExpiredException')) {
        require_once __DIR__ . '/php-jwt/ExpiredException.php';
    }
    if (!class_exists('Firebase\JWT\SignatureInvalidException')) {
        require_once __DIR__ . '/php-jwt/SignatureInvalidException.php';
    }
    if (!class_exists('Firebase\JWT\BeforeValidException')) {
        require_once __DIR__ . '/php-jwt/BeforeValidException.php';
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false, 
        "message" => "JWT library initialization failed", 
        "error" => $e->getMessage()
    ]);
    exit;
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Verify JWT token
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token ไม่พบ"]);
        exit;
    }
    
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    $userId = $decoded->data->id;
    
    switch ($method) {
        case 'GET':
            // Get store by user_id
            $sql = "SELECT * FROM stores WHERE user_id = ?";
            $params = array($userId);
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
            
            $store = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmt);
            
            if ($store) {
                // Convert DateTime objects
                foreach ($store as $key => $value) {
                    if ($value instanceof DateTime) {
                        $store[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
                echo json_encode(["success" => true, "store" => $store]);
            } else {
                echo json_encode(["success" => false, "message" => "ไม่พบร้านค้า"]);
            }
            break;
            
        case 'POST':
            // Create or update store
            $data = json_decode(file_get_contents("php://input"), true);
            
            $storeName = isset($data['store_name']) ? trim($data['store_name']) : '';
            $storeDescription = isset($data['store_description']) ? trim($data['store_description']) : '';
            $storeAddress = isset($data['store_address']) ? trim($data['store_address']) : '';
            $storePhone = isset($data['store_phone']) ? trim($data['store_phone']) : '';
            $storeEmail = isset($data['store_email']) ? trim($data['store_email']) : '';
            
            if (empty($storeName)) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อร้านค้า"]);
                exit;
            }
            
            // Check if store exists
            $checkSql = "SELECT id FROM stores WHERE user_id = ?";
            $checkParams = array($userId);
            $checkStmt = sqlsrv_prepare($conn, $checkSql, $checkParams);
            
            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingStore = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
                sqlsrv_free_stmt($checkStmt);
                
                if ($existingStore) {
                    // Update existing store
                    $updateSql = "UPDATE stores SET 
                                    store_name = ?, 
                                    store_description = ?, 
                                    store_address = ?, 
                                    store_phone = ?, 
                                    store_email = ?,
                                    updated_at = GETDATE()
                                  WHERE user_id = ?";
                    $updateParams = array($storeName, $storeDescription, $storeAddress, $storePhone, $storeEmail, $userId);
                    $updateStmt = sqlsrv_prepare($conn, $updateSql, $updateParams);
                    
                    if ($updateStmt && sqlsrv_execute($updateStmt)) {
                        echo json_encode(["success" => true, "message" => "อัปเดตร้านค้าสำเร็จ"]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["success" => false, "message" => "ไม่สามารถอัปเดตร้านค้าได้"]);
                    }
                    sqlsrv_free_stmt($updateStmt);
                } else {
                    // Create new store
                    $insertSql = "INSERT INTO stores (user_id, store_name, store_description, store_address, store_phone, store_email) 
                                  OUTPUT INSERTED.id 
                                  VALUES (?, ?, ?, ?, ?, ?)";
                    $insertParams = array($userId, $storeName, $storeDescription, $storeAddress, $storePhone, $storeEmail);
                    $insertStmt = sqlsrv_prepare($conn, $insertSql, $insertParams);
                    
                    if ($insertStmt && sqlsrv_execute($insertStmt)) {
                        $newStore = sqlsrv_fetch_array($insertStmt, SQLSRV_FETCH_ASSOC);
                        sqlsrv_free_stmt($insertStmt);
                        echo json_encode(["success" => true, "message" => "สร้างร้านค้าสำเร็จ", "store_id" => $newStore['id']]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["success" => false, "message" => "ไม่สามารถสร้างร้านค้าได้"]);
                    }
                }
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
    
} catch (\Firebase\JWT\ExpiredException $e) {
    ob_clean();
    http_response_code(401);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(["success" => false, "message" => "Token หมดอายุ"]);
    exit;
} catch (\Exception $e) {
    ob_clean();
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false, 
        "message" => "เกิดข้อผิดพลาด", 
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
    exit;
} catch (\Error $e) {
    ob_clean();
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false, 
        "message" => "เกิดข้อผิดพลาดร้ายแรง", 
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
    exit;
}

