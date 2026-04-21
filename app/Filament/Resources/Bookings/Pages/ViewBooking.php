<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Add Note (append-only) ────────────────────────────────
            Action::make('add_note')
                ->label('Add Note')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->modalHeading('Add Internal Note')
                ->modalDescription('This note will be appended with your name and timestamp. It is visible only to agents.')
                ->modalWidth('lg')
                ->form([
                    Textarea::make('note')
                        ->label('Note')
                        ->placeholder('Type your note here...')
                        ->rows(4)
                        ->required()
                        ->minLength(3)
                        ->maxLength(2000),
                ])
                ->action(function (array $data, Booking $record): void {
                    $agent     = auth()->user()->name;
                    $timestamp = now()->format('d M Y, H:i');
                    $newEntry  = "[{$timestamp} — {$agent}]\n{$data['note']}";

                    $existing = $record->internal_notes;
                    $record->update([
                        'internal_notes' => $existing
                            ? $existing . "\n\n" . $newEntry
                            : $newEntry,
                    ]);

                    Notification::make()
                        ->title('Note added')
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
