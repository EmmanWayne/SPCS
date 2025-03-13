<?php

return [
    'pages' => [
        'dashboard' => [
            'title' => 'Panel de Control',
        ],
    ],

    'resources' => [
        'label.product' => 'Producto|Productos',
        'label.supplier' => 'Proveedor|Proveedores',
        'label.customer' => 'Cliente|Clientes',
        'label.purchase' => 'Compra|Compras',
        'label.sale' => 'Venta|Ventas',
        'label.inventory' => 'Inventario|Inventarios',
    ],

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
    ],

    'navigation' => [
        'group' => [
            'shop' => 'Tienda',
            'operations' => 'Operaciones',
            'management' => 'Administración',
        ],
    ],

    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar',
        'view' => 'Ver',
        'delete' => 'Eliminar',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'confirm' => 'Confirmar',
        'back' => 'Volver',
    ],

    'messages' => [
        'created' => 'Registro creado exitosamente',
        'updated' => 'Registro actualizado exitosamente',
        'deleted' => 'Registro eliminado exitosamente',
        'delete_confirmation' => '¿Está seguro que desea eliminar este registro?',
        'delete_selected_confirmation' => '¿Está seguro que desea eliminar los registros seleccionados?',
        'no_records' => 'No hay registros para mostrar',
    ],

    'buttons' => [
        'create_new' => 'Crear nuevo',
        'filters' => 'Filtros',
        'search' => 'Buscar',
        'bulk_actions' => 'Acciones masivas',
    ],
];
