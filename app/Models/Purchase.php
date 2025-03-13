<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'reference_number',
        'supplier_id',
        'purchase_date',
        'status',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (!isset($purchase->total_amount)) {
                $purchase->total_amount = 0;
            }
        });
    }
}
