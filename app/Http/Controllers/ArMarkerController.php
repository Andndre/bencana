<?php

namespace App\Http\Controllers;

use App\Helper\ArPatternHelper;
use App\Models\ArMarker;
use App\Models\Disaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $request->validate([
            'disaster_id' => 'nullable|integer|exists:disasters,id',
            'nama' => 'nullable|string|max:255',
            'path_gambar_marker' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'path_model' => 'nullable|file|mimes:glb,gltf,binary|max:20480',
            'path_audio' => 'nullable|file|mimes:mp3,wav,ogg,webm|max:10240',
        ]);

        $timestamp = now()->format('YmdHis');
        $originalName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $request->file('path_gambar_marker')->getClientOriginalName());
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'marker';

        $audioPath = null;
        if ($request->hasFile('path_audio')) {
            $audioFile = $request->file('path_audio');
            $ext = $audioFile->getClientOriginalExtension() ?: 'mp3';
            $audioPath = 'ar-markers/audio/'.$timestamp.'_audio_'.$baseName.'.'.$ext;
            Storage::disk('public')->put($audioPath, file_get_contents($audioFile->getRealPath()));
        }

        $markerData = $this->storeMarkerAssets(
            $request->file('path_gambar_marker'),
            $request->file('path_model'),
            $timestamp,
            $baseName
        );

        ArMarker::create([
            'disaster_id' => $request->disaster_id,
            'nama' => $request->nama,
            'path_gambar_marker' => $markerData['path_gambar_marker'],
            'path_patt' => $markerData['path_patt'],
            'path_model' => $markerData['path_model'] ?? null,
            'path_audio' => $audioPath,
        ]);

        return redirect()->route('admin.markers.index')
            ->with('success', 'Marker AR berhasil diupload.');
    }

    public function edit(ArMarker $marker): View
    {
        $disasters = Disaster::orderBy('name')->get();

        return view('admin.markers.edit', compact('marker', 'disasters'));
    }

    public function update(Request $request, ArMarker $marker)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'disaster_id' => 'nullable|integer|exists:disasters,id',
            'path_gambar_marker' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'path_model' => 'nullable|file|mimes:glb,gltf,binary|max:30720',
            'path_audio' => 'nullable|file|mimes:mp3,wav,ogg,webm|max:10240',
        ]);

        $marker->nama = $request->nama;
        $marker->disaster_id = $request->disaster_id;

        // Handle gambar marker baru — hapus file lama + generate ulang .patt
        if ($request->hasFile('path_gambar_marker')) {
            $this->deletePublicFile($marker->path_gambar_marker);
            $this->deletePublicFile($marker->path_patt);

            $markerData = $this->storeMarkerAssets(
                $request->file('path_gambar_marker'),
                $request->file('path_model')
            );

            $marker->path_gambar_marker = $markerData['path_gambar_marker'];
            $marker->path_patt = $markerData['path_patt'];
            $marker->path_model = $markerData['path_model'] ?? $marker->path_model;
        }

        // Handle model saja diubah (tanpa ganti gambar)
        if (! $request->hasFile('path_gambar_marker') && $request->hasFile('path_model')) {
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

    private function storeMarkerAssets($file, $modelFile = null, $timestamp = null, $baseName = null): array
    {
        $timestamp = $timestamp ?? now()->format('YmdHis');
        $originalName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
        $baseName = $baseName ?? (preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'marker');

        $markerPath = null;
        $patternPath = null;
        $modelPath = null;

        try {
            $sourcePath = $file->getRealPath();

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
                'path_gambar_marker' => 'Gagal membuat file pattern otomatis. Pastikan file gambar valid.',
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
