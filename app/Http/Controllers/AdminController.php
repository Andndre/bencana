<?php

namespace App\Http\Controllers;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use App\Models\MitigationStep;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        $disasterCount = Disaster::count();
        $locationCount = DisasterLocation::count();

        return view('admin.index', compact('disasterCount', 'locationCount'));
    }

    public function disastersIndex(): View
    {
        $disasters = Disaster::withCount('locations', 'mitigationSteps')->get();

        return view('admin.disasters.index', compact('disasters'));
    }

    public function createDisaster(): View
    {
        return view('admin.disasters.create');
    }

    public function storeDisaster(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|alpha_dash|unique:disasters,slug',
            'description' => 'nullable|string',
        ]);

        Disaster::create($validated);

        return redirect()->route('admin.disasters.index')->with('success', 'Bencana berhasil ditambahkan.');
    }

    public function destroyDisaster(Disaster $disaster): RedirectResponse
    {
        $disaster->delete();

        return redirect()->route('admin.disasters.index')->with('success', 'Bencana berhasil dihapus.');
    }

    public function editDisaster(Disaster $disaster): View
    {
        $disaster->load('mitigationSteps');

        return view('admin.disasters.edit', compact('disaster'));
    }

    public function updateDisaster(Request $request, Disaster $disaster): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
        ]);

        $disaster->update($validated);

        // Sync mitigation steps
        if ($request->has('steps')) {
            foreach ($request->input('steps') as $stepId => $stepData) {
                MitigationStep::where('id', $stepId)
                    ->where('disaster_id', $disaster->id)
                    ->update([
                        'content' => $stepData['content'],
                        'order' => (int) ($stepData['order'] ?? 0),
                    ]);
            }
        }

        // Add new steps
        if ($request->has('new_steps')) {
            foreach ($request->input('new_steps') as $phase => $contents) {
                $maxOrder = $disaster->mitigationSteps()->where('phase', $phase)->max('order') ?? 0;
                foreach ($contents as $content) {
                    if (trim($content)) {
                        $maxOrder++;
                        MitigationStep::create([
                            'disaster_id' => $disaster->id,
                            'phase' => $phase,
                            'order' => $maxOrder,
                            'content' => trim($content),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.disasters.index')->with('success', 'Data bencana berhasil diperbarui.');
    }

    public function editLocations(Request $request): View
    {
        $locations = $this->filteredLocations($request)->paginate(15)->withQueryString();
        $mapLocations = DisasterLocation::with('disaster')->get();
        $disasters = Disaster::orderBy('name')->get();

        return view('admin.locations.index', compact('locations', 'mapLocations', 'disasters'));
    }

    public function bulkDestroyLocations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:disaster_locations,id',
        ]);

        $count = DisasterLocation::whereIn('id', $validated['ids'])->delete();

        return redirect()->back()->with('success', "{$count} lokasi berhasil dihapus.");
    }

    public function exportLocations(Request $request): StreamedResponse
    {
        $locations = $this->filteredLocations($request)->get();

        return response()->streamDownload(function () use ($locations) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama Lokasi', 'Bencana', 'Latitude', 'Longitude']);
            foreach ($locations as $location) {
                fputcsv($out, [
                    $location->location_name,
                    $location->disaster?->name,
                    $location->latitude,
                    $location->longitude,
                ]);
            }
            fclose($out);
        }, 'lokasi-bencana-'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Query lokasi dengan filter pencarian, jenis bencana, dan urutan dari request.
     */
    private function filteredLocations(Request $request): Builder
    {
        $sort = in_array($request->query('sort'), ['location_name', 'disaster_id', 'latitude'], true)
            ? $request->query('sort')
            : 'location_name';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        return DisasterLocation::with('disaster')
            ->when($request->query('q'), fn (Builder $query, string $q) => $query->where('location_name', 'like', "%{$q}%"))
            ->when($request->query('disaster_id'), fn (Builder $query, $id) => $query->where('disaster_id', $id))
            ->orderBy($sort, $dir);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        DisasterLocation::create($validated);

        return redirect()->route('admin.locations')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, DisasterLocation $location): RedirectResponse
    {
        $validated = $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroyLocation(DisasterLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations')->with('success', 'Lokasi berhasil dihapus.');
    }

    public function destroyStep(Request $request, MitigationStep $step): Response|RedirectResponse
    {
        $step->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->back()->with('success', 'Langkah berhasil dihapus.');
    }
}
