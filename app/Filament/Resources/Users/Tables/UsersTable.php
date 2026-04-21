<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('semibold'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'super_admin' => 'danger',
                        'agent'       => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'super_admin' => 'Super Admin',
                        'agent'       => 'Agent',
                        default       => $state,
                    }),
                TextColumn::make('assignedBookings_count')
                    ->label('Leads')
                    ->counts('assignedBookings')
                    ->sortable(),
                TextColumn::make('created_at')->label('Joined')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(['super_admin' => 'Super Admin', 'agent' => 'Agent']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
