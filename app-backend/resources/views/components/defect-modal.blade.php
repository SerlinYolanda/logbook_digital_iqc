<!-- Defect Record Modal (Screen 08) -->
<div id="defectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-300 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/90">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                    <span class="material-symbols-outlined icon-md">report_problem</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Defect / Finding Record</h3>
                    <p id="defectLotSubtitle" class="text-[11px] text-slate-400 font-mono">Lot: # -</p>
                </div>
            </div>
            <button onclick="closeDefectModal()" class="text-slate-400 hover:text-slate-900 p-1 rounded-lg hover:bg-slate-100 transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Form Defect -->
        <form id="defectForm" method="POST" action="" class="p-6 space-y-4 text-xs">
            @csrf

            <!-- Defect Classification -->
            <div>
                <label for="defect_type" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                    Defect Type *
                </label>
                <select id="defect_type" name="defect_type" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-rose-500 transition">
                    <option value="">-- Select Defect Classification --</option>
                    <option value="Dimensional Out of Tolerance">Dimensional Out of Tolerance</option>
                    <option value="Surface Scratch & Dent">Surface Scratch & Dent</option>
                    <option value="Flash / Burrs Excessive">Flash / Burrs Excessive</option>
                    <option value="Material Crack / Porosity">Material Crack / Porosity</option>
                    <option value="Contamination / Oil Stain">Contamination / Oil Stain</option>
                    <option value="Thread Pitch Incomplete">Thread Pitch Incomplete</option>
                </select>
            </div>

            <!-- Severity Level -->
            <div>
                <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                    Severity Level *
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col items-center gap-1 cursor-pointer hover:border-amber-500/50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 transition">
                        <input type="radio" name="severity" value="minor" checked class="hidden">
                        <span class="text-[11px] font-bold text-amber-700">MINOR</span>
                        <span class="text-[9px] text-slate-400">Cosmetic</span>
                    </label>
                    <label class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col items-center gap-1 cursor-pointer hover:border-orange-500/50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-500/10 transition">
                        <input type="radio" name="severity" value="major" class="hidden">
                        <span class="text-[11px] font-bold text-orange-400">MAJOR</span>
                        <span class="text-[9px] text-slate-400">Reduced Function</span>
                    </label>
                    <label class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col items-center gap-1 cursor-pointer hover:border-rose-500/50 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 transition">
                        <input type="radio" name="severity" value="critical" class="hidden">
                        <span class="text-[11px] font-bold text-rose-600">CRITICAL</span>
                        <span class="text-[9px] text-slate-400">Safety / Assembly Fail</span>
                    </label>
                </div>
            </div>

            <!-- Defect Quantity -->
            <div>
                <label for="defect_quantity" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                    Defective Sample Count (Pcs) *
                </label>
                <input type="number" id="defect_quantity" name="quantity" min="1" value="1" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-rose-500 transition">
            </div>

            <!-- Description -->
            <div>
                <label for="defect_desc" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                    Location Description & Finding Evidence
                </label>
                <textarea id="defect_desc" name="description" rows="3"
                    placeholder="e.g.: Sharp burrs found on bolt hole radius #2, endangering wire harness assembly."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-rose-500 transition"></textarea>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                <button type="button" onclick="closeDefectModal()"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 font-medium transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-slate-900 font-semibold shadow-lg shadow-rose-600/30 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    <span>Save Defect Record</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDefectModal(inspectionId, lotNumber, partName) {
        const modal = document.getElementById('defectModal');
        const form = document.getElementById('defectForm');
        const subtitle = document.getElementById('defectLotSubtitle');
        
        subtitle.innerText = `Lot: ${lotNumber} • ${partName}`;
        form.action = `/inspections/${inspectionId}/defects`;
        
        modal.classList.remove('hidden');
    }

    function closeDefectModal() {
        document.getElementById('defectModal').classList.add('hidden');
    }
</script>
