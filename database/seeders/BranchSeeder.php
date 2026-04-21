<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name'      => 'Dubai — Al Quoz',
                'slug'      => 'dubai-al-quoz',
                'location'  => 'Al Quoz, Dubai',
                'address'   => 'Al Quoz Industrial Area 1, Dubai, UAE',
                'phone'     => '+971561774555',
                'latitude'  => 25.1480,
                'longitude' => 55.2219,
                'is_active' => true,
            ],
            [
                'name'      => 'Sharjah',
                'slug'      => 'sharjah',
                'location'  => 'Industrial Area, Sharjah',
                'address'   => 'Industrial Area 1, Sharjah, UAE',
                'phone'     => '+971561774555',
                'latitude'  => 25.3463,
                'longitude' => 55.4209,
                'is_active' => true,
            ],
        ];

        foreach ($branches as $data) {
            Branch::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
