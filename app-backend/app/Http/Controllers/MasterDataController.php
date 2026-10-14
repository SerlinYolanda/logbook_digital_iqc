<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index()
    {
        $parts = Part::withCount('inspections')->latest()->paginate(10);
        return view('master-data.index', compact('parts'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Poka-Yoke Guard: Only Administrator has authorization to create or modify Master Part parameters.');
        }

        $partNumber = strtoupper(trim($request->part_number));
        $existingPart = Part::where('part_number', $partNumber)->first();

        // If part already exists, admin can update/overwrite its parameters
        if ($existingPart) {
            $validated = $request->validate([
                'part_number' => 'required|string|max:50',
                'part_name' => 'required|string|max:150',
                'supplier_name' => 'required|string|max:150',
                'sampling_level' => 'required|string',
                'aql_level' => 'required|string',
                'nominal_dimension' => 'required|numeric',
                'tolerance_min' => 'required|numeric',
                'tolerance_max' => 'required|numeric|gte:tolerance_min',
                'unit' => 'required|string|max:10',
            ]);
            $validated['part_number'] = $partNumber;
            $existingPart->update($validated);

            return redirect()->route('master-data.index')
                ->with('success', "Existing part [{$existingPart->part_number}] specifications successfully updated.");
        }

        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts,part_number|max:50',
            'part_name' => 'required|string|max:150',
            'supplier_name' => 'required|string|max:150',
            'sampling_level' => 'required|string',
            'aql_level' => 'required|string',
            'nominal_dimension' => 'required|numeric',
            'tolerance_min' => 'required|numeric',
            'tolerance_max' => 'required|numeric|gte:tolerance_min',
            'unit' => 'required|string|max:10',
        ]);
        $validated['part_number'] = $partNumber;

        Part::create($validated);

        return redirect()->route('master-data.index')
            ->with('success', "Master part [{$validated['part_number']}] successfully created.");
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Poka-Yoke Guard: Only Administrator has authorization to edit Master Part parameters.');
        }

        $part = Part::findOrFail($id);

        $validated = $request->validate([
            'part_number' => 'required|string|max:50|unique:parts,part_number,' . $part->id,
            'part_name' => 'required|string|max:150',
            'supplier_name' => 'required|string|max:150',
            'sampling_level' => 'required|string',
            'aql_level' => 'required|string',
            'nominal_dimension' => 'required|numeric',
            'tolerance_min' => 'required|numeric',
            'tolerance_max' => 'required|numeric|gte:tolerance_min',
            'unit' => 'required|string|max:10',
        ]);

        $validated['part_number'] = strtoupper(trim($validated['part_number']));
        $part->update($validated);

        return redirect()->route('master-data.index')
            ->with('success', "Master part [{$part->part_number}] specifications updated successfully.");
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Poka-Yoke Guard: Only Administrator has authorization to delete Master Part data.');
        }

        $part = Part::findOrFail($id);
        $partNumber = $part->part_number;
        $part->delete();

        return redirect()->route('master-data.index')
            ->with('success', "Master part [{$partNumber}] has been deleted.");
    }
}
