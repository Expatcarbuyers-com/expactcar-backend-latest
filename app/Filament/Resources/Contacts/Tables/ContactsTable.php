<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Filament\Resources\Contacts\Schemas\ContactForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('semibold')
                    ->description(fn ($r) => $r->email),
                TextColumn::make('subject')->default('General Inquiry')->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'new'      => 'warning',
                        'read'     => 'info',
                        'replied'  => 'success',
                        'archived' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ContactForm::$statuses[$state] ?? $state),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ContactForm::$statuses),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
