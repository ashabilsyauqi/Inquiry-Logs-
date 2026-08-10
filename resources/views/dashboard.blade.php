<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM MVP Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="text-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="dashboard-content">
        
        <!-- Disconnection Alert Banner -->
        @php
            $disconnectedAccounts = $waAccounts->where('status', '!=', 'CONNECTED');
        @endphp

        @if($disconnectedAccounts->isNotEmpty())
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-full text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-amber-900 text-sm">Peringatan: Perangkat WA Terputus!</h4>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Ada {{ $disconnectedAccounts->count() }} Akun WA terputus koneksi ({{ $disconnectedAccounts->pluck('name')->join(', ') }}). Pesan baru akan otomatis tersinkron saat tersambung kembali.
                    </p>
                </div>
            </div>
            <button onclick="openDeviceModal();" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-lg transition whitespace-nowrap shadow-sm">
                📲 Scan Ulang Barcode Sekarang
            </button>
        </div>
        @endif

        <!-- Header & Top Navigation -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">CRM Admin Panel</h1>
                <p class="text-sm text-gray-500 mt-1">Multi-Account WhatsApp & Interactive Chat Tracking System</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Device Manager Button -->
                <button onclick="openDeviceModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    📱 Perangkat WA & QR Code
                </button>

                <!-- Period Filters -->
                <div class="flex gap-1 bg-white p-1 rounded-lg shadow-sm border border-gray-200">
                    <a href="/?filter=all&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs rounded-md transition {{ $filter == 'all' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                    <a href="/?filter=daily&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs rounded-md transition {{ $filter == 'daily' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Hari Ini</a>
                    <a href="/?filter=monthly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs rounded-md transition {{ $filter == 'monthly' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Bulan Ini</a>
                    <a href="/?filter=yearly&account_id={{ $accountId }}" class="px-3 py-1.5 text-xs rounded-md transition {{ $filter == 'yearly' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Tahun Ini</a>
                </div>
            </div>
        </div>

        <!-- PIPELINE SWITCHER TABS -->
        <div class="mb-8 overflow-x-auto">
            <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Pilih Pipeline:</span>
                
                <a href="/?filter={{ $filter }}&account_id=all" 
                   class="px-4 py-2 text-sm rounded-xl font-medium transition flex items-center gap-2 whitespace-nowrap {{ $accountId == 'all' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    🌐 Semua Pipeline (Global)
                </a>

                @foreach($waAccounts as $acc)
                <a href="/?filter={{ $filter }}&account_id={{ $acc->id }}" 
                   class="px-4 py-2 text-sm rounded-xl font-medium transition flex items-center gap-2 whitespace-nowrap {{ $accountId == $acc->id ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    <span>📱 {{ $acc->name }}</span>
                    <span class="w-2 h-2 rounded-full {{ $acc->status == 'CONNECTED' ? 'bg-emerald-300' : 'bg-yellow-400' }}"></span>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Active Pipeline Banner -->
        @if($activeAccount)
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 mb-8 text-white shadow-lg flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold">Pipeline Sales: {{ $activeAccount->name }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $activeAccount->status == 'CONNECTED' ? 'bg-emerald-400 text-emerald-950' : 'bg-yellow-300 text-yellow-950' }}">
                        {{ $activeAccount->status == 'CONNECTED' ? '🟢 Online (' . ($activeAccount->phone ?: 'Connected') . ')' : '🟡 Belum Scan QR' }}
                    </span>
                </div>
                <p class="text-xs text-emerald-100 mt-1">Daftar Leads & Stat Cards di bawah dikhususkan untuk saluran komunikasi ini saja.</p>
            </div>
            
            <button onclick="startScanQr('{{ $activeAccount->session_id }}'); openDeviceModal();" class="px-4 py-2 bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-bold rounded-xl shadow transition whitespace-nowrap">
                📲 Scan / Sambungkan WA Ini
            </button>
        </div>
        @endif
        
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Leads</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalLeads }}</h3>
                </div>
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xl font-bold">#</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-purple-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">1. Lead Masuk</p>
                    <h3 class="text-3xl font-bold text-purple-600 mt-1">{{ $totalLeadMasuk }}</h3>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-blue-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">2. Meeting Call</p>
                    <h3 class="text-3xl font-bold text-blue-600 mt-1">{{ $totalMeetingCall }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-yellow-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">3. Kirim Penawaran</p>
                    <h3 class="text-3xl font-bold text-yellow-600 mt-1">{{ $totalKirimPenawaran }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-green-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">4. Deal</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-1">{{ $totalDeal }}</h3>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">
                    Daftar Leads {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
                </h2>
                <span class="text-xs text-gray-400">Total: {{ $leads->count() }} Data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm border-b">
                            <th class="px-6 py-3 font-medium">Nama Lead</th>
                            <th class="px-6 py-3 font-medium">No. WhatsApp</th>
                            <th class="px-6 py-3 font-medium">Akun WA CS</th>
                            <th class="px-6 py-3 font-medium">Stage</th>
                            <th class="px-6 py-3 font-medium">Waktu Dibuat</th>
                            <th class="px-6 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($leads as $lead)
                        <tr class="hover:bg-gray-50 cursor-pointer transition" onclick="openLeadDetailModal({{ $lead->id }})">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800 flex items-center gap-2">
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
                                    <div class="text-xs text-gray-500 mt-1 italic">{{ Str::limit($lead->notes, 30) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-mono">{{ $lead->phone }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded text-xs font-medium">
                                    {{ $lead->waAccount->name ?? 'Default' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($lead->stage == 'Lead Masuk')
                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">Lead Masuk</span>
                                @elseif($lead->stage == 'Meeting Call')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Meeting Call</span>
                                @elseif($lead->stage == 'Kirim Penawaran')
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">Kirim Penawaran</span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Deal</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $lead->created_at->format('d M, H:i') }}</td>
                            <td class="px-6 py-4 text-center" onclick="event.stopPropagation()">
                                <button onclick="openLeadDetailModal({{ $lead->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm transition">
                                    💬 Detail & Chat
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if($leads->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">Belum ada data lead di pipeline ini.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Kanban Board (Clickable Lead Cards for Pop-up Details & Chat History) -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            Kanban Board {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
        </h2>
        <div class="flex flex-col md:flex-row gap-6">
            <!-- 1. Lead Masuk Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-purple-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-purple-600 flex justify-between items-center">
                    Lead Masuk
                    <span class="bg-purple-100 text-purple-800 text-xs py-1 px-2 rounded-full">{{ $totalLeadMasuk }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Lead Masuk') as $lead)
                    <div onclick="openLeadDetailModal({{ $lead->id }})" class="bg-purple-50 hover:bg-purple-100 transition duration-150 ease-in-out p-4 rounded-xl shadow-sm border border-purple-100 cursor-pointer">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                            @if($lead->priority > 0)
                                <div class="text-yellow-400 text-xs flex mt-1">
                                    @for($i = 0; $i < $lead->priority; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 font-sans flex items-center gap-1">
                            <span>🕒</span> {{ $lead->created_at->format('d M, H:i') }}
                        </p>
                        <div class="mt-2 text-xs text-purple-700 bg-purple-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Lead Masuk')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">Belum ada lead masuk.</p>
                    @endif
                </div>
            </div>

            <!-- 2. Meeting Call Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-blue-600 flex justify-between items-center">
                    Meeting Call
                    <span class="bg-blue-100 text-blue-800 text-xs py-1 px-2 rounded-full">{{ $totalMeetingCall }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Meeting Call') as $lead)
                    <div onclick="openLeadDetailModal({{ $lead->id }})" class="bg-blue-50 hover:bg-blue-100 transition duration-150 ease-in-out p-4 rounded-xl shadow-sm border border-blue-100 cursor-pointer">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                            @if($lead->priority > 0)
                                <div class="text-yellow-400 text-xs flex mt-1">
                                    @for($i = 0; $i < $lead->priority; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 font-sans flex items-center gap-1">
                            <span>🕒</span> {{ $lead->created_at->format('d M, H:i') }}
                        </p>
                        <div class="mt-2 text-xs text-blue-700 bg-blue-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Meeting Call')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">Belum ada meeting call.</p>
                    @endif
                </div>
            </div>

            <!-- 3. Kirim Penawaran Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-yellow-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-yellow-600 flex justify-between items-center">
                    Kirim Penawaran
                    <span class="bg-yellow-100 text-yellow-800 text-xs py-1 px-2 rounded-full">{{ $totalKirimPenawaran }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Kirim Penawaran') as $lead)
                    <div onclick="openLeadDetailModal({{ $lead->id }})" class="bg-yellow-50 hover:bg-yellow-100 transition duration-150 ease-in-out p-4 rounded-xl shadow-sm border border-yellow-100 cursor-pointer">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                            @if($lead->priority > 0)
                                <div class="text-yellow-400 text-xs flex mt-1">
                                    @for($i = 0; $i < $lead->priority; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 font-sans flex items-center gap-1">
                            <span>🕒</span> {{ $lead->created_at->format('d M, H:i') }}
                        </p>
                        <div class="mt-2 text-xs text-yellow-700 bg-yellow-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Kirim Penawaran')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">Belum ada penawaran.</p>
                    @endif
                </div>
            </div>

            <!-- 4. Deal Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-green-600 flex justify-between items-center">
                    Deal
                    <span class="bg-green-100 text-green-800 text-xs py-1 px-2 rounded-full">{{ $totalDeal }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Deal') as $lead)
                    <div onclick="openLeadDetailModal({{ $lead->id }})" class="bg-green-50 hover:bg-green-100 transition duration-150 ease-in-out p-4 rounded-xl shadow-sm border border-green-100 cursor-pointer">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-800">{{ $lead->name }}</h3>
                            @if($lead->priority > 0)
                                <div class="text-yellow-400 text-xs flex mt-1">
                                    @for($i = 0; $i < $lead->priority; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 font-sans flex items-center gap-1">
                            <span>🕒</span> {{ $lead->created_at->format('d M, H:i') }}
                        </p>
                        <div class="mt-2 text-xs text-green-700 bg-green-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Deal')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">Belum ada deal.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Detail & WA Chat History Pop-up Modal -->
    <div id="leadDetailModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gray-900 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center font-bold text-lg text-white">
                        👤
                    </div>
                    <div>
                        <h3 class="text-lg font-bold" id="detailLeadName">Memuat...</h3>
                        <p class="text-xs text-gray-300 font-mono" id="detailLeadPhone">-</p>
                    </div>
                </div>
                <button onclick="closeLeadDetailModal()" class="text-gray-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <!-- Modal Content Grid -->
            <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
                
                <!-- Left Column: Lead Settings & Controls -->
                <div class="w-full md:w-1/2 p-6 border-b md:border-b-0 md:border-r border-gray-200 overflow-y-auto space-y-4">
                    <h4 class="font-bold text-gray-800 text-sm border-b pb-2">⚙️ Pengaturan & Status Lead</h4>

                    <form id="leadForm" method="POST" action="">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lead</label>
                            <input type="text" name="name" id="modalName" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tahapan / Stage Pipeline</label>
                            <select name="stage" id="modalStage" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="Lead Masuk">1. Lead Masuk</option>
                                <option value="Meeting Call">2. Meeting Call</option>
                                <option value="Kirim Penawaran">3. Kirim Penawaran</option>
                                <option value="Deal">4. Deal</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Prioritas Prospek (Bintang)</label>
                            <select name="priority" id="modalPriority" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="0">0 Bintang (Normal)</option>
                                <option value="1">1 Bintang ⭐</option>
                                <option value="2">2 Bintang ⭐⭐</option>
                                <option value="3">3 Bintang ⭐⭐⭐</option>
                                <option value="4">4 Bintang ⭐⭐⭐⭐</option>
                                <option value="5">5 Bintang ⭐⭐⭐⭐⭐ (Hot Lead)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Internal Sales</label>
                            <textarea name="notes" id="modalNotes" rows="3" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-400" placeholder="Tambahkan catatan khusus mengenai prospek ini..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow transition">
                            💾 Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Right Column: WhatsApp Chat History -->
                <div class="w-full md:w-1/2 p-6 flex flex-col bg-slate-100 overflow-hidden">
                    <div class="flex justify-between items-center mb-3 border-b pb-2 border-slate-200">
                        <h4 class="font-bold text-gray-800 text-sm flex items-center gap-1.5">
                            💬 Riwayat Percakapan WhatsApp
                        </h4>
                        <span class="text-xs text-gray-500 bg-white px-2 py-0.5 rounded border" id="modalAccountTag">-</span>
                    </div>

                    <div id="chatHistoryContainer" class="flex-1 overflow-y-auto space-y-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-center py-10 text-gray-400 text-xs">Memuat percakapan WhatsApp...</div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button onclick="closeLeadDetailModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium text-xs rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Device & Multi-Account Manager Modal -->
    <div id="deviceModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden flex items-center justify-center z-50 p-4">
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
                <!-- Add New Account Bar -->
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

                <!-- Accounts List -->
                <div>
                    <h4 class="font-semibold text-gray-800 text-sm mb-3">Daftar Akun WA Terdaftar</h4>
                    <div id="accountsListContainer" class="space-y-3">
                        <div class="text-center py-6 text-gray-400 text-sm">Memuat data akun...</div>
                    </div>
                </div>

                <!-- QR Scanner Area -->
                <div id="qrSection" class="hidden border-t pt-6 text-center bg-gray-50 p-6 rounded-xl border border-gray-200">
                    <h4 class="font-bold text-gray-800 text-base flex items-center justify-center gap-2">
                        📲 Scan Barcode QR Code
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 mb-4" id="qrSubtitle">Buka WhatsApp > Tautkan Perangkat (Link a Device)</p>

                    <div class="flex justify-center items-center my-4 min-h-[220px]">
                        <div id="qrLoading" class="text-gray-400 text-sm flex flex-col items-center gap-2">
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
                        <button onclick="closeQrSection()" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium rounded-lg">
                            Tutup Scanner
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button onclick="closeDeviceModal()" class="px-5 py-2 bg-gray-200 text-gray-700 font-medium text-sm rounded-lg hover:bg-gray-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let isModalOpen = false;
        let activeQrPollInterval = null;
        let currentScanningSession = 'default';

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

                    // Render Chat History
                    const chatContainer = document.getElementById('chatHistoryContainer');
                    if (!lead.messages || lead.messages.length === 0) {
                        chatContainer.innerHTML = `
                            <div class="text-center py-10 text-gray-400 text-xs italic">
                                Belum ada riwayat pesan tercatat untuk prospek ini.
                            </div>`;
                    } else {
                        chatContainer.innerHTML = lead.messages.map(m => `
                            <div class="flex flex-col ${m.is_from_me ? 'items-end' : 'items-start'}">
                                <div class="max-w-[80%] p-3 rounded-xl text-xs shadow-sm ${m.is_from_me ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-200'}">
                                    <p class="whitespace-pre-wrap">${escapeHtml(m.message)}</p>
                                    <span class="text-[10px] block mt-1 text-right ${m.is_from_me ? 'text-emerald-100' : 'text-gray-400'}">
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
                            <div class="p-4 bg-gray-50 rounded-xl text-center text-gray-500 text-sm">
                                Belum ada akun WA. Klik "Tambah" di atas untuk menambahkan.
                            </div>`;
                        return;
                    }

                    container.innerHTML = accounts.map(acc => `
                        <div class="p-4 bg-white border border-gray-200 rounded-xl flex flex-col sm:flex-row justify-between items-center gap-3 hover:border-emerald-300 transition">
                            <div>
                                <div class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                    ${acc.name}
                                    <span class="px-2 py-0.5 text-xs rounded-full ${acc.status === 'CONNECTED' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700'} font-semibold">
                                        ${acc.status === 'CONNECTED' ? '🟢 Terhubung (' + (acc.phone || '') + ')' : '🟡 Terputus / Scan QR'}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1 font-mono">Session ID: ${acc.session_id}</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="/?filter={{ $filter }}&account_id=${acc.id}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition border border-blue-200 flex items-center gap-1">
                                    📊 Lihat Pipeline
                                </a>
                                <button onclick="startScanQr('${acc.session_id}')" class="px-3 py-1.5 ${acc.status === 'CONNECTED' ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-emerald-600 text-white hover:bg-emerald-700'} text-xs font-semibold rounded-lg transition shadow-sm">
                                    ${acc.status === 'CONNECTED' ? '🔄 Re-Scan' : '📲 Scan Barcode QR'}
                                </button>
                                <button onclick="deleteAccount(${acc.id})" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition">
                                    Hapus
                                </button>
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
