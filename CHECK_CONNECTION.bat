@echo off
chcp 65001 >nul
echo ========================================
echo ตรวจสอบความพร้อมเชื่อมต่อ SQL Server
echo ========================================
echo.

set PHP_PATH=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64
set EXT_PATH=%PHP_PATH%\ext
set PHP_INI=%PHP_PATH%\php.ini

echo [1/5] ตรวจสอบ ODBC Driver...
echo.
odbcinst -q -d 2>nul | findstr /i "SQL Server" >nul
if %errorlevel% equ 0 (
    echo [OK] ODBC Driver for SQL Server ติดตั้งแล้ว
    odbcinst -q -d | findstr /i "SQL Server"
) else (
    echo [ERROR] ไม่พบ ODBC Driver for SQL Server
    echo         ต้องติดตั้ง ODBC Driver 18 ก่อน
)
echo.

echo [2/5] ตรวจสอบไฟล์ DLL ในโฟลเดอร์ ext...
echo.
if exist "%EXT_PATH%\php_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_sqlsrv_83_ts_x64.dll
    echo         ต้องคัดลอกไฟล์ไปยัง: %EXT_PATH%
)

if exist "%EXT_PATH%\php_pdo_sqlsrv_83_ts_x64.dll" (
    echo [OK] พบ php_pdo_sqlsrv_83_ts_x64.dll
) else (
    echo [ERROR] ไม่พบ php_pdo_sqlsrv_83_ts_x64.dll
    echo         ต้องคัดลอกไฟล์ไปยัง: %EXT_PATH%
)
echo.

echo [3/5] ตรวจสอบการตั้งค่าใน php.ini...
echo.
if exist "%PHP_INI%" (
    findstr /i "^extension=php_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
    if %errorlevel% equ 0 (
        echo [OK] พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
    ) else (
        echo [ERROR] ไม่พบ extension=php_sqlsrv_83_ts_x64 ใน php.ini
        echo         ต้องเพิ่ม extension ใน php.ini
    )
    
    findstr /i "^extension=php_pdo_sqlsrv_83_ts_x64" "%PHP_INI%" >nul
    if %errorlevel% equ 0 (
        echo [OK] พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
    ) else (
        echo [ERROR] ไม่พบ extension=php_pdo_sqlsrv_83_ts_x64 ใน php.ini
        echo         ต้องเพิ่ม extension ใน php.ini
    )
) else (
    echo [ERROR] ไม่พบ php.ini: %PHP_INI%
)
echo.

echo [4/5] ตรวจสอบ SQL Server Service...
echo.
sc query MSSQLSERVER 2>nul | findstr "RUNNING" >nul
if %errorlevel% equ 0 (
    echo [OK] SQL Server (MSSQLSERVER) กำลังทำงานอยู่
) else (
    sc query MSSQL$SQLEXPRESS 2>nul | findstr "RUNNING" >nul
    if %errorlevel% equ 0 (
        echo [OK] SQL Server Express กำลังทำงานอยู่
    ) else (
        echo [WARNING] ไม่พบ SQL Server Service ที่ทำงานอยู่
        echo            ตรวจสอบว่า SQL Server ทำงานอยู่หรือไม่
    )
)
echo.

echo [5/5] ตรวจสอบการตั้งค่าใน db.php...
echo.
if exist "api\db.php" (
    findstr /i "DESKTOP-S0OP3KB" "api\db.php" >nul
    if %errorlevel% equ 0 (
        echo [OK] Server name: DESKTOP-S0OP3KB
    )
    findstr /i "pawarit" "api\db.php" >nul
    if %errorlevel% equ 0 (
        echo [OK] Database: pawarit
    )
) else (
    echo [ERROR] ไม่พบไฟล์ api\db.php
)
echo.

echo ========================================
echo สรุป
echo ========================================
echo.
echo หากทุกอย่างเป็น [OK] แล้ว:
echo 1. รีสตาร์ท Laragon (Stop All แล้ว Start All)
echo 2. ทดสอบที่: http://workinternetnindam.test/api/check_sqlsrv.php
echo.
echo หากมี [ERROR] หรือ [WARNING]:
echo - ต้องแก้ไขตามที่ระบุไว้ข้างต้น
echo.
pause

