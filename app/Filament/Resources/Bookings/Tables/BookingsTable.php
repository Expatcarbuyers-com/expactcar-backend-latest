<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\Make;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Ref #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn ($record) => $record->phone),

                TextColumn::make('make_name')
                    ->label('Vehicle')
                    ->formatStateUsing(fn ($record) => "{$record->year} {$record->make_name} {$record->model_name}")
                    ->description(fn ($record) => "{$record->variant_name} · " . number_format((float) ($record->mileage ?? 0)) . ' KM'),

                TextColumn::make('status')
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
                    ->formatStateUsing(fn (string $state): string => BookingForm::$statuses[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('assignedAgent.name')
                    ->label('Agent')
                    ->default('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y, H:i')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingForm::$statuses)
                    ->label('Status'),

                SelectFilter::make('assigned_to')
                    ->options(User::pluck('name', 'id'))
                    ->label('Agent'),

                SelectFilter::make('make_name')
                    ->options(Make::active()->orderBy('name')->pluck('name', 'name'))
                    ->label('Make'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign_agent')
                        ->label('Assign to Agent')
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('assigned_to')
                                ->label('Agent')
                                ->options(User::pluck('name', 'id'))
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn (Collection $records, array $data) =>
                            $records->each->update(['assigned_to' => $data['assigned_to']])
                        )
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->options(BookingForm::$statuses)
                                ->required()
                                ->native(false),
                        ])
                        ->action(fn (Collection $records, array $data) =>
                            $records->each->update(['status' => $data['status']])
                        )
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): StreamedResponse {
                            $filename = 'bookings-' . now()->format('Y-m-d-His') . '.csv';

                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');

                                fputcsv($handle, [
                                    'Reference #', 'Name', 'Phone', 'Email',
                                    'Year', 'Make', 'Model', 'Variant', 'Mileage (KM)',
                                    'Status', 'Agent', 'Submitted At',
                                ]);

                                foreach ($records as $r) {
                                    fputcsv($handle, [
                                        $r->reference_number,
                                        $r->name,
                                        $r->phone,
                                        $r->email,
                                        $r->year,
                                        $r->make_name,
                                        $r->model_name,
                                        $r->variant_name,
                                        $r->mileage,
                                        BookingForm::$statuses[$r->status] ?? $r->status,
                                        $r->assignedAgent?->name ?? '—',
                                        $r->created_at->format('Y-m-d H:i:s'),
                                    ]);
                                }

                                fclose($handle);
                            }, $filename, ['Content-Type' => 'text/csv']);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
