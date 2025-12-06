@echo off
chcp 65001 >nul
echo ========================================
echo   คู่มือการตั้งค่า Laragon
echo ========================================
echo.

echo 📋 ขั้นตอนการใช้งาน Laragon:
echo.
echo 1. ดาวน์โหลด Laragon
echo    → https://laragon.org/
echo.
echo 2. ติดตั้ง Laragon
echo    → ดับเบิลคลิกไฟล์ที่ดาวน์โหลด
echo    → เลือก Install Location (แนะนำ: C:\laragon)
echo.
echo 3. คัดลอกไฟล์โปรเจกต์
echo    → คัดลอกโฟลเดอร์ workinternetnindam
echo    → วางใน: C:\laragon\www\workinternetnindam
echo.
echo 4. เริ่ม Laragon
echo    → เปิด Laragon
echo    → Laragon จะเริ่ม Apache อัตโนมัติ
echo.
echo 5. เปิดเว็บไซต์
echo    → http://localhost/workinternetnindam
echo    → หรือ http://workinternetnindam.test
echo.
echo ========================================
echo.

REM ตรวจสอบว่า Laragon ติดตั้งแล้วหรือไม่
if exist "C:\laragon\laragon.exe" (
    echo ✅ พบ Laragon ที่: C:\laragon
    echo.
    echo ต้องการเปิด Laragon ตอนนี้หรือไม่? (Y/N)
    set /p choice=
    if /i "%choice%"=="Y" (
        start "" "C:\laragon\laragon.exe"
        echo.
        echo Laragon กำลังเปิด...
    )
) else (
    echo ❌ ไม่พบ Laragon
    echo.
    echo กรุณาดาวน์โหลดและติดตั้ง Laragon ก่อน:
    echo https://laragon.org/
    echo.
)

echo.
echo 📖 สำหรับรายละเอียดเพิ่มเติม ดูไฟล์: LARAGON_SETUP.md
echo.
pause

