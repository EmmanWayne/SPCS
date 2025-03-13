<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();

        // Crear algunas ventas de ejemplo
        for ($i = 1; $i <= 8; $i++) {
            $customer = $customers->random();
            $saleDate = now()->subDays(rand(1, 15));
            
            $sale = Sale::create([
                'reference_number' => 'VENT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'total_amount' => 0, // Se calculará basado en los items
                'status' => collect(['pending', 'processing', 'completed'])->random(),
                'sale_date' => $saleDate,
                'notes' => 'Venta de prueba #' . $i,
            ]);

            // Agregar items aleatorios a la venta
            $totalAmount = 0;
            $numberOfItems = rand(1, 4);
            $selectedProducts = $products->random($numberOfItems);

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);
                $unitPrice = $product->sale_price;
                $totalPrice = $quantity * $unitPrice;
                $totalAmount += $totalPrice;

                // Verificar que hay suficiente inventario
                $inventory = Inventory::where('product_id', $product->id)->first();
                if ($inventory->quantity >= $quantity) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);

                    // Actualizar inventario si la venta está completada
                    if ($sale->status === 'completed') {
                        $inventory->quantity -= $quantity;
                        $inventory->save();
                    }
                }
            }

            // Actualizar el monto total de la venta
            $sale->update(['total_amount' => $totalAmount]);
        }
    }
}
