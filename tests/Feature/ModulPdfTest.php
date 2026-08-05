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

    public function test_download_marker_button_uses_pdf_when_available(): void
    {
        Storage::fake('public');

        $this->get(route('simulasi-bencana'))
            ->assertOk()
            ->assertSee(route('ar-markers.download'))
            ->assertDontSee(route('modul.download'));

        Storage::disk('public')->put(ModulController::PATH, 'dummy');

        $this->get(route('simulasi-bencana'))
            ->assertOk()
            ->assertSee(route('modul.download'))
            ->assertDontSee(route('ar-markers.download'));
    }

    public function test_download_sends_the_pdf_as_attachment(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(ModulController::PATH, 'dummy');

        $this->get(route('modul.download'))
            ->assertOk()
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.ModulController::FILENAME
            );
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
