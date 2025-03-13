<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Inventory;

class PurchaseObserver
{
    public function created(Purchase $purchase)
    {
        if ($purchase->status === 'completed') {
            $this->updateInventory($purchase);
        }
    }

    public function updated(Purchase $purchase)
    {
        if ($purchase->status === 'completed' && $purchase->getOriginal('status') !== 'completed') {
            $this->updateInventory($purchase);
        }

        if ($purchase->getOriginal('status') === 'completed' && $purchase->status !== 'completed') {
            $this->restoreInventory($purchase);
        }
    }

    private function updateInventory(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $item->product_id],
                ['quantity' => 0]
            );
            
            $inventory->quantity += $item->quantity;
            $inventory->save();
        }
    }

    private function restoreInventory(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)->first();
            
            if ($inventory) {
                $inventory->quantity -= $item->quantity;
                $inventory->save();
            }
        }
    }
}
