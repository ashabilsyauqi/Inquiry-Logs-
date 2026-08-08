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
        <!-- Header & Filters -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-gray-900">CRM Admin Panel</h1>
            <div class="flex gap-2 bg-white p-2 rounded-lg shadow-sm border">
                <a href="/?filter=all" class="px-4 py-2 text-sm rounded-md transition {{ $filter == 'all' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                <a href="/?filter=daily" class="px-4 py-2 text-sm rounded-md transition {{ $filter == 'daily' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Hari Ini</a>
                <a href="/?filter=monthly" class="px-4 py-2 text-sm rounded-md transition {{ $filter == 'monthly' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Bulan Ini</a>
                <a href="/?filter=yearly" class="px-4 py-2 text-sm rounded-md transition {{ $filter == 'yearly' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">Tahun Ini</a>
            </div>
        </div>
        
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
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Daftar Leads</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm border-b">
                            <th class="px-6 py-3 font-medium">Nama Lead</th>
                            <th class="px-6 py-3 font-medium">No. WhatsApp</th>
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
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Belum ada data untuk periode ini.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <h2 class="text-xl font-bold text-gray-800 mb-4">Kanban Board</h2>
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
                    </div>
                    @endforeach
                    @if($leads->where('stage', 'Closed')->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4 italic">No leads here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Lead Modal (Outside #dashboard-content so it doesn't get overwritten by auto-refresh) -->
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

    <script>
        // Modal Logic
        let isModalOpen = false;

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

        // Auto-refresh the page content every 5 seconds without hard reloading, 
        // to preserve scroll position and query parameters seamlessly.
        function fetchLeads() {
            if (isModalOpen) return; // Pause refresh if user is editing!

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
