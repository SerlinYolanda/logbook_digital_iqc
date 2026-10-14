<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use Illuminate\Http\Request;

class InstrumentController extends Controller
{
    public function index()
    {
        $instruments = Instrument::orderBy('calibration_due_date')->get();

        $stats = [
            'total'         => $instruments->count(),
            'valid'         => $instruments->filter(fn ($i) => $i->calibrationStatus() === 'valid')->count(),
            'due_soon'      => $instruments->filter(fn ($i) => $i->calibrationStatus() === 'due_soon')->count(),
            'expired'       => $instruments->filter(fn ($i) => $i->calibrationStatus() === 'expired')->count(),
            'verified_today'=> $instruments->filter(fn ($i) => $i->isVerifiedToday())->count(),
        ];

        $stats['verified_pct'] = $stats['total'] > 0
            ? (int) round(($stats['verified_today'] / $stats['total']) * 100)
            : 0;

        return view('instruments.index', compact('instruments', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInstrument($request);
        Instrument::create($validated);

        return redirect()->route('instruments.index')
            ->with('success', "Instrument {$validated['instrument_code']} registered.");
    }

    public function update(Request $request, Instrument $instrument)
    {
        $validated = $this->validateInstrument($request, $instrument->id);
        $instrument->update($validated);

        return redirect()->route('instruments.index')
            ->with('success', "Instrument {$instrument->instrument_code} updated.");
    }

    public function verify(Instrument $instrument)
    {
        $instrument->update(['last_verified_at' => now()->toDateString()]);

        return redirect()->route('instruments.index')
            ->with('success', "Daily verification recorded for {$instrument->instrument_code}.");
    }

    public function destroy(Instrument $instrument)
    {
        $code = $instrument->instrument_code;
        $instrument->delete();

        return redirect()->route('instruments.index')->with('success', "Instrument {$code} deleted.");
    }

    private function validateInstrument(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'instrument_code'      => 'required|string|max:50|unique:instruments,instrument_code' . ($ignoreId ? ",{$ignoreId}" : ''),
            'name'                 => 'required|string|max:150',
            'brand_model'          => 'nullable|string|max:150',
            'measuring_range'      => 'nullable|string|max:150',
            'calibration_due_date' => 'required|date',
        ]);
    }
}
