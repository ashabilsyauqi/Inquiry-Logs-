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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-[#0d1117] text-white border-b border-[#30363d] px-6 py-4 sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="/" class="p-2 bg-[#21262d] hover:bg-[#30363d] text-[#c9d1d9] hover:text-white rounded-xl border border-[#30363d] transition flex items-center justify-center shadow-sm" title="Kembali ke Dashboard Utama">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16"><path d="M7.78 12.53a.75.75 0 01-1.06 0L2.47 8.28a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 1.06L4.81 7.5h8.44a.75.75 0 010 1.5H4.81l2.97 2.97a.75.75 0 010 1.06z"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🤖</span>
                        <h1 class="text-lg font-extrabold text-white tracking-tight">Perbandingan Leads: Real CS vs Analisa AI</h1>
                        <span class="bg-amber-500/20 border border-amber-500/40 text-amber-300 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase">Dual Analytics</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Membandingkan stage aktual yang dicatat CS via keyword trigger dengan kesimpulan analisis AI konteks chat WhatsApp.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Form Simpan Snapshot Mingguan -->
                <form method="POST" action="{{ route('ai-comparison.snapshot') }}" onsubmit="return confirm('Simpan snapshot data perbandingan saat ini ke arsip database mingguan?')">
                    @csrf
                    <input type="hidden" name="account_id" value="{{ $accountId }}">
                    <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <span>📸</span> Simpan Snapshot Mingguan
                    </button>
                </form>

                <a href="/" class="px-3.5 py-2 bg-[#21262d] hover:bg-[#30363d] text-[#c9d1d9] hover:text-white font-bold text-xs rounded-xl border border-[#30363d] transition shadow-sm flex items-center gap-1.5">
                    <span>📊</span> Ke Dashboard Utama
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 space-y-6">

        @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-sm font-semibold">
                <span>✅</span> {{ session('success') }}
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold">Tutup</button>
        </div>
        @endif

        <!-- Filter Controls Strip -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('ai-comparison.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Brand Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Brand / WA:</label>
                    <select name="account_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @if($user->isCeo())
                        <option value="all" {{ $accountId == 'all' ? 'selected' : '' }}>🏢 Semua Brand (Portfolio)</option>
                        @endif
                        @foreach($waAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                            📱 {{ $acc->name }} ({{ $acc->phone ?? 'No Phone' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Period Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Periode:</label>
                    <select name="period" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="all_time" {{ $period == 'all_time' ? 'selected' : '' }}>📅 Semua Waktu</option>
                        <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>📆 Minggu Ini</option>
                        <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>🗓️ Bulan Ini</option>
                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>🕒 Hari Ini</option>
                    </select>
                </div>
            </form>

            <div class="text-right text-xs text-slate-400 font-medium">
                Terakhir diperbarui: <span class="font-mono text-slate-600 font-bold">{{ $comparison['generated_at'] }}</span>
            </div>
        </div>

        <!-- 4 KPI Summary Cards Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Leads -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider">Total Leads Dianalisis</span>
                    <span class="p-2 bg-slate-100 text-slate-700 rounded-xl text-base">👥</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-black text-slate-900">{{ $comparison['total_leads'] }}</span>
                    <span class="text-xs font-bold text-slate-500">Real & AI Sync</span>
                </div>
            </div>

            <!-- Stage Match Rate -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider">Tingkat Keselarasan</span>
                    <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl text-base">🎯</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-black text-emerald-600">{{ $comparison['match_rate'] }}%</span>
                    <span class="text-xs font-bold text-emerald-700">{{ $comparison['match_count'] }} Leads Match</span>
                </div>
            </div>

            <!-- Discrepancies Count -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider">Selisih / Potensi Missed</span>
                    <span class="p-2 bg-amber-100 text-amber-700 rounded-xl text-base">⚠️</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-black {{ $comparison['discrepancy_count'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                        {{ $comparison['discrepancy_count'] }}
                    </span>
                    <span class="text-xs font-bold text-amber-700">{{ $comparison['discrepancy_rate'] }}% Selisih</span>
                </div>
            </div>

            <!-- Deal Comparison Summary -->
            @php
                $realDeals = $comparison['real_counts']['Deal'] ?? 0;
                $aiDeals = $comparison['ai_counts']['Deal'] ?? 0;
            @endphp
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider">Perbandingan Deal</span>
                    <span class="p-2 bg-blue-100 text-blue-700 rounded-xl text-base">🏆</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-2xl font-black text-slate-900">{{ $realDeals }}</span>
                        <span class="text-xs text-slate-400 font-bold">Real CS</span>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-blue-600">{{ $aiDeals }}</span>
                        <span class="text-xs text-blue-500 font-bold">Analisa AI</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Analytics Strip: Chart & Stage Breakdown Table -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Bar Comparison Chart -->
            <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Visualisasi Sebaran Stage: Real vs AI</h2>
                        <p class="text-xs text-slate-500">Perbandingan jumlah lead pada tiap stage menurut input CS vs AI Gemini.</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">Bar Chart</span>
                </div>
                <div class="h-64 w-full relative">
                    <canvas id="stageComparisonChart"></canvas>
                </div>
            </div>

            <!-- Stage Comparison Matrix Table -->
            <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider mb-1">Matriks Per Stage</h2>
                    <p class="text-xs text-slate-500 mb-4">Rincian angka real versus kesimpulan AI di masing-masing stage.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 uppercase font-extrabold">
                                    <th class="py-2.5 px-2">Stage</th>
                                    <th class="py-2.5 px-2 text-center">Real (CS)</th>
                                    <th class="py-2.5 px-2 text-center">AI</th>
                                    <th class="py-2.5 px-2 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($comparison['stage_comparison'] as $stName => $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-2.5 px-2 font-bold text-slate-800">{{ $stName }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-slate-700 bg-slate-50/80 rounded">{{ $row['real_count'] }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-blue-600 bg-blue-50/50 rounded">{{ $row['ai_count'] }}</td>
                                    <td class="py-2.5 px-2 text-right font-extrabold">
                                        @if($row['difference'] > 0)
                                            <span class="text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">+{{ $row['difference'] }} AI</span>
                                        @elseif($row['difference'] < 0)
                                            <span class="text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">{{ $row['difference'] }} Real</span>
                                        @else
                                            <span class="text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">0 (Match)</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-amber-50/80 border border-amber-200/80 rounded-xl text-[11px] text-amber-800 leading-relaxed">
                    💡 <strong>Tips Analisis:</strong> Jika nilai <em>AI lebih tinggi</em> dari Real pada stage Meeting / Deal, ada kemungkinan admin CS lupa mengetik keyword trigger saat percakapan sudah deal/meeting.
                </div>
            </div>
        </div>

        <!-- Discrepant Leads Detail Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Daftar Leads yang Memiliki Perbedaan Stage (Discrepancies)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Leads di mana kesimpulan AI berbeda dengan stage yang saat ini tercatat oleh CS.</p>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold text-xs rounded-lg">
                    {{ count($comparison['discrepant_leads']) }} Leads Perlu Ditinjau
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase font-extrabold border-b border-slate-200">
                            <th class="py-3 px-4">Nama & Nomor</th>
                            <th class="py-3 px-4">Brand WA</th>
                            <th class="py-3 px-4">Stage CS (Real)</th>
                            <th class="py-3 px-4">Kesimpulan AI</th>
                            <th class="py-3 px-4">Alasan & Indikasi AI</th>
                            <th class="py-3 px-4">Waktu Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($comparison['discrepant_leads'] as $lead)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div>{{ $lead['name'] }}</div>
                                <div class="text-[11px] font-normal text-slate-500 font-mono">{{ $lead['phone'] }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-slate-100 text-slate-700 font-bold px-2 py-0.5 rounded text-[10px]">
                                    {{ $lead['account_name'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-slate-200 text-slate-800 font-extrabold px-2.5 py-1 rounded-md text-[11px]">
                                    {{ $lead['real_stage'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-amber-100 text-amber-900 border border-amber-300 font-extrabold px-2.5 py-1 rounded-md text-[11px] flex items-center gap-1 w-max">
                                    <span>🤖</span> {{ $lead['ai_stage'] }}
                                </span>
                                @if(!empty($lead['ai_keyword']))
                                <div class="text-[10px] text-amber-700 font-mono mt-1">
                                    Trigger: {{ $lead['ai_keyword'] }}
                                </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                <p class="line-clamp-2 text-[11px] italic">"{{ $lead['ai_reason'] }}"</p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $lead['ai_suggested_at'] !== '-' ? $lead['ai_suggested_at'] : $lead['created_at'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">
                                🎉 Tidak ada perbedaan stage! Seluruh pencatatan CS selaras 100% dengan analisis AI.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Historical Snapshots (Mingguan) Archive Section -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Arsip Snapshot Mingguan (History)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan performa dan perbandingan per akhir minggu yang tersimpan otomatis di database.</p>
                </div>
                <span class="text-xs font-bold text-slate-400">Total Snapshot: {{ $snapshots->count() }}</span>
            </div>

            @if($snapshots->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($snapshots as $snap)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/80 transition flex flex-col justify-between gap-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-xs font-bold text-slate-500 uppercase">Snapshot Tanggal</div>
                            <div class="text-sm font-extrabold text-slate-900">{{ $snap->report_date->format('d F Y') }}</div>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                            {{ $snap->differences['match_rate'] ?? 0 }}% Match
                        </span>
                    </div>

                    <div class="text-xs text-slate-600 space-y-1 bg-white p-2.5 rounded-lg border border-slate-200/60">
                        <div class="flex justify-between">
                            <span>Total Leads:</span>
                            <strong class="text-slate-800">{{ $snap->differences['total_leads'] ?? 0 }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Selisih Discrepancy:</span>
                            <strong class="text-amber-700">{{ $snap->differences['discrepancy_count'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                Belum ada snapshot mingguan yang disimpan. Klik tombol <strong>"📸 Simpan Snapshot Mingguan"</strong> di atas untuk membuat snapshot pertama.
            </div>
            @endif
        </div>

    </main>

    <!-- Chart.js Render Script -->
    <script>
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
                            label: 'Real Stage (CS)',
                            data: realData,
                            backgroundColor: 'rgba(71, 85, 105, 0.85)',
                            borderColor: 'rgba(71, 85, 105, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Analisa AI (Gemini)',
                            data: aiData,
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
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
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Plus Jakarta Sans',
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
