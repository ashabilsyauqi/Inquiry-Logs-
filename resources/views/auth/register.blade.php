<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Brand & Supervisor - Difitech CRM</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0d1117; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-[#c9d1d9] px-4 py-10">
    <div class="w-full max-w-xl bg-[#161b22] border border-[#30363d] p-8 rounded-2xl shadow-2xl space-y-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-[#21262d] border border-[#30363d] rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md text-[#2f81f7]">
                <svg class="w-8 h-8 fill-current" viewBox="0 0 16 16"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25H1.75zM4 4.75a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 4.75zm0 3a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 014 7.75zm0 3a.75.75 0 01.75-.75h4a.75.75 0 010 1.5h-4a.75.75 0 01-.75-.75z"></path></svg>
            </div>
            <h1 class="text-xl font-bold text-[#f0f6fc]">Pendaftaran Brand & Supervisor Baru</h1>
            <p class="text-xs text-[#8b949e] mt-1">Daftarkan Brand baru Anda beserta akun Supervisor penanggung jawab.</p>
        </div>

        @if ($errors->any())
            <div class="p-3.5 bg-[#da3633]/20 border border-[#da3633]/50 text-[#ff7b72] text-xs rounded-xl">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/register" class="space-y-6">
            @csrf
            
            <!-- Seksi 1: Identitas Supervisor -->
            <div class="bg-[#0d1117] border border-[#30363d] p-4 rounded-xl space-y-3">
                <h3 class="text-xs font-bold text-[#58a6ff] uppercase tracking-wider flex items-center gap-2">
                    👤 1. Identitas Supervisor (Admin Utama Brand)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Nama Supervisor</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Nama Penanggung Jawab" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#2f81f7]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Nomor WA Supervisor</label>
                        <input type="text" name="supervisor_phone" value="{{ old('supervisor_phone') }}" placeholder="Contoh: 081234567890" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#2f81f7]">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Email (Untuk Login)</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="supervisor@brand.com" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#2f81f7]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Password</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#2f81f7]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Ketik ulang password" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#2f81f7]">
                    </div>
                </div>
            </div>

            <!-- Seksi 2: Detail Brand -->
            <div class="bg-[#0d1117] border border-[#30363d] p-4 rounded-xl space-y-3">
                <h3 class="text-xs font-bold text-[#3fb950] uppercase tracking-wider flex items-center gap-2">
                    🏢 2. Detail Brand Yang Didaftarkan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Nama Brand</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name') }}" required placeholder="Contoh: GlowUp Skincare / Difitech Agency" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#3fb950]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Kategori / Industri</label>
                        <select name="category" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#3fb950]">
                            <option value="Beauty & Skincare">Beauty & Skincare</option>
                            <option value="Fashion & Apparel">Fashion & Apparel</option>
                            <option value="Food & Beverage">Food & Beverage</option>
                            <option value="Agency & Creative Studio">Agency & Creative Studio</option>
                            <option value="Technology & Software">Technology & Software</option>
                            <option value="Retail & E-commerce">Retail & E-commerce</option>
                            <option value="General Business">Lainnya / General Business</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#8b949e] mb-1">Nomor WA Utama Brand (Opsional)</label>
                        <input type="text" name="brand_phone" value="{{ old('brand_phone') }}" placeholder="Contoh: 089988776655" class="w-full px-3 py-2 bg-[#161b22] border border-[#30363d] rounded-lg text-xs text-[#f0f6fc] focus:outline-none focus:border-[#3fb950]">
                    </div>
                </div>
            </div>

            <div class="p-3 bg-[#388bfd]/10 border border-[#388bfd]/30 text-[#58a6ff] text-xs rounded-xl flex items-start gap-2">
                <span class="text-sm">⏳</span>
                <span>Pendaftaran Brand & Akun Supervisor akan dikirimkan ke <strong>CEO/Owner</strong> untuk persetujuan (Approval). Setelah disetujui, Anda dapat langsung login & mengakses Dashboard Brand.</span>
            </div>

            <button type="submit" class="w-full py-3 bg-[#238636] hover:bg-[#2ea043] text-white font-bold text-sm rounded-xl shadow-lg transition">
                🚀 Kirim Pendaftaran Brand ke CEO
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-[#8b949e] border-t border-[#30363d] pt-4">
            Sudah punya akun? <a href="/login" class="text-[#58a6ff] font-semibold hover:underline">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
