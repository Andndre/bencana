<?php

namespace Tests\Feature;

use App\Http\Controllers\ModulController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModulPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_download_and_delete_modul(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.modul.store'), [
                'file_pdf' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.modul'));

        Storage::disk('public')->assertExists(ModulController::PATH);

        $this->get(route('modul.download'))->assertOk();
        $this->get(route('modul.show'))->assertOk();

        $this->actingAs($admin)
            ->delete(route('admin.modul.destroy'))
            ->assertRedirect(route('admin.modul'));

        Storage::disk('public')->assertMissing(ModulController::PATH);
    }

    public function test_modul_routes_404_when_no_file_uploaded(): void
    {
        Storage::fake('public');

        $this->get(route('modul.show'))->assertNotFound();
        $this->get(route('modul.download'))->assertNotFound();
    }

    public function test_public_pages_show_modul_buttons_only_when_pdf_exists(): void
    {
        Storage::fake('public');

        $this->get(route('simulasi-bencana'))->assertOk()->assertDontSee('btn-baca-modul');
        $this->get(route('penanggulangan-bencana'))->assertOk()->assertDontSee('btn-baca-modul');

        Storage::disk('public')->put(ModulController::PATH, 'dummy');

        foreach (['simulasi-bencana', 'penanggulangan-bencana'] as $page) {
            $this->get(route($page))
                ->assertOk()
                ->assertSee('btn-baca-modul')
                ->assertSee('modul-overlay');
        }
    }

    public function test_non_pdf_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.modul.store'), [
                'file_pdf' => UploadedFile::fake()->image('bukan-pdf.png'),
            ])
            ->assertSessionHasErrors('file_pdf');

        Storage::disk('public')->assertMissing(ModulController::PATH);
    }
}
