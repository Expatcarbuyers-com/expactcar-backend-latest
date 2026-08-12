<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        // "Add Note" moved onto the Internal Notes field itself (see BookingInfolist),
        // so notes and status are both edited in place rather than from up here.
        return [
            EditAction::make(),
        ];
    }
}
