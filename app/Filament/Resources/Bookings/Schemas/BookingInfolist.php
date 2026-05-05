<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
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
                        TextEntry::make('phone')->copyable(),
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
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0)) . ' KM'),
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
                Section::make('Internal Notes')
                    ->description('Visible only to agents. Use the "Add Note" button to append entries.')
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label('')
                            ->default('No notes yet.')
                            ->columnSpanFull()
                            ->prose(),
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
