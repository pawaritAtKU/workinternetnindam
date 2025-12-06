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
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

$tables = [
    'users' => false,
    'products' => false,
    'cart' => false,
    'stores' => false,
    'orders' => false,
    'order_items' => false
];

$columns = [
    'products' => [
        'stock_quantity' => false,
        'store_id' => false
    ]
];

try {
    // Check if tables exist
    foreach ($tables as $tableName => &$exists) {
        $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_NAME = ? AND TABLE_TYPE = 'BASE TABLE'";
        $params = array($tableName);
        $stmt = sqlsrv_prepare($conn, $sql, $params);
        
        if ($stmt && sqlsrv_execute($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $exists = $row && $row['count'] > 0;
            sqlsrv_free_stmt($stmt);
        }
    }
    
    // Check if columns exist in products table
    if ($tables['products']) {
        foreach ($columns['products'] as $columnName => &$exists) {
            $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'products' AND COLUMN_NAME = ?";
            $params = array($columnName);
            $stmt = sqlsrv_prepare($conn, $sql, $params);
            
            if ($stmt && sqlsrv_execute($stmt)) {
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $exists = $row && $row['count'] > 0;
                sqlsrv_free_stmt($stmt);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'tables' => $tables,
        'columns' => $columns,
        'allTablesExist' => !in_array(false, $tables),
        'allColumnsExist' => !in_array(false, $columns['products'])
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database check failed',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

