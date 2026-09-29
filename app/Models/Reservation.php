<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'table_id',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'status',
        'notes',
        'estimated_total',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(TableModel::class, 'table_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReservationLog::class);
    }

    public function dpTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->where('type', 'dp');
    }

    public function pelunasanTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->where('type', 'pelunasan')->latestOfMany();
    }

    public function remainingBalance(): float
    {
        $dpPaid = (float) $this->transactions()
            ->where('type', 'dp')
            ->where('status', 'settlement')
            ->sum('amount');

        $pelunasanPaid = (float) $this->transactions()
            ->where('type', 'pelunasan')
            ->where('status', 'settlement')
            ->sum('amount');

        return max(0, (float) $this->estimated_total - $dpPaid - $pelunasanPaid);
    }
}