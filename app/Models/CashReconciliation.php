<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    protected $fillable = [
        'restaurant_id',
        'staff_id',
        'date',
        'total_cash_system',
        'total_cash_physical',
        'selisih',
        'note',
        'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'total_cash_system' => 'decimal:2',
            'total_cash_physical' => 'decimal:2',
            'selisih' => 'decimal:2',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
