<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "db.php";

$id       = $_POST["id"] ?? null;
$quantity = $_POST["quantity"] ?? null;

if (!$id || !$quantity) {
    echo json_encode(["error" => "Missing id or quantity"]);
    exit;
}

if ($quantity < 1) {
    $quantity = 1;
}

$sql = "UPDATE cart SET quantity = ? WHERE id = ?";
$params = array($quantity, $id);
$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    echo json_encode(["success" => false, "error" => $errorMsg]);
    exit;
}

if (sqlsrv_execute($stmt)) {
    sqlsrv_free_stmt($stmt);
    echo json_encode(["success" => true, "message" => "อัปเดตจำนวนเรียบร้อย"]);
} else {
    $errors = sqlsrv_errors();
    $errorMsg = "Database query execution failed";
    if ($errors) {
        $errorMsg .= ": " . $errors[0]['message'];
    }
    sqlsrv_free_stmt($stmt);
    echo json_encode(["success" => false, "error" => $errorMsg]);
}
?>
