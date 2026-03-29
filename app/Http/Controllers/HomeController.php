<?php

namespace App\Http\Controllers;

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
        return view('penanggulangan-bencana');
    }
}
