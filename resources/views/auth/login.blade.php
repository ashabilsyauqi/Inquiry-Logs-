<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-gray-100 px-4">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-900/50">
                <span class="text-3xl">🚀</span>
            </div>
            <h1 class="text-2xl font-bold text-white">CRM Admin Panel</h1>
            <p class="text-xs text-slate-400 mt-1">Masuk untuk mengelola sales pipeline & WhatsApp leads</p>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 bg-emerald-900/50 border border-emerald-500/50 text-emerald-300 text-xs rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-900/50 border border-rose-500/50 text-rose-300 text-xs rounded-xl">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="contoh@difitech.id" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                    <input type="checkbox" name="remember" checked class="rounded border-slate-700 bg-slate-800 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-slate-300 font-medium">Ingat Saya (Tetap Login)</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/40 transition">
                Masuk ke CRM
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400 border-t border-slate-800 pt-4">
            Belum punya akun? <a href="/register" class="text-emerald-400 font-semibold hover:underline">Daftar Akun Baru</a>
            <p class="text-[10px] text-slate-500 mt-2">*Akun baru memerlukan persetujuan (Approval) dari CEO/Owner.</p>
        </div>
    </div>
</body>
</html>
