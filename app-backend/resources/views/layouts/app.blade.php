<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logbook Digital IQC Portal')</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal; font-style: normal; font-size: 20px;
            line-height: 1; letter-spacing: normal; text-transform: none;
            display: inline-block; white-space: nowrap; word-wrap: normal;
            direction: ltr; -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga'; -webkit-font-smoothing: antialiased;
            user-select: none;
        }
        .icon-sm  { font-size: 16px !important; }
        .icon-md  { font-size: 20px !important; }
        .icon-lg  { font-size: 24px !important; }
        .icon-xl  { font-size: 28px !important; }
        .icon-2xl { font-size: 36px !important; }

        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.3); border-radius: 9999px; }

        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .font-mono, code, pre, .nik, .monospace { font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, monospace; }

        [type='text'], [type='email'], [type='number'], [type='password'],
        [type='date'], [type='url'], [type='search'], textarea, select {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e2e8f0 !important;
            border-radius: 0.75rem !important;
        }
        [type='text']::placeholder, [type='email']::placeholder,
        [type='number']::placeholder, [type='password']::placeholder,
        [type='date']::placeholder, textarea::placeholder { color: #94a3b8 !important; }
        [type='text']:focus, [type='email']:focus, [type='number']:focus,
        [type='password']:focus, [type='date']:focus, [type='url']:focus,
        [type='search']:focus, textarea:focus, select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.18) !important;
            outline: none !important;
        }
        [type='text']:-webkit-autofill, [type='password']:-webkit-autofill,
        [type='email']:-webkit-autofill {
            -webkit-text-fill-color: #0f172a !important;
            -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
        }
        select { color-scheme: light; cursor: pointer; }

        @media print {
            aside, header, .no-print, form#inspectionFilterForm, nav[role="navigation"], .pagination {
                display: none !important;
            }
            body, main, div.flex-1 {
                overflow: visible !important;
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .shadow-xl, .shadow-md, .shadow-lg, .shadow-sm, .shadow-2xs {
                box-shadow: none !important;
            }
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex font-sans bg-slate-50 text-slate-800 overflow-hidden">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col z-30 shrink-0 h-full hidden md:flex border-r border-slate-800">
        <div class="h-14 flex items-center px-4 border-b border-slate-800 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-black text-white text-xs tracking-wider shadow-sm shrink-0">
                    IQC
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-xs tracking-wider text-white uppercase">LOGBOOK DIGITAL IQC</div>
                    @php
                        $user = auth()->user();
                        $role = $user->role ?? 'inspector_ee';
                        $subLabel = $user->role_label ?? 'IQC Inspector';
                        $commLabel = match($role) {
                            'inspector_ee' => 'EE PART',
                            'inspector_automotive' => 'AUTOMOTIVE',
                            'inspector_wire_harness' => 'WIRE HARNESS',
                            'supervisor' => 'IQC SUPERVISOR',
                            'master_data' => 'LOGISTICS & MASTER DATA',
                            'admin' => 'SYSTEM ADMINISTRATOR',
                            default => strtoupper(str_replace('_', ' ', $role)),
                        };
                        $dotColor = match($role) {
                            'inspector_ee' => 'bg-emerald-400',
                            'inspector_automotive' => 'bg-teal-400',
                            'inspector_wire_harness' => 'bg-purple-400',
                            'supervisor' => 'bg-amber-400',
                            'master_data' => 'bg-blue-400',
                            default => 'bg-indigo-400',
                        };
                    @endphp
                    <div class="text-[9px] text-slate-400 font-semibold tracking-wider truncate flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span> {{ $commLabel }}
                    </div>
                </div>
            </a>
        </div>

        @php 
            $pendingMrbCount = \App\Models\Logbook::where('status', 'menunggu_penetapan')->count();
            $waitingGrnCount = \App\Models\Receiving::where('status_antrean', 'menunggu_inspeksi')->count();
            $myRevisionCount = $user && $user->isInspector() ? \App\Models\Logbook::where('inspector_id', $user->id)->where('status', 'perlu_revisi')->count() : 0;
        @endphp

        <nav class="flex-1 overflow-y-auto custom-scrollbar py-3 px-3 flex flex-col gap-1">
            <div class="px-3 pt-2 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Main Navigation</div>
            
            {{-- Dashboard: visible to ALL roles --}}
            <a href="{{ route('dashboard') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">dashboard</span> Dashboard
            </a>

            {{-- 1. INSPECTOR IQC MENUS --}}
            @if($user && ($user->isInspector() || $user->isSupervisor()))
            <div class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Quality Inspection</div>

            @if($user->isInspector())
            <a href="{{ route('logbooks.create') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('logbooks.create') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">assignment</span> Inspection Logbook
            </a>
            @endif

            <a href="{{ route('logbooks.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('logbooks.index', 'logbooks.show', 'logbooks.edit') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">history_edu</span> Logbook History
                @if($myRevisionCount > 0)
                    <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.2 rounded-full">
                        {{ $myRevisionCount }} Revisions
                    </span>
                @endif
            </a>
            @endif

            {{-- 2. SUPERVISOR IQC MENUS --}}
            @if($user && $user->isSupervisor())
            <div class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Supervision & MRB</div>

            <a href="{{ route('supervisor.mrb.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('supervisor.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">gavel</span> MRB Disposition
                @if($pendingMrbCount > 0)
                    <span class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.2 rounded-full">
                        {{ $pendingMrbCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('reports.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">bar_chart</span> Summary Reports
            </a>
            @endif

            {{-- 3. MASTER DATA / LOGISTIK MENUS --}}
            @if($user && ($user->isMasterData() || $user->isAdmin()))
            <div class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Logistics & Master Data</div>

            <a href="{{ route('receivings.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('receivings.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">conveyor_belt</span> Inbound GRN Queue
                @if($waitingGrnCount > 0)
                    <span class="ml-auto bg-blue-500 text-white text-[10px] font-bold px-2 py-0.2 rounded-full">
                        {{ $waitingGrnCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('master-data.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('master-data.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">database</span> Master Data
            </a>
            @endif

            {{-- 4. TRACEABILITY (Inspector, Supervisor, Logistik) --}}
            @if($user && ($user->isInspector() || $user->isSupervisor() || $user->isMasterData()))
            <div class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Quality Traceability</div>
            <a href="{{ route('traceability.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('traceability.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">search_check</span> Material Traceability
            </a>
            @endif

            {{-- 5. ADMIN SISTEM MENUS --}}
            @if($user && $user->isAdmin())
            <div class="px-3 pt-3 pb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">System & Security</div>

            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">manage_accounts</span> User Accounts
            </a>

            <a href="{{ route('admin.audit-trails.index') }}" class="px-3 py-2 text-xs rounded-lg transition flex items-center gap-2.5 {{ request()->routeIs('admin.audit-trails.*') ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800 font-medium' }}">
                <span class="material-symbols-outlined text-[18px]">security</span> Audit Trail
            </a>
            @endif
        </nav>

        <div class="p-4 border-t border-slate-800 flex justify-between items-center text-slate-500 text-[10px] font-mono">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px] text-emerald-400">shield</span> ISO 9001:2015</span>
            <span>v5.0-PROPOSAL</span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden w-full">
        <!-- Top Header: Clean Corporate Navigation -->
        <header class="h-14 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0 hidden md:flex">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="font-medium text-slate-400">Portal</span>
                <span class="text-slate-300">/</span>
                @php
                    $currentPageLabel = match(true) {
                        request()->routeIs('dashboard*') => 'Executive Overview',
                        request()->routeIs('receivings*') => 'Material Inbound (GRN)',
                        request()->routeIs('logbooks*') => 'Inspection Records',
                        request()->routeIs('reports*') => 'Quality Reports',
                        request()->routeIs('supervisor*') => 'MRB Review & Decisions',
                        request()->routeIs('master-data*') => 'Master Catalog',
                        request()->routeIs('traceability*') => 'Traceability',
                        request()->routeIs('admin.users*') => 'User Accounts',
                        request()->routeIs('admin.audit-trails*') => 'Audit Trails',
                        request()->routeIs('profile*') => 'My Profile',
                        default => 'Portal',
                    };
                @endphp
                <span class="font-bold text-slate-900">{{ $currentPageLabel }}</span>
            </div>
            
            <div class="flex items-center gap-5">
                <!-- Date -->
                <div class="flex items-center gap-1.5 text-slate-500 text-xs font-medium">
                    <span class="material-symbols-outlined text-[15px] text-slate-400">calendar_today</span>
                    <span>{{ now()->translatedFormat('d F Y') }}</span>
                </div>

                <span class="h-4 w-px bg-slate-200"></span>

                <!-- Active Role Badge -->
                <div class="flex items-center gap-1.5 text-xs text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md font-medium">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                    <span>{{ auth()->user()->role_label ?? 'Inspector' }}</span>
                </div>

                <!-- Notif -->
                <div class="relative cursor-pointer p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                    @if($pendingMrbCount > 0)
                        <span class="absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-rose-500"></span>
                    @endif
                </div>

                <!-- Profile Dropdown -->
                <div class="relative group cursor-pointer pl-3 border-l border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            @php
                                $uName = auth()->user()->name ?? 'User';
                                $uWords = explode(' ', trim($uName));
                                $uInitials = count($uWords) >= 2 ? strtoupper(substr($uWords[0], 0, 1) . substr($uWords[1], 0, 1)) : strtoupper(substr($uName, 0, 2));
                            @endphp
                            {{ $uInitials }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800 leading-tight">{{ $uName }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">Shift {{ auth()->user()->shift ?? '1' }} • {{ auth()->user()->nik ?? '-' }}</div>
                        </div>
                    </div>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-1">
                            <a href="{{ route('profile') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">person</span> User Profile
                            </a>
                            <a href="{{ route('traceability.index') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">search_check</span> Material Traceability
                            </a>
                            @if(auth()->user() && auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">manage_accounts</span> Manage Users
                            </a>
                            <a href="{{ route('admin.audit-trails.index') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">security</span> Audit Trail
                            </a>
                            @endif
                            @if(auth()->user() && auth()->user()->isSupervisor())
                            <a href="{{ route('reports.index') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">bar_chart</span> Summary Reports
                            </a>
                            @endif
                            <a href="{{ route('instruments.index') }}" class="px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">speed</span> Instrument Status
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-bold">
                                    <span class="material-symbols-outlined icon-sm">logout</span> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Header (keep minimal) -->
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
                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:text-rose-600" title="Log Out">
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

        @if(isset($errors) && $errors->any())
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-2.5 text-xs font-medium text-rose-800 shrink-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined icon-sm text-rose-600">warning</span>
                    <span>Input validation errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Content Body -->
        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8 bg-slate-50 relative">
            @yield('content')
        </main>
    </div>
@stack('scripts')
</body>
</html>
