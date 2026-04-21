<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')->required()->maxLength(150),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(200),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'agent'       => 'Agent',
                            ])
                            ->required()
                            ->default('agent')
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}
