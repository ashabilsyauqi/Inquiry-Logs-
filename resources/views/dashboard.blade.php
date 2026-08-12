<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Admin Panel - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .draggable-card { cursor: grab; }
        .draggable-card:active { cursor: grabbing; opacity: 0.8; }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">

    <!-- LEFT SIDEBAR NAVIGATION (GITHUB ENTERPRISE DARK THEME) -->
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
                        <a href="/?account_id=all" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md {{ $accountId == 'all' ? 'bg-[#161b22] text-[#f0f6fc] font-semibold border-l-2 border-[#2f81f7]' : 'text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc]' }} transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#2f81f7]" viewBox="0 0 16 16"><path d="M1.75 2.5h12.5a.25.25 0 01.25.25v10.5a.25.25 0 01-.25.25H1.75a.25.25 0 01-.25-.25V2.75c0-.138.112-.25.25-.25zM1.75 1A1.75 1.75 0 000 2.75v10.5C0 14.216.784 15 1.75 15h12.5A1.75 1.75 0 0016 13.25V2.75A1.75 1.75 0 0014.25 1H1.75zM4 4.75a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 4.75zm0 3a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 7.75zm0 3a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z"></path></svg>
                                <span>Running Brands</span>
                            </span>
                            <span class="text-[11px] bg-[#21262d] text-[#8b949e] px-2 py-0.5 rounded-full font-mono border border-[#30363d]">{{ $waAccounts->count() }}</span>
                        </a>
                        @endif

                        @if(!$user->isCeo() || $accountId != 'all')
                        <button onclick="switchTab('all')" id="nav-all" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md bg-[#161b22] text-[#f0f6fc] font-semibold border-l-2 border-[#2f81f7] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#58a6ff]" viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"></path></svg>
                            <span>Overview All</span>
                        </button>
                        <button onclick="switchTab('analytics')" id="nav-analytics" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#bc8cff]" viewBox="0 0 16 16"><path d="M1.75 1.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h11.5a.75.75 0 000-1.5H2.5V2.25a.75.75 0 00-.75-.75zm3 4a.75.75 0 00-.75.75v5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-5a.75.75 0 00-.75-.75h-1.5zm4-2a.75.75 0 00-.75.75v7c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-7a.75.75 0 00-.75-.75h-1.5zm4 4a.75.75 0 00-.75.75v3c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75v-3a.75.75 0 00-.75-.75h-1.5z"></path></svg>
                            <span>Analytics & Insights</span>
                        </button>
                        <button onclick="switchTab('kanban')" id="nav-kanban" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#3fb950]" viewBox="0 0 16 16"><path d="M0 2.75C0 1.784.784 1 1.75 1h12.5C15.216 1 16 1.784 16 2.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75zm1.75-.25a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h3.5v-11h-3.5zm5 0v11h3.5v-11h-3.5zm5 0v11h2.75a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25h-2.75z"></path></svg>
                            <span>Kanban Pipeline</span>
                        </button>
                        <button onclick="switchTab('table')" id="nav-table" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <svg class="w-4 h-4 fill-current text-[#d29922]" viewBox="0 0 16 16"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25V5h13V1.75a.25.25 0 00-.25-.25H1.75zm13 4.75h-13v8c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25v-8z"></path></svg>
                            <span>Lead Master List</span>
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Section 2: Management Suite -->
                <div>
                    <p class="px-2 text-[10px] font-semibold uppercase tracking-wider text-[#8b949e] mb-2">Organization Admin</p>
                    <div class="space-y-1">
                        @if($user->isCeo())
                        <button onclick="openBrandManagementModal()" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#a5d6ff]" viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 4.75a.75.75 0 01.75.75v2.5h2.5a.75.75 0 010 1.5h-2.5v2.5a.75.75 0 01-1.5 0v-2.5h-2.5a.75.75 0 010-1.5h2.5v-2.5A.75.75 0 018 4.75z"></path></svg>
                                <span>Brand Management</span>
                            </span>
                            <span class="bg-[#1f6feb]/15 text-[#58a6ff] border border-[#1f6feb]/30 text-[9px] font-semibold px-1.5 py-0.5 rounded">CRUD</span>
                        </button>

                        <button onclick="openBrandApprovalModal()" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#d2a8ff]" viewBox="0 0 16 16"><path d="M5.5 5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm5 6c0-1.5-3-2.25-5-2.25S.5 9.5.5 11V12h10v-1zM11.5 5a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5zm2.25 6c0-.85-.92-1.63-2.25-2.02.58.45 1 1.05 1 1.77V12h3.25v-1z"></path></svg>
                                <span>Brand Approvals</span>
                            </span>
                            <span id="pendingBrandBadge" class="bg-[#da3633] text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-full hidden">0</span>
                        </button>
                        @endif

                        @if(!$user->isCeo() || $accountId != 'all')
                        <button onclick="openCsTeamModal()" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#79c0ff]" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 100-6 3 3 0 000 6zm-5.784 6A2.238 2.238 0 015 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 005 9c-4 0-5 3-5 4 0 1 1 1 1 1h5.216zM4.5 8a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"></path></svg>
                                <span>Kelola Tim & Admin CS</span>
                            </span>
                            <span id="csTeamBadge" class="bg-[#1f6feb]/20 text-[#58a6ff] text-[10px] font-bold px-1.5 py-0.5 rounded-full font-mono">0 CS</span>
                        </button>
                        @endif

                        <button onclick="openDeviceModal()" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 fill-current text-[#3fb950]" viewBox="0 0 16 16"><path d="M4 1.75C4 .784 4.784 0 5.75 0h4.5C11.216 0 12 .784 12 1.75v12.5A1.75 1.75 0 0110.25 16h-4.5A1.75 1.75 0 014 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h4.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25h-4.5zM8 13a1 1 0 100-2 1 1 0 000 2z"></path></svg>
                                <span>WA Devices & QR</span>
                            </span>
                            <span class="w-2 h-2 rounded-full bg-[#238636]"></span>
                        </button>
                    </div>
                </div>
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
                <span class="text-[10px] font-semibold text-[#58a6ff] bg-[#1f6feb]/15 border border-[#1f6feb]/30 px-2 py-0.5 rounded-full uppercase tracking-wider">
                    {{ $user->isCeo() ? 'Executive CEO' : ($user->role == 'supervisor' ? 'Supervisor' : 'Sales Admin') }}
                </span>
                <span class="text-[10px] text-[#8b949e] font-mono">#{{ $user->id }}</span>
            </div>

            <div class="flex items-center gap-2">
                @if($user->isCeo())
                <button onclick="openSmtpSettingsModal()" class="p-2 bg-[#21262d] hover:bg-[#30363d] text-[#8b949e] hover:text-[#58a6ff] rounded-md transition border border-[#30363d] flex items-center justify-center shadow-sm" title="Pengaturan Server SMTP Email & Sistem">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16"><path d="M8 0a8.2 8.2 0 00-1.7.2c-.3.1-.5.3-.5.6l-.3 1.2c-.4.2-.9.4-1.3.7l-1.1-.6c-.3-.1-.6 0-.8.2l-1.2 2c-.2.3-.1.6.1.8l1 .8c0 .2-.1.5-.1.8s0 .5.1.8l-1 .8c-.2.2-.3.5-.1.8l1.2 2c.2.2.5.3.8.2l1.1-.6c.4.3.8.5 1.3.7l.3 1.2c0 .3.2.5.5.6A8.2 8.2 0 008 16a8.2 8.2 0 001.7-.2c.3-.1.5-.3.5-.6l.3-1.2c.4-.2.9-.4 1.3-.7l1.1.6c.3.1.6 0 .8-.2l1.2-2c.2-.3.1-.6-.1-.8l-1-.8c0-.2.1-.5.1-.8s0-.5-.1-.8l1-.8c.2-.2.3-.5.1-.8l-1.2-2c-.2-.2-.5-.3-.8-.2l-1.1.6c-.4-.3-.8-.5-1.3-.7l-.3-1.2c0-.3-.2-.5-.5-.6A8.2 8.2 0 008 0zm0 5a3 3 0 110 6 3 3 0 010-6z"></path></svg>
                </button>
                @endif
                <form method="POST" action="/logout" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-1.5 bg-[#21262d] hover:bg-[#30363d] hover:text-[#f85149] text-[#c9d1d9] font-medium text-xs rounded-md transition border border-[#30363d] flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16"><path d="M2 2.75C2 1.784 2.784 1 3.75 1h2.5a.75.75 0 010 1.5h-2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h2.5a.75.75 0 010 1.5h-2.5A1.75 1.75 0 012 13.25V2.75zm10.44 4.5H6.75a.75.75 0 000 1.5h5.69l-1.97 1.97a.75.75 0 101.06 1.06l3.25-3.25a.75.75 0 000-1.06l-3.25-3.25a.75.75 0 10-1.06 1.06l1.97 1.97z"></path></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto bg-slate-50" id="main-scroll-area">
        
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">
                            {{ $user->isCeo() ? ($accountId == 'all' ? 'Executive Brand Portfolio' : 'Dashboard Brand: ' . ($activeAccount->name ?? '')) : 'Dashboard Brand: ' . ($user->waAccount->name ?? 'Default Account') }}
                        </h2>
                        @if($user->isCeo() && $accountId == 'all')
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase">CEO View</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $user->isCeo() ? ($accountId == 'all' ? ' Ringkasan real-time performa seluruh running brands & pipeline WA.' : 'Analisis & Pipeline Khusus Brand ' . ($activeAccount->name ?? '')) : 'Pipeline Sales Khusus: ' . ($user->waAccount->name ?? 'Default Account') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                @if($activeAccount)
                <button onclick="openBrandSettingsModal({{ $activeAccount->id }})" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl transition flex items-center gap-2">
                    ⚙️ Stage & Trigger
                </button>
                @endif

                <button onclick="openDeviceModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    📱 WA Device Manager
                </button>
                <form method="POST" action="/logout" class="md:hidden">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">Logout</button>
                </form>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6" id="dashboard-content">

            <!-- Disconnection Alert Banner -->
            @php
                $disconnectedAccounts = $waAccounts->where('status', '!=', 'CONNECTED');
            @endphp

            @if($disconnectedAccounts->isNotEmpty())
            <div class="bg-amber-50 border border-amber-300 p-4 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-xl text-amber-800 font-bold text-sm">
                        ⚠️
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">Perhatian: {{ $disconnectedAccounts->count() }} Perangkat WA Terputus!</h4>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Akun terputus: <strong>{{ $disconnectedAccounts->pluck('name')->join(', ') }}</strong>. Email peringatan darurat otomatis dikirim ke Supervisor Brand & CC ke CEO (ashabil@difitech.id).
                        </p>
                    </div>
                </div>
                <button onclick="openDeviceModal();" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition whitespace-nowrap shadow-sm">
                    📲 Scan Ulang Barcode
                </button>
            </div>
            @endif

            <!-- 1. CEO EXECUTIVE LANDING PAGE (MINI WIDGETS & EXECUTIVE SUMMARY) -->
            @if($user->isCeo() && $accountId == 'all')
            <section class="space-y-6">
                <!-- Corporate Executive KPI Summary Strip -->
                @php
                    $allLeads = \App\Models\Lead::all();
                    $totalLeadsCount = $allLeads->count();
                    $todayLeadsCount = $allLeads->where('created_at', '>=', \Carbon\Carbon::today())->count();
                    $dealsTotalCount = $allLeads->where('stage', 'Deal')->count();
                    $connectedCount = $waAccounts->where('status', 'CONNECTED')->count();
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div class="flex justify-between items-center text-slate-500 mb-1">
                            <span class="text-xs font-bold uppercase tracking-wider">Running Brands</span>
                            <span class="text-base">🏢</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900">{{ $waAccounts->count() }}</span>
                            <span class="text-xs text-emerald-600 font-bold">{{ $connectedCount }} Online</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div class="flex justify-between items-center text-slate-500 mb-1">
                            <span class="text-xs font-bold uppercase tracking-wider">Total Leads</span>
                            <span class="text-base">👥</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900">{{ $totalLeadsCount }}</span>
                            <span class="text-xs text-slate-400 font-medium">Akumulasi</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div class="flex justify-between items-center text-slate-500 mb-1">
                            <span class="text-xs font-bold uppercase tracking-wider">Inbound Hari Ini</span>
                            <span class="text-base">📥</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-blue-600">{{ $todayLeadsCount }}</span>
                            <span class="text-xs text-blue-500 font-medium">Leads Baru</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div class="flex justify-between items-center text-slate-500 mb-1">
                            <span class="text-xs font-bold uppercase tracking-wider">Closing Deal</span>
                            <span class="text-base">🎯</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-emerald-600">{{ $dealsTotalCount }}</span>
                            <span class="text-xs text-emerald-500 font-bold">
                                {{ $totalLeadsCount > 0 ? round(($dealsTotalCount / $totalLeadsCount) * 100, 1) : 0 }}% Conv.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Running Brands Cards Section -->
                <div class="flex justify-between items-center border-t border-slate-200 pt-6">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            🏢 Portfolio Overview Per Brand
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih brand untuk masuk ke kontrol pipeline & kanban khusus.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openBrandManagementModal()" class="px-3.5 py-1.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition">
                            + kelola Brand
                        </button>
                    </div>
                </div>

                <!-- SLEEK MINI WIDGETS GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="brandCardsContainer">
                    @foreach($waAccounts as $acc)
                    @php
                        $accLeads = $acc->leads;
                        $totalAccLeads = $accLeads->count();
                        $todayLeads = $accLeads->where('created_at', '>=', \Carbon\Carbon::today())->count();
                        $dealsCount = $accLeads->where('stage', 'Deal')->count();
                    @endphp
                    <div draggable="true" ondragstart="dragCard(event)" ondragover="allowDropCard(event)" ondrop="dropCard(event)" class="draggable-card bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition flex flex-col justify-between">
                        <div>
                            <!-- Header Mini Widget -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">
                                        📱
                                    </div>
                                    <div class="truncate">
                                        <h4 class="font-bold text-slate-900 text-sm truncate leading-tight">{{ $acc->name }}</h4>
                                        <p class="text-[10px] text-slate-400 font-mono truncate">{{ $acc->phone ?: 'Disconnected' }}</p>
                                    </div>
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full {{ $acc->status == 'CONNECTED' ? 'bg-emerald-500' : 'bg-amber-400' }} flex-shrink-0" title="{{ $acc->status == 'CONNECTED' ? 'Online' : 'Terputus' }}"></span>
                            </div>

                            <!-- Mini Stats Bar -->
                            <div class="grid grid-cols-4 gap-1 bg-slate-50 p-2 rounded-xl border border-slate-100 text-center mb-3">
                                <div>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">Total</span>
                                    <p class="text-xs font-bold text-slate-800">{{ $totalAccLeads }}</p>
                                </div>
                                <div>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">Hari Ini</span>
                                    <p class="text-xs font-bold text-blue-600">{{ $todayLeads }}</p>
                                </div>
                                <div>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">Deal</span>
                                    <p class="text-xs font-bold text-emerald-600">{{ $dealsCount }}</p>
                                </div>
                                <div>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">Tim CS</span>
                                    <p class="text-xs font-bold text-purple-600">{{ $acc->csTeam->count() }} CS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sleek Link Text "Lihat Detail →" -->
                        <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                            <button onclick="openBrandSettingsModal({{ $acc->id }})" class="text-slate-400 hover:text-slate-600 text-xs">
                                ⚙️
                            </button>
                            <a href="/?filter={{ $filter }}&account_id={{ $acc->id }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-1">
                                Lihat Detail &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @if($waAccounts->isEmpty())
                    <div class="col-span-1 sm:col-span-2 lg:col-span-4 bg-gradient-to-b from-white to-slate-50/80 p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/40 text-center relative overflow-hidden group">
                        <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                        <div class="max-w-md mx-auto space-y-4 relative z-10">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center text-3xl shadow-lg shadow-emerald-500/30">
                                🎨
                            </div>
                            <div>
                                <h4 class="text-lg font-extrabold text-slate-900 tracking-tight">Belum Ada Running Brand / Pipeline CS</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Mulailah membuat Brand pertama Anda untuk memantau aktivitas sales pipeline dan WhatsApp lead secara real-time.
                                </p>
                            </div>
                            <div class="flex flex-wrap justify-center gap-3 pt-2">
                                <button onclick="openBrandManagementModal()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 hover:scale-105 transition-all">
                                    + Tambah Brand Pertama
                                </button>
                                <button onclick="openDeviceModal()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md hover:scale-105 transition-all">
                                    📱 WA Device Manager
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- 2. BRAND DASHBOARD VIEW (STAT CARDS, ADJUSTABLE PERIOD, TREND CHART, KANBAN, DATA TABLE) -->
            @if(!$user->isCeo() || $accountId != 'all')
            
            @if($user->isCeo() && $accountId != 'all')
            <div class="mb-3">
                <a href="/?filter={{ $filter }}&account_id=all" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs rounded-xl font-bold bg-slate-900 text-white hover:bg-slate-800 shadow transition">
                    ⬅️ Kembali ke Portfolio Brands CEO
                </a>
            </div>
            @endif

            <!-- Active Pipeline Banner -->
            @if($activeAccount)
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold">Pipeline Sales: {{ $activeAccount->name }}</h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $activeAccount->status == 'CONNECTED' ? 'bg-emerald-400 text-emerald-950' : 'bg-yellow-300 text-yellow-950' }}">
                            {{ $activeAccount->status == 'CONNECTED' ? '🟢 Online (' . ($activeAccount->phone ?: 'Connected') . ')' : '🟡 Belum Scan QR' }}
                        </span>
                    </div>
                    <p class="text-xs text-emerald-100 mt-1">Daftar Leads & Stat Cards dikhususkan untuk saluran ini saja.</p>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="openBrandSettingsModal({{ $activeAccount->id }})" class="px-4 py-2 bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold rounded-xl shadow transition whitespace-nowrap">
                        ⚙️ Kelola Stage & Keyword Trigger
                    </button>
                    @if($user->isCeo())
                    <button onclick="startScanQr('{{ $activeAccount->session_id }}'); openDeviceModal();" class="px-4 py-2 bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-bold rounded-xl shadow transition whitespace-nowrap">
                        📲 Scan / Sambungkan WA
                    </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- SECTION 1: STAT CARDS & INTERACTIVE TREND CHART -->
            <section id="section-analytics" class="space-y-6">
                <!-- Dynamic Custom Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Total Leads</p>
                            <h3 class="text-3xl font-bold text-slate-900 mt-1">{{ $leads->count() }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 text-lg font-bold">#</div>
                    </div>

                    @foreach($stages as $stage)
                    @php
                        $stageCount = $leads->where('stage', $stage->name)->count();
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex items-center justify-between border-l-4 border-l-emerald-500">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider truncate">{{ $stage->name }}</p>
                                @if(!empty($stage->is_default))
                                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">Entry</span>
                                @endif
                            </div>
                            <h3 class="text-3xl font-bold text-emerald-600 mt-1">{{ $stageCount }}</h3>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Interactive Trend Chart Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                📈 Grafik Tren Inquiry Masuk
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Analisis perkembangan prospek masuk secara real-time</p>
                        </div>
                        
                        <!-- Adjustable Chart Period Buttons -->
                        <div class="flex gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                            <button onclick="updateChartPeriod('daily')" id="btn-daily" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white shadow-sm transition">
                                📅 Harian (Daily)
                            </button>
                            <button onclick="updateChartPeriod('weekly')" id="btn-weekly" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition">
                                🗓️ Mingguan (Weekly)
                            </button>
                            <button onclick="updateChartPeriod('monthly')" id="btn-monthly" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition">
                                📊 Bulanan (Monthly)
                            </button>
                        </div>
                    </div>

                    <div class="h-72 relative">
                        <canvas id="inquiryChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: DAFTAR LEADS TABLE VIEW -->
            <section id="section-table" class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            📋 Daftar Leads {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
                        </h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Total: {{ $leads->count() }} Data Lead</p>
                    </div>

                    <!-- Filter Periode Leads (Semua, Hari Ini, Bulan Ini, Tahun Ini) -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 hidden sm:inline">Filter Periode:</span>
                        <div class="flex gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                            <a href="/?filter=all&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'all' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Semua</a>
                            <a href="/?filter=daily&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'daily' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Hari Ini</a>
                            <a href="/?filter=monthly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'monthly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Bulan Ini</a>
                            <a href="/?filter=yearly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'yearly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Tahun Ini</a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="px-6 py-3 font-semibold">Nama Lead</th>
                                <th class="px-6 py-3 font-semibold">No. WhatsApp</th>
                                <th class="px-6 py-3 font-semibold">Akun WA CS</th>
                                <th class="px-6 py-3 font-semibold">Stage Status</th>
                                <th class="px-6 py-3 font-semibold">Waktu Dibuat</th>
                                <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($leads as $lead)
                            <tr class="hover:bg-slate-50/80 cursor-pointer transition" onclick="openLeadDetailModal({{ $lead->id }})">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 flex items-center gap-2">
                                        {{ $lead->name }}
                                        @if($lead->priority > 0)
                                            <div class="text-yellow-400 text-xs flex">
                                                @for($i = 0; $i < $lead->priority; $i++)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                    @if($lead->notes)
                                        <div class="text-xs text-slate-500 mt-0.5 italic">{{ Str::limit($lead->notes, 35) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $lead->phone }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-semibold border border-slate-200">
                                        {{ $lead->waAccount->name ?? 'Default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        {{ $lead->stage }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $lead->created_at->format('d M, H:i') }}</td>
                                <td class="px-6 py-4 text-center" onclick="event.stopPropagation()">
                                    <button onclick="openLeadDetailModal({{ $lead->id }})" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition border border-blue-200">
                                        ⚙️ Detail Lead
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @if($leads->isEmpty())
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data lead di pipeline ini.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
            
            <!-- SECTION 3: INTERACTIVE & DRAGGABLE KANBAN BOARD WITH INLINE DOUBLE-CLICK TITLE EDIT & DIRECT ENTRY SELECTOR -->
            <section id="section-kanban" class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Kanban Board {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
                        </h2>
                        <p class="text-xs text-slate-500">💡 <strong>Double-click judul stage</strong> untuk ganti nama langsung. Klik tombol <strong>⭐ Set Entry</strong> untuk pintu masuk WA.</p>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6 overflow-x-auto pb-4" id="kanbanColumnsContainer">
                    @foreach($stages as $stage)
                    @php
                        $stageLeads = $leads->where('stage', $stage->name);
                    @endphp
                    <div draggable="true" ondragstart="dragCard(event)" ondragover="allowDropCard(event)" ondrop="dropCard(event)" class="draggable-card flex-1 min-w-[260px] bg-white rounded-2xl shadow-sm p-4 border-t-4 border-emerald-500 border-x border-b border-slate-200/80">
                        <!-- Kanban Column Header with Inline Editing & Entry Selector -->
                        <div class="border-b border-slate-100 pb-3 mb-4">
                            <div class="flex justify-between items-center">
                                <h2 class="text-base font-bold text-slate-800 flex items-center gap-1.5 truncate">
                                    <!-- Double Clickable Stage Title -->
                                    <span id="stage-title-{{ $stage->id }}" 
                                          ondblclick="inlineEditStageName({{ $stage->id }}, '{{ $stage->name }}')" 
                                          title="Double click untuk ubah nama stage ini" 
                                          class="cursor-pointer hover:text-emerald-700 hover:underline transition">
                                        {{ $stage->name }}
                                    </span>
                                </h2>
                                <span class="bg-emerald-100 text-emerald-800 text-xs py-0.5 px-2.5 rounded-full font-bold flex-shrink-0">{{ $stageLeads->count() }}</span>
                            </div>

                            <!-- Direct Entry Stage Selector Badge -->
                            <div class="mt-2 flex items-center justify-between">
                                @if(!empty($stage->is_default))
                                    <span title="Inquiry WA baru akan otomatis masuk ke stage ini" class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full border border-emerald-300 flex items-center gap-1">
                                        🟢 Entry Stage WA
                                    </span>
                                @else
                                    <button onclick="setAsDefaultStageDirect({{ $stage->id }})" title="Klik untuk jadikan pintu masuk inquiry WA baru" class="text-[10px] font-semibold text-slate-500 hover:text-emerald-700 bg-slate-100 hover:bg-emerald-50 px-2 py-0.5 rounded border border-slate-200 transition">
                                        ⭐ Set Entry Stage
                                    </button>
                                @endif
                                <span class="text-[10px] text-slate-400 italic">Double-click title</span>
                            </div>
                        </div>

                        <!-- Kanban Cards Container -->
                        <div class="space-y-3">
                            @foreach($stageLeads as $lead)
                            <div onclick="openLeadDetailModal({{ $lead->id }})" class="bg-slate-50 hover:bg-emerald-50/60 transition duration-150 p-4 rounded-xl shadow-sm border border-slate-200/80 cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-slate-800 text-sm">{{ $lead->name }}</h3>
                                    @if($lead->priority > 0)
                                        <div class="text-yellow-400 text-xs flex mt-0.5">
                                            @for($i = 0; $i < $lead->priority; $i++)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1 font-sans flex items-center gap-1">
                                    <span>🕒</span> {{ $lead->created_at->format('d M, H:i') }}
                                </p>
                                <div class="mt-2.5 text-[10px] font-bold text-slate-600 bg-slate-200/70 inline-block px-2 py-0.5 rounded">
                                    {{ $lead->waAccount->name ?? 'Default Account' }}
                                </div>
                            </div>
                            @endforeach
                            @if($stageLeads->isEmpty())
                                <p class="text-xs text-slate-400 text-center py-6 italic">Kosong.</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            @endif

        </div>
    </main>

    <!-- SIMPLIFIED CLEAN LEAD DETAIL MODAL (FEATURING NO LIVE CHAT CONTAINER) -->
    <div id="leadDetailModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-slate-900 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-lg text-white">
                        👤
                    </div>
                    <div>
                        <h3 class="text-lg font-bold" id="detailLeadName">Memuat...</h3>
                        <p class="text-xs text-slate-300 font-mono" id="detailLeadPhone">-</p>
                    </div>
                </div>
                <button onclick="closeLeadDetailModal()" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h4 class="font-bold text-slate-800 text-sm">⚙️ Pengaturan Status Lead</h4>
                    <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md border font-medium" id="modalAccountTag">-</span>
                </div>

                <form id="leadForm" method="POST" action="">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lead / Pelanggan</label>
                        <input type="text" name="name" id="modalName" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold">
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tahapan / Stage Pipeline</label>
                        <select name="stage" id="modalStage" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white font-medium">
                            @foreach($stages as $st)
                                <option value="{{ $st->name }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Prioritas Prospek (Bintang)</label>
                        <select name="priority" id="modalPriority" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white font-medium">
                            <option value="0">0 Bintang (Normal)</option>
                            <option value="1">1 Bintang ⭐</option>
                            <option value="2">2 Bintang ⭐⭐</option>
                            <option value="3">3 Bintang ⭐⭐⭐</option>
                            <option value="4">4 Bintang ⭐⭐⭐⭐</option>
                            <option value="5">5 Bintang ⭐⭐⭐⭐⭐ (Hot Lead)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Internal Sales / CS</label>
                        <textarea name="notes" id="modalNotes" rows="4" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none placeholder-slate-400" placeholder="Tambahkan catatan khusus mengenai prospek ini..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow transition">
                        💾 Simpan Perubahan Status
                    </button>
                </form>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button onclick="closeLeadDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- BRAND SETTINGS MODAL (Custom Stages & Keyword Triggers Management) -->
    <div id="brandSettingsModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        ⚙️ Kelola Stage & Keyword Trigger Brand
                    </h3>
                    <p class="text-xs text-purple-200 mt-0.5" id="brandSettingsSubtitle">Kustomisasi alokasi stage & otomasi kata kunci WA</p>
                </div>
                <button onclick="closeBrandSettingsModal()" class="text-purple-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- Section 1: Add New Stage -->
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                    <h4 class="font-bold text-purple-900 text-sm mb-2">➕ Tambah Stage Baru untuk Brand Ini</h4>
                    <div class="flex gap-2">
                        <input type="text" id="newStageName" placeholder="Nama Stage (Misal: Discovery Call / Kirim Invoice)" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none flex-1">
                        <button onclick="addNewStage()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-lg shadow whitespace-nowrap transition">
                            + Tambah Stage
                        </button>
                    </div>
                </div>

                <!-- Section 2: Stages List -->
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-slate-800 text-sm">📋 Daftar Stage Aktif</h4>
                        <span class="text-[11px] text-slate-500 italic">Pilih 1 Stage sebagai <strong>Entry Stage (Pintu Masuk Lead Baru)</strong></span>
                    </div>
                    <div id="stagesListContainer" class="space-y-2">
                        <div class="text-center py-4 text-slate-400 text-xs">Memuat data stage...</div>
                    </div>
                </div>

                <!-- Section 3: Add Keyword Trigger -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <h4 class="font-bold text-emerald-900 text-sm mb-2">⚡ Tambah Otomasi Trigger Kata Kunci WA</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <select id="triggerStageSelect" class="px-3 py-2 text-sm border rounded-lg outline-none bg-white">
                            <option value="">Pilih Target Stage...</option>
                        </select>
                        <input type="text" id="newTriggerKeyword" placeholder="Kata Kunci (Misal: zoom, penawaran)" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        <button onclick="addNewTrigger()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow whitespace-nowrap transition">
                            + Tambah Trigger
                        </button>
                    </div>
                </div>

                <!-- Section 4: Triggers List -->
                <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-3">⚡ Daftar Trigger Kata Kunci Aktif</h4>
                    <div id="triggersListContainer" class="space-y-2">
                        <div class="text-center py-4 text-slate-400 text-xs">Memuat data trigger...</div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeBrandSettingsModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Device & Multi-Account Manager Modal -->
    <div id="deviceModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        📱 Device & Multi-Account Manager
                    </h3>
                    <p class="text-xs text-emerald-100 mt-0.5">Kelola Akun WA & Scan QR Code Langsung Tanpa Terminal</p>
                </div>
                <button onclick="closeDeviceModal()" class="text-emerald-100 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                @if($user->isCeo())
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div>
                        <h4 class="font-bold text-emerald-900 text-sm">Tambah Akun WA / CS Baru</h4>
                        <p class="text-xs text-emerald-700">Buat sesi baru untuk menghubungkan nomor WA tambahan</p>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <input type="text" id="newAccountName" placeholder="Nama Akun (Misal: CS Brand A)" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none w-full">
                        <button onclick="addNewAccount()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg whitespace-nowrap transition">
                            + Tambah
                        </button>
                    </div>
                </div>
                @endif

                <!-- Disconnection Email Alert Control Panel (Testing vs Production) -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                📧 Pengaturan Email Notifikasi Disconnect
                            </h4>
                            <p class="text-xs text-slate-500">Kirim email darurat otomatis ke Supervisor Brand (CC ke CEO ashabil@difitech.id) jika WA terputus</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="disconnectEmailToggle" class="sr-only peer" onchange="saveDisconnectSettings()" {{ ($activeAccount && $activeAccount->disconnect_email_enabled) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200 pt-3">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs font-semibold text-slate-600 whitespace-nowrap">Mode Interval:</span>
                            <select id="disconnectIntervalSelect" onchange="saveDisconnectSettings()" class="text-xs font-semibold bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="10" {{ ($activeAccount && $activeAccount->disconnect_email_interval == 10) ? 'selected' : '' }}>⚡ 10 Detik (Mode Testing / Validation)</option>
                                <option value="1800" {{ ($activeAccount && $activeAccount->disconnect_email_interval == 1800) ? 'selected' : '' }}>⏱️ 30 Menit (Mode Production)</option>
                            </select>
                        </div>
                        <button onclick="triggerTestDisconnectEmail()" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg transition shadow-sm whitespace-nowrap">
                            🧪 Test Kirim Email Disconnect Now
                        </button>
                    </div>
                </div>

                <div id="qrSection" class="hidden border-t pt-6 text-center bg-slate-50 p-6 rounded-xl border border-slate-200">
                    <h4 class="font-bold text-slate-800 text-base flex items-center justify-center gap-2">
                        📲 Scan Barcode QR Code
                    </h4>
                    <p class="text-xs text-slate-500 mt-1 mb-4" id="qrSubtitle">Buka WhatsApp > Tautkan Perangkat (Link a Device)</p>

                    <div class="flex justify-center items-center my-4 min-h-[220px]">
                        <div id="qrLoading" class="text-slate-400 text-sm flex flex-col items-center gap-2">
                            <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mengunduh Barcode QR...</span>
                        </div>
                        <img id="qrImage" src="" alt="QR Code" class="hidden border-4 border-white shadow-lg rounded-xl max-w-[220px] bg-white p-2">
                    </div>

                    <div id="qrStatusBadge" class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mt-2">
                        Menunggu Scan...
                    </div>

                    <div class="mt-4 flex justify-center gap-2">
                        <button onclick="startScanQr(currentScanningSession)" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg shadow">
                            🔄 Regenerate / Scan Ulang QR
                        </button>
                        <button onclick="closeQrSection()" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-medium rounded-lg">
                            Tutup Scanner
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeDeviceModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- CEO Brand Management Modal (Full CRUD) -->
    @if($user->isCeo())
    <div id="brandManagementModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        🏢 Brand & Account Management (CEO)
                    </h3>
                    <p class="text-xs text-emerald-100 mt-0.5">Kelola Akun Brand, Nomor WA Terhubung, & Fitur CRUD</p>
                </div>
                <button onclick="closeBrandManagementModal()" class="text-emerald-100 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- Create New Brand Section -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 space-y-3">
                    <h4 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                        ➕ Tambah Brand Baru
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                        <div class="sm:col-span-2">
                            <input type="text" id="newBrandNameInput" placeholder="Nama Brand (Contoh: Skincare CS 1)" class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="text" id="newBrandPhoneInput" placeholder="Nomor WA (Opsional: 628123...)" class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div class="sm:col-span-1">
                            <button onclick="createNewBrandSubmit()" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                                + Simpan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Brand Accounts Table List -->
                <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-3">Daftar Brand & Akun WA Terdaftar</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Nama Brand</th>
                                    <th class="p-3">Nomor WA</th>
                                    <th class="p-3">Status Device</th>
                                    <th class="p-3">Session ID</th>
                                    <th class="p-3 text-right">Aksi (CRUD)</th>
                                </tr>
                            </thead>
                            <tbody id="brandManagementTableBody" class="divide-y divide-slate-100 bg-white">
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">Memuat data brand...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeBrandManagementModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div id="editBrandModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h4 class="font-bold text-slate-900 text-base">✏️ Edit Data Brand</h4>
                <button onclick="closeEditBrandModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <input type="hidden" id="editBrandId">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Brand</label>
                <input type="text" id="editBrandName" class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WA Terhubung</label>
                <input type="text" id="editBrandPhone" placeholder="628123456789" class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button onclick="closeEditBrandModal()" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-300">
                    Batal
                </button>
                <button onclick="saveBrandEditSubmit()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    💾 Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- CEO Brand & Supervisor Approval Modal -->
    @if($user->isCeo())
    <div id="brandApprovalModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        🏢 Brand & Supervisor Approvals (CEO)
                    </h3>
                    <p class="text-xs text-purple-200 mt-0.5">Persetujuan Pendaftaran Brand Baru & Alokasi Supervisor Penanggung Jawab</p>
                </div>
                <button onclick="closeBrandApprovalModal()" class="text-purple-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b pb-2">Daftar Pengajuan Pendaftaran Brand & Supervisor</h4>
                <div id="brandApprovalListContainer" class="space-y-3">
                    <div class="text-center py-8 text-slate-400 text-sm">Memuat pengajuan pendaftaran brand...</div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeBrandApprovalModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- CEO Dynamic SMTP Server Settings Modal -->
    @if($user->isCeo())
    <div id="smtpSettingsModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        ⚙️ Pengaturan Server SMTP Email (CEO Settings)
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5">Konfigurasi Pengiriman Email Notifikasi Automatic System & Disconnect Alert</p>
                </div>
                <button onclick="closeSmtpSettingsModal()" class="text-slate-300 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Host Server</label>
                        <input type="text" id="smtp_host" placeholder="e.g. smtp.gmail.com atau mail.difitech.id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Port</label>
                        <input type="number" id="smtp_port" placeholder="587" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Username / Email</label>
                        <input type="text" id="smtp_username" placeholder="user@difitech.id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Password / App Password</label>
                        <input type="password" id="smtp_password" placeholder="••••••••••••" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Enkripsi Mail</label>
                        <select id="smtp_encryption" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                            <option value="tls">TLS (Default / Port 587)</option>
                            <option value="ssl">SSL (Port 465)</option>
                            <option value="null">Tanpa Enkripsi (Port 25)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Pengirim (From)</label>
                        <input type="email" id="smtp_from_address" placeholder="no-reply@difitech.id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pengirim (From Name)</label>
                        <input type="text" id="smtp_from_name" placeholder="Difitech CRM Alert" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-slate-900 outline-none">
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl flex items-start gap-2">
                    <span>💡</span>
                    <span><strong>Petunjuk SMTP Gmail:</strong> Gunakan Host <code>smtp.gmail.com</code>, Port <code>587</code>, Enkripsi <code>TLS</code>, dan gunakan <strong>App Password 16 Karakter</strong> dari Akun Google Anda.</span>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <button onclick="testSmtpConnection()" id="btnTestSmtp" class="w-full sm:w-auto px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg transition shadow-sm flex items-center justify-center gap-2">
                    🧪 Tes Koneksi SMTP & Kirim Test Email
                </button>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button onclick="closeSmtpSettingsModal()" class="flex-1 sm:flex-none px-4 py-2 bg-slate-200 text-slate-700 font-medium text-xs rounded-lg hover:bg-slate-300 transition">
                        Batal
                    </button>
                    <button onclick="saveSmtpSettings()" id="btnSaveSmtp" class="flex-1 sm:flex-none px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        💾 Simpan Pengaturan SMTP
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Supervisor CS Team & Admin WA Recruitment Modal -->
    <div id="csTeamManagementModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        👥 Kelola Tim & Admin CS WhatsApp
                    </h3>
                    <p class="text-xs text-blue-100">Rekrut dan kelola akun staff CS yang mengelola lead masuk brand ini</p>
                </div>
                <button onclick="closeCsTeamModal()" class="text-blue-100 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                <!-- Add New CS Form -->
                <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl">
                    <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                        <span>➕ Rekrut Admin CS Baru</span>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Langsung Aktif</span>
                    </h4>
                    <form id="createCsForm" onsubmit="submitCreateCsMember(event)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Admin CS *</label>
                            <input type="text" id="cs_name" required placeholder="Contoh: Wijaya CS 1" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Login CS *</label>
                            <input type="email" id="cs_email" required placeholder="cs1@difitech.co.id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp (Opsional)</label>
                            <input type="text" id="cs_phone" placeholder="628123456789" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password CS *</label>
                            <input type="password" id="cs_password" required placeholder="••••••••" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                            <button type="submit" id="btnSubmitCs" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                                🚀 Simpan & Rekrut Admin CS
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CS Team List Table -->
                <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-3 flex items-center justify-between">
                        <span>Daftar Tim Admin CS Terdaftar</span>
                        <span id="csTeamTableCount" class="text-xs text-slate-500 font-normal">0 Data</span>
                    </h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Nama Admin CS</th>
                                    <th class="p-3">Email Login</th>
                                    <th class="p-3">No. WhatsApp</th>
                                    <th class="p-3">Role Status</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="csTeamTableBody" class="divide-y divide-slate-100 text-slate-700">
                                <tr><td colspan="5" class="p-6 text-center text-slate-400">Memuat data tim CS...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeCsTeamModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-xs rounded-xl hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let isModalOpen = false;
        let activeQrPollInterval = null;
        let currentScanningSession = 'default';
        let inquiryChartInstance = null;
        let currentChartPeriod = 'daily';
        let activeSettingsBrandId = null;
        let draggedElement = null;

        // HTML5 DRAG & DROP FOR CARDS & COLUMNS
        function dragCard(e) {
            draggedElement = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
        }
        function allowDropCard(e) {
            e.preventDefault();
        }
        function dropCard(e) {
            e.preventDefault();
            const target = e.currentTarget;
            if (draggedElement && target && draggedElement !== target) {
                const parent = target.parentNode;
                const children = Array.from(parent.children);
                const draggedIdx = children.indexOf(draggedElement);
                const targetIdx = children.indexOf(target);

                if (draggedIdx < targetIdx) {
                    parent.insertBefore(draggedElement, target.nextSibling);
                } else {
                    parent.insertBefore(draggedElement, target);
                }
            }
        }

        // DOUBLE-CLICK INLINE STAGE RENAME LOGIC
        function inlineEditStageName(stageId, currentName) {
            const titleSpan = document.getElementById('stage-title-' + stageId);
            if (!titleSpan) return;

            const newName = prompt('Ubah Nama Stage:', currentName);
            if (newName && newName.trim() !== '' && newName.trim() !== currentName) {
                titleSpan.textContent = 'Menyimpan...';

                fetch('/pipeline-stages/' + stageId + '/rename', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: newName.trim() })
                })
                .then(res => res.json())
                .then(res => {
                    window.location.reload();
                });
            }
        }

        // DIRECT ENTRY STAGE SELECTOR ON KANBAN HEADER
        function setAsDefaultStageDirect(stageId) {
            fetch('/pipeline-stages/' + stageId + '/set-default', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(res => {
                window.location.reload();
            });
        }

        let currentActiveTab = 'all';

        // INTERACTIVE TAB SWITCHING LOGIC
        function switchTab(tabName) {
            currentActiveTab = tabName;
            const secAnalytics = document.getElementById('section-analytics');
            const secTable = document.getElementById('section-table');
            const secKanban = document.getElementById('section-kanban');

            const navs = ['all', 'analytics', 'kanban', 'table'];
            navs.forEach(t => {
                const btn = document.getElementById('nav-' + t);
                if (btn) {
                    if (t === tabName) {
                        btn.className = "w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md bg-[#161b22] text-[#f0f6fc] font-semibold border-l-2 border-[#2f81f7] transition text-left";
                    } else {
                        btn.className = "w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[#8b949e] hover:bg-[#161b22] hover:text-[#f0f6fc] transition text-left";
                    }
                }
            });

            if (secAnalytics) secAnalytics.classList.toggle('hidden', tabName !== 'all' && tabName !== 'analytics');
            if (secTable) secTable.classList.toggle('hidden', tabName !== 'all' && tabName !== 'table');
            if (secKanban) secKanban.classList.toggle('hidden', tabName !== 'all' && tabName !== 'kanban');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('inquiryChart')) {
                initInquiryChart();
            }
            loadPendingBadgeCount();
            loadCsTeamCount();
        });

        function loadCsTeamCount() {
            const badge = document.getElementById('csTeamBadge');
            if (!badge) return;
            const accId = '{{ $accountId }}';
            fetch('/brand/cs-team?account_id=' + accId)
                .then(res => res.json())
                .then(data => {
                    const list = data.csTeam || [];
                    badge.innerText = list.length + ' CS';
                })
                .catch(err => {});
        }

        function openCsTeamModal() {
            isModalOpen = true;
            document.getElementById('csTeamManagementModal').classList.remove('hidden');
            loadCsTeamList();
        }

        function closeCsTeamModal() {
            isModalOpen = false;
            document.getElementById('csTeamManagementModal').classList.add('hidden');
        }

        function loadCsTeamList() {
            const tbody = document.getElementById('csTeamTableBody');
            const badge = document.getElementById('csTeamBadge');
            const countLabel = document.getElementById('csTeamTableCount');
            if (!tbody) return;

            const accId = '{{ $accountId }}';
            fetch('/brand/cs-team?account_id=' + accId)
                .then(res => res.json())
                .then(data => {
                    const list = data.csTeam || [];
                    if (badge) badge.innerText = list.length + ' CS';
                    if (countLabel) countLabel.innerText = list.length + ' Data Admin CS';

                    if (list.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-slate-400 font-medium">Belum ada Admin CS terdaftar. Gunakan form di atas untuk merekrut Admin CS baru.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = list.map(cs => `
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="p-3 font-bold text-slate-800 flex items-center gap-2">
                                👤 ${cs.name}
                            </td>
                            <td class="p-3 text-slate-600 font-mono">
                                ${cs.email}
                            </td>
                            <td class="p-3 text-slate-600 font-mono">
                                ${cs.phone ? '+' + cs.phone : '<span class="text-slate-400 font-sans italic">-</span>'}
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    🎧 Sales Admin CS
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="deleteCsMember(${cs.id}, '${cs.name}')" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs rounded-lg transition border border-red-200">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-rose-500 font-semibold">Gagal memuat daftar tim CS.</td></tr>`;
                });
        }

        function submitCreateCsMember(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitCs');
            const name = document.getElementById('cs_name').value;
            const email = document.getElementById('cs_email').value;
            const phone = document.getElementById('cs_phone').value;
            const password = document.getElementById('cs_password').value;
            const wa_account_id = '{{ $accountId }}';

            btn.disabled = true;
            btn.innerText = 'Memproses...';

            fetch('/brand/cs-team', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, email, phone, password, wa_account_id })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let msg = data.message || 'Gagal merekrut Admin CS';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join('\n• ');
                    }
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                btn.disabled = false;
                btn.innerText = '🚀 Simpan & Rekrut Admin CS';
                showToastNotification('✅ ' + data.message);
                document.getElementById('createCsForm').reset();
                loadCsTeamList();
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = '🚀 Simpan & Rekrut Admin CS';
                alert('⚠️ GAGAL REKRUT ADMIN CS:\n• ' + err.message);
            });
        }

        function deleteCsMember(id, name) {
            if (!confirm('Apakah Anda yakin ingin menghapus Admin CS "' + name + '" dari tim brand ini?')) return;

            fetch('/brand/cs-team/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToastNotification('✅ ' + data.message);
                    loadCsTeamList();
                } else {
                    alert('Gagal: ' + (data.message || data.error));
                }
            })
            .catch(err => {
                alert('Gagal menghapus Admin CS.');
            });
        }

        function initInquiryChart() {
            const ctx = document.getElementById('inquiryChart').getContext('2d');
            inquiryChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Jumlah Lead / Inquiry Masuk',
                        data: [],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#059669',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
            fetchChartData(currentChartPeriod);
        }

        function updateChartPeriod(period) {
            currentChartPeriod = period;
            ['btn-daily', 'btn-weekly', 'btn-monthly'].forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    if (id === 'btn-' + period) {
                        btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white shadow-sm transition";
                    } else {
                        btn.className = "px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-200 transition";
                    }
                }
            });
            fetchChartData(period);
        }

        function fetchChartData(period) {
            const currentParams = new URLSearchParams(window.location.search);
            const accountId = currentParams.get('account_id') || 'all';

            fetch('/api/analytics/chart-data?period=' + period + '&account_id=' + accountId)
                .then(res => res.json())
                .then(res => {
                    if (inquiryChartInstance) {
                        inquiryChartInstance.data.labels = res.labels;
                        inquiryChartInstance.data.datasets[0].data = res.data;
                        inquiryChartInstance.update();
                    }
                });
        }

        // BRAND SETTINGS (CUSTOM STAGES & TRIGGERS MODAL)
        function openBrandSettingsModal(brandId) {
            isModalOpen = true;
            activeSettingsBrandId = brandId;
            document.getElementById('brandSettingsModal').classList.remove('hidden');
            loadBrandSettingsData(brandId);
        }

        function closeBrandSettingsModal() {
            isModalOpen = false;
            document.getElementById('brandSettingsModal').classList.add('hidden');
        }

        function loadBrandSettingsData(brandId) {
            fetch('/wa-accounts')
                .then(res => res.json())
                .then(accounts => {
                    const acc = accounts.find(a => a.id == brandId);
                    if (!acc) return;

                    document.getElementById('brandSettingsSubtitle').textContent = "Brand: " + acc.name + " (" + (acc.phone || 'Perangkat Disconnected') + ")";

                    // Render Stages
                    const stagesContainer = document.getElementById('stagesListContainer');
                    const stageSelect = document.getElementById('triggerStageSelect');
                    stageSelect.innerHTML = `<option value="">Pilih Target Stage...</option>`;

                    if (!acc.pipeline_stages || acc.pipeline_stages.length === 0) {
                        stagesContainer.innerHTML = `<div class="p-3 bg-slate-50 text-slate-400 text-xs text-center rounded-lg">Belum ada stage khusus.</div>`;
                    } else {
                        stagesContainer.innerHTML = acc.pipeline_stages.map(s => `
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col sm:flex-row justify-between items-center gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 text-xs flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                        ${s.order}. ${s.name}
                                    </span>
                                    ${s.is_default ? `
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full border border-emerald-300">
                                            🟢 Entry Stage WA (Default)
                                        </span>
                                    ` : ''}
                                </div>
                                <div class="flex items-center gap-2">
                                    ${!s.is_default ? `
                                        <button onclick="setAsDefaultStage(${s.id})" class="text-xs font-semibold text-slate-600 hover:text-emerald-700 bg-slate-100 hover:bg-emerald-50 px-2.5 py-1 rounded-md border border-slate-200 transition">
                                            ⭐ Set Sebagai Entry Stage
                                        </button>
                                    ` : ''}
                                    <button onclick="deleteStage(${s.id})" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 bg-rose-50 rounded-md">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        `).join('');

                        acc.pipeline_stages.forEach(s => {
                            stageSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                        });
                    }

                    // Render Triggers
                    const triggersContainer = document.getElementById('triggersListContainer');
                    const triggers = acc.pipeline_stages ? acc.pipeline_stages.flatMap(s => (s.triggers || []).map(t => ({...t, stageName: s.name}))) : [];

                    if (triggers.length === 0) {
                        triggersContainer.innerHTML = `<div class="p-3 bg-slate-50 text-slate-400 text-xs text-center rounded-lg">Belum ada otomasi trigger kata kunci.</div>`;
                    } else {
                        triggersContainer.innerHTML = triggers.map(t => `
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center">
                                <div class="text-xs">
                                    <span class="font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-300 font-bold">"${t.keyword}"</span>
                                    <span class="text-slate-400 mx-1">➔</span>
                                    <span class="font-bold text-purple-700">${t.stageName}</span>
                                </div>
                                <button onclick="deleteTrigger(${t.id})" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 bg-rose-50 rounded-md">Hapus</button>
                            </div>
                        `).join('');
                    }
                });
        }

        function setAsDefaultStage(stageId) {
            fetch('/pipeline-stages/' + stageId + '/set-default', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(res => {
                loadBrandSettingsData(activeSettingsBrandId);
            });
        }

        function addNewStage() {
            const nameInput = document.getElementById('newStageName');
            const name = nameInput.value.trim();
            if (!name) return alert('Ketik nama stage terlebih dahulu.');

            fetch('/pipeline-stages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ wa_account_id: activeSettingsBrandId, name })
            })
            .then(res => res.json())
            .then(res => {
                nameInput.value = '';
                loadBrandSettingsData(activeSettingsBrandId);
            });
        }

        function deleteStage(id) {
            if (!confirm('Hapus stage ini?')) return;
            fetch('/pipeline-stages/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(() => loadBrandSettingsData(activeSettingsBrandId));
        }

        function addNewTrigger() {
            const stageSelect = document.getElementById('triggerStageSelect');
            const keywordInput = document.getElementById('newTriggerKeyword');
            const stageId = stageSelect.value;
            const keyword = keywordInput.value.trim();

            if (!stageId || !keyword) return alert('Pilih stage dan ketik kata kunci.');

            fetch('/stage-triggers', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ wa_account_id: activeSettingsBrandId, pipeline_stage_id: stageId, keyword })
            })
            .then(res => res.json())
            .then(res => {
                keywordInput.value = '';
                loadBrandSettingsData(activeSettingsBrandId);
            });
        }

        function deleteTrigger(id) {
            if (!confirm('Hapus trigger kata kunci ini?')) return;
            fetch('/stage-triggers/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(() => loadBrandSettingsData(activeSettingsBrandId));
        }

        // Lead Modal Logic
        function openLeadDetailModal(leadId) {
            isModalOpen = true;
            document.getElementById('leadDetailModal').classList.remove('hidden');
            document.getElementById('leadForm').action = '/leads/' + leadId + '/update';

            fetch('/leads/' + leadId + '/detail')
                .then(res => res.json())
                .then(lead => {
                    document.getElementById('detailLeadName').textContent = lead.name;
                    document.getElementById('detailLeadPhone').textContent = lead.phone;
                    document.getElementById('modalName').value = lead.name;
                    document.getElementById('modalStage').value = lead.stage;
                    document.getElementById('modalPriority').value = lead.priority;
                    document.getElementById('modalNotes').value = lead.notes || '';
                    document.getElementById('modalAccountTag').textContent = lead.wa_account ? lead.wa_account.name : 'Default Account';
                });
        }

        function closeLeadDetailModal() {
            isModalOpen = false;
            document.getElementById('leadDetailModal').classList.add('hidden');
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // Device Modal Logic
        function openDeviceModal() {
            isModalOpen = true;
            document.getElementById('deviceModal').classList.remove('hidden');
            loadWaAccounts();
        }

        function closeDeviceModal() {
            isModalOpen = false;
            document.getElementById('deviceModal').classList.add('hidden');
            closeQrSection();
        }

        function loadWaAccounts() {
            fetch('/wa-accounts')
                .then(res => res.json())
                .then(accounts => {
                    const container = document.getElementById('accountsListContainer');
                    if (accounts.length === 0) {
                        container.innerHTML = `
                            <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-500 text-sm">
                                Belum ada akun WA. Klik "Tambah" di atas untuk menambahkan.
                            </div>`;
                        return;
                    }

                    container.innerHTML = accounts.map(acc => `
                        <div class="p-4 bg-white border border-slate-200 rounded-xl flex flex-col sm:flex-row justify-between items-center gap-3 hover:border-emerald-300 transition">
                            <div>
                                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                    ${acc.name}
                                    <span class="px-2 py-0.5 text-xs rounded-full ${acc.status === 'CONNECTED' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700'} font-semibold">
                                        ${acc.status === 'CONNECTED' ? '🟢 Terhubung (' + (acc.phone || '') + ')' : '🟡 Terputus / Scan QR'}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 mt-1 font-mono">Session ID: ${acc.session_id}</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="/?filter={{ $filter }}&account_id=${acc.id}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition border border-blue-200 flex items-center gap-1">
                                    Lihat Detail &rarr;
                                </a>
                                <button onclick="startScanQr('${acc.session_id}')" class="px-3 py-1.5 ${acc.status === 'CONNECTED' ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700'} text-xs font-semibold rounded-lg transition shadow-sm">
                                    ${acc.status === 'CONNECTED' ? '🔄 Re-Scan' : '📲 Scan Barcode QR'}
                                </button>
                                ${'{{ $user->isCeo() }}' ? `
                                <button onclick="deleteAccount(${acc.id})" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition">
                                    Hapus
                                </button>` : ''}
                            </div>
                        </div>
                    `).join('');
                });
        }

        function saveDisconnectSettings() {
            const toggle = document.getElementById('disconnectEmailToggle');
            const select = document.getElementById('disconnectIntervalSelect');
            const enabled = toggle ? (toggle.checked ? 1 : 0) : 1;
            const interval = select ? select.value : 10;
            const accId = activeSettingsBrandId || '{{ $activeAccount ? $activeAccount->id : ($waAccounts->first() ? $waAccounts->first()->id : "") }}' || 'active';

            fetch('/wa-accounts/' + accId + '/update-disconnect-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ enabled, interval })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToastNotification('✅ ' + data.message);
                } else {
                    alert('Gagal: ' + (data.error || data.message));
                }
            })
            .catch(err => {
                alert('Gagal menyimpan pengaturan disconnect email.');
            });
        }

        function triggerTestDisconnectEmail() {
            const accId = '{{ $activeAccount ? $activeAccount->id : ($waAccounts->first() ? $waAccounts->first()->id : 1) }}';
            if (!confirm('Kirim email uji coba disconnect sekarang ke Inbox CEO?')) return;

            fetch('/wa-accounts/' + accId + '/test-disconnect-email', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                alert('📬 SUCCESS! Email darurat disconnect berhasil dikirim ke Inbox CEO!');
            })
            .catch(err => {
                alert('⚠️ Gagal mengirim email test disconnect.');
            });
        }

        function addNewAccount() {
            const nameInput = document.getElementById('newAccountName');
            const name = nameInput.value.trim();
            if (!name) return alert('Ketik nama akun terlebih dahulu.');

            fetch('/wa-accounts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name })
            })
            .then(res => res.json())
            .then(data => {
                nameInput.value = '';
                loadWaAccounts();
            });
        }

        function deleteAccount(id) {
            if (!confirm('Yakin ingin menghapus akun WA ini?')) return;
            fetch('/wa-accounts/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(() => loadWaAccounts());
        }

        function startScanQr(sessionId) {
            currentScanningSession = sessionId;
            document.getElementById('qrSection').classList.remove('hidden');
            document.getElementById('qrLoading').classList.remove('hidden');
            document.getElementById('qrImage').classList.add('hidden');
            document.getElementById('qrStatusBadge').textContent = 'Mengakses WA Bridge Server...';

            if (activeQrPollInterval) clearInterval(activeQrPollInterval);

            fetch('http://localhost:3001/api/connect', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session: sessionId })
            }).catch(e => console.log('WA Bridge connecting...'));

            pollQr();
            activeQrPollInterval = setInterval(pollQr, 2000);
        }

        function pollQr() {
            fetch('http://localhost:3001/api/qr?session=' + currentScanningSession)
                .then(res => res.json())
                .then(data => {
                    const loading = document.getElementById('qrLoading');
                    const img = document.getElementById('qrImage');
                    const badge = document.getElementById('qrStatusBadge');

                    if (data.sessionStatus === 'CONNECTED') {
                        badge.className = "inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full mt-2";
                        badge.textContent = "🟢 Terhubung! HP: " + (data.phone || '');
                        loading.classList.add('hidden');
                        img.classList.add('hidden');
                        clearInterval(activeQrPollInterval);
                        loadWaAccounts();
                    } else if (data.qrDataUrl) {
                        loading.classList.add('hidden');
                        img.src = data.qrDataUrl;
                        img.classList.remove('hidden');
                        badge.className = "inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mt-2";
                        badge.textContent = "📲 Silakan Scan QR Code di atas";
                    }
                })
                .catch(err => {
                    const badge = document.getElementById('qrStatusBadge');
                    badge.className = "inline-block px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full mt-2";
                    badge.textContent = "⚠️ Pastikan `wa-bridge` jalan di port 3001";
                });
        }

        function closeQrSection() {
            document.getElementById('qrSection').classList.add('hidden');
            if (activeQrPollInterval) clearInterval(activeQrPollInterval);
        }

        // --- BRAND MANAGEMENT (CEO FULL CRUD) ---
        let allBrandsCache = [];

        function openBrandManagementModal() {
            const modal = document.getElementById('brandManagementModal');
            if (modal) modal.classList.remove('hidden');
            loadBrandManagementTable();
        }

        function closeBrandManagementModal() {
            const modal = document.getElementById('brandManagementModal');
            if (modal) modal.classList.add('hidden');
        }

        function loadBrandManagementTable() {
            const tbody = document.getElementById('brandManagementTableBody');
            if (!tbody) return;

            fetch('/wa-accounts')
                .then(res => res.json())
                .then(data => {
                    allBrandsCache = data;
                    if (!data || data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada brand terdaftar. Klik "+ Tambah Brand Baru" di atas.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = data.map(b => `
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="p-3 font-bold text-slate-800 flex items-center gap-2">
                                🏢 ${b.name}
                            </td>
                            <td class="p-3 text-slate-600 font-mono">
                                ${b.phone ? '+' + b.phone : '<span class="text-slate-400 font-sans italic">Belum di-set</span>'}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold ${b.status === 'CONNECTED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                    ${b.status === 'CONNECTED' ? '🟢 TERHUBUNG' : '🔴 TERPUTUS'}
                                </span>
                            </td>
                            <td class="p-3 text-slate-400 font-mono text-[11px]">
                                ${b.session_id}
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <a href="/?filter={{ $filter }}&account_id=${b.id}" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-lg border border-blue-200 transition">
                                        📊 Dashboard
                                    </a>
                                    <button onclick="openEditBrandModal(${b.id})" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold rounded-lg border border-amber-200 transition">
                                        ✏️ Edit
                                    </button>
                                    <button onclick="deleteBrandSubmit(${b.id}, '${b.name}')" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-lg transition">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        function createNewBrandSubmit() {
            const nameInput = document.getElementById('newBrandNameInput');
            const phoneInput = document.getElementById('newBrandPhoneInput');
            const name = nameInput ? nameInput.value.trim() : '';
            const phone = phoneInput ? phoneInput.value.trim() : '';

            if (!name) return alert('Silakan ketik Nama Brand terlebih dahulu.');

            fetch('/wa-accounts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, phone })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (nameInput) nameInput.value = '';
                    if (phoneInput) phoneInput.value = '';
                    showToastNotification('✅ Brand Baru Berhasil Dibuat!');
                    loadBrandManagementTable();
                    loadWaAccounts();
                } else {
                    alert('Gagal membuat brand baru.');
                }
            });
        }

        function openEditBrandModal(brandId) {
            const brand = allBrandsCache.find(b => b.id == brandId);
            if (!brand) return;

            document.getElementById('editBrandId').value = brand.id;
            document.getElementById('editBrandName').value = brand.name || '';
            document.getElementById('editBrandPhone').value = brand.phone || '';
            document.getElementById('editBrandModal').classList.remove('hidden');
        }

        function closeEditBrandModal() {
            document.getElementById('editBrandModal').classList.add('hidden');
        }

        function saveBrandEditSubmit() {
            const id = document.getElementById('editBrandId').value;
            const name = document.getElementById('editBrandName').value.trim();
            const phone = document.getElementById('editBrandPhone').value.trim();

            if (!name) return alert('Nama Brand tidak boleh kosong.');

            fetch('/wa-accounts/' + id + '/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, phone })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeEditBrandModal();
                    showToastNotification('✅ Data Brand Berhasil Diperbarui!');
                    loadBrandManagementTable();
                    loadWaAccounts();
                } else {
                    alert('Gagal memperbarui brand.');
                }
            });
        }

        function deleteBrandSubmit(id, name) {
            if (!confirm(`Yakin ingin menghapus Brand "${name}"? Seluruh stage & lead terkait akan terhapus!`)) return;

            fetch('/wa-accounts/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                showToastNotification('🗑️ Brand Berhasil Dihapus.');
                loadBrandManagementTable();
                loadWaAccounts();
            });
        }

        // --- CEO BRAND & SUPERVISOR APPROVAL LOGIC ---
        function openBrandApprovalModal() {
            const modal = document.getElementById('brandApprovalModal');
            if (modal) modal.classList.remove('hidden');
            loadBrandApprovalList();
        }

        function closeBrandApprovalModal() {
            const modal = document.getElementById('brandApprovalModal');
            if (modal) modal.classList.add('hidden');
        }

        function loadPendingBrandBadgeCount() {
            fetch('/brand-approvals')
                .then(res => res.json())
                .then(res => {
                    const pendingCount = res.pendingBrands ? res.pendingBrands.length : 0;
                    const badge = document.getElementById('pendingBrandBadge');
                    if (badge) {
                        if (pendingCount > 0) {
                            badge.textContent = pendingCount;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(() => {});
        }

        function loadBrandApprovalList() {
            fetch('/brand-approvals')
                .then(res => res.json())
                .then(res => {
                    const container = document.getElementById('brandApprovalListContainer');
                    if (!container) return;

                    const pending = res.pendingBrands || [];
                    const approved = res.approvedBrands || [];

                    if (pending.length === 0 && approved.length === 0) {
                        container.innerHTML = `<div class="p-6 bg-slate-50 text-center text-slate-500 text-sm rounded-xl">Belum ada pengajuan pendaftaran brand.</div>`;
                        return;
                    }

                    let html = '';
                    if (pending.length > 0) {
                        html += `<div class="mb-3"><h5 class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">⏳ Menunggu Persetujuan CEO (${pending.length})</h5></div>`;
                        html += pending.map(b => `
                            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                                <div>
                                    <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                        🏢 ${b.name}
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
                                            PENDING APPROVAL
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1">
                                        <strong>Supervisor:</strong> ${b.supervisor ? b.supervisor.name : '-'} (${b.supervisor ? b.supervisor.email : '-'})
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Industri: <span class="font-semibold text-slate-700">${b.category || 'General'}</span> | WA Brand: <span class="font-mono text-slate-700">${b.phone ? '+' + b.phone : 'Belum di-set'}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 w-full md:w-auto">
                                    <button onclick="approveBrandSubmit(${b.id}, '${b.name}')" class="flex-1 md:flex-none px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-sm whitespace-nowrap">
                                        ✅ Setujui Brand
                                    </button>
                                    <button onclick="rejectBrandSubmit(${b.id}, '${b.name}')" class="flex-1 md:flex-none px-3 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold text-xs rounded-lg transition whitespace-nowrap">
                                        ❌ Tolak
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    }

                    if (approved.length > 0) {
                        html += `<div class="mt-6 mb-2"><h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">✅ Brand Disetujui (${approved.length})</h5></div>`;
                        html += approved.map(b => `
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">🏢 ${b.name}</span>
                                    <span class="text-slate-400 ml-2">Supervisor: ${b.supervisor ? b.supervisor.name : '-'} (${b.supervisor ? b.supervisor.email : '-'})</span>
                                </div>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">APPROVED</span>
                            </div>
                        `).join('');
                    }

                    container.innerHTML = html;
                });
        }

        function approveBrandSubmit(brandId, brandName) {
            if (!confirm(`Setujui pendaftaran Brand "${brandName}" dan akun Supervisornya?`)) return;

            fetch('/brand-approvals/' + brandId + '/approve', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                showToastNotification('✅ ' + data.message);
                loadBrandApprovalList();
                loadPendingBrandBadgeCount();
                if (typeof loadWaAccounts === 'function') loadWaAccounts();
            });
        }

        function rejectBrandSubmit(brandId, brandName) {
            if (!confirm(`Yakin ingin MENOLAK pengajuan Brand "${brandName}"?`)) return;

            fetch('/brand-approvals/' + brandId + '/reject', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                showToastNotification('❌ ' + data.message);
                loadBrandApprovalList();
                loadPendingBrandBadgeCount();
            });
        }

        // --- CEO SMTP SETTINGS MODAL LOGIC ---
        function openSmtpSettingsModal() {
            const modal = document.getElementById('smtpSettingsModal');
            if (modal) modal.classList.remove('hidden');
            loadSmtpSettings();
        }

        function closeSmtpSettingsModal() {
            const modal = document.getElementById('smtpSettingsModal');
            if (modal) modal.classList.add('hidden');
        }

        function loadSmtpSettings() {
            fetch('/admin/smtp-settings')
                .then(res => res.json())
                .then(data => {
                    if (document.getElementById('smtp_host')) document.getElementById('smtp_host').value = data.mail_host || '';
                    if (document.getElementById('smtp_port')) document.getElementById('smtp_port').value = data.mail_port || 587;
                    if (document.getElementById('smtp_username')) document.getElementById('smtp_username').value = data.mail_username || '';
                    if (document.getElementById('smtp_password')) document.getElementById('smtp_password').value = data.mail_password || '';
                    if (document.getElementById('smtp_encryption')) document.getElementById('smtp_encryption').value = data.mail_encryption || 'tls';
                    if (document.getElementById('smtp_from_address')) document.getElementById('smtp_from_address').value = data.mail_from_address || 'no-reply@difitech.id';
                    if (document.getElementById('smtp_from_name')) document.getElementById('smtp_from_name').value = data.mail_from_name || 'Difitech CRM Alert';
                })
                .catch(() => {});
        }

        function saveSmtpSettings() {
            const btn = document.getElementById('btnSaveSmtp');
            if (btn) btn.innerText = 'Menyimpan...';

            const payload = {
                mail_host: document.getElementById('smtp_host').value,
                mail_port: document.getElementById('smtp_port').value,
                mail_username: document.getElementById('smtp_username').value,
                mail_password: document.getElementById('smtp_password').value,
                mail_encryption: document.getElementById('smtp_encryption').value,
                mail_from_address: document.getElementById('smtp_from_address').value,
                mail_from_name: document.getElementById('smtp_from_name').value,
            };

            fetch('/admin/smtp-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (btn) btn.innerText = '💾 Simpan Pengaturan SMTP';
                showToastNotification('✅ ' + data.message);
            })
            .catch(() => {
                if (btn) btn.innerText = '💾 Simpan Pengaturan SMTP';
                alert('Gagal menyimpan pengaturan SMTP.');
            });
        }

        function testSmtpConnection() {
            const btn = document.getElementById('btnTestSmtp');
            if (btn) btn.innerText = 'Mengirimkan Test Email...';

            fetch('/admin/smtp-settings/test', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                if (btn) btn.innerText = '🧪 Tes Koneksi SMTP & Kirim Test Email';
                if (data.status === 'success') {
                    showToastNotification('✅ ' + data.message);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                if (btn) btn.innerText = '🧪 Tes Koneksi SMTP & Kirim Test Email';
                alert('Gagal menguji SMTP server: Silakan periksa kredensial/port host.');
            });
        }

        // Auto-refresh content without disrupting active tab state
        function fetchLeads() {
            if (isModalOpen) return;
            const currentParams = window.location.search;
            fetch('/' + currentParams)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('#dashboard-content');
                    if (newContent) {
                        document.querySelector('#dashboard-content').innerHTML = newContent.innerHTML;
                        if (typeof switchTab === 'function' && currentActiveTab && currentActiveTab !== 'all') {
                            switchTab(currentActiveTab);
                        }
                    }
                });
        }
        setInterval(fetchLeads, 5000);
    </script>
</body>
</html>
