<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\ReturnSupplier;

class RecalculateSupplierDues extends Command
{
    protected $signature = 'supplier:recalculate-dues';
    protected $description = 'Recalculate purchase order due amounts based on existing returns';

    public function handle()
    {
        $this->info('Recalculating due amounts for orders with returns...');

        // Get all orders that have returns
        $orderIds = ReturnSupplier::distinct()->pluck('purchase_order_id');
        
        foreach ($orderIds as $orderId) {
            $order = PurchaseOrder::find($orderId);
            if (!$order) continue;

            $totalReturns = ReturnSupplier::where('purchase_order_id', $orderId)->sum('total_amount');
            
            // Calculate what the due should be (original due - returns)
            // But we need to check if returns were already applied
            $originalTotal = $order->total_amount;
            $currentDue = $order->due_amount;
            
            // If current due equals total, returns weren't applied
            if ($currentDue == $originalTotal && $totalReturns > 0) {
                $newDue = max(0, $currentDue - $totalReturns);
                $order->due_amount = $newDue;
                $order->save();
                
                $this->info("Order {$order->order_code}: Due updated from {$currentDue} to {$newDue} (Return: {$totalReturns})");
            } else {
                $this->info("Order {$order->order_code}: Already adjusted or partially paid");
            }
        }

        $this->info('Done!');
        return 0;
    }
}
