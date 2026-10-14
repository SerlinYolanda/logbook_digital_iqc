<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use App\Models\Inspection;
use Illuminate\Http\Request;

class DefectController extends Controller
{
    public function store(Request $request, $inspectionId)
    {
        $inspection = Inspection::findOrFail($inspectionId);

        $validated = $request->validate([
            'defect_type' => 'required|string|max:100',
            'severity' => 'required|in:minor,major,critical',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $defect = $inspection->defects()->create($validated);

        return back()->with('success', "Catatan cacat [{$defect->defect_type} - {$defect->severity}] berhasil ditambahkan!");
    }

    public function destroy($id)
    {
        $defect = Defect::findOrFail($id);
        $defect->delete();

        return back()->with('info', 'Catatan cacat telah dihapus.');
    }
}
