<?php

namespace Tests\Feature;

use App\Models\ArMarker;
use App\Models\Disaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArMarkerGenerateTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_mode_generates_marker_png_and_patt_from_logo(): void
    {
        Storage::fake('public');
        $disaster = Disaster::create(['slug' => 'banjir', 'name' => 'Banjir']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.markers.store'), [
                'mode' => 'auto',
                'nama' => 'Marker Banjir',
                'marker_code' => 'MRK-BANJIR-01',
                'disaster_id' => $disaster->id,
                'path_logo_tengah' => UploadedFile::fake()->image('logo.png', 128, 128),
            ])
            ->assertRedirect(route('admin.markers.index'));

        $marker = ArMarker::firstOrFail();

        $this->assertSame('MRK-BANJIR-01', $marker->marker_code);
        $this->assertNotNull($marker->path_logo_tengah);
        $this->assertNotNull($marker->path_gambar_marker);
        $this->assertNotNull($marker->path_patt);

        Storage::disk('public')->assertExists($marker->path_logo_tengah);
        Storage::disk('public')->assertExists($marker->path_gambar_marker);
        Storage::disk('public')->assertExists($marker->path_patt);

        // 4 orientasi × 3 kanal × 16 baris — gagal kalau pipeline generate rusak
        $patt = trim(Storage::disk('public')->get($marker->path_patt));
        $this->assertCount(192, explode("\n", $patt));
    }

    public function test_pattern_differs_per_marker_code(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 128, 128);

        $a = \App\Helper\ArPatternHelper::buildLogoMarkerSource($logo->getRealPath(), 'MRK-BANJIR-01');
        $b = \App\Helper\ArPatternHelper::buildLogoMarkerSource($logo->getRealPath(), 'MRK-GEMPA-01');
        $again = \App\Helper\ArPatternHelper::buildLogoMarkerSource($logo->getRealPath(), 'MRK-BANJIR-01');

        $this->assertNotSame($a, $b, 'Marker ID berbeda harus menghasilkan pola berbeda.');
        $this->assertSame($a, $again, 'Marker ID sama harus menghasilkan pola yang sama persis.');
    }

    public function test_auto_mode_requires_logo(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.markers.store'), [
                'mode' => 'auto',
                'nama' => 'Tanpa Logo',
            ])
            ->assertSessionHasErrors('path_logo_tengah');

        $this->assertSame(0, ArMarker::count());
    }

    public function test_marker_code_must_be_unique(): void
    {
        Storage::fake('public');
        ArMarker::create(['nama' => 'Lama', 'marker_code' => 'MRK-BANJIR-01']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.markers.store'), [
                'mode' => 'auto',
                'nama' => 'Baru',
                'marker_code' => 'MRK-BANJIR-01',
                'path_logo_tengah' => UploadedFile::fake()->image('logo.png', 128, 128),
            ])
            ->assertSessionHasErrors('marker_code');
    }

    public function test_custom_mode_still_works(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.markers.store'), [
                'mode' => 'custom',
                'nama' => 'Marker Kustom',
                'path_gambar_marker' => UploadedFile::fake()->image('kustom.png', 256, 256),
            ])
            ->assertRedirect(route('admin.markers.index'));

        $marker = ArMarker::firstOrFail();

        $this->assertNull($marker->path_logo_tengah);
        Storage::disk('public')->assertExists($marker->path_gambar_marker);
        Storage::disk('public')->assertExists($marker->path_patt);
    }

    public function test_admin_marker_forms_render(): void
    {
        Storage::fake('public');
        Disaster::create(['slug' => 'banjir', 'name' => 'Banjir']);
        $marker = ArMarker::create(['nama' => 'Marker Banjir', 'marker_code' => 'MRK-BANJIR-01']);
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.markers.create'))
            ->assertOk()->assertSee('Auto-Generate')->assertSee('Marker ID');

        $this->actingAs($admin)->get(route('admin.markers.edit', $marker))
            ->assertOk()->assertSee('MRK-BANJIR-01');

        $this->actingAs($admin)->get(route('admin.markers.index'))
            ->assertOk()->assertSee('MRK-BANJIR-01');
    }

    public function test_preview_endpoint_returns_png(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.markers.preview'), [
                'path_logo_tengah' => UploadedFile::fake()->image('logo.png', 128, 128),
            ])
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
}
