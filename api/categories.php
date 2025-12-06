<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';
require_once 'jwt_utils.php';
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Verify JWT token
    $decoded = verify_jwt_or_die();
    $userId = $decoded->data->id;
    $userRole = isset($decoded->data->role) ? $decoded->data->role : 'user';

    // ตรวจสอบว่า categories table มีอยู่หรือไม่
    $checkTableSql = "SELECT COUNT(*) AS count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'categories'";
    $checkTableStmt = sqlsrv_query($conn, $checkTableSql);
    $hasCategoriesTable = false;
    if ($checkTableStmt) {
        $checkTableRow = sqlsrv_fetch_array($checkTableStmt, SQLSRV_FETCH_ASSOC);
        if ($checkTableRow && $checkTableRow['count'] > 0) {
            $hasCategoriesTable = true;
        }
        sqlsrv_free_stmt($checkTableStmt);
    }

    // สร้าง categories table ถ้ายังไม่มี
    if (!$hasCategoriesTable) {
        $createTableSql = "CREATE TABLE categories (
            id INT IDENTITY(1,1) PRIMARY KEY,
            name NVARCHAR(255) NOT NULL,
            description NVARCHAR(MAX) NULL,
            is_active BIT DEFAULT 1,
            created_at DATETIME DEFAULT GETDATE(),
            updated_at DATETIME DEFAULT GETDATE()
        )";
        sqlsrv_query($conn, $createTableSql);
    }

    switch ($method) {
        case 'GET':
            // Get all categories
            $categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($categoryId) {
                // Get single category
                $sql = "SELECT * FROM categories WHERE id = ?";
                $params = array($categoryId);
            } else {
                // Get all categories
                $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
                $params = array();
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
            
            $categories = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $category = [];
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $category[$key] = $value->format('Y-m-d H:i:s');
                    } else {
                        $category[$key] = $value;
                    }
                }
                $categories[] = $category;
            }
            sqlsrv_free_stmt($stmt);
            
            if ($categoryId) {
                echo json_encode(["success" => true, "category" => $categories[0] ?? null]);
            } else {
                echo json_encode(["success" => true, "categories" => $categories]);
            }
            break;
            
        case 'POST':
            // Create category (admin only)
            if ($userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "ไม่มีสิทธิ์"]);
                exit;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $name = isset($data['name']) ? trim($data['name']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';
            
            if (empty($name)) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อหมวดหมู่"]);
                exit;
            }
            
            $insertSql = "INSERT INTO categories (name, description) OUTPUT INSERTED.id VALUES (?, ?)";
            $insertParams = array($name, $description);
            $stmt = sqlsrv_prepare($conn, $insertSql, $insertParams);
            
            if ($stmt === false || !sqlsrv_execute($stmt)) {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "ไม่สามารถสร้างหมวดหมู่ได้"]);
                exit;
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmt);
            
            echo json_encode([
                "success" => true,
                "message" => "สร้างหมวดหมู่สำเร็จ",
                "id" => $row['id']
            ]);
            break;
            
        case 'PUT':
            // Update category (admin only)
            if ($userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "ไม่มีสิทธิ์"]);
                exit;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $id = isset($data['id']) ? (int)$data['id'] : null;
            $name = isset($data['name']) ? trim($data['name']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            if (!$id || empty($name)) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
                exit;
            }
            
            $updateSql = "UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = GETDATE() WHERE id = ?";
            $updateParams = array($name, $description, $isActive, $id);
            $stmt = sqlsrv_prepare($conn, $updateSql, $updateParams);
            
            if ($stmt === false || !sqlsrv_execute($stmt)) {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "ไม่สามารถอัปเดตหมวดหมู่ได้"]);
                exit;
            }
            
            sqlsrv_free_stmt($stmt);
            echo json_encode(["success" => true, "message" => "อัปเดตหมวดหมู่สำเร็จ"]);
            break;
            
        case 'DELETE':
            // Delete category (admin only)
            if ($userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "ไม่มีสิทธิ์"]);
                exit;
            }
            
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณาระบุ ID หมวดหมู่"]);
                exit;
            }
            
            // ตรวจสอบว่ามีสินค้าใช้หมวดหมู่นี้หรือไม่
            $checkProductsSql = "SELECT COUNT(*) AS count FROM products WHERE category_id = ?";
            $checkProductsParams = array($id);
            $checkProductsStmt = sqlsrv_prepare($conn, $checkProductsSql, $checkProductsParams);
            
            if ($checkProductsStmt && sqlsrv_execute($checkProductsStmt)) {
                $checkProductsRow = sqlsrv_fetch_array($checkProductsStmt, SQLSRV_FETCH_ASSOC);
                sqlsrv_free_stmt($checkProductsStmt);
                
                if ($checkProductsRow && $checkProductsRow['count'] > 0) {
                    http_response_code(400);
                    echo json_encode([
                        "success" => false,
                        "message" => "ไม่สามารถลบได้ เนื่องจากมีสินค้าใช้หมวดหมู่นี้อยู่"
                    ]);
                    exit;
                }
            }
            
            $deleteSql = "DELETE FROM categories WHERE id = ?";
            $deleteParams = array($id);
            $deleteStmt = sqlsrv_prepare($conn, $deleteSql, $deleteParams);
            
            if ($deleteStmt === false || !sqlsrv_execute($deleteStmt)) {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "ไม่สามารถลบหมวดหมู่ได้"]);
                exit;
            }
            
            sqlsrv_free_stmt($deleteStmt);
            echo json_encode(["success" => true, "message" => "ลบหมวดหมู่สำเร็จ"]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
?>

