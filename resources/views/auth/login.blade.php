<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#17152b">
    <title>چوونەژوورەوە | فرۆشگای شیرینی</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{font-family:"Noto Sans Arabic","Segoe UI",Tahoma,sans-serif}
        .login-bg{background:radial-gradient(circle at 15% 20%,rgba(168,85,247,.28),transparent 30%),radial-gradient(circle at 85% 80%,rgba(245,158,11,.18),transparent 28%),linear-gradient(135deg,#17152b,#0f1020)}
        .glass{background:rgba(255,255,255,.94);backdrop-filter:blur(20px);box-shadow:0 30px 80px rgba(0,0,0,.25)}
    </style>
</head>
<body class="min-h-screen login-bg flex items-center justify-center p-4">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-6 items-center">
        <div class="hidden lg:block text-white px-8">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/10 rounded-full px-4 py-2 text-sm mb-5">✨ سیستەمی بەڕێوەبردنی فرۆشگا</div>
            <h1 class="text-5xl font-black leading-tight">فرۆشگاکەت<br><span class="text-violet-300">بە جوانی بەڕێوەببە.</span></h1>
            <p class="text-slate-300 mt-5 max-w-md leading-8">فرۆشتن، کۆگا، کڕیار، قەرز و ڕاپۆرتەکان لە یەک داشبۆردی سادە و خێرا.</p>
        </div>

        <div class="glass rounded-[2rem] p-7 sm:p-9">
            <div class="text-center mb-7">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 text-white grid place-items-center text-3xl shadow-lg shadow-violet-500/25">✦</div>
                <h1 class="text-2xl font-black mt-4">فرۆشگای شیرینی</h1>
                <p class="text-slate-500 mt-1">بەخێربێیتەوە، تکایە بچۆ ژوورەوە</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-100 text-red-700 rounded-xl p-3 mb-4 text-sm font-bold">{{$errors->first()}}</div>
            @endif

            <form method="POST" action="{{route('login.store')}}" class="space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-bold">ئیمەیڵ</span>
                    <input name="email" type="email" value="{{old('email','admin@sweetshop.local')}}" placeholder="admin@example.com" class="w-full border border-slate-200 rounded-xl p-3.5 mt-1.5 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10" required>
                </label>
                <label class="block">
                    <span class="text-sm font-bold">وشەی نهێنی</span>
                    <input name="password" type="password" value="admin12345" placeholder="••••••••" class="w-full border border-slate-200 rounded-xl p-3.5 mt-1.5 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10" required>
                </label>
                <button class="w-full bg-slate-900 hover:bg-violet-700 text-white rounded-xl p-3.5 font-black transition shadow-lg shadow-slate-900/10">چوونەژوورەوە</button>
            </form>
            <p class="text-[11px] text-slate-400 mt-5 text-center">Admin: admin@sweetshop.local / admin12345</p>
        </div>
    </div>
</body>
</html>
