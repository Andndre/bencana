<?php

namespace App\Http\Controllers;

use App\Helper\ArPatternHelper;
use App\Models\ArMarker;
use App\Models\Disaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArMarkerController extends Controller
{
    public function index(): View
    {
        $markers = ArMarker::with('disaster')->orderBy('created_at', 'desc')->get();

        return view('admin.markers.index', compact('markers'));
    }

    public function create(): View
    {
        $disasters = Disaster::orderBy('name')->get();

        return view('admin.markers.create', compact('disasters'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $timestamp = now()->format('YmdHis');
        $baseName = $this->resolveBaseName($request);

        $audioPath = null;
        if ($request->hasFile('path_audio')) {
            $audioFile = $request->file('path_audio');
            $ext = $audioFile->getClientOriginalExtension() ?: 'mp3';
            $audioPath = 'ar-markers/audio/'.$timestamp.'_audio_'.$baseName.'.'.$ext;
            Storage::disk('public')->put($audioPath, file_get_contents($audioFile->getRealPath()));
        }

        $logoPath = null;
        if ($request->input('mode') === 'auto') {
            $logoFile = $request->file('path_logo_tengah');
            $logoPath = 'ar-markers/logos/'.$timestamp.'_logo_'.$baseName.'.png';
            Storage::disk('public')->put($logoPath, file_get_contents($logoFile->getRealPath()));

            $markerData = $this->generateFromLogo(
                $logoFile->getRealPath(),
                $request->file('path_model'),
                $timestamp,
                $baseName,
                (string) $request->input('marker_code')
            );
        } else {
            $markerData = $this->storeMarkerAssets(
                $request->file('path_gambar_marker'),
                $request->file('path_model'),
                $timestamp,
                $baseName
            );
        }

        ArMarker::create([
            'disaster_id' => $request->disaster_id,
            'nama' => $request->nama,
            'marker_code' => $request->marker_code ?: null,
            'path_gambar_marker' => $markerData['path_gambar_marker'],
            'path_logo_tengah' => $logoPath,
            'path_patt' => $markerData['path_patt'],
            'path_model' => $markerData['path_model'] ?? null,
            'path_audio' => $audioPath,
        ]);

        return redirect()->route('admin.markers.index')
            ->with('success', 'Marker AR berhasil diupload.');
    }

    /** Preview marker auto-generate — memakai generator yang sama dengan penyimpanan. */
    public function preview(Request $request)
    {
        $request->validate([
            'path_logo_tengah' => 'required|image|mimes:png|max:2048',
            'marker_code' => 'nullable|string|max:64',
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'prv_');

        try {
            file_put_contents(
                $tmp,
                ArPatternHelper::buildLogoMarkerSource(
                    $request->file('path_logo_tengah')->getRealPath(),
                    (string) $request->input('marker_code')
                )
            );

            return response(ArPatternHelper::buildFullMarkerPng($tmp, 0.5, 512, 'black'))
                ->header('Content-Type', 'image/png');
        } finally {
            @unlink($tmp);
        }
    }

    public function edit(ArMarker $marker): View
    {
        $disasters = Disaster::orderBy('name')->get();

        return view('admin.markers.edit', compact('marker', 'disasters'));
    }

    public function update(Request $request, ArMarker $marker)
    {
        $request->validate($this->rules($marker));

        $marker->nama = $request->nama;
        $marker->disaster_id = $request->disaster_id;
        $codeChanged = ($request->marker_code ?: null) !== $marker->marker_code;
        $marker->marker_code = $request->marker_code ?: null;

        $mode = $request->input('mode');
        $regenerated = false;

        // Marker ID ikut jadi seed pola, jadi kalau berubah pola harus dibuat ulang
        // dari logo yang tersimpan — kalau tidak, PNG cetak dan .patt jadi beda.
        $regenerateFromStoredLogo = $mode === 'auto'
            && $codeChanged
            && ! $request->hasFile('path_logo_tengah')
            && $marker->path_logo_tengah
            && Storage::disk('public')->exists($marker->path_logo_tengah);

        // Mode auto dengan logo baru — hapus aset lama, generate ulang dari logo
        if ($mode === 'auto' && ($request->hasFile('path_logo_tengah') || $regenerateFromStoredLogo)) {
            $this->deletePublicFile($marker->path_gambar_marker);
            $this->deletePublicFile($marker->path_patt);

            $timestamp = now()->format('YmdHis');
            $baseName = $this->resolveBaseName($request);

            if ($regenerateFromStoredLogo) {
                $logoPath = $marker->path_logo_tengah;
                $logoSource = Storage::disk('public')->path($logoPath);
            } else {
                $this->deletePublicFile($marker->path_logo_tengah);
                $logoFile = $request->file('path_logo_tengah');
                $logoPath = 'ar-markers/logos/'.$timestamp.'_logo_'.$baseName.'.png';
                Storage::disk('public')->put($logoPath, file_get_contents($logoFile->getRealPath()));
                $logoSource = $logoFile->getRealPath();
            }

            $markerData = $this->generateFromLogo(
                $logoSource,
                $request->file('path_model'),
                $timestamp,
                $baseName,
                (string) $request->input('marker_code')
            );

            $marker->path_logo_tengah = $logoPath;
            $marker->path_gambar_marker = $markerData['path_gambar_marker'];
            $marker->path_patt = $markerData['path_patt'];
            $marker->path_model = $markerData['path_model'] ?? $marker->path_model;
            $regenerated = true;
        }

        // Mode custom dengan gambar marker baru — hapus file lama + generate ulang .patt
        if ($mode === 'custom' && $request->hasFile('path_gambar_marker')) {
            $this->deletePublicFile($marker->path_gambar_marker);
            $this->deletePublicFile($marker->path_patt);
            // Pindah dari auto ke custom: logo lama tidak terpakai lagi
            $this->deletePublicFile($marker->path_logo_tengah);

            $markerData = $this->storeMarkerAssets(
                $request->file('path_gambar_marker'),
                $request->file('path_model')
            );

            $marker->path_logo_tengah = null;
            $marker->path_gambar_marker = $markerData['path_gambar_marker'];
            $marker->path_patt = $markerData['path_patt'];
            $marker->path_model = $markerData['path_model'] ?? $marker->path_model;
            $regenerated = true;
        }

        // Handle model saja diubah (tanpa regenerate marker)
        if (! $regenerated && $request->hasFile('path_model')) {
            $this->deletePublicFile($marker->path_model);

            $modelFile = $request->file('path_model');
            $ext = $modelFile->getClientOriginalExtension() ?: 'glb';
            $modelPath = 'ar-markers/models/'.$marker->marker_id.'_model_'.now()->format('YmdHis').'.'.$ext;
            Storage::disk('public')->put($modelPath, file_get_contents($modelFile->getRealPath()));
            $marker->path_model = $modelPath;
        }

        // Handle audio baru
        if ($request->hasFile('path_audio')) {
            $this->deletePublicFile($marker->path_audio);

            $audioFile = $request->file('path_audio');
            $ext = $audioFile->getClientOriginalExtension() ?: 'mp3';
            $audioPath = 'ar-markers/audio/'.$marker->marker_id.'_audio_'.now()->format('YmdHis').'.'.$ext;
            Storage::disk('public')->put($audioPath, file_get_contents($audioFile->getRealPath()));
            $marker->path_audio = $audioPath;
        }

        $marker->save();

        return redirect()->route('admin.markers.index')
            ->with('success', 'Marker AR berhasil diperbarui.');
    }

    public function destroy(ArMarker $marker)
    {
        $this->deletePublicFile($marker->path_audio);
        $this->deletePublicFile($marker->path_gambar_marker);
        $this->deletePublicFile($marker->path_logo_tengah);
        $this->deletePublicFile($marker->path_patt);
        $this->deletePublicFile($marker->path_model);
        $marker->delete();

        return redirect()->route('admin.markers.index')
            ->with('success', 'Marker AR berhasil dihapus.');
    }

    public function downloadZip()
    {
        $markers = ArMarker::whereNotNull('path_gambar_marker')
            ->with('disaster')
            ->get();

        if ($markers->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada marker AR untuk diunduh.');
        }

        $zip = new \ZipArchive;
        $tempFile = tempnam(sys_get_temp_dir(), 'ar_markers_').'.zip';

        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file ZIP.');
        }

        foreach ($markers as $marker) {
            $disasterName = $marker->disaster?->slug ?? 'umum';
            $prefix = $disasterName.'/';

            if (Storage::disk('public')->exists($marker->path_gambar_marker)) {
                $pngContent = Storage::disk('public')->get($marker->path_gambar_marker);
                $name = $marker->nama ?? 'marker_'.$marker->marker_id;
                $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
                $zip->addFromString($prefix.$name.'.png', $pngContent);
            }
        }

        $zip->close();

        return response()->download($tempFile, 'ar_markers_'.now()->format('YmdHis').'.zip')
            ->deleteFileAfterSend(true);
    }

    /** Aturan validasi bersama store() dan update(). */
    private function rules(?ArMarker $marker = null): array
    {
        // Saat edit, file lama dipertahankan kalau tidak diunggah ulang — jadi tidak wajib.
        $isCreate = $marker === null;

        return [
            'mode' => 'required|in:auto,custom',
            'disaster_id' => 'nullable|integer|exists:disasters,id',
            'nama' => ($isCreate ? 'nullable' : 'required').'|string|max:255',
            'marker_code' => [
                'nullable', 'string', 'max:64', 'alpha_dash',
                Rule::unique('ar_marker', 'marker_code')->ignore($marker?->marker_id, 'marker_id'),
            ],
            'path_logo_tengah' => ($isCreate ? 'required_if:mode,auto|' : '').'nullable|image|mimes:png|max:2048',
            'path_gambar_marker' => ($isCreate ? 'required_if:mode,custom|' : '').'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'path_model' => 'nullable|file|mimes:glb,gltf,binary|max:20480',
            'path_audio' => 'nullable|file|mimes:mp3,wav,ogg,webm|max:10240',
        ];
    }

    /** Nama dasar file: dari marker_code/nama untuk mode auto, dari nama file untuk custom. */
    private function resolveBaseName(Request $request): string
    {
        if ($request->input('mode') === 'auto' || ! $request->hasFile('path_gambar_marker')) {
            $raw = $request->input('marker_code') ?: $request->input('nama') ?: 'marker';
        } else {
            $original = preg_replace('/[^a-zA-Z0-9\._-]/', '', $request->file('path_gambar_marker')->getClientOriginalName());
            $raw = pathinfo($original, PATHINFO_FILENAME);
        }

        return preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', $raw)) ?: 'marker';
    }

    /** Susun marker dari logo tengah, lalu jalankan pipeline generate yang sama. */
    private function generateFromLogo(
        string $logoPath,
        $modelFile,
        string $timestamp,
        string $baseName,
        string $seed = ''
    ): array {
        $tmp = tempnam(sys_get_temp_dir(), 'mrk_');

        try {
            file_put_contents($tmp, ArPatternHelper::buildLogoMarkerSource($logoPath, $seed));

            return $this->storeGeneratedAssets($tmp, $modelFile, $timestamp, $baseName, 'path_logo_tengah');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Gagal menyusun marker dari logo', ['message' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'path_logo_tengah' => 'Gagal menyusun marker dari logo. Pastikan file PNG valid.',
            ]);
        } finally {
            @unlink($tmp);
        }
    }

    private function storeMarkerAssets($file, $modelFile = null, $timestamp = null, $baseName = null): array
    {
        $timestamp = $timestamp ?? now()->format('YmdHis');
        $originalName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
        $baseName = $baseName ?? (preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'marker');

        return $this->storeGeneratedAssets($file->getRealPath(), $modelFile, $timestamp, $baseName, 'path_gambar_marker');
    }

    private function storeGeneratedAssets(
        string $sourcePath,
        $modelFile,
        string $timestamp,
        string $baseName,
        string $errorKey
    ): array {
        $markerPath = null;
        $patternPath = null;
        $modelPath = null;

        try {
            $patternContent = ArPatternHelper::encodeImageToPattern($sourcePath);
            $patternPath = 'ar-markers/patterns/'.$timestamp.'_patt_'.$baseName.'.patt';
            Storage::disk('public')->put($patternPath, $patternContent);

            $markerPng = ArPatternHelper::buildFullMarkerPng($sourcePath, 0.5, 512, 'black');
            $markerPath = 'ar-markers/markers/'.$timestamp.'_marker_'.$baseName.'.png';
            Storage::disk('public')->put($markerPath, $markerPng);

            if ($modelFile) {
                $ext = $modelFile->getClientOriginalExtension() ?: 'glb';
                $modelPath = 'ar-markers/models/'.$timestamp.'_model_'.$baseName.'.'.$ext;
                Storage::disk('public')->put($modelPath, file_get_contents($modelFile->getRealPath()));
            }
        } catch (\Throwable $e) {
            $this->deletePublicFile($markerPath);
            $this->deletePublicFile($patternPath);
            $this->deletePublicFile($modelPath);
            Log::error('Gagal membuat AR pattern otomatis', [
                'marker_path' => $markerPath,
                'message' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                $errorKey => 'Gagal membuat file pattern otomatis. Pastikan file gambar valid.',
            ]);
        }

        return [
            'path_gambar_marker' => $markerPath,
            'path_patt' => $patternPath,
            'path_model' => $modelPath,
        ];
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
