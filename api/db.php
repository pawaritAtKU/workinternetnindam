<?php
// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

$serverName = "DESKTOP-S0OP3KB"; // หรือ YOUR-PC\SQLEXPRESS
$connectionInfo = array(
    "Database" => "pawarit",
    "UID" => "sa",       // username
    "PWD" => "1234", // password
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    // Ensure JSON header is set
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=UTF-8");
    }
    $errors = sqlsrv_errors();
    if ($errors) {
        die(json_encode(["success" => false, "message" => "DB Connection failed: " . $errors[0]['message']]));
    }
    die(json_encode(["success" => false, "message" => "DB Connection failed"]));
}
?>