<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\NumberFormatter;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'purchase_price',
        'sale_price',
        'category',
        'origin',
        'roast_level',
    ];

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getFormattedPricesAttribute()
    {
        return [
            'purchase' => NumberFormatter::formatLempiras($this->purchase_price),
            'sale' => NumberFormatter::formatLempiras($this->sale_price)
        ];
    }
}
