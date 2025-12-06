@echo off
chcp 65001 >nul
echo ========================================
echo ติดตั้ง SQL Server Driver for PHP
echo ========================================
echo.

echo [1/5] ตรวจสอบข้อมูล PHP...
echo.

set PHP_PATH=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64
set EXT_PATH=%PHP_PATH%\ext
set PHP_INI=%PHP_PATH%\php.ini

if not exist "%PHP_PATH%" (
    echo [ERROR] ไม่พบโฟลเดอร์ PHP: %PHP_PATH%
    echo กรุณาตรวจสอบว่า Laragon ติดตั้งที่ C:\laragon
    pause
    exit /b 1
)

echo [OK] พบโฟลเดอร์ PHP: %PHP_PATH%
echo [OK] Extensions folder: %EXT_PATH%
echo [OK] php.ini: %PHP_INI%
echo.

echo [2/5] ตรวจสอบ ODBC Driver...
echo.
odbcinst -q -d 2>nul | findstr /i "SQL Server" >nul
if %errorlevel% equ 0 (
    echo [OK] พบ ODBC Driver for SQL Server
    odbcinst -q -d | findstr /i "SQL Server"
) else (
    echo [WARNING] ไม่พบ ODBC Driver for SQL Server
    echo กรุณาติดตั้ง ODBC Driver 17 หรือ 18 for SQL Server ก่อน
    echo ดาวน์โหลดจาก: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
    echo.
    pause
)
echo.

echo [3/5] ตรวจสอบไฟล์ DLL...
echo.
if exist "%EXT_PATH%\php_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_sqlsrv_83_ts_x64.dll
    echo กรุณาคัดลอกไฟล์ไปยัง: %EXT_PATH%
    echo.
)

if exist "%EXT_PATH%\php_pdo_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_pdo_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_pdo_sqlsrv_83_ts_x64.dll
    echo กรุณาคัดลอกไฟล์ไปยัง: %EXT_PATH%
    echo.
)
echo.

echo [4/5] ตรวจสอบการตั้งค่าใน php.ini...
echo.
findstr /i "php_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
if %errorlevel% equ 0 (
    echo [OK] พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
) else (
    echo [WARNING] ไม่พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
    echo.
    set /p ADD_EXT="ต้องการเพิ่ม extension ใน php.ini อัตโนมัติหรือไม่? (Y/N): "
    if /i "%ADD_EXT%"=="Y" (
        echo. >> "%PHP_INI%"
        echo ; SQL Server Driver >> "%PHP_INI%"
        echo extension=php_sqlsrv_83_ts_x64 >> "%PHP_INI%"
        echo extension=php_pdo_sqlsrv_83_ts_x64 >> "%PHP_INI%"
        echo [OK] เพิ่ม extension ใน php.ini แล้ว
    )
)

findstr /i "php_pdo_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
if %errorlevel% equ 0 (
    echo [OK] พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
) else (
    echo [WARNING] ไม่พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
)
echo.

echo [5/5] สรุปการตรวจสอบ...
echo.
echo ========================================
echo ขั้นตอนต่อไป:
echo ========================================
echo 1. ดาวน์โหลด Microsoft Drivers for PHP for SQL Server
echo    จาก: https://github.com/Microsoft/msphpsql/releases
echo.
echo 2. แตกไฟล์และคัดลอกไฟล์ต่อไปนี้ไปยัง:
echo    %EXT_PATH%
echo    - php_sqlsrv_83_ts_x64.dll
echo    - php_pdo_sqlsrv_83_ts_x64.dll
echo.
echo 3. ตรวจสอบว่าเพิ่ม extension ใน php.ini แล้ว:
echo    extension=php_sqlsrv_83_ts_x64
echo    extension=php_pdo_sqlsrv_83_ts_x64
echo.
echo 4. รีสตาร์ท Laragon (Stop All แล้ว Start All)
echo.
echo 5. ตรวจสอบการติดตั้ง:
echo    http://workinternetnindam.test/api/check_sqlsrv.php
echo.
echo ========================================
echo.
pause

