<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'address', 'phone', 'open_time', 'close_time', 'slot_duration', 'dp_type', 'dp_value'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(TableModel::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function cashReconciliations(): HasMany
    {
        return $this->hasMany(CashReconciliation::class);
    }
}
