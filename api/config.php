<?php

// SQL Server Configuration
$serverName = "DESKTOP-S0OP3KB"; // หรือ YOUR-PC\SQLEXPRESS
$connectionInfo = array(
    "Database" => "pawarit",
    "UID" => "sa",       // username
    "PWD" => "1234", // password
    "CharacterSet" => "UTF-8"
);

$secret_key = "4d2a8f9f23c5e17c0e7a4f2c6d76a2b19123a7bbde8d5cf3f9f8c33f6ac5d89c"; 

function getDBConnection() {
    global $serverName, $connectionInfo;
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        http_response_code(500);
        $errors = sqlsrv_errors();
        $errorMsg = "Database connection failed.";
        if ($errors) {
            $errorMsg .= " " . $errors[0]['message'];
        }
        die(json_encode(["status" => "error", "message" => $errorMsg]));
    }
    return $conn;
}
?>
