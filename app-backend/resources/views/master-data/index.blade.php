@extends('layouts.app')
@section('title', 'Manage Master Data - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">database</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Master Data Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data part, supplier, dan kategori defect.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($tab === 'parts')
            <button type="button" onclick="document.getElementById('addPartModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>+ Add Master Part</span>
            </button>
            @elseif($tab === 'suppliers')
            <button type="button" onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>+ Add Supplier</span>
            </button>
            @else
            <button type="button" onclick="document.getElementById('addDefectCategoryModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>+ Add Defect Category</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <a href="{{ route('master-data.index', ['tab' => 'parts']) }}" 
            class="px-5 py-3 text-xs font-bold transition border-b-2 flex items-center gap-2
            {{ $tab === 'parts' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[18px]">category</span>
            <span>Master Parts & Parameters ({{ $parts->total() }})</span>
        </a>
        <a href="{{ route('master-data.index', ['tab' => 'suppliers']) }}" 
            class="px-5 py-3 text-xs font-bold transition border-b-2 flex items-center gap-2
            {{ $tab === 'suppliers' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[18px]">store</span>
            <span>Master Suppliers & Vendors ({{ $suppliers->total() }})</span>
        </a>
        <a href="{{ route('master-data.index', ['tab' => 'defects']) }}" 
            class="px-5 py-3 text-xs font-bold transition border-b-2 flex items-center gap-2
            {{ $tab === 'defects' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[18px]">bug_report</span>
            <span>Defect Categories Taxonomy ({{ $defectCategories->total() }})</span>
        </a>
    </div>

    <!-- TAB 1: PARTS & PARAMETERS -->
    @if($tab === 'parts')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Part Code</th>
                        <th class="py-3 px-4">Component Name</th>
                        <th class="py-3 px-4">Commodity</th>
                        <th class="py-3 px-4">Sampling Standard</th>
                        <th class="py-3 px-4">Registered Parameters</th>
                        <th class="py-3 px-4 text-center">Total Inbound</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($parts as $part)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                            {{ $part->kode_part }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $part->nama_part }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[200px]">{{ $part->spesifikasi ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                @if($part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($part->kategori_komoditas === 'automotive') bg-teal-50 text-teal-700 border border-teal-200
                                @else bg-purple-50 text-purple-700 border border-purple-200 @endif">
                                {{ $part->komoditas_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <div>{{ $part->standar_aql }}</div>
                            <div class="text-[10px] text-slate-400">{{ $part->level_inspeksi }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                @foreach($part->parameters as $p)
                                    <div class="text-[11px] text-slate-700 flex items-center justify-between gap-2 bg-slate-50 px-2 py-0.5 rounded">
                                        <span><b>{{ $p->nama_parameter }}</b> ({{ $p->nominal }} {{ $p->satuan }})</span>
                                        <form action="{{ route('master-data.parameters.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this parameter?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 text-[10px] cursor-pointer">&times;</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" onclick="openAddParamModal({{ $part->id }}, '{{ $part->kode_part }}')" class="mt-1 text-[10px] font-bold text-indigo-600 hover:underline cursor-pointer">
                                + Add Parameter
                            </button>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                            {{ $part->receivings_count }} Lots
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" onclick="openEditPartModal({{ json_encode($part) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition cursor-pointer" title="Edit Part">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No master part records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parts->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $parts->links() }}</div>
        @endif
    </div>
    @endif

    <!-- TAB 2: SUPPLIERS -->
    @if($tab === 'suppliers')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Supplier Code</th>
                        <th class="py-3 px-4">Supplier Name</th>
                        <th class="py-3 px-4">Country of Origin</th>
                        <th class="py-3 px-4">Address & Contact</th>
                        <th class="py-3 px-4 text-center">Partner Status</th>
                        <th class="py-3 px-4 text-center">Total Inbound</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $sup)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                            {{ $sup->kode_supplier }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $sup->nama_supplier }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700">
                            {{ $sup->negara_asal }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            <div>{{ $sup->alamat ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $sup->kontak ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sup->status_aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $sup->status_aktif ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                            {{ $sup->receivings_count }} Lots
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" onclick="openEditSupplierModal({{ json_encode($sup) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition cursor-pointer" title="Edit Supplier">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No supplier records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $suppliers->links() }}</div>
        @endif
    </div>
    @endif

    <!-- TAB 3: DEFECT CATEGORIES -->
    @if($tab === 'defects')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Defect Code</th>
                        <th class="py-3 px-4">Defect Category Name</th>
                        <th class="py-3 px-4">Description / Scope</th>
                        <th class="py-3 px-4 text-center">Total Occurrences in Logbook</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($defectCategories as $cat)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-rose-600">
                            {{ $cat->kode_defect }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $cat->nama_defect }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 max-w-[300px]">
                            {{ $cat->deskripsi ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                            {{ $cat->defects_count }} Occurrences
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" onclick="openEditDefectModal({{ json_encode($cat) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition cursor-pointer" title="Edit Category">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">No defect categories registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($defectCategories->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $defectCategories->links() }}</div>
        @endif
    </div>
    @endif

</div>

<!-- Modal Tambah Part -->
<div id="addPartModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add New Master Part</h3>
            <button type="button" onclick="document.getElementById('addPartModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('master-data.parts.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Part Code *</label>
                <input type="text" name="kode_part" required placeholder="e.g. EE-MCU-3204-STM" class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Part / Component Name *</label>
                <input type="text" name="nama_part" required placeholder="e.g. Microcontroller 32-Bit ARM..." class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Commodity Category *</label>
                <select name="kategori_komoditas" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="ee_part">EE Part (Electrical & Electronics)</option>
                    <option value="automotive">Automotive (Mechanical)</option>
                    <option value="wire_harness">Wire Harness (Cables & Terminals)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">AQL Standard *</label>
                    <input type="text" name="standar_aql" required value="MIL-STD-105E AQL 0.65" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Inspection Level *</label>
                    <input type="text" name="level_inspeksi" required value="General Level II" class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Additional Specifications</label>
                <textarea name="spesifikasi" rows="2" class="w-full text-xs rounded-xl border-slate-200" placeholder="Technical specifications or notes..."></textarea>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addPartModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Part</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Parameter Uji -->
<div id="addParamModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add Parameter: <span id="addParamPartCode" class="font-mono text-indigo-600"></span></h3>
            <button type="button" onclick="document.getElementById('addParamModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="addParamForm" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Test Parameter Name *</label>
                <input type="text" name="nama_parameter" required placeholder="e.g. Body Length (mm)" class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal</label>
                    <input type="number" step="any" name="nominal" placeholder="14.00" class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Min Limit</label>
                    <input type="number" step="any" name="batas_min" placeholder="13.90" class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Max Limit</label>
                    <input type="number" step="any" name="batas_max" placeholder="14.10" class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Unit *</label>
                <input type="text" name="satuan" required value="mm" placeholder="mm, V, Ohm, uF..." class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addParamModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Parameter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div id="addSupplierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add New Supplier Partner</h3>
            <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('master-data.suppliers.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Supplier Code *</label>
                <input type="text" name="kode_supplier" required placeholder="e.g. SUP-STM-01" class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Supplier Company Name *</label>
                <input type="text" name="nama_supplier" required placeholder="PT STMicroelectronics Indonesia" class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Country of Origin *</label>
                    <input type="text" name="negara_asal" required value="Indonesia" class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Contact / Phone Number</label>
                    <input type="text" name="kontak" placeholder="+62 778..." class="w-full text-xs rounded-xl border-slate-200">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Factory / Warehouse Address</label>
                <textarea name="alamat" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Kategori Defect -->
<div id="addDefectCategoryModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add New Defect Category</h3>
            <button type="button" onclick="document.getElementById('addDefectCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('master-data.defects.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Defect Code *</label>
                <input type="text" name="kode_defect" required placeholder="e.g. DEF-PIN-01" class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Defect Name *</label>
                <input type="text" name="nama_defect" required placeholder="e.g. Solder Bridge / Pin Bent" class="w-full text-xs rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Defect Scope / Description</label>
                <textarea name="deskripsi" rows="3" class="w-full text-xs rounded-xl border-slate-200" placeholder="Describe visual or functional criteria considered as defect..."></textarea>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addDefectCategoryModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddParamModal(partId, partCode) {
    document.getElementById('addParamPartCode').innerText = partCode;
    document.getElementById('addParamForm').action = "/master-data/parts/" + partId + "/parameters";
    document.getElementById('addParamModal').classList.remove('hidden');
}
</script>
@endsection