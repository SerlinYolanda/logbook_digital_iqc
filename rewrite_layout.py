import re

with open('app-backend/resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the body tag and its contents
# Find everything from <body ...> to </body>
start_idx = content.find('<body')
end_idx = content.find('</body>') + len('</body>')

target = content[start_idx:end_idx]

new_body = """<body class="h-full flex font-sans bg-slate-50 text-slate-800 overflow-hidden">

    <!-- Sidebar Navigation -->
    <aside class="w-64 border-r border-slate-200 bg-white/98 flex flex-col z-30 shrink-0 h-full hidden md:flex">
        <div class="h-16 flex items-center px-4 border-b border-slate-200 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-400 flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                    <span class="material-symbols-outlined text-white icon-xl">fact_check</span>
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-base tracking-tight text-slate-900 truncate">Logbook Digital IQC</div>
                    <div class="text-xs text-slate-400 truncate">Incoming QC • Plant 1</div>
                </div>
            </a>
        </div>

        <!-- Navigation Links (role-aware) -->
        <nav class="flex-1 overflow-y-auto custom-scrollbar p-3 flex flex-col gap-1.5">
            <div class="px-3 py-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">dashboard</span> Dashboard
            </a>

            @php $role = auth()->user()->role ?? 'inspector'; @endphp

            @if($role === 'inspector')
                <div class="px-3 py-2 mt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Inspektur</div>
                <a href="{{ route('inspections.create') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.create') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">straighten</span> Form Dimensi
                </a>
                <a href="{{ route('inspections.index') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.index*') && !request()->has('filter') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">history</span> Riwayat & Arsip
                </a>
                <a href="{{ route('inspections.index') }}?filter=defect" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->has('filter') && request('filter') == 'defect' ? 'bg-rose-50 text-rose-700 shadow-sm border border-rose-100' : 'text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">bug_report</span> Rekap Defek
                </a>

            @elseif($role === 'supervisor')
                <div class="px-3 py-2 mt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Supervisor</div>
                <a href="{{ route('inspections.index') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.index*') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">history</span> Riwayat & Arsip
                </a>
                <a href="{{ route('supervisor.validation') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('supervisor.*') ? 'bg-amber-50 text-amber-700 shadow-sm border border-amber-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">verified</span> Validasi SPV
                </a>
                <a href="{{ route('master-data.index') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('master-data.*') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">settings_suggest</span> Master Part
                </a>

            @elseif($role === 'admin')
                <div class="px-3 py-2 mt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Admin</div>
                <a href="{{ route('inspections.index') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.index*') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">history</span> Log Inspeksi
                </a>
                <a href="{{ route('master-data.index') }}" class="px-3.5 py-2 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('master-data.*') ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-transparent' }}">
                    <span class="material-symbols-outlined icon-sm">settings_suggest</span> Master Data
                </a>
            @endif
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            @auth
                <div class="flex items-center justify-between">
                    <div class="truncate pr-2">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-500 font-mono truncate">
                            {{ auth()->user()->nik }}
                            <span class="uppercase font-sans font-bold text-indigo-600 bg-indigo-100 px-1 py-0.5 rounded ml-1">{{ auth()->user()->role }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 transition flex items-center justify-center text-slate-600" title="Keluar">
                            <span class="material-symbols-outlined icon-sm">logout</span>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden w-full">
        <!-- Mobile Header -->
        <header class="md:hidden h-14 border-b border-slate-200 bg-white/98 px-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-blue-400 flex items-center justify-center shadow">
                    <span class="material-symbols-outlined text-white icon-sm">fact_check</span>
                </div>
                <span class="font-bold text-sm tracking-tight text-slate-900">Logbook IQC</span>
            </div>
            @auth
                 <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:text-rose-600" title="Keluar">
                        <span class="material-symbols-outlined icon-sm">logout</span>
                    </button>
                </form>
            @endauth
        </header>

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-2.5 text-xs font-medium text-emerald-800 flex items-center justify-between shrink-0">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined icon-sm text-emerald-600">check_circle</span>
                    {{ session('success') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-2.5 text-xs font-medium text-rose-800 flex items-center justify-between shrink-0">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined icon-sm text-rose-600">error</span>
                    {{ session('error') }}
                </span>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-2.5 text-xs font-medium text-rose-800 shrink-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined icon-sm text-rose-600">warning</span>
                    <span>Terdapat kesalahan input:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Content Body -->
        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8 bg-slate-50">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-500 shrink-0">
            Logbook Digital IQC System • Laravel & Tailwind CSS
        </footer>
    </div>
</body>"""

new_content = content[:start_idx] + new_body + content[end_idx:]

with open('app-backend/resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(new_content)
