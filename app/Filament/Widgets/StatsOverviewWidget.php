<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Contact;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayLeads = Booking::whereDate('created_at', today())->count();
        $pending    = Booking::where('status', 'pending')->count();
        $contacted  = Booking::where('status', 'contacted')->count();
        $appraised  = Booking::where('status', 'appraised')->count();
        $offerMade  = Booking::where('status', 'offer_made')->count();
        $closedWon  = Booking::where('status', 'closed_won')->count();
        $totalLeads = Booking::count();
        $convRate   = $totalLeads > 0 ? round(($closedWon / $totalLeads) * 100, 1) : 0;
        $newInquiries = Contact::where('status', 'new')->count();

        return [
            Stat::make('New Leads Today', $todayLeads)
                ->description('Submitted valuation forms')
                ->color('primary'),

            Stat::make('Pending Action', $pending)
                ->description('Awaiting first contact')
                ->color($pending > 10 ? 'danger' : 'warning'),

            Stat::make('In Pipeline', $contacted + $appraised + $offerMade)
                ->description("{$contacted} contacted · {$appraised} appraised · {$offerMade} offer made")
                ->color('info'),

            Stat::make('Won', $closedWon)
                ->description("Conversion rate: {$convRate}%")
                ->color('success'),

            Stat::make('New Inquiries', $newInquiries)
                ->description('Unread contact form messages')
                ->color($newInquiries > 0 ? 'warning' : 'gray'),
        ];
    }
}
