<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ModulController extends Controller
{
    // ponytail: satu file global, bukan tabel. Tambah tabel kalau nanti butuh
    // banyak PDF, PDF per-bencana, atau riwayat versi.
    public const PATH = 'ar-markers/modul/modul-bencana.pdf';

    public const FILENAME = 'Marker-Bencana.pdf';

    /** Dipakai view publik: tombol Download Marker pakai PDF ini kalau ada. */
    public static function available(): bool
    {
        return Storage::disk('public')->exists(self::PATH);
    }

    public function admin(): View
    {
        $disk = Storage::disk('public');
        $exists = $disk->exists(self::PATH);

        return view('admin.modul.index', [
            'exists' => $exists,
            'size' => $exists ? $disk->size(self::PATH) : null,
            'lastModified' => $exists ? $disk->lastModified(self::PATH) : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        Storage::disk('public')->put(
            self::PATH,
            file_get_contents($request->file('file_pdf')->getRealPath())
        );

        return redirect()->route('admin.modul')
            ->with('success', 'PDF Marker berhasil diunggah.');
    }

    public function destroy()
    {
        Storage::disk('public')->delete(self::PATH);

        return redirect()->route('admin.modul')
            ->with('success', 'PDF Marker berhasil dihapus.');
    }

    /** Streaming inline — dipakai <iframe> preview. */
    public function show()
    {
        $disk = Storage::disk('public');
        abort_unless($disk->exists(self::PATH), 404);

        return $disk->response(self::PATH, self::FILENAME, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.self::FILENAME.'"',
        ]);
    }

    public function download()
    {
        $disk = Storage::disk('public');
        abort_unless($disk->exists(self::PATH), 404);

        return $disk->download(self::PATH, self::FILENAME);
    }
}
