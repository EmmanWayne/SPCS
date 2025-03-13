<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Cafetería El Aroma',
                'email' => 'pedidos@elaroma.com',
                'phone' => '5551234567',
                'address' => 'Av. Reforma 789, CDMX',
                'tax_id' => 'CAFE456789XYZ',
                'notes' => 'Cliente mayorista',
            ],
            [
                'name' => 'Restaurante La Taza',
                'email' => 'compras@lataza.com',
                'phone' => '3331234567',
                'address' => 'Calle López 321, Guadalajara',
                'tax_id' => 'TAZA789123ABC',
                'notes' => 'Cliente frecuente',
            ],
            [
                'name' => 'Panadería Dulce',
                'email' => 'contacto@panaderiadulce.com',
                'phone' => '8181234567',
                'address' => 'Av. Principal 456, Monterrey',
                'tax_id' => 'PAND321654XYZ',
                'notes' => 'Cliente minorista',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}