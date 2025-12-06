@echo off
chcp 65001 >nul
echo ========================================
echo   เริ่มต้น Development Server
echo ========================================
echo.

cd /d "%~dp0"

echo 📋 วิธีใช้งาน:
echo.
echo 1. ใช้ Laragon (แนะนำ):
echo    - วางไฟล์ใน C:\laragon\www\workinternetnindam
echo    - เปิด Laragon
echo    - ไปที่ http://localhost/workinternetnindam
echo.
echo 2. ใช้ XAMPP:
echo    - วางไฟล์ใน C:\xampp\htdocs\workinternetnindam
echo    - เริ่ม Apache
echo    - ไปที่ http://localhost/workinternetnindam
echo.
echo 3. ใช้ PHP Built-in Server:
echo    - รันคำสั่ง: php -S localhost:8000
echo    - ไปที่ http://localhost:8000
echo.
echo ========================================
echo.

REM ตรวจสอบ Laragon
if exist "C:\laragon\laragon.exe" (
    echo ✅ พบ Laragon
    echo.
    echo ต้องการเปิด Laragon ตอนนี้หรือไม่? (Y/N)
    set /p choice=
    if /i "%choice%"=="Y" (
        start "" "C:\laragon\laragon.exe"
        echo.
        echo Laragon กำลังเปิด...
        echo เปิดเบราว์เซอร์ไปที่: http://localhost/workinternetnindam
    )
) else (
    echo ℹ️  ไม่พบ Laragon
    echo.
    echo ดาวน์โหลด Laragon: https://laragon.org/
    echo.
)

echo.
pause

