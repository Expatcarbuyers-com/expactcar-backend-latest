<?php

namespace App\Filament\Resources\CarModels\Schemas;

use App\Models\Make;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('make_id')
                            ->label('Make')
                            ->options(Make::active()->orderBy('display_order')->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name on create.')
                            ->maxLength(180),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
