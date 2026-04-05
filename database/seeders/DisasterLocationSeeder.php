<?php

namespace Database\Seeders;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use Illuminate\Database\Seeder;

class DisasterLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Approximate coordinates for locations in Buleleng regency, Bali
        // Center: approximately -8.2, 115.0
        $locations = [
            'pohon-tumbang' => [
                // There's no pohon-tumbang disaster, but the map lists it — skip or could add as a generic disaster
            ],
            'tanah-longsor' => [
                ['location_name' => 'Buleleng', 'latitude' => -8.15, 'longitude' => 115.08],
                ['location_name' => 'Gitgit', 'latitude' => -8.24, 'longitude' => 115.12],
                ['location_name' => 'Wanagiri', 'latitude' => -8.28, 'longitude' => 115.15],
                ['location_name' => 'Gobleg', 'latitude' => -8.31, 'longitude' => 115.18],
            ],
            'kebakaran' => [
                // There are no kebakaran seed entries for this app's 5 disasters
            ],
            'banjir' => [
                ['location_name' => 'Banjar', 'latitude' => -8.18, 'longitude' => 115.05],
                ['location_name' => 'Seririt', 'latitude' => -8.16, 'longitude' => 114.92],
                ['location_name' => 'Kampung Tinggi', 'latitude' => -8.22, 'longitude' => 115.10],
            ],
        ];

        // Map from markdown names to slugs
        $slugMap = [
            'pohon-tumbang' => 'pohon-tumbang',
            'tanah-longsor' => 'tanah-longsor',
            'kebakaran' => 'kebakaran',
            'banjir' => 'banjir',
        ];

        foreach ($locations as $disasterKey => $locationList) {
            $disaster = Disaster::where('slug', $disasterKey)->first();
            if (! $disaster || empty($locationList)) {
                continue;
            }

            foreach ($locationList as $loc) {
                DisasterLocation::updateOrCreate(
                    ['disaster_id' => $disaster->id, 'location_name' => $loc['location_name']],
                    ['latitude' => $loc['latitude'], 'longitude' => $loc['longitude']]
                );
            }
        }
    }
}
