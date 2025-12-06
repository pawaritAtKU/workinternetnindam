<?php
require_once __DIR__ . '/headers.php';
require_once __DIR__ . '/db.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
  switch ($method) {

    case 'GET':
      if ($id) {
        $sql = "SELECT id, name, price, image_url, category_id, description, stock_quantity, store_id FROM products WHERE id = ?";
        $params = array($id);
        $stmt = sqlsrv_prepare($conn, $sql, $params);
        
        if ($stmt === false || !sqlsrv_execute($stmt)) {
          http_response_code(500);
          echo json_encode(['success' => false, 'message' => 'Database query failed']);
          break;
        }
        
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($stmt);
        
        if (!$row) {
          http_response_code(404);
          echo json_encode(['success' => false, 'message' => 'Not found']);
          break;
        }
        
        // Ensure stock_quantity exists
        if (!isset($row['stock_quantity'])) {
            $row['stock_quantity'] = 0;
        }
        
        // Get store name if store_id exists
        if (!empty($row['store_id'])) {
            $storeSql = "SELECT store_name FROM stores WHERE id = ?";
            $storeParams = array($row['store_id']);
            $storeStmt = sqlsrv_prepare($conn, $storeSql, $storeParams);
            if ($storeStmt && sqlsrv_execute($storeStmt)) {
                $store = sqlsrv_fetch_array($storeStmt, SQLSRV_FETCH_ASSOC);
                if ($store) {
                    $row['store_name'] = $store['store_name'];
                }
                sqlsrv_free_stmt($storeStmt);
            }
        }
        
        echo json_encode(['success' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
      } else {
        $sql = "SELECT id, name, price, image_url, category_id, description FROM products ORDER BY id DESC";
        $stmt = sqlsrv_query($conn, $sql);
        
        if ($stmt === false) {
          http_response_code(500);
          echo json_encode(['success' => false, 'message' => 'Database query failed']);
          break;
        }
        
        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
          $rows[] = $row;
        }
        sqlsrv_free_stmt($stmt);
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
      }
      break;

    case 'POST':
      $data = json_decode(file_get_contents('php://input'), true);
      if (
        !$data ||
        !isset($data['name']) ||
        !isset($data['price']) ||
        !isset($data['category_id'])
      ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'name, price, category_id are required']);
        break;
      }

      $sql = "INSERT INTO products (name, price, image_url, category_id, description) OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?)";
      $params = array(
        trim($data['name']),
        (float)$data['price'],
        $data['image_url'] ?? null,
        trim($data['category_id']),
        $data['description'] ?? null,
      );
      $stmt = sqlsrv_prepare($conn, $sql, $params);
      
      if ($stmt === false || !sqlsrv_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        break;
      }
      
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
      $newId = $row ? $row['id'] : null;
      sqlsrv_free_stmt($stmt);
      echo json_encode(['success' => true, 'id' => $newId], JSON_UNESCAPED_UNICODE);
      break;

    case 'PUT':
      if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing id']);
        break;
      }
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid body']);
        break;
      }

      $fields = [];
      $params = [];
      foreach (['name','price','image_url','category_id','description'] as $f) {
        if (array_key_exists($f, $data)) {
          $fields[] = "$f = ?";
          $params[] = ($f === 'price') ? (float)$data[$f] : $data[$f];
        }
      }
      if (!$fields) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        break;
      }

      $params[] = $id;
      $sql = "UPDATE products SET ".implode(', ', $fields)." WHERE id = ?";
      $stmt = sqlsrv_prepare($conn, $sql, $params);
      
      if ($stmt === false || !sqlsrv_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        break;
      }
      
      sqlsrv_free_stmt($stmt);
      echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
      break;

    case 'DELETE':
      if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing id']);
        break;
      }
      
      // ตรวจสอบว่ามี order_items ที่อ้างอิงสินค้านี้หรือไม่
      $checkOrderItemsSql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
      $checkParams = array($id);
      $checkStmt = sqlsrv_prepare($conn, $checkOrderItemsSql, $checkParams);
      
      if ($checkStmt === false || !sqlsrv_execute($checkStmt)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        break;
      }
      
      $orderItemsResult = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
      sqlsrv_free_stmt($checkStmt);
      
      if ($orderItemsResult && $orderItemsResult['count'] > 0) {
        http_response_code(400);
        echo json_encode([
          'success' => false, 
          'message' => 'ไม่สามารถลบสินค้าได้ เนื่องจากมีคำสั่งซื้อที่เกี่ยวข้องกับสินค้านี้อยู่ (' . $orderItemsResult['count'] . ' รายการ) กรุณาตรวจสอบประวัติการสั่งซื้อก่อน'
        ], JSON_UNESCAPED_UNICODE);
        break;
      }
      
      $sql = "DELETE FROM products WHERE id = ?";
      $params = array($id);
      $stmt = sqlsrv_prepare($conn, $sql, $params);
      
      if ($stmt === false || !sqlsrv_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        break;
      }
      
      sqlsrv_free_stmt($stmt);
      echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
      break;

    case 'OPTIONS':
      http_response_code(204);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
