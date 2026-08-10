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
        <!-- Header & Top Navigation -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">CRM Admin Panel</h1>
                <p class="text-sm text-gray-500 mt-1">Multi-Account WhatsApp & Isolated Pipeline Management System</p>
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

        <!-- PIPELINE SWITCHER TABS (Dedicated Pipeline per WA Account) -->
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

        <!-- Active Pipeline Banner (If a specific account is selected) -->
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
        
        <!-- Stat Cards (Isolated by selected Pipeline) -->
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
                    <p class="text-sm text-gray-500 font-medium">Inquiries</p>
                    <h3 class="text-3xl font-bold text-purple-600 mt-1">{{ $totalInquiries }}</h3>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-blue-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Follow Up</p>
                    <h3 class="text-3xl font-bold text-blue-600 mt-1">{{ $totalFollowUp }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-yellow-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Payment</p>
                    <h3 class="text-3xl font-bold text-yellow-600 mt-1">{{ $totalPayment }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-green-500">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Closed</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-1">{{ $totalClosed }}</h3>
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
                            <th class="px-6 py-3 font-medium">Tanggal Dibuat</th>
                            <th class="px-6 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($leads as $lead)
                        <tr class="hover:bg-gray-50 transition">
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
                                @if($lead->stage == 'Inquiries')
                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">Inquiries</span>
                                @elseif($lead->stage == 'Follow Up')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Follow Up</span>
                                @elseif($lead->stage == 'Payment')
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">Payment</span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Closed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $lead->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openEditModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', `{{ addslashes($lead->notes) }}`, {{ $lead->priority }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm transition">
                                    ✏️ Edit
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
        
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            Kanban Board {{ $activeAccount ? '(' . $activeAccount->name . ')' : '(Semua Pipeline)' }}
        </h2>
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Inquiries Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-purple-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-purple-600 flex justify-between items-center">
                    Inquiries
                    <span class="bg-purple-100 text-purple-800 text-xs py-1 px-2 rounded-full">{{ $totalInquiries }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Inquiries') as $lead)
                    <div class="bg-purple-50 hover:bg-purple-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-purple-100">
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
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <div class="mt-2 text-xs text-purple-700 bg-purple-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Inquiries')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>

            <!-- Follow Up Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-blue-600 flex justify-between items-center">
                    Follow Up
                    <span class="bg-blue-100 text-blue-800 text-xs py-1 px-2 rounded-full">{{ $totalFollowUp }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Follow Up') as $lead)
                    <div class="bg-blue-50 hover:bg-blue-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-blue-100">
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
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <div class="mt-2 text-xs text-blue-700 bg-blue-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Follow Up')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>

            <!-- Payment Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-yellow-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-yellow-600 flex justify-between items-center">
                    Payment
                    <span class="bg-yellow-100 text-yellow-800 text-xs py-1 px-2 rounded-full">{{ $totalPayment }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Payment') as $lead)
                    <div class="bg-yellow-50 hover:bg-yellow-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-yellow-100">
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
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <div class="mt-2 text-xs text-yellow-700 bg-yellow-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Payment')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>

            <!-- Closed Column -->
            <div class="flex-1 bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-green-600 flex justify-between items-center">
                    Closed
                    <span class="bg-green-100 text-green-800 text-xs py-1 px-2 rounded-full">{{ $totalClosed }}</span>
                </h2>
                <div class="space-y-4">
                    @foreach($leads->where('stage', 'Closed') as $lead)
                    <div class="bg-green-50 hover:bg-green-100 transition duration-150 ease-in-out p-4 rounded shadow-sm border border-green-100">
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
                        <p class="text-sm text-gray-600 mt-1 font-mono">{{ $lead->phone }}</p>
                        <div class="mt-2 text-xs text-green-700 bg-green-100 inline-block px-2 py-0.5 rounded">
                            {{ $lead->waAccount->name ?? 'Default Account' }}
                        </div>
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Closed')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Lead Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Edit Lead</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">&times;</button>
            </div>
            <form id="editForm" method="POST" action="" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lead</label>
                    <input type="text" name="name" id="editName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" id="editNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" placeholder="Ketik catatan di sini..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas (Bintang)</label>
                    <select name="priority" id="editPriority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0">0 Bintang (Normal)</option>
                        <option value="1">1 Bintang ⭐</option>
                        <option value="2">2 Bintang ⭐⭐</option>
                        <option value="3">3 Bintang ⭐⭐⭐</option>
                        <option value="4">4 Bintang ⭐⭐⭐⭐</option>
                        <option value="5">5 Bintang ⭐⭐⭐⭐⭐ (Hot)</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium text-sm">Simpan</button>
                </div>
            </form>
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

                <!-- QR Scanner Area (Hidden by Default until user clicks Scan) -->
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

                    <div class="mt-4">
                        <button onclick="closeQrSection()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium rounded-lg">
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

        function openEditModal(id, name, notes, priority) {
            isModalOpen = true;
            document.getElementById('editForm').action = '/leads/' + id + '/update';
            document.getElementById('editName').value = name;
            document.getElementById('editNotes').value = notes || '';
            document.getElementById('editPriority').value = priority;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            isModalOpen = false;
            document.getElementById('editModal').classList.add('hidden');
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
                                        ${acc.status === 'CONNECTED' ? '🟢 Terhubung (' + (acc.phone || '') + ')' : '🟡 Belum Scan'}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1 font-mono">Session ID: ${acc.session_id}</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="/?filter={{ $filter }}&account_id=${acc.id}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition border border-blue-200 flex items-center gap-1">
                                    📊 Lihat Pipeline
                                </a>
                                <button onclick="startScanQr('${acc.session_id}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                                    📲 Scan Barcode QR
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
