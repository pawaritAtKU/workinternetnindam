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

$action = $_REQUEST['action'] ?? '';

if ($action === 'add') {
    // รับข้อมูลจาก POST body (URLSearchParams)
    $input = file_get_contents('php://input');
    
    // Debug log - เริ่มต้น
    $debug_info = [
        "timestamp" => date('Y-m-d H:i:s'),
        "input_raw" => $input,
        "input_length" => strlen($input),
        "POST" => $_POST,
        "REQUEST" => $_REQUEST,
        "CONTENT_TYPE" => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        "REQUEST_METHOD" => $_SERVER['REQUEST_METHOD'] ?? 'not set'
    ];
    error_log("=== CART ADD START ===");
    error_log("Cart ADD - Debug Info: " . json_encode($debug_info, JSON_UNESCAPED_UNICODE));
    
    // Parse URLSearchParams format
    parse_str($input, $postData);
    error_log("Cart ADD - Parsed postData: " . print_r($postData, true));
    
    // รวมข้อมูลจาก $_POST, parsed data, และ $_REQUEST
    $user_id    = intval($_POST['user_id'] ?? $postData['user_id'] ?? $_REQUEST['user_id'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? $postData['product_id'] ?? $_REQUEST['product_id'] ?? 0);
    $name       = $_POST['name'] ?? $postData['name'] ?? $_REQUEST['name'] ?? '';
    $price      = floatval($_POST['price'] ?? $postData['price'] ?? $_REQUEST['price'] ?? 0);
    $image_url  = $_POST['image_url'] ?? $postData['image_url'] ?? $_REQUEST['image_url'] ?? null;
    $qty        = intval($_POST['qty'] ?? $postData['qty'] ?? $_REQUEST['qty'] ?? 1);
    
    $parsed_data = [
        "user_id" => $user_id,
        "product_id" => $product_id,
        "name" => $name,
        "price" => $price,
        "image_url" => $image_url,
        "qty" => $qty
    ];
    error_log("Cart ADD - Parsed Data: " . json_encode($parsed_data, JSON_UNESCAPED_UNICODE));
    
    // ถ้ายังไม่มีข้อมูล ลองอ่านจาก raw input
    if (empty($user_id) || empty($product_id)) {
        // ลอง parse JSON ถ้าเป็น JSON
        $jsonData = json_decode($input, true);
        if ($jsonData) {
            $user_id = intval($user_id ?: ($jsonData['user_id'] ?? 0));
            $product_id = intval($product_id ?: ($jsonData['product_id'] ?? 0));
            $name = $name ?: ($jsonData['name'] ?? '');
            $price = $price ?: floatval($jsonData['price'] ?? 0);
            $image_url = $image_url ?: ($jsonData['image_url'] ?? null);
            $qty = $qty ?: intval($jsonData['qty'] ?? 1);
            error_log("Cart ADD - From JSON: user_id=$user_id, product_id=$product_id");
        }
    }

    if (!$user_id || $user_id <= 0 || !$product_id || $product_id <= 0) {
        error_log("Cart ADD - Missing data: user_id=$user_id, product_id=$product_id");
        echo json_encode([
            "success" => false, 
            "error" => "Missing user_id or product_id",
            "debug" => [
                "user_id" => $user_id,
                "product_id" => $product_id,
                "input" => substr($input, 0, 200),
                "POST" => $_POST,
                "parsed" => $postData
            ]
        ]);
        exit;
    }

    // Check if item exists
    error_log("Cart ADD - Checking existing item: user_id=$user_id, product_id=$product_id");
    $checkSql = "SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?";
    $checkParams = array($user_id, $product_id);
    $check = sqlsrv_prepare($conn, $checkSql, $checkParams);

    if ($check === false) {
        $errors = sqlsrv_errors();
        error_log("Cart ADD - SQL prepare error: " . print_r($errors, true));
        echo json_encode([
            "success" => false, 
            "error" => "Database query failed", 
            "debug" => [
                "errors" => $errors,
                "sql" => $checkSql,
                "params" => $checkParams
            ]
        ]);
        exit;
    }

    if (!sqlsrv_execute($check)) {
        $errors = sqlsrv_errors();
        error_log("Cart ADD - SQL execute error: " . print_r($errors, true));
        echo json_encode([
            "success" => false, 
            "error" => "Database query execution failed", 
            "debug" => [
                "errors" => $errors,
                "sql" => $checkSql,
                "params" => $checkParams
            ]
        ]);
        exit;
    }

    $row = sqlsrv_fetch_array($check, SQLSRV_FETCH_ASSOC);
    $rowDebug = [
        "row" => $row,
        "is_false" => ($row === false),
        "is_null" => ($row === null),
        "has_id" => isset($row['id']),
        "row_data" => $row ? json_encode($row, JSON_UNESCAPED_UNICODE) : "null/false"
    ];
    error_log("Cart ADD - Existing item check result: " . json_encode($rowDebug, JSON_UNESCAPED_UNICODE));

    if ($row !== false && $row !== null && isset($row['id'])) {
        // Update existing cart item
        $newQty = $row['quantity'] + $qty;
        sqlsrv_free_stmt($check);
        $updateSql = "UPDATE cart SET quantity=? WHERE id=?";
        $updateParams = array($newQty, $row['id']);
        $update = sqlsrv_prepare($conn, $updateSql, $updateParams);
        if ($update === false) {
            $errors = sqlsrv_errors();
            error_log("Cart ADD - Update prepare error: " . print_r($errors, true));
            error_log("=== CART ADD FAILED - UPDATE PREPARE ERROR ===");
            echo json_encode([
                "success" => false, 
                "error" => "Failed to prepare update query", 
                "debug" => [
                    "errors" => $errors,
                    "update_sql" => $updateSql,
                    "update_params" => $updateParams
                ]
            ]);
        } else if (!sqlsrv_execute($update)) {
            $errors = sqlsrv_errors();
            error_log("Cart ADD - Update execute error: " . print_r($errors, true));
            error_log("=== CART ADD FAILED - UPDATE EXECUTE ERROR ===");
            echo json_encode([
                "success" => false, 
                "error" => "Failed to execute update query", 
                "debug" => [
                    "errors" => $errors,
                    "update_sql" => $updateSql,
                    "update_params" => $updateParams
                ]
            ]);
        } else {
            $rowsAffected = sqlsrv_rows_affected($update);
            sqlsrv_free_stmt($update);
            error_log("Cart ADD - Updated existing item: id=" . $row['id'] . ", new_qty=$newQty, rows_affected=$rowsAffected");
            
            // Verify update
            $verifyUpdateSql = "SELECT * FROM cart WHERE id = ?";
            $verifyUpdateParams = array($row['id']);
            $verifyUpdateStmt = sqlsrv_prepare($conn, $verifyUpdateSql, $verifyUpdateParams);
            if ($verifyUpdateStmt && sqlsrv_execute($verifyUpdateStmt)) {
                $verifyUpdateRow = sqlsrv_fetch_array($verifyUpdateStmt, SQLSRV_FETCH_ASSOC);
                error_log("Cart ADD - Verified updated row: " . json_encode($verifyUpdateRow, JSON_UNESCAPED_UNICODE));
                sqlsrv_free_stmt($verifyUpdateStmt);
            }
            
            error_log("=== CART ADD SUCCESS - UPDATED ===");
            echo json_encode([
                "success" => true, 
                "message" => "Updated cart item", 
                "cart_id" => $row['id'],
                "debug" => [
                    "rows_affected" => $rowsAffected,
                    "old_quantity" => $row['quantity'],
                    "new_quantity" => $newQty
                ]
            ]);
        }
    } else {
        // Insert new cart item
        sqlsrv_free_stmt($check);
        $insertSql = "INSERT INTO cart (user_id, product_id, name, price, image_url, quantity) 
                      OUTPUT INSERTED.id
                      VALUES (?, ?, ?, ?, ?, ?)";
        $insertParams = array($user_id, $product_id, $name, $price, $image_url, $qty);
        $insert = sqlsrv_prepare($conn, $insertSql, $insertParams);
        error_log("Cart ADD - Attempting INSERT with params: " . json_encode($insertParams, JSON_UNESCAPED_UNICODE));
        if ($insert !== false && sqlsrv_execute($insert)) {
            error_log("Cart ADD - INSERT executed successfully");
            $insertedRow = sqlsrv_fetch_array($insert, SQLSRV_FETCH_ASSOC);
            error_log("Cart ADD - Inserted row data: " . ($insertedRow ? json_encode($insertedRow, JSON_UNESCAPED_UNICODE) : "NULL"));
            
            if ($insertedRow && isset($insertedRow['id'])) {
                $newCartId = $insertedRow['id'];
                sqlsrv_free_stmt($insert);
                error_log("Cart ADD - Inserted new item: id=$newCartId, user_id=$user_id, product_id=$product_id");
                
                // Verify the insert by querying back
                $verifySql = "SELECT * FROM cart WHERE id = ?";
                $verifyParams = array($newCartId);
                $verifyStmt = sqlsrv_prepare($conn, $verifySql, $verifyParams);
                if ($verifyStmt && sqlsrv_execute($verifyStmt)) {
                    $verifyRow = sqlsrv_fetch_array($verifyStmt, SQLSRV_FETCH_ASSOC);
                    error_log("Cart ADD - Verified inserted row: " . json_encode($verifyRow, JSON_UNESCAPED_UNICODE));
                    
                    // Also verify by user_id
                    $verifyUserSql = "SELECT * FROM cart WHERE user_id = ?";
                    $verifyUserParams = array($user_id);
                    $verifyUserStmt = sqlsrv_prepare($conn, $verifyUserSql, $verifyUserParams);
                    if ($verifyUserStmt && sqlsrv_execute($verifyUserStmt)) {
                        $userItems = [];
                        while ($userRow = sqlsrv_fetch_array($verifyUserStmt, SQLSRV_FETCH_ASSOC)) {
                            $item = [];
                            foreach ($userRow as $key => $value) {
                                if ($value instanceof DateTime) {
                                    $item[$key] = $value->format('Y-m-d H:i:s');
                                } else {
                                    $item[$key] = $value;
                                }
                            }
                            $userItems[] = $item;
                        }
                        error_log("Cart ADD - All items for user_id $user_id: " . json_encode($userItems, JSON_UNESCAPED_UNICODE));
                        sqlsrv_free_stmt($verifyUserStmt);
                    }
                    sqlsrv_free_stmt($verifyStmt);
                }
                
                error_log("=== CART ADD SUCCESS ===");
                echo json_encode([
                    "success" => true, 
                    "message" => "Added to cart", 
                    "cart_id" => $newCartId,
                    "debug" => [
                        "user_id" => $user_id,
                        "product_id" => $product_id,
                        "inserted_id" => $newCartId,
                        "inserted_data" => $insertedRow
                    ]
                ]);
            } else {
                sqlsrv_free_stmt($insert);
                error_log("Cart ADD - Insert executed but no ID returned. insertedRow: " . print_r($insertedRow, true));
                error_log("=== CART ADD FAILED - NO ID ===");
                echo json_encode([
                    "success" => false, 
                    "error" => "Insert succeeded but no ID returned",
                    "debug" => [
                        "insertedRow" => $insertedRow,
                        "insert_params" => $insertParams
                    ]
                ]);
            }
        } else {
            $errors = sqlsrv_errors();
            error_log("Cart ADD - Insert failed. Errors: " . print_r($errors, true));
            error_log("=== CART ADD FAILED ===");
            echo json_encode([
                "success" => false, 
                "error" => "Failed to add to cart", 
                "debug" => [
                    "errors" => $errors,
                    "user_id" => $user_id,
                    "product_id" => $product_id,
                    "insert_params" => $insertParams,
                    "insert_sql" => $insertSql
                ]
            ]);
        }
    }
    exit;

} elseif ($action === 'get') {
    error_log("=== CART GET START ===");
    $user_id = intval($_REQUEST['user_id'] ?? $_GET['user_id'] ?? 0);
    
    // Debug log
    $get_debug = [
        "timestamp" => date('Y-m-d H:i:s'),
        "user_id" => $user_id,
        "user_id_type" => gettype($user_id),
        "REQUEST" => $_REQUEST,
        "GET" => $_GET
    ];
    error_log("Cart GET request: " . json_encode($get_debug, JSON_UNESCAPED_UNICODE));
    
    if (!$user_id || $user_id <= 0) {
        error_log("Cart GET - Invalid user_id: $user_id");
        error_log("=== CART GET FAILED - INVALID USER_ID ===");
        echo json_encode([
            "success" => false, 
            "error" => "Missing or invalid user_id", 
            "debug" => [
                "REQUEST" => $_REQUEST, 
                "GET" => $_GET, 
                "parsed_user_id" => $user_id
            ]
        ]);
        exit;
    }

    $sql = "SELECT * FROM cart WHERE user_id=? ORDER BY id DESC";
    $params = array($user_id);
    
    error_log("Cart GET - Executing SQL: $sql with user_id=$user_id");
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        error_log("Cart GET - SQL prepare failed: " . print_r($errors, true));
        error_log("=== CART GET FAILED - PREPARE ERROR ===");
        echo json_encode([
            "success" => false, 
            "error" => "Database query failed", 
            "debug" => [
                "errors" => $errors,
                "sql" => $sql,
                "params" => $params
            ]
        ]);
        exit;
    }

    $items = [];
    require_once "base_url.php";
    
    if (!sqlsrv_execute($stmt)) {
        $errors = sqlsrv_errors();
        error_log("Cart GET - SQL execute failed: " . print_r($errors, true));
        error_log("=== CART GET FAILED - EXECUTE ERROR ===");
        echo json_encode([
            "success" => false, 
            "error" => "Database query execution failed", 
            "debug" => [
                "errors" => $errors,
                "sql" => $sql,
                "params" => $params
            ]
        ]);
        exit;
    }
    
    error_log("Cart GET - SQL executed successfully, fetching rows...");
    $rowCount = 0;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rowCount++;
        // แปลง DateTime objects เป็น string
        $item = [];
        foreach ($row as $key => $value) {
            if ($value instanceof DateTime) {
                $item[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $item[$key] = $value;
            }
        }
        
        // แก้ไข image_url
        if (!empty($item['image_url']) && strpos($item['image_url'], 'http') !== 0) {
            $item['image_url'] = $uploadsUrl . ltrim($item['image_url'], '/');
        }
        
        error_log("Cart GET - Item #$rowCount: " . json_encode($item, JSON_UNESCAPED_UNICODE));
        $items[] = $item;
    }
    sqlsrv_free_stmt($stmt);

    error_log("Cart GET - Total items found: " . count($items) . " (rows fetched: $rowCount)");
    error_log("=== CART GET END ===");
    echo json_encode([
        "success" => true, 
        "cart" => $items, 
        "debug" => [
            "user_id" => $user_id,
            "user_id_type" => gettype($user_id),
            "count" => count($items),
            "rows_fetched" => $rowCount,
            "sql" => $sql,
            "params" => $params
        ]
    ]);
    exit;

} elseif ($action === 'remove') {
    $id = $_REQUEST['id'] ?? '';
    if (!$id) {
        echo json_encode(["success" => false, "error" => "Missing cart item id"]);
        exit;
    }

    $sql = "DELETE FROM cart WHERE id=?";
    $params = array($id);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt !== false) {
        sqlsrv_execute($stmt);
        sqlsrv_free_stmt($stmt);
    }

    echo json_encode(["success" => true]);
    exit;

} elseif ($action === 'clear') {
    $user_id = $_REQUEST['user_id'] ?? '';
    if (!$user_id) {
        echo json_encode(["success" => false, "error" => "Missing user_id", "debug" => $_REQUEST]);
        exit;
    }

    $sql = "DELETE FROM cart WHERE user_id=?";
    $params = array($user_id);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    $ok = false;
    if ($stmt !== false) {
        $ok = sqlsrv_execute($stmt);
        sqlsrv_free_stmt($stmt);
    }

    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Cart cleared" : "Delete failed",
        "user_id" => $user_id
    ]);
    exit;
}

echo json_encode(["success" => false, "error" => "Invalid action"]);
