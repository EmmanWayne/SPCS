<?php

namespace App\Models;

use App\Helpers\NumberFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }

    // Accessor para formatear el precio unitario
    public function getFormattedUnitPriceAttribute(): string
    {
        return NumberFormatter::formatLempiras($this->unit_price);
    }

    // Accessor para formatear el total
    public function getFormattedTotalAttribute(): string
    {
        return NumberFormatter::formatLempiras($this->total_price);
    }

    // Método para verificar si hay suficiente stock
    public function hasEnoughStock(): bool
    {
        $inventory = Inventory::where('product_id', $this->product_id)->first();
        return $inventory && $inventory->quantity >= $this->quantity;
    }
}