<?php

namespace App\Filament\Resources\Variants\Schemas;

use App\Models\CarModel;
use App\Models\Make;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vehicle Identity')
                    ->schema([
                        Select::make('make_id')
                            ->label('Make')
                            ->options(Make::active()->orderBy('display_order')->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('model_id', null))
                            ->native(false),

                        Select::make('model_id')
                            ->label('Model')
                            ->options(fn (callable $get) =>
                                $get('make_id')
                                    ? CarModel::active()->where('make_id', $get('make_id'))->orderBy('name')->pluck('name', 'id')
                                    : []
                            )
                            ->required()
                            ->native(false),

                        TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->minValue(1980)
                            ->maxValue(date('Y') + 1),

                        TextInput::make('name')
                            ->label('Variant Name')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('e.g. GXR, SE, Limited'),
                    ])->columns(2),

                Section::make('Specs')
                    ->schema([
                        TextInput::make('body_type')->placeholder('SUV, Sedan, Pickup...'),
                        TextInput::make('engine')->placeholder('3.5L V6'),
                        TextInput::make('transmission')->placeholder('Automatic / Manual'),
                        Toggle::make('gcc_specs')->label('GCC Specs')->default(false),
                        Toggle::make('is_active')->label('Active')->default(true),
                    ])->columns(3),
            ]);
    }
}
