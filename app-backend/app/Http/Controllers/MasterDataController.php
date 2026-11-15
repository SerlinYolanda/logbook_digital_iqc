<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Part;
use App\Models\PartParameter;
use App\Models\DefectCategory;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * UC-13: Kelola Data Master (Actor: Master Data / Logistik)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'parts');

        $suppliers = Supplier::withCount('receivings')->orderBy('nama_supplier')->paginate(10, ['*'], 'suppliers_page');
        $parts = Part::with('parameters')->withCount('receivings')->orderBy('nama_part')->paginate(10, ['*'], 'parts_page');
        $defectCategories = DefectCategory::withCount('defects')->orderBy('nama_defect')->paginate(10, ['*'], 'defects_page');

        return view('master-data.index', compact('tab', 'suppliers', 'parts', 'defectCategories'));
    }

    // -------------------------------------------------------------------------
    // SUPPLIERS
    // -------------------------------------------------------------------------
    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'kode_supplier' => 'required|string|unique:suppliers,kode_supplier|max:30',
            'nama_supplier' => 'required|string|max:150',
            'alamat'        => 'nullable|string|max:300',
            'kontak'        => 'nullable|string|max:100',
            'negara_asal'   => 'required|string|max:100',
        ]);

        $validated['status_aktif'] = true;
        $supplier = Supplier::create($validated);

        AuditTrail::log('CREATE_SUPPLIER', "Supplier {$supplier->kode_supplier}", null, $supplier->nama_supplier);

        return redirect()->route('master-data.index', ['tab' => 'suppliers'])
            ->with('success', "Supplier '{$supplier->nama_supplier}' has been added successfully.");
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:150',
            'alamat'        => 'nullable|string|max:300',
            'kontak'        => 'nullable|string|max:100',
            'negara_asal'   => 'required|string|max:100',
            'status_aktif'  => 'required|boolean',
        ]);

        $supplier->update($validated);

        AuditTrail::log('UPDATE_SUPPLIER', "Supplier {$supplier->kode_supplier}", null, json_encode($validated));

        return redirect()->route('master-data.index', ['tab' => 'suppliers'])
            ->with('success', "Supplier '{$supplier->nama_supplier}' details have been updated.");
    }

    // -------------------------------------------------------------------------
    // PARTS
    // -------------------------------------------------------------------------
    public function storePart(Request $request)
    {
        $validated = $request->validate([
            'kode_part'          => 'required|string|unique:parts,kode_part|max:50',
            'nama_part'          => 'required|string|max:150',
            'spesifikasi'        => 'nullable|string|max:300',
            'kategori_komoditas' => 'required|in:ee_part,automotive,wire_harness',
            'standar_aql'        => 'required|string|max:100',
            'level_inspeksi'     => 'required|string|max:100',
        ]);

        $part = Part::create($validated);

        AuditTrail::log('CREATE_PART', "Part {$part->kode_part}", null, "Komoditas: {$part->kategori_komoditas}");

        return redirect()->route('master-data.index', ['tab' => 'parts'])
            ->with('success', "Master Part '{$part->kode_part}' has been added successfully.");
    }

    public function updatePart(Request $request, Part $part)
    {
        $validated = $request->validate([
            'nama_part'          => 'required|string|max:150',
            'spesifikasi'        => 'nullable|string|max:300',
            'kategori_komoditas' => 'required|in:ee_part,automotive,wire_harness',
            'standar_aql'        => 'required|string|max:100',
            'level_inspeksi'     => 'required|string|max:100',
        ]);

        $part->update($validated);

        AuditTrail::log('UPDATE_PART', "Part {$part->kode_part}", null, json_encode($validated));

        return redirect()->route('master-data.index', ['tab' => 'parts'])
            ->with('success', "Part '{$part->kode_part}' details have been updated.");
    }

    // -------------------------------------------------------------------------
    // PART PARAMETERS
    // -------------------------------------------------------------------------
    public function storeParameter(Request $request, Part $part)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:100',
            'nominal'        => 'nullable|numeric',
            'batas_min'      => 'nullable|numeric',
            'batas_max'      => 'nullable|numeric',
            'satuan'         => 'required|string|max:20',
        ]);

        $param = $part->parameters()->create($validated);

        AuditTrail::log('ADD_PARAMETER', "Part {$part->kode_part}", null, "Param: {$param->nama_parameter} ({$param->nominal} {$param->satuan})");

        return back()->with('success', "Parameter '{$param->nama_parameter}' has been added to Part {$part->kode_part}.");
    }

    public function destroyParameter(PartParameter $parameter)
    {
        $part = $parameter->part;
        $paramName = $parameter->nama_parameter;
        $parameter->delete();

        AuditTrail::log('DELETE_PARAMETER', "Part {$part->kode_part}", $paramName, 'Deleted');

        return back()->with('info', "Parameter '{$paramName}' has been removed.");
    }

    // -------------------------------------------------------------------------
    // DEFECT CATEGORIES
    // -------------------------------------------------------------------------
    public function storeDefectCategory(Request $request)
    {
        $validated = $request->validate([
            'kode_defect' => 'required|string|unique:defect_categories,kode_defect|max:30',
            'nama_defect' => 'required|string|max:150',
            'deskripsi'   => 'nullable|string|max:300',
        ]);

        $cat = DefectCategory::create($validated);

        AuditTrail::log('CREATE_DEFECT_CATEGORY', "Category {$cat->kode_defect}", null, $cat->nama_defect);

        return redirect()->route('master-data.index', ['tab' => 'defects'])
            ->with('success', "Defect category '{$cat->nama_defect}' has been added successfully.");
    }

    public function updateDefectCategory(Request $request, DefectCategory $category)
    {
        $validated = $request->validate([
            'nama_defect' => 'required|string|max:150',
            'deskripsi'   => 'nullable|string|max:300',
        ]);

        $category->update($validated);

        AuditTrail::log('UPDATE_DEFECT_CATEGORY', "Category {$category->kode_defect}", null, json_encode($validated));

        return redirect()->route('master-data.index', ['tab' => 'defects'])
            ->with('success', "Defect category '{$category->nama_defect}' has been updated.");
    }
}
