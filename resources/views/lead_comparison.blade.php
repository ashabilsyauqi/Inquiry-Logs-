<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perbandingan Leads: Real CS vs Analisa AI - Difitech CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">

    <!-- LEFT SIDEBAR NAVIGATION (GITHUB ENTERPRISE DARK THEME - UNIFIED WITH MAIN DASHBOARD) -->
    <aside class="w-64 bg-[#0d1117] text-[#c9d1d9] flex flex-col justify-between hidden md:flex flex-shrink-0 border-r border-[#30363d]">
        <div>
            <!-- Workspace / Organization Header -->
            <div class="px-4 py-4 border-b border-[#30363d] flex items-center justify-between bg-[#161b22]/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#21262d] border border-[#30363d] flex items-center justify-center text-[#2f81f7] font-bold shadow-sm">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25H1.75zM4 4.75a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 4.75zm0 3a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 7.75zm0 3a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-[#f0f6fc] text-xs tracking-tight leading-tight flex items-center gap-1.5">
                            Difitech CRM
                        </h2>
                        <p class="text-[10px] text-[#8b949e] font-mono">Enterprise Suite</p>
                    </div>
                </div>
                <span class="text-[10px] font-mono text-[#8b949e] bg-[#21262d] px-2 py-0.5 rounded border border-[#30363d]">
                    v2.4
                </span>
            </div>

            <!-- Nav Links -->
            <nav class="p-3 space-y-4 text-xs font-medium">
                <!-- Section 1: Navigation -->
                <div>
                    <p class="px-2 text-[10px] font-semibold uppercase tracking-wider text-[#8b949e] mb-2">Repositories & Pipelines</p>
                    <div class="space-y-1">
                        @if($user->isCeo())
                        <a href="/?account_id=all" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md {{ $accountId == 'all' && !request()->has('start_date') ? 'bg-[#161b22] text-[#f0f6fc] font-semibold' : 'text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc]' }} transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#2f81f7]" viewBox="0 0 16 16"><path d="M1.75 2.5h12.5a.25.25 0 01.25.25v10.5a.25.25 0 01-.25.25H1.75a.25.25 0 01-.25-.25V2.75c0-.138.112-.25.25-.25zM1.75 1A1.75 1.75 0 000 2.75v10.5C0 14.216.784 15 1.75 15h12.5A1.75 1.75 0 0016 13.25V2.75A1.75 1.75 0 0014.25 1H1.75zM4 4.75a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 4.75zm0 3a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 7.75zm0 3a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z"></path></svg>
                                <span>🌐 General (Semua Brand)</span>
                            </span>
                            <span class="text-[11px] bg-[#21262d] text-[#8b949e] px-2 py-0.5 rounded-full font-mono border border-[#30363d]">{{ $waAccounts->count() }} Brands</span>
                        </a>
                        @endif

                        <a href="/?filter=all" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#58a6ff]" viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"></path></svg>
                            <span>Overview All</span>
                        </a>

                        <a href="/?filter=all&view=analytics" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#bc8cff]" viewBox="0 0 16 16"><path d="M1.75 1.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h11.5a.75.75 0 000-1.5H2.5V2.25a.75.75 0 00-.75-.75zm3 4a.75.75 0 00-.75.75v5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-5a.75.75 0 00-.75-.75h-1.5zm4-2a.75.75 0 00-.75.75v7c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-7a.75.75 0 00-.75-.75h-1.5zm4 4a.75.75 0 00-.75.75v3c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-3a.75.75 0 00-.75-.75h-1.5z"></path></svg>
                            <span>Analytics & Insights</span>
                        </a>

                        <a href="/?filter=all&view=kanban" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#3fb950]" viewBox="0 0 16 16"><path d="M0 2.75C0 1.784.784 1 1.75 1h12.5C15.216 1 16 1.784 16 2.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75zm1.75-.25a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h3.5v-11h-3.5zm5 0v11h3.5v-11h-3.5zm5 0v11h2.75a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25h-2.75z"></path></svg>
                            <span>Kanban Pipeline</span>
                        </a>

                        <a href="/?filter=all&view=table" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#d29922]" viewBox="0 0 16 16"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25V5h13V1.75a.25.25 0 00-.25-.25H1.75zm13 4.75h-13v8c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25v-8z"></path></svg>
                            <span>Lead Master List</span>
                        </a>

                        <!-- Active Link: Perbandingan AI -->
                        <a href="/ai-comparison" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md bg-[#161b22] text-[#f0f6fc] font-semibold border-l-2 border-amber-400 transition text-left group">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-amber-400" viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zm.75 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.75.75h3a.75.75 0 000-1.5H8.75V4.75zM8 12a1 1 0 100-2 1 1 0 000 2z"></path></svg>
                                <span class="font-bold text-white">Perbandingan AI</span>
                            </span>
                            <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full">Active</span>
                        </a>
                    </div>
                </div>

                <!-- Section 2: Management Suite (Only for CEO & Supervisor) -->
                @if($user->isCeo() || $user->role === 'SUPERVISOR')
                <div>
                    <p class="px-2 text-[10px] font-semibold uppercase tracking-wider text-[#8b949e] mb-2">Organization Admin</p>
                    <div class="space-y-1">
                        @if($user->isCeo())
                        <a href="/?open_modal=brand_management" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#a5d6ff]" viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 4.75a.75.75 0 01.75.75v2.5h2.5a.75.75 0 010 1.5h-2.5v2.5a.75.75 0 01-1.5 0v-2.5h-2.5a.75.75 0 010-1.5h2.5v-2.5A.75.75 0 018 4.75z"></path></svg>
                                <span>Brand Management</span>
                            </span>
                            <span class="bg-[#1f6feb]/15 text-[#58a6ff] border border-[#1f6feb]/30 text-[9px] font-semibold px-1.5 py-0.5 rounded">CRUD</span>
                        </a>
                        @endif

                        <a href="/?open_modal=cs_team" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#79c0ff]" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 100-6 3 3 0 000 6zm-5.784 6A2.238 2.238 0 015 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 005 9c-4 0-5 3-5 4 0 1 1 1 1 1h5.216zM4.5 8a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"></path></svg>
                                <span>Kelola Tim & Admin CS</span>
                            </span>
                        </a>

                        <a href="/?open_modal=wa_device" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#3fb950]" viewBox="0 0 16 16"><path d="M4 1.75C4 .784 4.784 0 5.75 0h4.5C11.216 0 12 .784 12 1.75v12.5A1.75 1.75 0 0110.25 16h-4.5A1.75 1.75 0 014 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h4.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25h-4.5zM8 13a1 1 0 100-2 1 1 0 000 2z"></path></svg>
                                <span>WA Devices & QR</span>
                            </span>
                            <span class="w-2 h-2 rounded-full bg-[#238636]"></span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>
        </div>

        <!-- GITHUB-STYLE USER PROFILE & LOGOUT FOOTER -->
        <div class="p-3 border-t border-[#30363d] bg-[#090d13]">
            <div class="p-2.5 bg-[#161b22] border border-[#30363d] rounded-lg mb-2 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-[#21262d] border border-[#30363d] text-[#f0f6fc] flex items-center justify-center font-bold text-xs shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#238636] border-2 border-[#161b22] rounded-full" title="Online"></span>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-[#f0f6fc] truncate leading-tight">{{ $user->name }}</p>
                        <p class="text-[10px] text-[#8b949e] truncate font-mono mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between px-1 mb-2.5">
                <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded {{ $user->isCeo() ? 'bg-indigo-950/80 text-indigo-400 border border-indigo-800/50' : ($user->isSupervisor() ? 'bg-amber-950/80 text-amber-400 border border-amber-800/50' : 'bg-emerald-950/80 text-emerald-400 border border-emerald-800/50') }} uppercase tracking-wider">
                    {{ $user->isCeo() ? 'Executive CEO' : ($user->isSupervisor() ? 'Supervisor' : 'Sales Admin') }}
                </span>
                <span class="text-[10px] text-[#8b949e] font-mono">#{{ $user->id }}</span>
            </div>

            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full py-1.5 bg-[#21262d] hover:bg-[#30363d] hover:text-[#f85149] text-[#c9d1d9] font-medium text-xs rounded-md transition border border-[#30363d] flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16"><path d="M2 2.75C2 1.784 2.784 1 3.75 1h2.5a.75.75 0 010 1.5h-2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h2.5a.75.75 0 010 1.5h-2.5A1.75 1.75 0 012 13.25V2.75zm10.44 4.5H6.75a.75.75 0 000 1.5h5.69l-1.97 1.97a.75.75 0 101.06 1.06l3.25-3.25a.75.75 0 000-1.06l-3.25-3.25a.75.75 0 10-1.06 1.06l1.97 1.97z"></path></svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto bg-slate-50 flex flex-col" id="main-scroll-area">
        
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <a href="/" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 md:hidden" title="Dashboard Utama">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 16 16"><path d="M7.78 12.53a.75.75 0 01-1.06 0L2.47 8.28a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 1.06L4.81 7.5h8.44a.75.75 0 010 1.5H4.81l2.97 2.97a.75.75 0 010 1.06z"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>🤖</span>
                            <span>Analisa AI vs Real CS</span>
                        </h1>
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase">Dual Analytics</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Membandingkan stage aktual inputan CS dengan analisa AI percakapan WhatsApp.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Scan Ulang AI Button -->
                <form method="POST" action="{{ route('ai-comparison.scan-all') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <span>⚡</span>
                        <span class="hidden sm:inline">Scan Chat Real</span>
                    </button>
                </form>

                <!-- Simpan Snapshot Button -->
                <form method="POST" action="{{ route('ai-comparison.snapshot') }}" onsubmit="return confirm('Simpan snapshot data perbandingan saat ini ke arsip database mingguan?')">
                    @csrf
                    <input type="hidden" name="account_id" value="{{ $accountId }}">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <span>📸</span>
                        <span class="hidden sm:inline">Simpan Snapshot</span>
                    </button>
                </form>

                <a href="/" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <span>📊</span>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            </div>
        </header>

        <!-- Content Body -->
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-5 space-y-5">

            @if(session('success'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
            </div>
            @endif

            <!-- Minimalist Unified Filter Toolbar -->
            <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('ai-comparison.index') }}" class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Brand Selector -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-medium">Brand:</span>
                            <select name="account_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-800 font-semibold rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-slate-400 focus:outline-none">
                                @if($user->isCeo())
                                <option value="all" {{ $accountId == 'all' ? 'selected' : '' }}>🏢 Semua Brand</option>
                                @endif
                                @foreach($waAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                                    📱 {{ $acc->name }} ({{ $acc->phone ?? 'No Phone' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Period Quick Preset -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-medium">Periode:</span>
                            <select name="period" id="periodSelect" onchange="handlePeriodChange(this)" class="bg-slate-50 border border-slate-200 text-slate-800 font-semibold rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-slate-400 focus:outline-none">
                                <option value="all_time" {{ $period == 'all_time' ? 'selected' : '' }}>📅 Semua Waktu</option>
                                <option value="today" {{ $period == 'today' ? 'selected' : '' }}>🕒 Hari Ini</option>
                                <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>📆 Minggu Ini</option>
                                <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>🗓️ Bulan Ini</option>
                                <option value="custom" {{ $period == 'custom' || $startDate || $endDate ? 'selected' : '' }}>⏳ Kustom Tanggal</option>
                            </select>
                        </div>

                        <!-- Date Range (Dari & Sampai) -->
                        <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                            <span class="text-slate-400">Dari:</span>
                            <input type="date" name="start_date" id="startDateInput" value="{{ $startDate }}" class="bg-white border border-slate-200 text-slate-800 text-[11px] font-semibold rounded px-2 py-1 focus:outline-none" onchange="document.getElementById('periodSelect').value = 'custom'">
                            
                            <span class="text-slate-400">Sampai:</span>
                            <input type="date" name="end_date" id="endDateInput" value="{{ $endDate }}" class="bg-white border border-slate-200 text-slate-800 text-[11px] font-semibold rounded px-2 py-1 focus:outline-none" onchange="document.getElementById('periodSelect').value = 'custom'">
                        </div>

                        <!-- Submit & Reset Action -->
                        <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg transition shadow-sm flex items-center gap-1">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/></svg>
                            <span>Filter</span>
                        </button>

                        @if($startDate || $endDate || $period !== 'all_time' || $accountId !== 'all')
                        <a href="{{ route('ai-comparison.index') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-lg transition" title="Reset Filter">
                            Reset
                        </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-between xl:justify-end gap-2 text-[11px] text-slate-400">
                        @if($startDate || $endDate)
                        <span class="text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded font-bold">
                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Awal' }} — {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Sekarang' }}
                        </span>
                        @endif
                        <span>Update: <strong class="text-slate-600 font-mono">{{ $comparison['generated_at'] }}</strong></span>
                    </div>
                </form>
            </div>

            <!-- Minimalist KPI Summary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- Total Leads -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Total Leads</span>
                        <span class="text-sm">👥</span>
                    </div>
                    <div class="flex items-baseline justify-between mt-1">
                        <span class="text-2xl font-black text-slate-900">{{ $comparison['total_leads'] }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">Sync Data</span>
                    </div>
                </div>

                <!-- Match Rate -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Keselarasan</span>
                        <span class="text-sm">🎯</span>
                    </div>
                    <div class="flex items-baseline justify-between mt-1">
                        <span class="text-2xl font-black text-emerald-600">{{ $comparison['match_rate'] }}%</span>
                        <span class="text-[10px] text-emerald-700 font-semibold">{{ $comparison['match_count'] }} Match</span>
                    </div>
                </div>

                <!-- Discrepancies Count -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Perlu Ditinjau</span>
                        <span class="text-sm">⚠️</span>
                    </div>
                    <div class="flex items-baseline justify-between mt-1">
                        <span class="text-2xl font-black {{ $comparison['discrepancy_count'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                            {{ $comparison['discrepancy_count'] }}
                        </span>
                        <span class="text-[10px] text-amber-700 font-semibold">{{ $comparison['discrepancy_rate'] }}% Selisih</span>
                    </div>
                </div>

                <!-- Deal Summary -->
                @php
                    $realDeals = $comparison['real_counts']['Deal'] ?? 0;
                    $aiDeals = $comparison['ai_counts']['Deal'] ?? 0;
                @endphp
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider">Perbandingan Deal</span>
                        <span class="text-sm">🏆</span>
                    </div>
                    <div class="flex items-baseline justify-between mt-1">
                        <div>
                            <span class="text-xl font-black text-slate-800">{{ $realDeals }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">CS</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-blue-600">{{ $aiDeals }}</span>
                            <span class="text-[10px] text-blue-500 font-medium">AI</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Stage Matrix Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <!-- Bar Comparison Chart -->
                <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Visualisasi Sebaran: Real vs AI</h2>
                        <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded">Bar Chart</span>
                    </div>
                    <div class="h-60 w-full relative">
                        <canvas id="stageComparisonChart"></canvas>
                    </div>
                </div>

                <!-- Stage Matrix Table -->
                <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-1">Matriks Per Stage</h2>
                        <p class="text-[11px] text-slate-400 mb-3">Rincian angka real inputan CS versus kesimpulan AI.</p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                                        <th class="py-2 px-1.5">Stage</th>
                                        <th class="py-2 px-1.5 text-center">Real</th>
                                        <th class="py-2 px-1.5 text-center">AI</th>
                                        <th class="py-2 px-1.5 text-right">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($comparison['stage_comparison'] as $stName => $row)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2 px-1.5 font-semibold text-slate-700">{{ $stName }}</td>
                                        <td class="py-2 px-1.5 text-center font-bold text-slate-600">{{ $row['real_count'] }}</td>
                                        <td class="py-2 px-1.5 text-center font-bold text-blue-600">{{ $row['ai_count'] }}</td>
                                        <td class="py-2 px-1.5 text-right font-bold text-[11px]">
                                            @if($row['difference'] > 0)
                                                <span class="text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">+{{ $row['difference'] }} AI</span>
                                            @elseif($row['difference'] < 0)
                                                <span class="text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">{{ $row['difference'] }} CS</span>
                                            @else
                                                <span class="text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Match</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3 p-2.5 bg-amber-50/60 border border-amber-200/60 rounded-lg text-[10px] text-amber-800 leading-relaxed">
                        💡 <strong>Indikasi:</strong> Jika nilai <em>AI lebih tinggi</em> dari Real, kemungkinan CS belum memperbarui stage saat percakapan sudah mencapai kesepakatan/meeting.
                    </div>
                </div>
            </div>

            <!-- Discrepant Leads Detail Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Daftar Leads yang Memiliki Perbedaan Stage (Discrepancies)</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Leads di mana kesimpulan analisa AI berbeda dengan stage saat ini yang tercatat oleh CS.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs rounded-lg w-max">
                        {{ count($comparison['discrepant_leads']) }} Leads Perlu Ditinjau
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-200">
                                <th class="py-3 px-4">Nama & Nomor</th>
                                <th class="py-3 px-4">Brand WA</th>
                                <th class="py-3 px-4">Suhu Prospek</th>
                                <th class="py-3 px-4">Stage CS (Real)</th>
                                <th class="py-3 px-4">Kesimpulan AI</th>
                                <th class="py-3 px-4">Alasan & Indikasi AI</th>
                                <th class="py-3 px-4">Waktu Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($comparison['discrepant_leads'] as $lead)
                            @php $temp = $lead['temperature'] ?? ['key' => 'cold', 'label' => 'Cold Lead', 'icon' => '❄️', 'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200']; @endphp
                            <tr class="hover:bg-amber-50/20 transition">
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <div>{{ $lead['name'] }}</div>
                                    <div class="text-[11px] font-normal text-slate-400 font-mono">{{ $lead['phone'] }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        {{ $lead['account_name'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $temp['badge_class'] }}">
                                        <span>{{ $temp['icon'] }}</span>
                                        <span>{{ $temp['label'] }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-100 text-slate-700 font-bold px-2 py-1 rounded text-[11px] border border-slate-200">
                                        {{ $lead['real_stage'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="text-xs font-bold text-slate-800 flex items-center gap-1 flex-wrap">
                                            <span>🤖</span>
                                            <span>Leads ini sudah sampai di <span class="bg-blue-50 text-blue-800 border border-blue-200 font-extrabold px-1.5 py-0.5 rounded text-[11px] inline-block">[{{ $lead['ai_stage'] }}]</span></span>
                                        </div>
                                        @if(!empty($lead['ai_keyword']))
                                        <div class="text-[10px] text-amber-700 font-mono flex items-center gap-1">
                                            <span>💡 Saran Trigger CS:</span>
                                            <span class="bg-amber-50 text-amber-900 font-bold px-1 py-0.5 rounded border border-amber-200">{{ $lead['ai_keyword'] }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs">
                                    <p class="text-[11px] leading-relaxed italic text-slate-500">"{{ $lead['ai_reason'] }}"</p>
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                    {{ $lead['ai_suggested_at'] !== '-' ? $lead['ai_suggested_at'] : $lead['created_at'] }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 text-xs italic">
                                    🎉 Tidak ada perbedaan stage! Seluruh pencatatan CS selaras 100% dengan analisis AI.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Historical Snapshots Archive Section -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Arsip Snapshot Mingguan</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Catatan perbandingan otomatis per akhir minggu.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">Total: {{ $snapshots->count() }}</span>
                </div>

                @if($snapshots->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($snapshots as $snap)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-100/80 transition flex flex-col justify-between gap-2.5">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Snapshot Tanggal</div>
                                <div class="text-xs font-extrabold text-slate-800">{{ $snap->report_date->format('d M Y') }}</div>
                            </div>
                            <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ $snap->differences['match_rate'] ?? 0 }}% Match
                            </span>
                        </div>

                        <div class="text-[11px] text-slate-600 space-y-0.5 bg-white p-2 rounded border border-slate-200/60">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Total Leads:</span>
                                <strong class="text-slate-700">{{ $snap->differences['total_leads'] ?? 0 }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Selisih:</span>
                                <strong class="text-amber-700">{{ $snap->differences['discrepancy_count'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 text-slate-400 text-xs italic bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    Belum ada snapshot mingguan yang disimpan. Klik tombol "📸 Simpan Snapshot" di atas untuk membuat snapshot.
                </div>
                @endif
            </div>

        </div>

    </main>

    <!-- JS Helper for Period Change -->
    <script>
        function handlePeriodChange(select) {
            if (select.value !== 'custom') {
                document.getElementById('startDateInput').value = '';
                document.getElementById('endDateInput').value = '';
                select.form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('stageComparisonChart').getContext('2d');
            
            const stages = {!! json_encode(array_keys($comparison['stage_comparison'])) !!};
            const realData = {!! json_encode(array_values(array_map(fn($item) => $item['real_count'], $comparison['stage_comparison']))) !!};
            const aiData = {!! json_encode(array_values(array_map(fn($item) => $item['ai_count'], $comparison['stage_comparison']))) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: stages,
                    datasets: [
                        {
                            label: 'Real (CS)',
                            data: realData,
                            backgroundColor: 'rgba(71, 85, 105, 0.85)',
                            borderColor: 'rgba(71, 85, 105, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Analisa AI (Gemini)',
                            data: aiData,
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { size: 10 }
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 10 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
