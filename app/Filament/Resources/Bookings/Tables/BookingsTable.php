<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                \Filament\Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'New Lead',
                        'contacting' => 'Contacting',
                        'qualified' => 'Qualified',
                        'appointment' => 'Appointment Fixed',
                        'inspected' => 'Inspected',
                        'purchased' => 'Car Purchased',
                        'closed' => 'Closed / Lost',
                    ])
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn ($record) => $record->phone),
                \Filament\Tables\Columns\TextColumn::make('car_details')
                    ->label('Car')
                    ->state(fn ($record) => "{$record->year} {$record->make_name} {$record->model_name}")
                    ->description(fn ($record) => $record->variant_name),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'New Lead',
                        'contacting' => 'Contacting',
                        'qualified' => 'Qualified',
                        'appointment' => 'Appointment Fixed',
                        'inspected' => 'Inspected',
                        'purchased' => 'Car Purchased',
                        'closed' => 'Closed / Lost',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
