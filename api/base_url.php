<?php
/**
 * Base URL Configuration
 * แก้ไขที่นี่เพื่อเปลี่ยนระหว่าง localhost และ production
 */

// ตรวจสอบว่าเป็น localhost หรือไม่
$isLocalhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0
);

if ($isLocalhost) {
    // สำหรับ localhost
    $baseUrl = "http://localhost/workinternetnindam/";
    $uploadsUrl = "http://localhost/workinternetnindam/uploads/";
} else {
    // สำหรับ production (nindam.sytes.net)
    $baseUrl = "http://nindam.sytes.net/std6530251065/workinternetnindam/";
    $uploadsUrl = "http://nindam.sytes.net/std6530251065/workinternetnindam/uploads/";
}

// หรือกำหนดเองตรงนี้:
// $baseUrl = "http://localhost/workinternetnindam/";
// $uploadsUrl = "http://localhost/workinternetnindam/uploads/";

?>

