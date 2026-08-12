<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\Booking;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards the CRM booking screens against the bugs reported in Aug 2026:
 * mileage was rendered via (float) on a range string, and specs was never shown.
 */
class BookingCrmViewTest extends TestCase
{
    protected function actingAsAdmin(): User
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_view_page_renders_with_mileage_range_and_specs(): void
    {
        $this->actingAsAdmin();

        $booking = Booking::whereNotNull('specs')
            ->where('mileage', 'like', '%-%')
            ->firstOrFail();

        Livewire::test(ViewBooking::class, ['record' => $booking->getRouteKey()])
            ->assertOk()
            // the full range, not the truncated "90 KM"
            ->assertSee($booking->mileage . ' KM')
            ->assertSee($booking->specs);
    }

    public function test_list_page_renders_and_shows_full_mileage_range(): void
    {
        $this->actingAsAdmin();

        // The table sorts by created_at desc and paginates, so assert against a
        // record that is actually on the first page.
        $booking = Booking::orderByDesc('created_at')->firstOrFail();

        Livewire::test(ListBookings::class)
            ->assertOk()
            ->assertSee($booking->reference_number)
            ->assertSee(BookingForm::formatMileage($booking->mileage));
    }

    /**
     * NOTE: phpunit.xml has no separate test database, so these tests run against
     * the live connection. Keep every assertion read-only.
     */
    public function test_mileage_ranges_are_never_truncated_by_a_numeric_cast(): void
    {
        $this->assertSame('90,000 - 120,000 KM', BookingForm::formatMileage('90,000 - 120,000'));
        $this->assertSame('250,000+ KM', BookingForm::formatMileage('250,000+'));
        $this->assertSame('2,331 KM', BookingForm::formatMileage('2331'));
        $this->assertSame('—', BookingForm::formatMileage(null));
    }

    public function test_uae_phone_numbers_normalise_for_whatsapp(): void
    {
        $this->assertSame('971505988844', BookingForm::whatsappNumber('0505988844'));
        $this->assertSame('971524881200', BookingForm::whatsappNumber('+971 52 488 1200'));
        $this->assertSame('971509986038', BookingForm::whatsappNumber('971509986038'));
        $this->assertNull(BookingForm::whatsappNumber('abc'));
    }
}
