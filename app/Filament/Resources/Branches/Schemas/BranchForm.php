<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')->required()->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        TextInput::make('location')->required()->placeholder('City/Area'),
                        TextInput::make('address')->maxLength(255),
                        TextInput::make('phone')->tel()->maxLength(20),
                        TextInput::make('latitude')->numeric()->step(0.00000001),
                        TextInput::make('longitude')->numeric()->step(0.00000001),
                        Toggle::make('is_active')->default(true),
                    ])->columns(2),
            ]);
    }
}
