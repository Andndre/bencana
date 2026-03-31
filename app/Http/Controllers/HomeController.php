<?php

namespace App\Http\Controllers;

use App\Models\Disaster;
use App\Models\DisasterLocation;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function simulasiBencana()
    {
        return view('simulasi-bencana');
    }

    public function penanggulanganBencana()
    {
        $disasters = Disaster::with('mitigationSteps')->get();
        return view('penanggulangan-bencana', compact('disasters'));
    }

    public function petaBencana(): View
    {
        $locations = DisasterLocation::with('disaster')->get();
        return view('peta-bencana', compact('locations'));
    }
}
