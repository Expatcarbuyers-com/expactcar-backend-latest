<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inquiry Details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('phone')->copyable()->default('—'),
                        TextEntry::make('subject')->default('General Inquiry'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'new'      => 'warning',
                                'read'     => 'info',
                                'replied'  => 'success',
                                'archived' => 'gray',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn (string $state) =>
                                ContactForm::$statuses[$state] ?? $state
                            ),
                        TextEntry::make('created_at')->label('Received')->dateTime('d M Y, H:i'),
                    ])->columns(3),

                Section::make('Message')
                    ->schema([
                        TextEntry::make('message')->columnSpanFull(),
                    ]),
            ]);
    }
}
