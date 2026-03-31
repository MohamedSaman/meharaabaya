@echo off
REM Product Performance Optimization - Test Script (Windows)
REM Run this to verify everything is working

echo.
echo ===============================================
echo   Testing Product Performance Optimization
echo ===============================================
echo.

REM Test 1: Check if API route exists
echo [Test 1] Checking API route...
php artisan route:list | findstr "api/products/all" >nul 2>&1
if %errorlevel%==0 (
    echo [PASS] API route registered
) else (
    echo [FAIL] API route NOT found
    echo Run: php artisan route:clear
)
echo.

REM Test 2: Check if ProductApiController exists
echo [Test 2] Checking ProductApiController...
if exist "app\Http\Controllers\ProductApiController.php" (
    echo [PASS] ProductApiController exists
) else (
    echo [FAIL] ProductApiController NOT found
)
echo.

REM Test 3: Clear cache
echo [Test 3] Clearing cache for clean test...
php artisan cache:clear >nul 2>&1
echo [INFO] Cache cleared
echo.

REM Test 4: Count products
echo [Test 4] Checking products in database...
php artisan tinker --execute="echo App\Models\ProductDetail::count();"
echo.

echo ===============================================
echo   QUICK TEST - Run These Commands:
echo ===============================================
echo.
echo 1. Start Laravel server:
echo    php artisan serve
echo.
echo 2. Open browser and visit:
echo    http://localhost:8000/api/products/all
echo.
echo 3. You should see JSON response with:
echo    - "success": true
echo    - "data": [...your products...]
echo    - "count": number
echo    - "cached_at": timestamp
echo.
echo 4. Open your products page:
echo    http://localhost:8000/admin/Product-list
echo.
echo 5. Press F12 to open DevTools
echo    - Go to Network tab
echo    - Search for product
echo    - Should see ZERO requests!
echo.
echo ===============================================
echo   Full Guide: TESTING_GUIDE.md
echo ===============================================
echo.
pause
