<?php
/**
 * ไฟล์ตรวจสอบการติดตั้ง SQL Server Driver for PHP
 * เปิดไฟล์นี้ในเบราว์เซอร์เพื่อตรวจสอบ
 */

header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ตรวจสอบ SQL Server Driver</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>ตรวจสอบการติดตั้ง SQL Server Driver for PHP</h1>
    
    <h2>1. ตรวจสอบ Extension</h2>
    <?php if (extension_loaded('sqlsrv')): ?>
        <p class="success">✓ SQL Server Driver (sqlsrv) ติดตั้งแล้ว</p>
    <?php else: ?>
        <p class="error">✗ SQL Server Driver (sqlsrv) ยังไม่ได้ติดตั้ง</p>
        <p>กรุณาติดตั้งตามคำแนะนำในไฟล์ INSTALL_SQLSERVER.md</p>
    <?php endif; ?>
    
    <h2>2. ตรวจสอบ PDO SQL Server</h2>
    <?php if (extension_loaded('pdo_sqlsrv')): ?>
        <p class="success">✓ PDO SQL Server Driver ติดตั้งแล้ว</p>
    <?php else: ?>
        <p class="error">✗ PDO SQL Server Driver ยังไม่ได้ติดตั้ง</p>
    <?php endif; ?>
    
    <h2>3. ตรวจสอบ ODBC Driver</h2>
    <div class="info">
        <p><strong>หมายเหตุ:</strong> ตรวจสอบ ODBC Driver ด้วยคำสั่งใน Command Prompt:</p>
        <pre>odbcinst -q -d</pre>
        <p>ควรมี "ODBC Driver 17 for SQL Server" หรือ "ODBC Driver 18 for SQL Server"</p>
        <p>หากไม่มี ให้ดาวน์โหลดและติดตั้งจาก:</p>
        <p>https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server</p>
    </div>
    
    <h2>4. ทดสอบการเชื่อมต่อ</h2>
    <?php
    if (extension_loaded('sqlsrv')) {
        require_once "db.php";
        
        if ($conn !== false) {
            echo "<p class='success'>✓ เชื่อมต่อ SQL Server สำเร็จ!</p>";
            
            // แสดง Client Info
            $info = sqlsrv_client_info($conn);
            if ($info) {
                echo "<div class='info'>";
                echo "<strong>Client Info:</strong><br>";
                echo "<pre>";
                print_r($info);
                echo "</pre>";
                echo "</div>";
            }
            
            // ทดสอบ query
            $sql = "SELECT @@VERSION AS Version";
            $stmt = sqlsrv_query($conn, $sql);
            
            if ($stmt !== false) {
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                echo "<div class='info'>";
                echo "<strong>SQL Server Version:</strong><br>";
                echo "<pre>" . htmlspecialchars($row['Version']) . "</pre>";
                echo "</div>";
                sqlsrv_free_stmt($stmt);
            }
            
            sqlsrv_close($conn);
        } else {
            echo "<p class='error'>✗ ไม่สามารถเชื่อมต่อ SQL Server ได้</p>";
            echo "<div class='info'>";
            echo "<strong>Error:</strong><br>";
            $errors = sqlsrv_errors();
            if ($errors) {
                echo "<pre>";
                print_r($errors);
                echo "</pre>";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='error'>ไม่สามารถทดสอบได้เพราะ SQL Server Driver ยังไม่ได้ติดตั้ง</p>";
    }
    ?>
    
    <h2>5. ข้อมูล PHP</h2>
    <div class="info">
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>PHP Architecture:</strong> <?php echo (PHP_INT_SIZE * 8) . '-bit'; ?></p>
        <p><strong>Thread Safety:</strong> <?php echo ZEND_THREAD_SAFE ? 'Yes (TS)' : 'No (NTS)'; ?></p>
        <p><strong>php.ini location:</strong> <?php echo php_ini_loaded_file(); ?></p>
    </div>
    
    <h2>6. Extensions ที่โหลดอยู่</h2>
    <div class="info">
        <pre>
<?php
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    if (stripos($ext, 'sql') !== false || stripos($ext, 'odbc') !== false || stripos($ext, 'pdo') !== false) {
        echo $ext . "\n";
    }
}
?>
        </pre>
    </div>
    
    <hr>
    <p><small>สำหรับข้อมูลเพิ่มเติม ดูไฟล์ INSTALL_SQLSERVER.md</small></p>
</body>
</html>

