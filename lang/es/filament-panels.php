<?php

return [
    'fields' => [
        'name' => 'Nombre',
        'code' => 'Código',
        'description' => 'Descripción',
        'purchase_price' => 'Precio de Compra',
        'sale_price' => 'Precio de Venta',
        'category' => 'Categoría',
        'origin' => 'Origen',
        'roast_level' => 'Nivel de Tostado',
        'email' => 'Correo Electrónico',
        'phone' => 'Teléfono',
        'address' => 'Dirección',
        'tax_id' => 'RFC',
        'notes' => 'Notas',
        'reference_number' => 'Número de Referencia',
        'supplier_id' => 'Proveedor',
        'customer_id' => 'Cliente',
        'total_amount' => 'Monto Total',
        'status' => 'Estado',
        'purchase_date' => 'Fecha de Compra',
        'sale_date' => 'Fecha de Venta',
        'quantity' => 'Cantidad',
        'minimum_stock' => 'Stock Mínimo',
        'product_id' => 'Producto',
        'unit_price' => 'Precio Unitario',
        'total_price' => 'Precio Total',
    ],

    'resources' => [
        'product' => [
            'label' => 'Producto',
            'plural_label' => 'Productos',
        ],
        'supplier' => [
            'label' => 'Proveedor',
            'plural_label' => 'Proveedores',
        ],
        'customer' => [
            'label' => 'Cliente',
            'plural_label' => 'Clientes',
        ],
        'purchase' => [
            'label' => 'Compra',
            'plural_label' => 'Compras',
        ],
        'sale' => [
            'label' => 'Venta',
            'plural_label' => 'Ventas',
        ],
        'inventory' => [
            'label' => 'Inventario',
            'plural_label' => 'Inventarios',
        ],
    ],

    'navigation' => [
        'group' => [
            'shop' => 'Tienda',
            'operations' => 'Operaciones',
            'management' => 'Administración',
        ],
    ],

    'table' => [
        'columns' => [
            'name' => 'Nombre',
            'email' => 'Correo',
            'phone' => 'Teléfono',
            'status' => 'Estado',
            'total' => 'Total',
            'date' => 'Fecha',
        ],
    ],

    'form' => [
        'sections' => [
            'products' => 'Productos',
            'customer_info' => 'Información del Cliente',
            'supplier_info' => 'Información del Proveedor',
            'transaction_details' => 'Detalles de la Transacción',
        ],
    ],

    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
    ],

    'messages' => [
        'created' => 'Registro creado exitosamente',
        'updated' => 'Registro actualizado exitosamente',
        'deleted' => 'Registro eliminado exitosamente',
        'delete_confirmation' => '¿Está seguro que desea eliminar este registro?',
    ],

    'status_options' => [
        'pending' => 'Pendiente',
        'processing' => 'En Proceso',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ],
];