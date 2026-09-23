<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'from_store_id',
        'to_store_id',
        'user_id',
        'status',
        'notes',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'transferred_at' => 'datetime',
        ];
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function sourceStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function destinationStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }
}