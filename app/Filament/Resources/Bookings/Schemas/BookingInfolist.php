<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Lead Status ──────────────────────────────────────────
                Section::make('Lead Status')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('reference_number')
                            ->label('Reference')
                            ->weight('bold')
                            ->fontFamily('mono')
                            ->copyable(),

                        // Click the pencil beside the badge to change status without
                        // leaving the page. BookingObserver logs it to Status Timeline.
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending'     => 'gray',
                                'contacted'   => 'info',
                                'appraised'   => 'warning',
                                'offer_made'  => 'warning',
                                'closed_won'  => 'success',
                                'closed_lost' => 'danger',
                                default       => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string =>
                                BookingForm::$statuses[$state] ?? $state
                            )
                            ->suffixAction(
                                Action::make('edit_status')
                                    ->label('Change status')
                                    ->icon('heroicon-m-pencil-square')
                                    ->tooltip('Change status')
                                    ->modalHeading('Change Status')
                                    ->modalSubmitActionLabel('Save')
                                    ->modalWidth('sm')
                                    ->fillForm(fn (Booking $record): array => [
                                        'status' => $record->status,
                                    ])
                                    ->form([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options(BookingForm::$statuses)
                                            ->required()
                                            ->native(false),
                                    ])
                                    ->action(function (array $data, Booking $record): void {
                                        $record->update(['status' => $data['status']]);

                                        Notification::make()
                                            ->title('Status updated')
                                            ->success()
                                            ->send();
                                    })
                            ),

                        TextEntry::make('assignedAgent.name')
                            ->label('Assigned Agent')
                            ->default('Unassigned'),

                        TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime('d M Y, H:i'),
                    ]),

                // ── Customer ─────────────────────────────────────────────
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('phone')
                            ->copyable()
                            ->suffixAction(
                                Action::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->icon('heroicon-m-chat-bubble-left-right')
                                    ->color('success')
                                    ->tooltip('Open chat in WhatsApp')
                                    ->url(fn (Booking $record): ?string => BookingForm::whatsappUrl($record))
                                    ->openUrlInNewTab()
                                    ->visible(fn (Booking $record): bool => filled(BookingForm::whatsappNumber($record->phone)))
                            ),
                        TextEntry::make('email')->copyable(),
                    ]),

                // ── Vehicle ──────────────────────────────────────────────
                Section::make('Vehicle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('year'),
                        TextEntry::make('make_name')->label('Make'),
                        TextEntry::make('model_name')->label('Model'),
                        TextEntry::make('variant_name')->label('Variant'),
                        TextEntry::make('mileage')
                            ->formatStateUsing(fn ($state): string => BookingForm::formatMileage($state)),
                        TextEntry::make('specs')
                            ->label('Specs')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                                'GCC'     => 'success',
                                'NON-GCC' => 'warning',
                                default   => 'gray',
                            })
                            ->placeholder('Not specified'),
                    ]),

                // ── Status Timeline ──────────────────────────────────────
                Section::make('Status Timeline')
                    ->description('Chronological record of all status changes.')
                    ->schema([
                        RepeatableEntry::make('status_history')
                            ->label('')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('label')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match (
                                        // match on the slug we stored, not the label
                                        collect(BookingForm::$statuses)
                                            ->search($state) ?: 'pending'
                                    ) {
                                        'pending'     => 'gray',
                                        'contacted'   => 'info',
                                        'appraised'   => 'warning',
                                        'offer_made'  => 'warning',
                                        'closed_won'  => 'success',
                                        'closed_lost' => 'danger',
                                        default       => 'gray',
                                    }),

                                TextEntry::make('changed_by')
                                    ->label('Changed By'),

                                TextEntry::make('changed_at')
                                    ->label('When')
                                    ->formatStateUsing(fn ($state) =>
                                        $state
                                            ? \Carbon\Carbon::parse($state)->format('d M Y, H:i')
                                            : '—'
                                    ),
                            ])
                            ->placeholder('No status changes recorded yet.'),
                    ]),

                // ── Internal Notes ───────────────────────────────────────
                // Heading/description removed per client request. Notes are edited
                // in place via the pencil beside the field; entries are append-only
                // so one agent can never overwrite another's note.
                Section::make()
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label('Internal notes')
                            ->placeholder('No notes yet.')
                            ->columnSpanFull()
                            ->prose()
                            ->suffixAction(
                                Action::make('add_note')
                                    ->label('Add note')
                                    ->icon('heroicon-m-pencil-square')
                                    ->tooltip('Add a note')
                                    ->modalHeading('Add Internal Note')
                                    ->modalDescription('Appended with your name and the current time. Visible only to agents.')
                                    ->modalSubmitActionLabel('Add note')
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
                                        $entry = '[' . now()->format('d M Y, H:i') . ' — ' . auth()->user()->name . "]\n"
                                            . $data['note'];

                                        $existing = $record->internal_notes;

                                        $record->update([
                                            'internal_notes' => $existing
                                                ? $existing . "\n\n" . $entry
                                                : $entry,
                                        ]);

                                        Notification::make()
                                            ->title('Note added')
                                            ->success()
                                            ->send();
                                    })
                            ),
                    ]),

                // ── Tracking / UTM ───────────────────────────────────────
                Section::make('Tracking & UTM')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->default('—'),

                        TextEntry::make('user_agent')
                            ->label('Browser / Device')
                            ->default('—')
                            ->limit(60),

                        TextEntry::make('utm_data')
                            ->label('UTM Parameters')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state): string {
                                if (empty($state)) {
                                    return '—';
                                }
                                $data = is_array($state) ? $state : [];
                                if (empty($data)) {
                                    return '—';
                                }
                                return collect($data)
                                    ->map(fn ($v, $k) => strtoupper(str_replace('_', ' ', $k)) . ': ' . $v)
                                    ->implode("\n");
                            })
                            ->fontFamily('mono'),
                    ]),
            ]);
    }
}
