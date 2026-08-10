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
                    <span class="text-base">🏢</span> Running Brands (Portfolio)
                </a>
                @endif

                <button onclick="switchTab('all')" id="nav-all" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
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
                <button onclick="openDeviceModal()" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition text-left">
                    <span class="text-base">📱</span> Perangkat WA & QR
                </button>

                @if($user->isCeo())
                <button onclick="openUserManagementModal()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gradient-to-r from-purple-900/40 to-indigo-900/40 text-purple-200 border border-purple-700/50 hover:bg-purple-800/50 transition text-left mt-4">
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
                    {{ $user->isCeo() ? 'Executive Dashboard CEO' : 'Dashboard Sales Admin' }}
                </h2>
                <p class="text-xs text-slate-500">
                    {{ $user->isCeo() ? ($accountId == 'all' ? 'Halaman Utama Portfolio Running Brands (Pipelines Overview)' : 'Detail Pipeline: ' . ($activeAccount->name ?? '')) : 'Terisolasi Khusus Pipeline: ' . ($user->waAccount->name ?? 'Default Account') }}
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

            <!-- CEO LANDING PAGE: EXECUTIVE RUNNING BRANDS GRID -->
            @if($user->isCeo() && $accountId == 'all')
            <section class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">🏢 Portfolio Running Brands (Pipelines)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu brand untuk melihat detail pipeline, stages, dan pergerakan lead-nya.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($waAccounts as $acc)
                    @php
                        $accLeads = $acc->leads;
                        $totalAccLeads = $accLeads->count();
                        $dealsCount = $accLeads->where('stage', 'Deal')->count();
                    @endphp
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xl">
                                        📱
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-base leading-tight">{{ $acc->name }}</h4>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $acc->phone ?: 'Session: ' . $acc->session_id }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $acc->status == 'CONNECTED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $acc->status == 'CONNECTED' ? '🟢 Online' : '🔴 Terputus' }}
                                </span>
                            </div>

                            <!-- Metrics Summary -->
                            <div class="grid grid-cols-2 gap-3 mb-6 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Lead</span>
                                    <p class="text-lg font-bold text-slate-800 mt-0.5">{{ $totalAccLeads }}</p>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Closing Deal</span>
                                    <p class="text-lg font-bold text-emerald-600 mt-0.5">{{ $dealsCount }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="/?filter={{ $filter }}&account_id={{ $acc->id }}" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition text-center">
                                📊 Buka Pipeline Brand Ini
                            </a>
                            <button onclick="openBrandSettingsModal({{ $acc->id }})" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition border border-slate-200">
                                ⚙️ Stages & Triggers
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @if($waAccounts->isEmpty())
                    <div class="col-span-3 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
                        Belum ada Running Brand / Akun WA CS. Klik "WA Device Manager" untuk menambahkan.
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- BRAND PIPELINE DETAIL VIEW -->
            @if(!$user->isCeo() || $accountId != 'all')
            
            <!-- PIPELINE SWITCHER TABS (CEO Only) -->
            @if($user->isCeo())
            <div class="overflow-x-auto bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="/?filter={{ $filter }}&account_id=all" 
                       class="px-4 py-2 text-xs rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                        ⬅️ Kembali ke Portfolio Brands
                    </a>

                    @foreach($waAccounts as $acc)
                    <a href="/?filter={{ $filter }}&account_id={{ $acc->id }}" 
                       class="px-4 py-2 text-xs rounded-xl font-semibold transition flex items-center gap-2 whitespace-nowrap {{ $accountId == $acc->id ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>📱 {{ $acc->name }}</span>
                        <span class="w-2 h-2 rounded-full {{ $acc->status == 'CONNECTED' ? 'bg-emerald-300' : 'bg-yellow-400' }}"></span>
                    </a>
                    @endforeach
                </div>
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
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider truncate">{{ $stage->name }}</p>
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
                                <th class="px-6 py-3 font-semibold">Stage</th>
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
                                        💬 Detail & Chat
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
            
            <!-- SECTION 3: DYNAMIC CUSTOM KANBAN BOARD VIEW -->
            <section id="section-kanban" class="space-y-4">
                <h2 class="text-xl font-bold text-slate-900">
                    Kanban Board {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
                </h2>
                
                <div class="flex flex-col md:flex-row gap-6 overflow-x-auto pb-4">
                    @foreach($stages as $stage)
                    @php
                        $stageLeads = $leads->where('stage', $stage->name);
                    @endphp
                    <div class="flex-1 min-w-[260px] bg-white rounded-2xl shadow-sm p-4 border-t-4 border-emerald-500 border-x border-b border-slate-200/80">
                        <h2 class="text-base font-bold border-b border-slate-100 pb-3 mb-4 text-slate-800 flex justify-between items-center">
                            <span>{{ $stage->name }}</span>
                            <span class="bg-emerald-100 text-emerald-800 text-xs py-0.5 px-2.5 rounded-full font-bold">{{ $stageLeads->count() }}</span>
                        </h2>
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

    <!-- Lead Detail & WA Chat History Pop-up Modal -->
    <div id="leadDetailModal" class="fixed inset-0 bg-slate-950/70 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
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

            <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
                <div class="w-full md:w-1/2 p-6 border-b md:border-b-0 md:border-r border-slate-200 overflow-y-auto space-y-4">
                    <h4 class="font-bold text-slate-800 text-sm border-b pb-2">⚙️ Pengaturan & Status Lead</h4>

                    <form id="leadForm" method="POST" action="">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lead</label>
                            <input type="text" name="name" id="modalName" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tahapan / Stage Pipeline</label>
                            <select name="stage" id="modalStage" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                @foreach($stages as $st)
                                    <option value="{{ $st->name }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Prioritas Prospek (Bintang)</label>
                            <select name="priority" id="modalPriority" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="0">0 Bintang (Normal)</option>
                                <option value="1">1 Bintang ⭐</option>
                                <option value="2">2 Bintang ⭐⭐</option>
                                <option value="3">3 Bintang ⭐⭐⭐</option>
                                <option value="4">4 Bintang ⭐⭐⭐⭐</option>
                                <option value="5">5 Bintang ⭐⭐⭐⭐⭐ (Hot Lead)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Internal Sales</label>
                            <textarea name="notes" id="modalNotes" rows="3" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none placeholder-slate-400" placeholder="Tambahkan catatan khusus mengenai prospek ini..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow transition">
                            💾 Simpan Perubahan
                        </button>
                    </form>
                </div>

                <div class="w-full md:w-1/2 p-6 flex flex-col bg-slate-100 overflow-hidden">
                    <div class="flex justify-between items-center mb-3 border-b pb-2 border-slate-200">
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                            💬 Riwayat Percakapan WhatsApp
                        </h4>
                        <span class="text-xs text-slate-500 bg-white px-2 py-0.5 rounded border" id="modalAccountTag">-</span>
                    </div>

                    <div id="chatHistoryContainer" class="flex-1 overflow-y-auto space-y-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-center py-10 text-slate-400 text-xs">Memuat percakapan WhatsApp...</div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button onclick="closeLeadDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium text-xs rounded-xl transition">
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
                    <h4 class="font-bold text-slate-800 text-sm mb-3">📋 Daftar Stage Aktif</h4>
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

                <div>
                    <h4 class="font-semibold text-slate-800 text-sm mb-3">Daftar Akun WA Terdaftar</h4>
                    <div id="accountsListContainer" class="space-y-3">
                        <div class="text-center py-6 text-slate-400 text-sm">Memuat data akun...</div>
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
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    ${s.order}. ${s.name}
                                </span>
                                <button onclick="deleteStage(${s.id})" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 bg-rose-50 rounded-md">Hapus</button>
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

                    const chatContainer = document.getElementById('chatHistoryContainer');
                    if (!lead.messages || lead.messages.length === 0) {
                        chatContainer.innerHTML = `
                            <div class="text-center py-10 text-slate-400 text-xs italic">
                                Belum ada riwayat pesan tercatat untuk prospek ini.
                            </div>`;
                    } else {
                        chatContainer.innerHTML = lead.messages.map(m => `
                            <div class="flex flex-col ${m.is_from_me ? 'items-end' : 'items-start'}">
                                <div class="max-w-[80%] p-3 rounded-xl text-xs shadow-sm ${m.is_from_me ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-white text-slate-800 rounded-bl-none border border-slate-200'}">
                                    <p class="whitespace-pre-wrap">${escapeHtml(m.message)}</p>
                                    <span class="text-[10px] block mt-1 text-right ${m.is_from_me ? 'text-emerald-100' : 'text-slate-400'}">
                                        ${new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                    </span>
                                </div>
                            </div>
                        `).join('');
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
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
                                    📊 Lihat Pipeline
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
