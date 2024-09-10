<?php

namespace App\Http\Controllers;

use App\Models\Sponsorship;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    public function index()
    {
        $sponsorships = Sponsorship::all();
        return view('doctors.sponsorships.index', compact('sponsorships'));
    }

    public function chooseSponsorship()
    {
      $sponsorships = Sponsorship::all();
      return view('sponsorships.choose', compact('sponsorships'));
    }


    public function create()
{
    // Recupera tutte le sponsorizzazioni
    $sponsorships = Sponsorship::all();

    // Recupera il medico associato all'utente loggato
    $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

    // Passa le sponsorizzazioni e il medico alla vista
    return view('doctors.sponsorships.create', compact('sponsorships', 'doctor'));
}


    public function store(Request $request)
{
    $request->validate([
        'sponsorship_id' => 'required|exists:sponsorships,id',
        'doctor_id' => 'required|exists:doctors,id',
    ]);

    // Recupera la sponsorizzazione selezionata
    $sponsorship = Sponsorship::findOrFail($request->sponsorship_id);

    // Calcola date di inizio e fine
    $date_start = now();

     // Assicurati che 'duration' sia un intero
    $duration = (int) $sponsorship->duration;

    $date_end = $date_start->copy()->addDays($duration);

    // Inserisci nella tabella pivot
    $sponsorship->doctors()->attach($request->doctor_id, [
        'name' => $sponsorship->name,
        'price' => $sponsorship->price,
        'date_start' => $date_start,
        'date_end' => $date_end,
    ]);

    return redirect()->route('sponsorships.index')->with('success', 'Sponsorship associated successfully.');
}

    public function show(Sponsorship $sponsorship)
    {
        return view('doctors.sponsorships.show', compact('sponsorship'));
    }

    public function edit(Sponsorship $sponsorship)
    {
        return view('doctors.sponsorships.edit', compact('sponsorship'));
    }

    public function update(Request $request, Sponsorship $sponsorship)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|integer',
        ]);

        $sponsorship->update($request->all());

        return redirect()->route('doctors.sponsorships.index')->with('success', 'Sponsorship updated successfully.');
    }

    public function destroy(Sponsorship $sponsorship)
    {
        $sponsorship->delete();

        return redirect()->route('sponsorships.index')->with('success', 'Sponsorship deleted successfully.');
    }
}