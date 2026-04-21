<?php

namespace App\Observers;

use App\Models\Booking;
use App\Filament\Resources\Bookings\Schemas\BookingForm;

class BookingObserver
{
    /**
     * When status changes, append an entry to status_history.
     * Entry shape: { status, label, changed_by, changed_at }
     */
    public function updating(Booking $booking): void
    {
        if (! $booking->isDirty('status')) {
            return;
        }

        $newStatus = $booking->status;
        $history   = $booking->status_history ?? [];

        $history[] = [
            'status'     => $newStatus,
            'label'      => BookingForm::$statuses[$newStatus] ?? $newStatus,
            'changed_by' => auth()->user()?->name ?? 'System',
            'changed_at' => now()->toIso8601String(),
        ];

        $booking->status_history = $history;
    }
}
