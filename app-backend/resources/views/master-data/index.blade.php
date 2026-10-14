@extends('layouts.app')

@section('title', 'Master Data Management - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                    <span class="material-symbols-outlined icon-xl">settings_suggest</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">Component Master Data & AQL Standards</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Manage part drawing catalog, critical dimension tolerance limits, and quality sampling parameters.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleAddPartModal(true)" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                <span class="material-symbols-outlined icon-md">add_circle</span>
                <span>Add New Part</span>
            </button>
        </div>
    </div>

    <!-- Parts Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400 icon-md">category</span>
                Registered Part Number Catalog ({{ $parts->total() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Part Number</th>
                        <th class="py-3 px-4">Component Name</th>
                        <th class="py-3 px-4">Supplier Vendor</th>
                        <th class="py-3 px-4 text-center">Nominal</th>
                        <th class="py-3 px-4 text-center">Tolerance (Min - Max)</th>
                        <th class="py-3 px-4 text-center">Sampling Level & AQL</th>
                        <th class="py-3 px-4 text-center">Total Inspections</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($parts as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                {{ $p->part_number }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ $p->part_name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $p->supplier_name }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900">
                                {{ number_format($p->nominal_dimension, 3) }} {{ $p->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-emerald-600">
                                {{ number_format($p->tolerance_min, 3) }} - {{ number_format($p->tolerance_max, 3) }} {{ $p->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $p->sampling_level }} • AQL {{ $p->aql_level }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-600">
                                {{ $p->inspections_count }} lot
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('inspections.create', ['part_id' => $p->id]) }}" class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 inline-flex items-center" title="Start Testing This Part">
                                    <span class="material-symbols-outlined icon-sm">straighten</span>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                <button type="button" onclick="openEditPartModal({{ json_encode($p) }})" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 border border-amber-200 transition inline-flex items-center cursor-pointer" title="Edit Master Part Specifications">
                                    <span class="material-symbols-outlined icon-sm">edit</span>
                                </button>
                                <form action="{{ route('master-data.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete master part [{{ $p->part_number }}]?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 border border-slate-300 transition inline-flex items-center cursor-pointer" title="Delete">
                                        <span class="material-symbols-outlined icon-sm">delete</span>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-xs">
                                No component part data yet. Click "Add New Part" to register the first part.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($parts->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/90">
                {{ $parts->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Add New Part Modal -->
<div id="addPartModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-300 rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/90">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <span class="material-symbols-outlined icon-md">add_circle</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Add / Update Component Master Part</h3>
                    <p class="text-[10px] text-slate-500">Register new part or update existing part specifications.</p>
                </div>
            </div>
            <button onclick="toggleAddPartModal(false)" class="text-slate-400 hover:text-slate-900 p-1 rounded-lg hover:bg-slate-100 transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('master-data.store') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div class="bg-indigo-50/80 border border-indigo-100 rounded-xl p-3 text-[11px] text-indigo-800 flex items-center gap-2">
                <span class="material-symbols-outlined icon-sm text-indigo-600">verified</span>
                <span><strong>Admin Privilege:</strong> If you enter a Part Number that already exists, its specifications will be automatically updated.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="part_number" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Part Number (Unique) *
                    </label>
                    <input type="text" id="part_number" name="part_number" required placeholder="Example: PRT-CVR-005"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono uppercase focus:outline-none focus:border-indigo-500 transition font-bold">
                </div>
                <div>
                    <label for="part_name" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Component Name *
                    </label>
                    <input type="text" id="part_name" name="part_name" required placeholder="Example: Cover Engine Guard"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <div>
                <label for="supplier_name" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                    Vendor / Supplier Name *
                </label>
                <input type="text" id="supplier_name" name="supplier_name" required placeholder="Example: PT Jaya Abadi Presisi"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="nominal_dimension" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Nominal *
                    </label>
                    <input type="number" step="0.001" id="nominal_dimension" name="nominal_dimension" required placeholder="25.000"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition font-bold">
                </div>
                <div>
                    <label for="tolerance_min" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Min Tolerance *
                    </label>
                    <input type="number" step="0.001" id="tolerance_min" name="tolerance_min" required placeholder="24.850"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition text-emerald-600 font-bold">
                </div>
                <div>
                    <label for="tolerance_max" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Max Tolerance *
                    </label>
                    <input type="number" step="0.001" id="tolerance_max" name="tolerance_max" required placeholder="25.150"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition text-emerald-600 font-bold">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="unit" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Unit *
                    </label>
                    <input type="text" id="unit" name="unit" value="mm" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition">
                </div>
                <div>
                    <label for="sampling_level" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Sampling Level *
                    </label>
                    <select id="sampling_level" name="sampling_level"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                        <option value="General II">General Level II (Standard)</option>
                        <option value="General I">General Level I (Reduced)</option>
                        <option value="General III">General Level III (Tightened)</option>
                        <option value="Special S-4">Special S-4 (Destructive)</option>
                    </select>
                </div>
                <div>
                    <label for="aql_level" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        AQL Level *
                    </label>
                    <select id="aql_level" name="aql_level"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                        <option value="0.65">AQL 0.65 (Critical)</option>
                        <option value="1.0" selected>AQL 1.0 (Standard)</option>
                        <option value="2.5">AQL 2.5 (Minor)</option>
                        <option value="4.0">AQL 4.0 (Non-critical)</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                <button type="button" onclick="toggleAddPartModal(false)"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    <span>Save Master Part</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Part Modal (Admin Only) -->
<div id="editPartModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-300 rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/90">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined icon-md">edit</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Edit Master Part Specifications</h3>
                    <p class="text-[10px] text-slate-500">Modify dimensions, tolerance boundaries, and sampling parameters.</p>
                </div>
            </div>
            <button onclick="toggleEditPartModal(false)" class="text-slate-400 hover:text-slate-900 p-1 rounded-lg hover:bg-slate-100 transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editPartForm" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_part_number" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Part Number (Unique) *
                    </label>
                    <input type="text" id="edit_part_number" name="part_number" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono uppercase focus:outline-none focus:border-indigo-500 transition font-bold">
                </div>
                <div>
                    <label for="edit_part_name" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Component Name *
                    </label>
                    <input type="text" id="edit_part_name" name="part_name" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <div>
                <label for="edit_supplier_name" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                    Vendor / Supplier Name *
                </label>
                <input type="text" id="edit_supplier_name" name="supplier_name" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="edit_nominal_dimension" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Nominal *
                    </label>
                    <input type="number" step="0.001" id="edit_nominal_dimension" name="nominal_dimension" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition font-bold">
                </div>
                <div>
                    <label for="edit_tolerance_min" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Min Tolerance *
                    </label>
                    <input type="number" step="0.001" id="edit_tolerance_min" name="tolerance_min" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition text-emerald-600 font-bold">
                </div>
                <div>
                    <label for="edit_tolerance_max" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Max Tolerance *
                    </label>
                    <input type="number" step="0.001" id="edit_tolerance_max" name="tolerance_max" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition text-emerald-600 font-bold">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="edit_unit" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Unit *
                    </label>
                    <input type="text" id="edit_unit" name="unit" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-indigo-500 transition">
                </div>
                <div>
                    <label for="edit_sampling_level" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        Sampling Level *
                    </label>
                    <select id="edit_sampling_level" name="sampling_level"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                        <option value="General II">General Level II (Standard)</option>
                        <option value="General I">General Level I (Reduced)</option>
                        <option value="General III">General Level III (Tightened)</option>
                        <option value="Special S-4">Special S-4 (Destructive)</option>
                    </select>
                </div>
                <div>
                    <label for="edit_aql_level" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1">
                        AQL Level *
                    </label>
                    <select id="edit_aql_level" name="aql_level"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                        <option value="0.65">AQL 0.65 (Critical)</option>
                        <option value="1.0">AQL 1.0 (Standard)</option>
                        <option value="2.5">AQL 2.5 (Minor)</option>
                        <option value="4.0">AQL 4.0 (Non-critical)</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                <button type="button" onclick="toggleEditPartModal(false)"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    <span>Update Specifications</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAddPartModal(show) {
        const modal = document.getElementById('addPartModal');
        if (show) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    function toggleEditPartModal(show) {
        const modal = document.getElementById('editPartModal');
        if (show) modal.classList.remove('hidden');
        else modal.classList.add('hidden');
    }

    function openEditPartModal(part) {
        document.getElementById('editPartForm').action = '/master-data/' + part.id;
        document.getElementById('edit_part_number').value = part.part_number;
        document.getElementById('edit_part_name').value = part.part_name;
        document.getElementById('edit_supplier_name').value = part.supplier_name;
        document.getElementById('edit_nominal_dimension').value = part.nominal_dimension;
        document.getElementById('edit_tolerance_min').value = part.tolerance_min;
        document.getElementById('edit_tolerance_max').value = part.tolerance_max;
        document.getElementById('edit_unit').value = part.unit;
        document.getElementById('edit_sampling_level').value = part.sampling_level;
        document.getElementById('edit_aql_level').value = part.aql_level;
        toggleEditPartModal(true);
    }
</script>
@endsection
