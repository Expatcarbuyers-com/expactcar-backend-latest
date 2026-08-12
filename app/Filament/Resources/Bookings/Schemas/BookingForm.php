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

    /**
     * Mileage is stored as a free-text range (e.g. "90,000 - 120,000" or "250,000+"),
     * so it must never be cast to a number — (float) "90,000 - 120,000" evaluates to 90.
     * Older legacy rows may hold a bare integer, which we format for readability.
     */
    public static function formatMileage(?string $state): string
    {
        $state = trim((string) $state);

        if ($state === '') {
            return '—';
        }

        if (preg_match('/^\d+$/', $state)) {
            return number_format((int) $state) . ' KM';
        }

        return $state . ' KM';
    }

    /**
     * Normalise a UAE phone number to the international digits-only form
     * WhatsApp expects: 0505988844 -> 971505988844.
     */
    public static function whatsappNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '971')) {
            return $digits;
        }

        return '971' . ltrim($digits, '0');
    }

    /**
     * Deep link that opens the chat directly in WhatsApp (desktop app or web),
     * pre-filled with a greeting referencing the lead.
     */
    public static function whatsappUrl(\App\Models\Booking $record): ?string
    {
        $number = self::whatsappNumber($record->phone);

        if ($number === null) {
            return null;
        }

        $vehicle = trim("{$record->year} {$record->make_name} {$record->model_name}");

        $message = "Hello {$record->name}, this is ExpatCarBuyers regarding your {$vehicle}"
            . " (Ref: {$record->reference_number}).";

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }

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
                        TextInput::make('specs')->label('Specs')->disabled()->dehydrated(false),
                    ])->columns(3),
            ]);
    }
}
