<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use App\Models\MitigationStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocationsTest extends TestCase
{
    use RefreshDatabase;

    private function disaster(string $slug = 'banjir', string $name = 'Banjir'): Disaster
    {
        return Disaster::create(['slug' => $slug, 'name' => $name]);
    }

    public function test_locations_can_be_searched_and_filtered(): void
    {
        $banjir = $this->disaster();
        $gempa = $this->disaster('gempa-bumi', 'Gempa Bumi');

        DisasterLocation::create(['disaster_id' => $banjir->id, 'location_name' => 'Gitgit', 'latitude' => -8.2, 'longitude' => 115.1]);
        DisasterLocation::create(['disaster_id' => $gempa->id, 'location_name' => 'Kuta', 'latitude' => -8.7, 'longitude' => 115.2]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.locations', ['q' => 'Git']));

        $response->assertOk()
            ->assertSee('Gitgit')
            ->assertDontSee('>Kuta<', false);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.locations', ['disaster_id' => $gempa->id]))
            ->assertOk()
            ->assertSee('Kuta')
            ->assertDontSee('>Gitgit<', false);
    }

    public function test_map_popup_templates_are_rendered(): void
    {
        $disaster = $this->disaster();
        DisasterLocation::create(['disaster_id' => $disaster->id, 'location_name' => 'Gitgit', 'latitude' => -8.2, 'longitude' => 115.1]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.locations'))
            ->assertOk()
            ->assertSee('id="tpl-add"', false)
            ->assertSee('id="tpl-edit"', false)
            ->assertSee(route('admin.locations.update', ['location' => '__ID__']), false);
    }

    public function test_locations_can_be_bulk_deleted(): void
    {
        $disaster = $this->disaster();
        $a = DisasterLocation::create(['disaster_id' => $disaster->id, 'location_name' => 'A', 'latitude' => -8.2, 'longitude' => 115.1]);
        $b = DisasterLocation::create(['disaster_id' => $disaster->id, 'location_name' => 'B', 'latitude' => -8.3, 'longitude' => 115.2]);
        $c = DisasterLocation::create(['disaster_id' => $disaster->id, 'location_name' => 'C', 'latitude' => -8.4, 'longitude' => 115.3]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.locations.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect();

        $this->assertDatabaseMissing('disaster_locations', ['id' => $a->id]);
        $this->assertDatabaseMissing('disaster_locations', ['id' => $b->id]);
        $this->assertDatabaseHas('disaster_locations', ['id' => $c->id]);
    }

    public function test_locations_export_returns_csv(): void
    {
        $disaster = $this->disaster();
        DisasterLocation::create(['disaster_id' => $disaster->id, 'location_name' => 'Gitgit', 'latitude' => -8.2, 'longitude' => 115.1]);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.locations.export'));

        $response->assertOk();
        $this->assertStringContainsString('Gitgit', $response->streamedContent());
    }

    public function test_mitigation_step_order_is_saved(): void
    {
        $disaster = $this->disaster();
        $first = MitigationStep::create(['disaster_id' => $disaster->id, 'phase' => 'pra', 'order' => 1, 'content' => 'Satu']);
        $second = MitigationStep::create(['disaster_id' => $disaster->id, 'phase' => 'pra', 'order' => 2, 'content' => 'Dua']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.disasters.update', $disaster), [
                'description' => 'Deskripsi',
                'steps' => [
                    $first->id => ['content' => 'Satu', 'order' => 2],
                    $second->id => ['content' => 'Dua', 'order' => 1],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mitigation_steps', ['id' => $first->id, 'order' => 2]);
        $this->assertDatabaseHas('mitigation_steps', ['id' => $second->id, 'order' => 1]);
    }

    public function test_step_delete_returns_no_content_for_ajax(): void
    {
        $disaster = $this->disaster();
        $step = MitigationStep::create(['disaster_id' => $disaster->id, 'phase' => 'pra', 'order' => 1, 'content' => 'Satu']);

        $this->actingAs(User::factory()->create())
            ->deleteJson(route('admin.disasters.steps.destroy', $step))
            ->assertNoContent();

        $this->assertDatabaseMissing('mitigation_steps', ['id' => $step->id]);
    }
}
