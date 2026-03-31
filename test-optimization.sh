#!/bin/bash

# Product Performance Optimization - Test Script
# Run this to verify everything is working

echo "🧪 Testing Product Performance Optimization..."
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check if API route exists
echo "📍 Test 1: Checking API route..."
if php artisan route:list | grep -q "api/products/all"; then
    echo -e "${GREEN}✅ API route registered${NC}"
else
    echo -e "${RED}❌ API route NOT found${NC}"
    echo "Run: php artisan route:clear"
fi
echo ""

# Test 2: Check if ProductApiController exists
echo "📍 Test 2: Checking ProductApiController..."
if [ -f "app/Http/Controllers/ProductApiController.php" ]; then
    echo -e "${GREEN}✅ ProductApiController exists${NC}"
else
    echo -e "${RED}❌ ProductApiController NOT found${NC}"
fi
echo ""

# Test 3: Check if cache is working
echo "📍 Test 3: Testing cache..."
php artisan cache:clear > /dev/null 2>&1
echo -e "${YELLOW}ℹ️  Cache cleared for clean test${NC}"
echo ""

# Test 4: Count products in database
echo "📍 Test 4: Checking products in database..."
PRODUCT_COUNT=$(php artisan tinker --execute="echo App\Models\ProductDetail::count();")
echo -e "${GREEN}✅ Found $PRODUCT_COUNT products${NC}"
echo ""

# Test 5: Test API endpoint
echo "📍 Test 5: Testing API endpoint (requires server running)..."
echo "Run this command in your browser or curl:"
echo -e "${YELLOW}curl http://localhost:8000/api/products/all${NC}"
echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 NEXT STEPS:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "1️⃣  Start Laravel server:"
echo "   php artisan serve"
echo ""
echo "2️⃣  Visit API in browser:"
echo "   http://localhost:8000/api/products/all"
echo ""
echo "3️⃣  Should see JSON with products"
echo ""
echo "4️⃣  Open products page and check DevTools (F12)"
echo "   Network tab should show minimal requests"
echo ""
echo "📖 Full testing guide: TESTING_GUIDE.md"
echo ""
