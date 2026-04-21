<?php

namespace App\Filament\Resources\Makes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MakeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name. Edit only if you need a custom slug.')
                            ->maxLength(120),

                        TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->placeholder('https://...')
                            ->maxLength(500),

                        TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number = appears first in dropdowns.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
