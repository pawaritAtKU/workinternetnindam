@echo off
chcp 65001 >nul
echo ========================================
echo ตรวจสอบ SQL Server และการเชื่อมต่อ
echo ========================================
echo.

echo [1/4] ตรวจสอบ SQL Server Service...
echo.
sc query MSSQLSERVER | findstr "RUNNING" >nul
if %errorlevel% equ 0 (
    echo [OK] SQL Server (MSSQLSERVER) กำลังทำงานอยู่
) else (
    echo [WARNING] SQL Server (MSSQLSERVER) ไม่ทำงาน
    echo.
    echo วิธีแก้:
    echo 1. เปิด SQL Server Configuration Manager
    echo 2. ตรวจสอบว่า SQL Server Service ทำงานอยู่
    echo 3. หรือเปิด Services (services.msc) แล้วเริ่ม SQL Server Service
    echo.
)

sc query MSSQL$SQLEXPRESS | findstr "RUNNING" >nul
if %errorlevel% equ 0 (
    echo [OK] SQL Server Express กำลังทำงานอยู่
) else (
    echo [INFO] SQL Server Express ไม่ทำงาน (อาจไม่ได้ติดตั้ง)
)
echo.

echo [2/4] ตรวจสอบการตั้งค่าใน db.php...
echo.
if exist "api\db.php" (
    echo [OK] พบไฟล์ api\db.php
    findstr /i "DESKTOP-S0OP3KB" "api\db.php" >nul
    if %errorlevel% equ 0 (
        echo [OK] Server name: DESKTOP-S0OP3KB
    ) else (
        echo [INFO] ตรวจสอบ server name ใน db.php
    )
    findstr /i "pawarit" "api\db.php" >nul
    if %errorlevel% equ 0 (
        echo [OK] Database: pawarit
    )
) else (
    echo [ERROR] ไม่พบไฟล์ api\db.php
)
echo.

echo [3/4] ตรวจสอบ PHP Extensions...
echo.
set PHP_PATH=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64
set EXT_PATH=%PHP_PATH%\ext

if exist "%EXT_PATH%\php_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_sqlsrv_83_ts_x64.dll
    echo        ต้องดาวน์โหลดและคัดลอกไปยัง: %EXT_PATH%
)

if exist "%EXT_PATH%\php_pdo_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_pdo_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_pdo_sqlsrv_83_ts_x64.dll
    echo        ต้องดาวน์โหลดและคัดลอกไปยัง: %EXT_PATH%
)
echo.

echo [4/4] ตรวจสอบ php.ini...
echo.
set PHP_INI=%PHP_PATH%\php.ini
if exist "%PHP_INI%" (
    findstr /i "php_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
    if %errorlevel% equ 0 (
        echo [OK] พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
    ) else (
        echo [WARNING] ไม่พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
        echo           ต้องเพิ่ม extension ใน php.ini
    )
    
    findstr /i "php_pdo_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
    if %errorlevel% equ 0 (
        echo [OK] พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
    ) else (
        echo [WARNING] ไม่พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
        echo           ต้องเพิ่ม extension ใน php.ini
    )
) else (
    echo [ERROR] ไม่พบ php.ini: %PHP_INI%
)
echo.

echo ========================================
echo สรุป: ขั้นตอนที่ต้องทำ
echo ========================================
echo.
echo 1. ตรวจสอบว่า SQL Server ทำงานอยู่
echo    - เปิด SQL Server Management Studio
echo    - ถ้าเชื่อมต่อได้ แสดงว่า SQL Server ทำงานอยู่
echo.
echo 2. ติดตั้ง ODBC Driver (ถ้ายังไม่มี)
echo    - ดาวน์โหลด: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
echo    - ติดตั้ง ODBC Driver 17 หรือ 18 for SQL Server
echo.
echo 3. ดาวน์โหลดและติดตั้ง PHP Driver
echo    - ดาวน์โหลด: https://github.com/Microsoft/msphpsql/releases
echo    - เลือก PHP 8.3 Thread Safe (TS) 64-bit
echo    - คัดลอกไฟล์ DLL ไปยัง: %EXT_PATH%
echo.
echo 4. แก้ไข php.ini
echo    - เปิด: %PHP_INI%
echo    - เพิ่ม: extension=php_sqlsrv_83_ts_x64
echo    - เพิ่ม: extension=php_pdo_sqlsrv_83_ts_x64
echo.
echo 5. รีสตาร์ท Laragon
echo    - คลิกขวาที่ Laragon → Stop All
echo    - คลิกขวาที่ Laragon → Start All
echo.
echo 6. ทดสอบ
echo    - เปิด: http://workinternetnindam.test/api/check_sqlsrv.php
echo.
echo ========================================
echo.
pause


