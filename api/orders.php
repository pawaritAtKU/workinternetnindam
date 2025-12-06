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
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
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
    $userRole = $decoded->data->role ?? 'user';
    
    switch ($method) {
        case 'GET':
            // Get orders
            $orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            
            if ($orderId) {
                // Get single order with items
                $sql = "SELECT o.*, 
                        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                        FROM orders o 
                        WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')";
                $params = array($orderId, $userId, $userRole);
            } else {
                // Get all orders
                if ($userRole === 'admin') {
                    // Admin can see all orders
                    $sql = "SELECT o.*, 
                            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                            FROM orders o";
                    if ($status) {
                        $sql .= " WHERE o.order_status = ?";
                        $params = array($status);
                    } else {
                        $params = array();
                    }
                    $sql .= " ORDER BY o.created_at DESC";
                } else {
                    // User can only see their own orders
                    $sql = "SELECT o.*, 
                            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                            FROM orders o 
                            WHERE o.user_id = ?";
                    $params = array($userId);
                    if ($status) {
                        $sql .= " AND o.order_status = ?";
                        $params[] = $status;
                    }
                    $sql .= " ORDER BY o.created_at DESC";
                }
            }
            
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
            
            $orders = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Convert DateTime objects
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $row[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
                
                // Get order items if single order
                if ($orderId) {
                    $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
                    $itemsParams = array($orderId);
                    $itemsStmt = sqlsrv_prepare($conn, $itemsSql, $itemsParams);
                    
                    if ($itemsStmt && sqlsrv_execute($itemsStmt)) {
                        $items = [];
                        while ($item = sqlsrv_fetch_array($itemsStmt, SQLSRV_FETCH_ASSOC)) {
                            foreach ($item as $key => $value) {
                                if ($value instanceof DateTime) {
                                    $item[$key] = $value->format('Y-m-d H:i:s');
                                }
                            }
                            $items[] = $item;
                        }
                        $row['items'] = $items;
                        sqlsrv_free_stmt($itemsStmt);
                    }
                }
                
                $orders[] = $row;
            }
            sqlsrv_free_stmt($stmt);
            
            if ($orderId && count($orders) === 0) {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "ไม่พบคำสั่งซื้อ"]);
            } else {
                echo json_encode(["success" => true, "orders" => $orders]);
            }
            break;
            
        case 'POST':
            // Create new order
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "ไม่มีสินค้าในคำสั่งซื้อ"]);
                exit;
            }
            
            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Calculate total
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }
            $shippingFee = isset($data['shipping_fee']) ? (float)$data['shipping_fee'] : 50.00;
            $finalTotal = $totalAmount + $shippingFee;
            
            // Start transaction
            sqlsrv_begin_transaction($conn);
            
            try {
                // Insert order
                $orderSql = "INSERT INTO orders (
                    user_id, order_number, total_amount, shipping_fee,
                    shipping_name, shipping_phone, shipping_address, shipping_province, shipping_postcode,
                    payment_method, payment_status, order_status, notes
                ) OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $orderParams = array(
                    $userId,
                    $orderNumber,
                    $finalTotal,
                    $shippingFee,
                    $data['shipping_name'] ?? '',
                    $data['shipping_phone'] ?? '',
                    $data['shipping_address'] ?? '',
                    $data['shipping_province'] ?? '',
                    $data['shipping_postcode'] ?? '',
                    $data['payment_method'] ?? 'cod',
                    'pending',
                    'pending',
                    $data['notes'] ?? null
                );
                
                $orderStmt = sqlsrv_prepare($conn, $orderSql, $orderParams);
                
                if (!$orderStmt || !sqlsrv_execute($orderStmt)) {
                    throw new Exception("ไม่สามารถสร้างคำสั่งซื้อได้");
                }
                
                $orderRow = sqlsrv_fetch_array($orderStmt, SQLSRV_FETCH_ASSOC);
                $newOrderId = $orderRow['id'];
                sqlsrv_free_stmt($orderStmt);
                
                // Insert order items and update stock
                foreach ($data['items'] as $item) {
                    // Insert order item
                    $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
                                VALUES (?, ?, ?, ?, ?, ?)";
                    $itemParams = array(
                        $newOrderId,
                        $item['product_id'],
                        $item['name'],
                        $item['price'],
                        $item['quantity'],
                        $item['price'] * $item['quantity']
                    );
                    
                    $itemStmt = sqlsrv_prepare($conn, $itemSql, $itemParams);
                    if (!$itemStmt || !sqlsrv_execute($itemStmt)) {
                        throw new Exception("ไม่สามารถเพิ่มรายการสินค้าได้");
                    }
                    sqlsrv_free_stmt($itemStmt);
                    
                    // Update product stock - ลดจำนวนสินค้าตามจำนวนที่สั่ง
                    if (isset($item['product_id']) && $item['product_id'] > 0) {
                        $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?";
                        $updateStockParams = array($item['quantity'], $item['product_id'], $item['quantity']);
                        $updateStockStmt = sqlsrv_prepare($conn, $updateStockSql, $updateStockParams);
                        if ($updateStockStmt && sqlsrv_execute($updateStockStmt)) {
                            $rowsAffected = sqlsrv_rows_affected($updateStockStmt);
                            if ($rowsAffected === false || $rowsAffected === 0) {
                                throw new Exception("สินค้า " . ($item['name'] ?? '') . " มีจำนวนไม่พอ");
                            }
                            sqlsrv_free_stmt($updateStockStmt);
                        } else {
                            throw new Exception("ไม่สามารถอัปเดตจำนวนสินค้าได้");
                        }
                    } else {
                        throw new Exception("ไม่พบ product_id ในรายการสินค้า");
                    }
                }
                
                // Clear cart
                $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
                $clearCartParams = array($userId);
                $clearCartStmt = sqlsrv_prepare($conn, $clearCartSql, $clearCartParams);
                if ($clearCartStmt) {
                    sqlsrv_execute($clearCartStmt);
                    sqlsrv_free_stmt($clearCartStmt);
                }
                
                sqlsrv_commit($conn);
                
                echo json_encode([
                    "success" => true,
                    "message" => "สร้างคำสั่งซื้อสำเร็จ",
                    "order_id" => $newOrderId,
                    "order_number" => $orderNumber
                ]);
                
            } catch (Exception $e) {
                sqlsrv_rollback($conn);
                http_response_code(500);
                echo json_encode(["success" => false, "message" => $e->getMessage()]);
            }
            break;
            
        case 'PUT':
            // Update order status (admin only)
            if ($userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "ไม่มีสิทธิ์"]);
                exit;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $orderId = isset($data['id']) ? (int)$data['id'] : null;
            $orderStatus = isset($data['order_status']) ? $data['order_status'] : null;
            $paymentStatus = isset($data['payment_status']) ? $data['payment_status'] : null;
            
            if (!$orderId) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณาระบุ ID คำสั่งซื้อ"]);
                exit;
            }
            
            $updateSql = "UPDATE orders SET updated_at = GETDATE()";
            $params = array();
            
            if ($orderStatus) {
                $updateSql .= ", order_status = ?";
                $params[] = $orderStatus;
            }
            
            if ($paymentStatus) {
                $updateSql .= ", payment_status = ?";
                $params[] = $paymentStatus;
            }
            
            $updateSql .= " WHERE id = ?";
            $params[] = $orderId;
            
            $updateStmt = sqlsrv_prepare($conn, $updateSql, $params);
            
            if ($updateStmt && sqlsrv_execute($updateStmt)) {
                echo json_encode(["success" => true, "message" => "อัปเดตสถานะคำสั่งซื้อสำเร็จ"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "ไม่สามารถอัปเดตได้"]);
            }
            sqlsrv_free_stmt($updateStmt);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
    
} catch (\Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token หมดอายุ"]);
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token ไม่ถูกต้อง", "error" => $e->getMessage()]);
}

