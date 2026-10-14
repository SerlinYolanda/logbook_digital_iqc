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
    </style>
    @stack('styles')
</head>
<body class="h-full flex font-sans bg-slate-50 text-slate-800 overflow-hidden">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col z-30 shrink-0 h-full hidden md:flex">
        <div class="h-16 flex items-center px-4 border-b border-slate-800 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shrink-0">
                    <span class="material-symbols-outlined text-white icon-xl">memory</span>
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-sm tracking-tight text-white truncate">LOGBOOK DIGITAL IQC</div>
                    <div class="text-[9px] text-emerald-400 font-bold tracking-wider truncate flex items-center gap-1.5 mt-0.5 uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> EE PART DIVISION
                    </div>
                </div>
            </a>
        </div>

        @php 
            $role = auth()->user()->role ?? 'inspector'; 
            $pendingReviewCount = \App\Models\Inspection::where('status', 'pending_approval')->count();
        @endphp

        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4 px-3 flex flex-col gap-1.5">
            <div class="px-3 py-2 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Main Menu</div>
            
            {{-- Dashboard: visible to ALL roles --}}
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">dashboard</span> Dashboard
                @if(request()->routeIs('dashboard')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            
            {{-- Inspection Form: Inspector only --}}
            @if($role === 'inspector')
            <a href="{{ route('inspections.create') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.create') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">assignment</span> Inspection Form
                @if(request()->routeIs('inspections.create')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            @endif
            
            {{-- Pending Review: Supervisor only --}}
            @if($role === 'supervisor')
            <a href="{{ route('supervisor.validation') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('supervisor.*') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">pending_actions</span> Pending Review
                @if($pendingReviewCount > 0)
                    <span class="ml-auto bg-rose-900/80 text-rose-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-rose-700">
                        {{ $pendingReviewCount }}
                    </span>
                @endif
                @if(request()->routeIs('supervisor.*')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            @endif


            <div class="px-3 py-2 mt-4 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Documents & Reports</div>
            
            {{-- Logbook History: ALL roles --}}
            <a href="{{ route('inspections.index') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('inspections.index') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">history_edu</span> Logbook History
                @if(request()->routeIs('inspections.index')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            
            {{-- Reports & Output: ALL roles --}}
            <a href="{{ route('reports.index') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('reports.*') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">bar_chart</span> Output & Reports
                @if(request()->routeIs('reports.*')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>

            {{-- Master Data & Instruments: Admin and Supervisor only --}}
            @if(in_array($role, ['admin', 'supervisor']))
            <div class="px-3 py-2 mt-4 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Master Data & Tools</div>
            
            {{-- Master Part EE: Admin only --}}
            @if($role === 'admin')
            <a href="{{ route('master-data.index') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('master-data.*') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">dns</span> Master Part EE
                @if(request()->routeIs('master-data.*')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            @endif

            {{-- User Accounts: Admin and Supervisor --}}
            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent' }}">
                <span class="material-symbols-outlined icon-sm">manage_accounts</span> User Management
                @if(request()->routeIs('admin.users.*')) <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span> @endif
            </a>
            
            {{-- Instrument Status: Admin and Supervisor --}}
            <a href="{{ route('instruments.index') }}" class="px-3.5 py-2.5 text-sm font-medium rounded-lg transition flex items-center gap-3 {{ request()->routeIs('instruments.*') ? 'bg-indigo-900/50 text-white border border-indigo-500/30 shadow-sm relative' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800 border border-transparent relative' }}">
                <span class="material-symbols-outlined icon-sm">speed</span> Instrument Status
                <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            </a>
            @endif
        </nav>

        <div class="p-4 border-t border-slate-800 flex justify-between items-center text-slate-500 text-[10px] font-mono">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px] text-emerald-400">shield</span> ISO 9001:2015</span>
            <span>v4.8-PROD</span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden w-full">
        <!-- New Top Header based on mockup -->
        <header class="h-16 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0 hidden md:flex">
            <div class="flex items-center gap-4 text-sm font-semibold text-slate-900">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 icon-md">memory</span>
                    <span>IQC Digital Logbook</span>
                </div>
                <span class="text-slate-300">/</span>
                <div class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                    EE Part Division
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Date -->
                <div class="flex items-center gap-2 text-slate-500">
                    <span class="material-symbols-outlined icon-sm">calendar_today</span>
                    <span class="text-xs font-semibold">{{ now()->translatedFormat('d F Y') }}</span>
                </div>

                <!-- Active Role Badge -->
                <div class="flex items-center gap-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 px-2.5 py-1">
                    <span class="material-symbols-outlined text-slate-400 text-[14px]">person_outline</span>
                    <span class="font-semibold text-slate-500">Role:</span>
                    <span class="font-bold text-slate-800">{{ ucfirst(auth()->user()->role ?? 'Inspector') }}</span>
                </div>
                <!-- Notif -->
                <div class="relative cursor-pointer">
                    <span class="material-symbols-outlined text-slate-400 icon-md">notifications</span>
                    @if($pendingReviewCount > 0)
                        <span class="absolute top-0 right-0 w-2 h-2 rounded-full bg-rose-500 border-2 border-white animate-pulse"></span>
                    @endif
                </div>
                <!-- Profile Dropdown -->
                <div class="relative group cursor-pointer pl-4 border-l border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-bold shadow-sm group-hover:bg-indigo-600 transition">
                            @php
                                $uName = auth()->user()->name ?? 'User';
                                $uWords = explode(' ', trim($uName));
                                $uInitials = count($uWords) >= 2 ? strtoupper(substr($uWords[0], 0, 1) . substr($uWords[1], 0, 1)) : strtoupper(substr($uName, 0, 2));
                            @endphp
                            {{ $uInitials }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ ucfirst(auth()->user()->role ?? 'User') }} — {{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> NIK: {{ auth()->user()->nik ?? '-' }} • Active
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-1">
                            <a href="{{ route('profile') }}" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">person</span> My Profile
                            </a>
                            <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">bar_chart</span> Output & Reports
                            </a>
                            <a href="{{ route('instruments.index') }}" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined icon-sm">speed</span> Instruments
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-medium">
                                    <span class="material-symbols-outlined icon-sm">logout</span> Log Out
                                </button>
                            </form>
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
</body>
</html>
