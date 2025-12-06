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

$q = $_GET['q'] ?? '';
$q = trim($q);
$category_id = $_GET['category_id'] ?? '';
$category_id = trim($category_id);

$params = [];
$where  = [];

if ($q) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like;
    $params[] = $like;
}

// ค้นหาตาม category
if ($category_id) {
    $where[] = "category_id = ?";
    $params[] = $category_id;
}

$sql = "SELECT id, name, price, image_url, description, category_id
        FROM products";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "error" => "Database query failed"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!sqlsrv_execute($stmt)) {
    echo json_encode([
        "success" => false,
        "error" => "Database query execution failed"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $items[] = $row;
}
sqlsrv_free_stmt($stmt);

echo json_encode([
    "success" => true,
    "query" => $q,
    "category_id" => $category_id,
    "count" => count($items),
    "products" => $items
], JSON_UNESCAPED_UNICODE);
?>
