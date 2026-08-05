<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use App\Models\MitigationStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pages_render(): void
    {
        $d = Disaster::create(['slug' => 'banjir', 'name' => 'Banjir', 'description' => 'x']);
        MitigationStep::create(['disaster_id' => $d->id, 'phase' => 'pra', 'order' => 1, 'content' => 'Siapkan tas siaga']);
        DisasterLocation::create(['disaster_id' => $d->id, 'location_name' => 'Gitgit', 'latitude' => -8.2, 'longitude' => 115.1]);

        foreach (['/', '/peta-bencana', '/penanggulangan-bencana', '/simulasi-bencana', '/ar-kamera'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs(User::factory()->create());

        foreach ([
            '/admin',
            '/admin/locations',
            '/admin/disasters',
            '/admin/disasters/'.$d->id.'/edit',
            '/admin/disasters/create',
            '/admin/markers',
            '/admin/markers/create',
            '/admin/locations/export',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
