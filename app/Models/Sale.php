<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\NumberFormatter;

class Sale extends Model
{
    protected $fillable = [
        'reference_number',
        'customer_id',
        'sale_date',
        'status',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!isset($sale->total_amount)) {
                $sale->total_amount = 0;
            }
        });
    }

    // Accessor para formatear el total
    public function getFormattedTotalAttribute(): string
    {
        return NumberFormatter::formatLempiras($this->total_amount);
    }

    // Método para verificar si la venta está completada
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Método para verificar si la venta está pendiente
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Método para verificar si la venta está cancelada
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
