<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CRM MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-gray-100 px-4 py-8">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-blue-900/50">
                <span class="text-3xl">📝</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Daftar Akun Admin</h1>
            <p class="text-xs text-slate-400 mt-1">Registrasi Sales Admin untuk mengelola saluran WA Pipeline</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-900/50 border border-rose-500/50 text-rose-300 text-xs rounded-xl">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/register" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Misal: Ahmad CS" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="ahmad@difitech.id" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required placeholder="Ketik ulang password" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="p-3 bg-amber-900/30 border border-amber-500/30 text-amber-300 text-xs rounded-xl flex items-start gap-2">
                <span>⚠️</span>
                <span>Pendaftaran akun Anda memerlukan **persetujuan (Approval)** dari CEO/Owner sebelum dapat digunakan untuk masuk ke CRM.</span>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-900/40 transition">
                Kirim Pendaftaran ke CEO
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400 border-t border-slate-800 pt-4">
            Sudah punya akun? <a href="/login" class="text-blue-400 font-semibold hover:underline">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
