<?php

namespace App\Filament\Resources\Variants\Tables;

use App\Models\Make;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('year')->sortable()->width('80px'),
                TextColumn::make('model.make.name')->label('Make')->searchable()->sortable(),
                TextColumn::make('model.name')->label('Model')->searchable()->sortable(),
                TextColumn::make('name')->label('Variant')->searchable()->weight('semibold'),
                TextColumn::make('body_type')->label('Body')->default('—'),
                TextColumn::make('transmission')->label('Trans.')->default('—'),
                IconColumn::make('gcc_specs')->label('GCC')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('make')
                    ->label('Make')
                    ->options(Make::active()->orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) =>
                        $data['value'] ? $query->whereHas('model', fn ($q) => $q->where('make_id', $data['value'])) : $query
                    ),
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('gcc_specs')->label('GCC Specs'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
