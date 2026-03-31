# Default Batch Migration Guide

## Problem

You already have products with multiple purchase batches, but no default batch exists. Stock adjustments fail because there are no active batches when all batches are depleted.

## Solution

Run this command to create default batches for all existing products:

## Step 1: Create Default Batches for All Existing Products

Open your terminal in the project directory and run:

```bash
php artisan batch:create-defaults
```

This command will:

- ✅ Create a `DEFAULT-{product_id}` batch for each product
- ✅ Set initial quantity to 0 (won't affect current stock)
- ✅ Use current product prices from the database
- ✅ Skip products that already have a default batch
- ✅ Show a summary of created/skipped batches

### Expected Output:

```
Starting default batch creation process...
  ✅ Product #1 (Sample Product) - Default batch created
  ✅ Product #2 (Another Product) - Default batch created
  ⏭️  Product #3 (Product with default) - Default batch already exists

═══════════════════════════════════════════════
Summary:
  ✅ Created: 45
  ⏭️  Skipped: 3
═══════════════════════════════════════════════
```

## Step 2: Verify Default Batches Were Created

You can verify in your database:

```sql
SELECT * FROM product_batches WHERE batch_number LIKE 'DEFAULT-%';
```

Or check in your application:

1. Go to Products page
2. Click on any product to view details
3. Check the "Batches" section - you should see the DEFAULT batch

## Step 3: Test Stock Adjustment

1. Go to Products page
2. Select any product
3. Click "Adjust Stock"
4. Try adding available stock - it should now work!
5. The adjustment will be added to the DEFAULT batch

## How It Works Now

### For New Products (First Purchase):

```
Purchase Order Created → Stock added to DEFAULT batch
```

### For Existing Products (Subsequent Purchases):

```
New Purchase Order → New dated batch created (BATCH-X-20260121-001)
DEFAULT batch remains active for adjustments
```

### For Stock Adjustments:

```
Manual Adjustment → Always added to DEFAULT batch
Works even when all other batches are depleted
```

### For Sales (FIFO):

```
Sale → Deduct from oldest batch first
When batch depleted → Mark as depleted
DEFAULT batch only used when it's the oldest with stock
```

## Important Notes

✅ **Safe to Run Multiple Times**: The command will skip products that already have a default batch

✅ **No Stock Impact**: Creating default batches with 0 quantity doesn't affect current stock levels

✅ **FIFO Preserved**: The FIFO logic continues to work normally. Default batch participates in FIFO when it has stock.

✅ **Automatic for New Products**: Any new product ordered will automatically get a default batch

## Troubleshooting

### Issue: Command not found

**Solution**: Make sure you're in the project root directory and run:

```bash
composer dump-autoload
php artisan batch:create-defaults
```

### Issue: Some products show errors

**Solution**: Check the error message. Usually it's because the product doesn't have a price record. Fix those products first.

### Issue: Adjustment still not working

**Solution**:

1. Verify default batch exists: `SELECT * FROM product_batches WHERE product_id = X AND batch_number LIKE 'DEFAULT-%'`
2. Check if it's active: `status` should be 'active'
3. Clear cache: `php artisan cache:clear`

## Benefits of This Solution

✅ **No More Adjustment Failures**: Default batch always available for adjustments
✅ **Backward Compatible**: Works with existing batches
✅ **FIFO Maintained**: Doesn't break existing FIFO logic
✅ **Future-Proof**: All new products automatically get default batch
✅ **Clean Inventory**: Proper separation of purchase batches and adjustment batches

## Before vs After

### Before:

```
Product A:
  └─ BATCH-1-20260115-001 (Depleted after sale)
  └─ BATCH-1-20260118-001 (Depleted after sale)
  └─ No active batches! ❌ Adjustment fails!
```

### After:

```
Product A:
  └─ DEFAULT-1 (Always active ✅)
  └─ BATCH-1-20260115-001 (Depleted)
  └─ BATCH-1-20260118-001 (Depleted)

  Adjustment → Goes to DEFAULT-1 ✅ Works!
```

## Next Steps

1. ✅ Run `php artisan batch:create-defaults`
2. ✅ Verify all products have default batches
3. ✅ Test stock adjustments
4. ✅ Continue using your system normally

Your batch management system is now fully functional for both existing and new products! 🎉
