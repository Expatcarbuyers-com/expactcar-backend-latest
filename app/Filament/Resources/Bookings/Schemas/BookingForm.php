<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\User;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    // Status labels shared across form/table/infolist
    public static array $statuses = [
        'pending'     => 'New Lead',
        'contacted'   => 'Contacted',
        'appraised'   => 'Appraised',
        'offer_made'  => 'Offer Made',
        'closed_won'  => 'Won',
        'closed_lost' => 'Lost',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lead Management')
                    ->schema([
                        Select::make('status')
                            ->options(self::$statuses)
                            ->required()
                            ->native(false),
                        Select::make('assigned_to')
                            ->label('Assigned Agent')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Unassigned')
                            ->native(false),
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->placeholder('Add notes visible only to agents...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(150),
                        TextInput::make('phone')->tel()->required()->maxLength(20),
                        TextInput::make('email')->email()->required()->maxLength(200),
                    ])->columns(3),

                Section::make('Vehicle Details')
                    ->schema([
                        TextInput::make('reference_number')->disabled()->dehydrated(false),
                        TextInput::make('make_name')->label('Make')->disabled()->dehydrated(false),
                        TextInput::make('model_name')->label('Model')->disabled()->dehydrated(false),
                        TextInput::make('variant_name')->label('Variant')->disabled()->dehydrated(false),
                        TextInput::make('year')->disabled()->dehydrated(false),
                        TextInput::make('mileage')->disabled()->dehydrated(false)->suffix('KM'),
                    ])->columns(3),
            ]);
    }
}
