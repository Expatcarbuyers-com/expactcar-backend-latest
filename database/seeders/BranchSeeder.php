<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Dubai',
                'slug' => 'sell-my-car-dubai',
                'location' => 'Dubai',
                'address' => 'Sheikh Zayed Road, Dubai, UAE',
                'phone' => '+971 50 123 4567',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
            ],
            [
                'name' => 'Abu Dhabi',
                'slug' => 'sell-my-car-abu-dhabi',
                'location' => 'Abu Dhabi',
                'address' => 'Airport Road, Abu Dhabi, UAE',
                'phone' => '+971 50 123 4567',
                'latitude' => 24.4539,
                'longitude' => 54.3773,
            ],
            [
                'name' => 'Sharjah',
                'slug' => 'sell-my-car-sharjah',
                'location' => 'Sharjah',
                'address' => 'Al Khan Street, Sharjah, UAE',
                'phone' => '+971 50 123 4567',
                'latitude' => 25.3463,
                'longitude' => 55.4209,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['slug' => $branch['slug']], $branch);
        }
    }
}
