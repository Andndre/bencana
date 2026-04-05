<?php

namespace App\Http\Controllers;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use App\Models\MitigationStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
                    ->update(['content' => $stepData['content']]);
            }
        }

        // Add new steps
        if ($request->has('new_steps')) {
            $maxOrder = $disaster->mitigationSteps()->max('order') ?? 0;
            foreach ($request->input('new_steps') as $phase => $contents) {
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

    public function editLocations(): View
    {
        $disasters = Disaster::with('locations')->get();

        return view('admin.locations.index', compact('disasters'));
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

    public function destroyStep(MitigationStep $step): RedirectResponse
    {
        $step->delete();

        return redirect()->back()->with('success', 'Langkah berhasil dihapus.');
    }
}
