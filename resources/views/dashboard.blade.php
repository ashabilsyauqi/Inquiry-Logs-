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

    <!-- LEFT SIDEBAR NAVIGATION -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between hidden md:flex flex-shrink-0 border-r border-slate-800">
        <div>
            <!-- Sidebar Header / Logo -->
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-900/50">
                    🚀
                </div>
                <div>
                    <h2 class="font-bold text-white text-base leading-tight">CRM MVP</h2>
                    <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded {{ $user->isCeo() ? 'bg-purple-900/80 text-purple-300 border border-purple-700' : 'bg-blue-900/80 text-blue-300 border border-blue-700' }}">
                        {{ $user->role }}
                    </span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="p-4 space-y-1.5 text-sm font-medium">
                @if($user->isCeo())
                <a href="/?account_id=all" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ $accountId == 'all' ? 'bg-slate-800 text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition text-left">
                    <span class="text-base">🏢</span> Running Brands (CEO)
                </a>
                @endif

                @if(!$user->isCeo() || $accountId != 'all')
                <button onclick="switchTab('all')" id="nav-all" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800 text-white font-semibold shadow-sm transition text-left">
                    <span class="text-base">🌐</span> Semua Tampilan
                </button>
                <button onclick="switchTab('analytics')" id="nav-analytics" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
                    <span class="text-base">📊</span> Analytics & Chart
                </button>
                <button onclick="switchTab('kanban')" id="nav-kanban" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
                    <span class="text-base">📋</span> Kanban Board
                </button>
                <button onclick="switchTab('table')" id="nav-table" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
                    <span class="text-base">📑</span> Daftar Leads
                </button>
                @endif

                <button onclick="openDeviceModal()" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
                    <span class="text-base">📱</span> Perangkat WA & QR
                </button>

                @if($user->isCeo())
                <button onclick="openBrandManagementModal()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-900/40 to-teal-900/40 text-emerald-200 border border-emerald-700/50 hover:bg-emerald-800/50 transition text-left mt-3">
                    <span class="flex items-center gap-3 font-semibold">
                        <span class="text-base">🏢</span> Brand Management
                    </span>
                    <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">CRUD</span>
                </button>

                <button onclick="openUserManagementModal()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gradient-to-r from-purple-900/40 to-indigo-900/40 text-purple-200 border border-purple-700/50 hover:bg-purple-800/50 transition text-left mt-2">
                    <span class="flex items-center gap-3 font-semibold">
                        <span class="text-base">👥</span> User Approval
                    </span>
                    <span id="pendingBadge" class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full hidden">0</span>
                </button>
                @endif
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/50">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate">{{ $user->name }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-rose-900/50 hover:text-rose-300 text-slate-300 font-semibold text-xs rounded-lg transition border border-slate-700 flex items-center justify-center gap-2">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto" id="main-scroll-area">
        
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $user->isCeo() ? ($accountId == 'all' ? 'Executive Dashboard CEO' : 'Dashboard Brand: ' . ($activeAccount->name ?? '')) : 'Dashboard Brand: ' . ($user->waAccount->name ?? 'Default Account') }}
                </h2>
                <p class="text-xs text-slate-500">
                    {{ $user->isCeo() ? ($accountId == 'all' ? 'Portfolio Overview Seluruh Running Brands (Pipelines)' : 'Analisis & Pipeline Khusus Brand ' . ($activeAccount->name ?? '')) : 'Pipeline Sales Khusus: ' . ($user->waAccount->name ?? 'Default Account') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if($activeAccount)
                <button onclick="openBrandSettingsModal({{ $activeAccount->id }})" class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                    ⚙️ Custom Stage & Trigger
                </button>
                @endif

                <button onclick="openDeviceModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                    📱 WA Device Manager
                </button>
                <form method="POST" action="/logout" class="md:hidden">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl">Logout</button>
                </form>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-6 space-y-8" id="dashboard-content">

            <!-- Disconnection Alert Banner -->
            @php
                $disconnectedAccounts = $waAccounts->where('status', '!=', 'CONNECTED');
            @endphp

            @if($disconnectedAccounts->isNotEmpty())
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-full text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">Peringatan: Perangkat WA Terputus! Email Alert Telah Dikirim!</h4>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Ada {{ $disconnectedAccounts->count() }} Akun WA terputus ({{ $disconnectedAccounts->pluck('name')->join(', ') }}). Pesan baru akan otomatis tersinkron saat tersambung kembali.
                        </p>
                    </div>
                </div>
                <button onclick="openDeviceModal();" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-lg transition whitespace-nowrap shadow-sm">
                    📲 Scan Ulang Barcode
                </button>
            </div>
            @endif

            <!-- 1. CEO EXECUTIVE LANDING PAGE (MINI WIDGETS WITH "LIHAT DETAIL →" LINK TEXT) -->
            @if($user->isCeo() && $accountId == 'all')
            <section class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">🏢 Running Brands Overview</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Ringkasan mini widget per brand. Klik "Lihat Detail →" untuk masuk ke dashboard brand tersebut.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                        Total Brand: {{ $waAccounts->count() }}
                    </span>
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
                            <div class="grid grid-cols-3 gap-1 bg-slate-50 p-2 rounded-xl border border-slate-100 text-center mb-3">
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
                    <div class="col-span-4 bg-white p-6 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                        Belum ada Running Brand. Klik "WA Device Manager" untuk menambahkan.
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- 2. BRAND DASHBOARD VIEW (STAT CARDS, ADJUSTABLE PERIOD, TREND CHART, KANBAN, DATA TABLE) -->
            @if(!$user->isCeo() || $accountId != 'all')
            
            <!-- Top Action Header & Adjustable Period Filters -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    @if($user->isCeo())
                    <a href="/?filter={{ $filter }}&account_id=all" 
                       class="px-4 py-2 text-xs rounded-xl font-bold transition flex items-center gap-2 bg-slate-900 text-white hover:bg-slate-800 shadow">
                        ⬅️ Kembali ke Portfolio Brands CEO
                    </a>
                    @endif

                    <h3 class="font-bold text-slate-800 text-sm">
                        Filter Periode Data Brand:
                    </h3>
                </div>

                <!-- Adjustable Period Filter Buttons (Semua, Hari Ini, Bulan Ini, Tahun Ini) -->
                <div class="flex gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <a href="/?filter=all&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'all' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Semua</a>
                    <a href="/?filter=daily&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'daily' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Hari Ini</a>
                    <a href="/?filter=monthly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'monthly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Bulan Ini</a>
                    <a href="/?filter=yearly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $filter == 'yearly' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">Tahun Ini</a>
                </div>
            </div>

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
                            <h3 class="text-3xl font-bold text-slate-900 mt-1">{{ $totalLeads }}</h3>
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
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900">
                        Daftar Leads {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
                    </h2>
                    <span class="text-xs text-slate-400 font-medium">Total: {{ $leads->count() }} Data</span>
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
                            <p class="text-xs text-slate-500">Kirim email darurat otomatis ke CEO jika koneksi WA terputus</p>
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

    <!-- CEO User Approval & Management Modal -->
    @if($user->isCeo())
    <div id="userManagementModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        👥 User Approval & Sales Admin Management
                    </h3>
                    <p class="text-xs text-purple-200 mt-0.5">Persetujuan Registrasi User Baru & Alokasi WA Pipeline Sales</p>
                </div>
                <button onclick="closeUserManagementModal()" class="text-purple-200 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b pb-2">Daftar Pendaftaran User & Akses Admin</h4>
                <div id="userListContainer" class="space-y-3">
                    <div class="text-center py-8 text-slate-400 text-sm">Memuat data pendaftaran user...</div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeUserManagementModal()" class="px-5 py-2 bg-slate-200 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

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

        // INTERACTIVE TAB SWITCHING LOGIC
        function switchTab(tabName) {
            const secAnalytics = document.getElementById('section-analytics');
            const secTable = document.getElementById('section-table');
            const secKanban = document.getElementById('section-kanban');

            const navs = ['all', 'analytics', 'kanban', 'table'];
            navs.forEach(t => {
                const btn = document.getElementById('nav-' + t);
                if (btn) {
                    if (t === tabName) {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800 text-white font-semibold shadow-sm transition text-left";
                    } else {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left";
                    }
                }
            });

            if (secAnalytics) secAnalytics.classList.toggle('hidden', tabName !== 'all' && tabName !== 'analytics');
            if (secTable) secTable.classList.toggle('hidden', tabName !== 'all' && tabName !== 'table');
            if (secKanban) secKanban.classList.toggle('hidden', tabName !== 'all' && tabName !== 'kanban');

            document.getElementById('main-scroll-area').scrollTop = 0;
        }

        // Chart.js Trend Analytics Initialization
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('inquiryChart')) {
                initInquiryChart();
            }
            loadPendingBadgeCount();
        });

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
            const accId = '{{ $activeAccount ? $activeAccount->id : ($waAccounts->first() ? $waAccounts->first()->id : 1) }}';

            fetch('/wa-accounts/' + accId + '/update-disconnect-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ enabled, interval })
            })
            .then(res => res.json())
            .then(data => {
                showToastNotification('✅ ' + data.message);
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

        // CEO User Approval Logic
        function openUserManagementModal() {
            isModalOpen = true;
            document.getElementById('userManagementModal').classList.remove('hidden');
            loadUserList();
        }

        function closeUserManagementModal() {
            isModalOpen = false;
            document.getElementById('userManagementModal').classList.add('hidden');
        }

        function loadPendingBadgeCount() {
            fetch('/users')
                .then(res => res.json())
                .then(res => {
                    const pending = res.users.filter(u => u.status === 'PENDING').length;
                    const badge = document.getElementById('pendingBadge');
                    if (badge) {
                        if (pending > 0) {
                            badge.textContent = pending;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(() => {});
        }

        function loadUserList() {
            fetch('/users')
                .then(res => res.json())
                .then(res => {
                    const container = document.getElementById('userListContainer');
                    const users = res.users;
                    const waAccounts = res.waAccounts;

                    if (users.length === 0) {
                        container.innerHTML = `<div class="p-6 bg-slate-50 text-center text-slate-500 text-sm rounded-xl">Belum ada user terdaftar.</div>`;
                        return;
                    }

                    container.innerHTML = users.map(u => `
                        <div class="p-4 bg-white border border-slate-200 rounded-xl flex flex-col md:flex-row justify-between items-center gap-3">
                            <div>
                                <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    ${u.name}
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full ${u.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : (u.status === 'PENDING' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')}">
                                        ${u.status}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">${u.email}</div>
                                <div class="text-xs text-purple-700 mt-1 font-semibold">
                                    Alokasi Pipeline: ${u.wa_account ? u.wa_account.name : 'Belum Ada (Semua/Locked)'}
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
                                ${u.status === 'PENDING' ? `
                                    <select id="assign_wa_${u.id}" class="px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg outline-none bg-slate-50">
                                        <option value="">Pilih Pipeline WA CS...</option>
                                        ${waAccounts.map(acc => `<option value="${acc.id}">${acc.name}</option>`).join('')}
                                    </select>
                                    <button onclick="approveUser(${u.id})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm whitespace-nowrap">
                                        ✅ Approve
                                    </button>
                                    <button onclick="rejectUser(${u.id})" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-lg transition whitespace-nowrap">
                                        ❌ Reject
                                    </button>
                                ` : `
                                    <span class="text-xs font-bold text-slate-400">Status ${u.status}</span>
                                `}
                            </div>
                        </div>
                    `).join('');
                });
        }

        function approveUser(userId) {
            const waSelect = document.getElementById('assign_wa_' + userId);
            const waAccountId = waSelect ? waSelect.value : null;

            fetch('/users/' + userId + '/approve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ wa_account_id: waAccountId })
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                loadUserList();
                loadPendingBadgeCount();
            });
        }

        function rejectUser(userId) {
            if (!confirm('Yakin ingin menolak pendaftaran akun ini?')) return;
            fetch('/users/' + userId + '/reject', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(res => {
                loadUserList();
                loadPendingBadgeCount();
            });
        }

        // Auto-refresh content
        function fetchLeads() {
            if (isModalOpen) return;
            const currentParams = window.location.search;
            fetch('/' + currentParams)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    document.querySelector('#dashboard-content').innerHTML = doc.querySelector('#dashboard-content').innerHTML;
                });
        }
        setInterval(fetchLeads, 5000);
    </script>
</body>
</html>
