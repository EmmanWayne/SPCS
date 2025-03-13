<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $products = Product::all();

        // Crear algunas compras de ejemplo
        for ($i = 1; $i <= 5; $i++) {
            $supplier = $suppliers->random();
            $purchaseDate = now()->subDays(rand(1, 30));
            
            $purchase = Purchase::create([
                'reference_number' => 'COMP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'total_amount' => 0, // Se calculará basado en los items
                'status' => collect(['pending', 'processing', 'completed'])->random(),
                'purchase_date' => $purchaseDate,
                'notes' => 'Compra de prueba #' . $i,
            ]);

            // Agregar items aleatorios a la compra
            $totalAmount = 0;
            $numberOfItems = rand(1, 3);
            $selectedProducts = $products->random($numberOfItems);

            foreach ($selectedProducts as $product) {
                $quantity = rand(10, 50);
                $unitPrice = $product->purchase_price;
                $totalPrice = $quantity * $unitPrice;
                $totalAmount += $totalPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Actualizar inventario si la compra está completada
                if ($purchase->status === 'completed') {
                    $inventory = Inventory::where('product_id', $product->id)->first();
                    $inventory->quantity += $quantity;
                    $inventory->save();
                }
            }

            // Actualizar el monto total de la compra
            $purchase->update(['total_amount' => $totalAmount]);
        }
    }
}
