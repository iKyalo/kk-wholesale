<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'branch_id',
        'store_id',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'status',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}