<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Section::make('Lead Management')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'New Lead',
                                'contacting' => 'Contacting',
                                'qualified' => 'Qualified',
                                'appointment' => 'Appointment Fixed',
                                'inspected' => 'Inspected',
                                'purchased' => 'Car Purchased',
                                'closed' => 'Closed / Lost',
                            ])
                            ->required()
                            ->native(false),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->placeholder('Add internal notes here...')
                            ->rows(4),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Customer Information')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(150),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(200),
                    ])->columns(3),

                \Filament\Forms\Components\Section::make('Car Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('reference_number')
                            ->disabled()
                            ->dehydrated(false),
                        \Filament\Forms\Components\TextInput::make('make_name')
                            ->label('Make')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('model_name')
                            ->label('Model')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('variant_name')
                            ->label('Variant')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('year')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('mileage')
                            ->disabled()
                            ->suffix(' KM'),
                    ])->columns(3),
            ]);
    }
}
