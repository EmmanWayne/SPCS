<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Inventory;
use Filament\Notifications\Notification;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        if ($sale->isCompleted()) {
            $this->updateInventory($sale);
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // Si el estado cambia a completado
        if ($sale->isCompleted() && $sale->getOriginal('status') !== 'completed') {
            $this->updateInventory($sale);
        }

        // Si el estado cambia de completado a otro estado
        if ($sale->getOriginal('status') === 'completed' && !$sale->isCompleted()) {
            $this->restoreInventory($sale);
        }
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        //
    }

    /**
     * Método privado para actualizar el inventario
     */
    private function updateInventory(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)->first();
            
            if ($inventory) {
                if ($inventory->quantity >= $item->quantity) {
                    $inventory->quantity -= $item->quantity;
                    $inventory->save();
                } else {
                    Notification::make()
                        ->title('Error de Inventario')
                        ->body("Stock insuficiente para el producto {$item->product->name}")
                        ->danger()
                        ->send();
                }
            }
        }
    }

    /**
     * Método privado para restaurar el inventario
     */
    private function restoreInventory(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)->first();
            
            if ($inventory) {
                $inventory->quantity += $item->quantity;
                $inventory->save();
            }
        }
    }
}
