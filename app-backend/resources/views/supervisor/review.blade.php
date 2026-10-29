@extends('layouts.app')

@section('title', 'Lot Disposition Evaluation #' . $inspection->lot_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.validation') }}" class="p-2 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-900 transition">
                <span class="material-symbols-outlined icon-md">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Quality Disposition Validation</h1>
                <p class="text-xs text-slate-400">Determine the final disposition decision for incoming vendor material lot</p>
            </div>
        </div>
    </div>

    <!-- Lot Summary Card -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <span class="text-xs text-slate-400 block">Lot & PO Number:</span>
                <span class="text-base font-bold text-slate-900 font-mono">{{ $inspection->lot_number }}</span> 
                <span class="text-xs text-slate-400 font-mono">({{ $inspection->po_number }})</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Component Part:</span>
                <span class="text-sm font-bold text-indigo-600">{{ $inspection->part->part_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Supplier:</span>
                <span class="text-sm font-semibold text-slate-700">{{ $inspection->part->supplier_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Testing Inspector:</span>
                <span class="text-sm font-semibold text-slate-700">{{ $inspection->inspector->name ?? '-' }}</span>
            </div>
        </div>

        <!-- Measurement Summary -->
        <div class="text-xs space-y-2">
            <div class="font-semibold text-slate-600">Sampling Results Summary:</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] text-slate-400 uppercase">Sample Count</span>
                    <div class="text-base font-bold text-slate-900 font-mono">{{ $inspection->measurements->count() }} Pcs</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] text-slate-400 uppercase">Drawing Specification</span>
                    <div class="text-base font-bold text-indigo-600 font-mono">{{ $inspection->part->nominal_dimension }} mm</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] text-slate-400 uppercase">Pass Tolerance</span>
                    <div class="text-base font-bold text-emerald-600 font-mono">{{ $inspection->part->tolerance_min }} - {{ $inspection->part->tolerance_max }}</div>
                </div>
                <div class="p-3 rounded-xl {{ ($isFatalNCR ?? false) ? 'bg-rose-100/70 border-rose-300' : 'bg-slate-50 border-slate-200' }} border">
                    <span class="text-[10px] {{ ($isFatalNCR ?? false) ? 'text-rose-700 font-bold' : 'text-slate-400' }} uppercase">Fatality / Defect Rate</span>
                    <div class="text-base font-bold {{ ($isFatalNCR ?? false) ? 'text-rose-700' : ($inspection->defects->count() > 0 ? 'text-rose-600' : 'text-slate-700') }} font-mono">
                        {{ $fatalityRate ?? 0 }}% {{ ($isFatalNCR ?? false) ? '(100% FATAL)' : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isFatalNCR ?? false)
    <!-- Critical 100% Fatality Alert Banner -->
    <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 shadow-sm flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-200 text-rose-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined icon-lg">gpp_bad</span>
        </div>
        <div>
            <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wide">Poka-Yoke Quality Rule: 100% Fatality Rate Detected</h3>
            <p class="text-xs text-rose-700 mt-1">
                Every tested sample in this lot has failed dimensional/defect tolerances. Per IQC QA Standard Operating Procedure, <strong>Accept</strong> and <strong>MRB</strong> dispositions are locked. This lot <strong>MUST BE RETURNED TO VENDOR</strong> with an active NCR.
            </p>
        </div>
    </div>
    @endif

    {{-- ── PHOTO EVIDENCE GALLERY (Inspector Uploads) ── --}}
    @php
        $allPhotos    = $inspection->photos ?? collect();
        $passPhotos   = $allPhotos->where('photo_type', 'pass');
        $defectPhotos = $allPhotos->where('photo_type', 'defect');
    @endphp

    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center gap-3">
            <span class="p-2 rounded-xl bg-indigo-600 text-white">
                <span class="material-symbols-outlined icon-md">photo_library</span>
            </span>
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Photo Evidence from Inspector</h2>
                <p class="text-xs text-slate-400">{{ $allPhotos->count() }} file(s) uploaded — Pass evidence + Defect evidence</p>
            </div>
        </div>

        <div class="p-5 space-y-6">
            @if($allPhotos->count() === 0)
                <div class="text-center py-8 text-slate-400 text-xs">
                    <span class="material-symbols-outlined text-[36px] block mb-2">image_not_supported</span>
                    No photos were uploaded for this inspection.
                </div>
            @else
                {{-- Pass Photos --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide">
                            ✅ Pass / OK Evidence ({{ $passPhotos->count() }} photo{{ $passPhotos->count() === 1 ? '' : 's' }})
                        </span>
                    </div>
                    @if($passPhotos->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($passPhotos as $photo)
                                <div class="rounded-xl overflow-hidden border-2 border-emerald-200 bg-slate-50 group">
                                    <a href="{{ Storage::disk('public')->url($photo->path) }}" target="_blank" title="Click to enlarge">
                                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                                             alt="{{ $photo->label }}"
                                             class="w-full h-28 object-cover group-hover:brightness-90 transition">
                                    </a>
                                    <div class="p-2 text-[10px] text-slate-600">
                                        <div class="font-semibold truncate">{{ $photo->label ?: 'Pass Photo' }}</div>
                                        <div class="text-slate-400 mt-0.5">by {{ $photo->uploader->name ?? 'Inspector' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">No pass photos uploaded.</p>
                    @endif
                </div>

                {{-- Defect Photos --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="text-xs font-bold text-rose-700 uppercase tracking-wide">
                            🔴 Defect Evidence ({{ $defectPhotos->count() }} photo{{ $defectPhotos->count() === 1 ? '' : 's' }})
                        </span>
                    </div>
                    @if($defectPhotos->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($defectPhotos as $photo)
                                <div class="rounded-xl overflow-hidden border-2 border-rose-200 bg-slate-50 group">
                                    <a href="{{ Storage::disk('public')->url($photo->path) }}" target="_blank" title="Click to enlarge">
                                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                                             alt="{{ $photo->label }}"
                                             class="w-full h-28 object-cover group-hover:brightness-90 transition">
                                    </a>
                                    <div class="p-2 text-[10px] text-slate-600">
                                        <div class="font-semibold truncate">{{ $photo->label ?: 'Defect Photo' }}</div>
                                        <div class="text-slate-400 mt-0.5">by {{ $photo->uploader->name ?? 'Inspector' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">No defect photos uploaded.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- SPV Review Rules & Standards Callout -->
    <div class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-5 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-600 icon-md">rule</span>
            Quality Disposition Rules for Supervisor (SPV)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-[11px] text-slate-700">
            <div class="p-3 bg-white rounded-xl border border-indigo-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
                <div>
                    <strong class="text-rose-700">100% Fatality Rule (Critical NCR):</strong>
                    <p class="text-slate-500 mt-0.5">Total sample failure locks out Accept and MRB. Lot must be dispositioned as <strong>RTV</strong> or <strong>SCRAP</strong>.</p>
                </div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-indigo-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
                <div>
                    <strong class="text-rose-700">Return to Vendor (RTV):</strong>
                    <p class="text-slate-500 mt-0.5">Full lot rejection returned to supplier with formal 8D Corrective Action and Debit Note.</p>
                </div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-indigo-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
                <div>
                    <strong class="text-purple-700">Internal Scrap / Condemn:</strong>
                    <p class="text-slate-500 mt-0.5">Unusable non-returnable materials condemned for destructive disposal & scrap write-off.</p>
                </div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-indigo-100 flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0 text-[10px]">4</span>
                <div>
                    <strong class="text-amber-700">MRB / Rework (Conditional):</strong>
                    <p class="text-slate-500 mt-0.5">Minor deviations subjected to cross-functional review, 100% sorting, or precision trimming.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Disposition Decision Form -->
    <form action="{{ route('supervisor.decide', $inspection->id) }}" method="POST" class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl space-y-6">
        @csrf

        <div>
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-700 icon-md">gavel</span>
                Select Quality Disposition Decision *
            </h2>
            <p class="text-xs text-slate-400 mb-4">
                Select one of the validated disposition statuses per ISO 9001 / IQC Quality Standards:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <!-- Option 1: RETURN TO VENDOR (RTV) -->
                <label class="p-4 rounded-xl border {{ ($isFatalNCR ?? false) ? 'border-2 border-rose-500 bg-rose-50 shadow-md ring-2 ring-rose-300' : 'border-slate-200 bg-slate-50 hover:border-rose-500/50 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50' }} flex flex-col justify-between transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-rose-600 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">assignment_return</span>
                            RTV (RETURN TO VENDOR)
                        </span>
                        <input type="radio" name="disposition" value="return_to_vendor" {{ ($isFatalNCR ?? false) ? 'checked' : '' }} required class="text-rose-600 focus:ring-0">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">
                        Total rejection. 8D NCR issued to vendor with formal debit note & material return.
                    </p>
                </label>

                <!-- Option 2: SCRAP / DISPOSAL -->
                <label class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:border-purple-500/50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 flex flex-col justify-between transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-purple-700 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                            SCRAP (INTERNAL DISPOSAL)
                        </span>
                        <input type="radio" name="disposition" value="scrap" required class="text-purple-600 focus:ring-0">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">
                        Condemn unusable non-returnable materials for destructive disposal & inventory write-off.
                    </p>
                </label>

                <!-- Option 3: MRB -->
                <label class="p-4 rounded-xl border {{ ($isFatalNCR ?? false) ? 'opacity-40 bg-slate-100 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-slate-50 hover:border-amber-500/50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 cursor-pointer' }} flex flex-col justify-between transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-700 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">groups</span>
                            MRB (CONDITIONAL REVIEW)
                        </span>
                        <input type="radio" name="disposition" value="mrb" {{ ($isFatalNCR ?? false) ? 'disabled' : 'required' }} class="text-amber-600 focus:ring-0">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">
                        Material Review Board (Engineering deviation testing / 100% sorting protocol).
                    </p>
                </label>

                <!-- Option 4: REWORK -->
                <label class="p-4 rounded-xl border {{ ($isFatalNCR ?? false) ? 'opacity-40 bg-slate-100 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-slate-50 hover:border-blue-500/50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 cursor-pointer' }} flex flex-col justify-between transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-blue-700 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">build</span>
                            REWORK / 100% SORT
                        </span>
                        <input type="radio" name="disposition" value="rework" {{ ($isFatalNCR ?? false) ? 'disabled' : 'required' }} class="text-blue-600 focus:ring-0">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">
                        Authorized for internal deburring/cleaning or vendor on-site rework.
                    </p>
                </label>

                <!-- Option 5: ACCEPT -->
                <label class="p-4 rounded-xl border {{ ($isFatalNCR ?? false) ? 'opacity-40 bg-slate-100 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-slate-50 hover:border-emerald-500/50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 cursor-pointer' }} flex flex-col justify-between transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-600 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            ACCEPT (APPROVED)
                        </span>
                        <input type="radio" name="disposition" value="accept" {{ ($isFatalNCR ?? false) ? 'disabled' : 'required' }} class="text-emerald-600 focus:ring-0">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">
                        Lot conforms to quality tolerances. Authorized for production release.
                    </p>
                </label>
            </div>
        </div>

        <!-- Disposition Notes -->
        <div>
            <label for="disposition_notes" class="block font-semibold text-slate-600 text-xs uppercase tracking-wider mb-1.5">
                Quality Evaluation Notes & Digital Authorization (Supervisor Notes) *
            </label>
            <textarea id="disposition_notes" name="disposition_notes" rows="4" required
                placeholder="e.g.: Verified that the lot meets AQL criteria or specified NCR terms. Lot disposition authorized."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-900 focus:outline-none focus:border-amber-500 transition">{{ ($isFatalNCR ?? false) ? '100% Fatality rate detected (all samples failed drawing tolerance). Lot is completely rejected. Non-Conformance Report (NCR) issued for immediate Return to Vendor.' : '' }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-[11px] text-slate-500">
                Authorized by: <strong class="text-slate-600">{{ auth()->user()->name }}</strong> ({{ auth()->user()->nik }})
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ route('supervisor.validation') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-900 font-bold text-xs shadow-lg shadow-amber-600/30 transition flex items-center gap-2">
                    <span class="material-symbols-outlined icon-md">check_circle</span>
                    <span>Confirm Disposition</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
