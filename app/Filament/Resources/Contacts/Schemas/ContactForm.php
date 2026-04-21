<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ContactForm
{
    public static array $statuses = [
        'new'      => 'New',
        'read'     => 'Read',
        'replied'  => 'Replied',
        'archived' => 'Archived',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('status')
                            ->options(self::$statuses)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
