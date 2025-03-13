<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Cooperativa Café Orgánico',
                'email' => 'contacto@cooporganica.com',
                'phone' => '9551234567',
                'address' => 'Calle Principal 123, Chiapas',
                'tax_id' => 'COPR123456ABC',
                'notes' => 'Proveedor principal de café orgánico',
            ],
            [
                'name' => 'Productores Unidos',
                'email' => 'ventas@productoresunidos.com',
                'phone' => '2281234567',
                'address' => 'Av. Cafetal 456, Veracruz',
                'tax_id' => 'PRUN789012XYZ',
                'notes' => 'Especialistas en café arábica',
            ],
            [
                'name' => 'Café de Altura',
                'email' => 'info@cafedealtura.com',
                'phone' => '9611234567',
                'address' => 'Carretera Sierra 789, Chiapas',
                'tax_id' => 'CALT345678DEF',
                'notes' => 'Proveedor de café de especialidad',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}