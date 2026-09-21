<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches')
            ->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(
            StockTransfer::class,
            'from_branch_id'
        );
    }

    public function incomingTransfers()
    {
        return $this->hasMany(
            StockTransfer::class,
            'to_branch_id'
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}