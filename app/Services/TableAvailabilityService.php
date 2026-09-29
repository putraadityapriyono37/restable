<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\TableModel;

class TableAvailabilityService
{
    public function findAvailableTable(int $restaurantId, string $date, string $time, int $guestCount): ?TableModel
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if (! $this->isWithinOperatingHours($restaurant, $time)) {
            return null;
        }

        $slotDuration = (int) $restaurant->slot_duration;
        $startMinutes = $this->toMinutes($time);

        return TableModel::where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->where('capacity', '>=', $guestCount)
            ->orderBy('capacity', 'asc')
            ->lockForUpdate()
            ->get()
            ->first(fn (TableModel $table) => $this->isSlotAvailable($table->id, $date, $startMinutes, $slotDuration));
    }

    public function isSlotAvailable(int $tableId, string $date, int $startMinutes, int $slotDuration): bool
    {
        $newEnd = $startMinutes + $slotDuration;

        $hasOverlap = Reservation::where('table_id', $tableId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['waiting_payment', 'confirmed'])
            ->get()
            ->contains(function (Reservation $existing) use ($startMinutes, $newEnd, $slotDuration) {
                $existStart = $this->toMinutes($existing->reservation_time);
                $existEnd = $existStart + $slotDuration;

                return $startMinutes < $existEnd && $existStart < $newEnd;
            });

        return ! $hasOverlap;
    }

    public function isWithinOperatingHours(Restaurant $restaurant, string $time): bool
    {
        $start = $this->toMinutes($time);
        $end = $start + (int) $restaurant->slot_duration;

        return $start >= $this->toMinutes($restaurant->open_time)
            && $end <= $this->toMinutes($restaurant->close_time);
    }

    private function toMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return $hour * 60 + $minute;
    }
}
