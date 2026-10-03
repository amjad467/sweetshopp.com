<!doctype html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7c3aed">
    <title>{{ $title ?? 'فرۆشگای شیرینی' }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --brand: #7c3aed;
            --brand-dark: #5b21b6;
            --ink: #172033;
            --muted: #64748b;
            --surface: #ffffff;
            --page: #f8f7fc;
            --line: #e9e7f0;
        }
        html { scroll-behavior: smooth; }
        body {
            font-family: "Noto Sans Arabic", "Segoe UI", Tahoma, sans-serif;
            background: var(--page);
            color: var(--ink);
        }
        .app-sidebar {
            background:
                radial-gradient(circle at 20% 10%, rgba(168,85,247,.18), transparent 28%),
                linear-gradient(180deg, #17152b 0%, #0f1020 100%);
            box-shadow: -12px 0 40px rgba(15, 23, 42, .08);
        }
        .brand-mark {
            background: linear-gradient(135deg, #a855f7, #7c3aed 55%, #5b21b6);
            box-shadow: 0 12px 25px rgba(124,58,237,.28);
        }
        .nav-link {
            display:flex; align-items:center; gap:.75rem;
            min-height:44px; padding:.7rem .85rem;
            border:1px solid transparent; border-radius:.9rem;
            color:#cbd5e1; transition:all .18s ease;
        }
        .nav-link:hover { color:#fff; background:rgba(255,255,255,.07); transform:translateX(-2px); }
        .nav-link.active {
            color:#fff;
            background:linear-gradient(90deg, rgba(124,58,237,.28), rgba(168,85,247,.10));
            border-color:rgba(167,139,250,.20);
            box-shadow:inset -3px 0 0 #a78bfa;
        }
        .topbar {
            background:rgba(255,255,255,.88);
            backdrop-filter:blur(16px);
            border-bottom:1px solid rgba(226,232,240,.8);
        }
        .page-shell { max-width: 1600px; margin:0 auto; }
        .card {
            background:var(--surface);
            border:1px solid var(--line);
            border-radius:1.25rem;
            box-shadow:0 8px 30px rgba(30,27,75,.045);
        }
        .card-hover { transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
        .card-hover:hover { transform:translateY(-2px); box-shadow:0 14px 38px rgba(30,27,75,.09); border-color:#ddd6fe; }
        .btn-primary {
            display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            background:linear-gradient(135deg,#7c3aed,#6d28d9);
            color:#fff; border-radius:.9rem; padding:.7rem 1rem; font-weight:800;
            box-shadow:0 8px 18px rgba(124,58,237,.20); transition:.18s ease;
        }
        .btn-primary:hover { transform:translateY(-1px); box-shadow:0 12px 24px rgba(124,58,237,.26); }
        .btn-soft {
            display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            background:#f5f3ff; color:#6d28d9; border:1px solid #ede9fe;
            border-radius:.9rem; padding:.65rem .9rem; font-weight:800;
        }
        .field {
            width:100%; border:1px solid #e2e8f0; background:#fff;
            border-radius:.85rem; padding:.72rem .9rem; outline:none;
            transition:border-color .18s, box-shadow .18s;
        }
        .field:focus {
            border-color:#a78bfa; box-shadow:0 0 0 4px rgba(124,58,237,.09);
        }
        input:not([type="hidden"]), select, textarea {
            border-color:#e2e8f0;
            border-radius:.85rem;
            transition:border-color .18s, box-shadow .18s;
        }
        input:not([type="hidden"]):focus, select:focus, textarea:focus {
            outline:none;
            border-color:#a78bfa;
            box-shadow:0 0 0 4px rgba(124,58,237,.09);
        }
        table thead th { color:#64748b; font-size:.78rem; font-weight:800; white-space:nowrap; }
        table tbody tr { transition:background .15s ease; }
        table tbody tr:hover { background:#faf9ff; }
        .stat-icon { width:44px; height:44px; border-radius:14px; display:grid; place-items:center; }
        .empty-state { padding:2rem 1rem; text-align:center; color:#94a3b8; font-weight:800; border:1px dashed #e2e8f0; border-radius:1rem; background:#f8fafc; }
        .soft-scroll::-webkit-scrollbar { width:8px; height:8px; }
        .soft-scroll::-webkit-scrollbar-thumb { background:#d8d3e6; border-radius:20px; }
        @media (max-width: 1023px) {
            .mobile-main { padding-bottom: 84px; }
        }
    </style>
</head>
<body>
<div class="min-h-screen" x-data="{open:false}">
    <aside class="app-sidebar fixed inset-y-0 right-0 z-40 w-72 text-white p-4 hidden lg:flex lg:flex-col">
        <div class="flex items-center gap-3 px-2 py-2 mb-5">
            <div class="brand-mark w-11 h-11 rounded-2xl grid place-items-center">
                <x-icon name="sparkles" size="23"/>
            </div>
            <div class="min-w-0">
                <div class="font-black text-lg truncate">فرۆشگای شیرینی</div>
                <div class="text-xs text-slate-400">سیستەمی بەڕێوەبردن</div>
            </div>
        </div>

        <nav class="space-y-1 overflow-y-auto soft-scroll pr-1">
            @php
                $items = [
                    ['dashboard','dashboard','داشبۆرد'],
                    ['shopping-cart','pos','POS ـی فرۆشتن'],
                    ['receipt','sales.index','فرۆشتنەکان'],
                    ['package','products.index','بەرهەمەکان'],
                    ['layers','categories.index','جۆرەکان'],
                    ['users','customers.index','کڕیارەکان'],
                    ['database','stock.index','کۆگا'],
                    ['wallet','debts.index','قەرزەکان'],
                    ['bar-chart','reports.index','ڕاپۆرتەکان'],
                ];
            @endphp
            <div class="text-[11px] font-bold text-slate-500 px-3 py-2">سەرەکی</div>
            @foreach($items as [$icon,$routeName,$label])
                <a class="nav-link {{ request()->routeIs($routeName) ? 'active' : '' }}" href="{{ route($routeName) }}">
                    <x-icon :name="$icon" size="19"/>
                    <span>{{ $label }}</span>
                </a>
            @endforeach

            @if(in_array(auth()->user()->role, ['Super Admin','Admin'], true))
                <div class="text-[11px] font-bold text-slate-500 px-3 py-3 mt-2 border-t border-white/10">بەڕێوەبردن</div>
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <x-icon name="users" size="19"/> <span>بەکارهێنەران</span>
                </a>
                <a class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}" href="{{ route('backup.index') }}">
                    <x-icon name="database" size="19"/> <span>Backup</span>
                </a>
                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}">
                    <x-icon name="settings" size="19"/> <span>ڕێکخستنەکان</span>
                </a>
            @endif
        </nav>

        <div class="mt-auto pt-4">
            <div class="rounded-2xl bg-white/5 border border-white/10 p-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 grid place-items-center font-black">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-bold truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-400">{{ auth()->user()->role }}</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="lg:mr-72 min-h-screen mobile-main">
        <header class="topbar sticky top-0 z-30">
            <div class="page-shell px-4 lg:px-8 h-[76px] flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="open=true" class="lg:hidden w-11 h-11 rounded-xl bg-white border border-slate-200 text-slate-700 grid place-items-center shadow-sm">
                        <x-icon name="menu" size="22"/>
                    </button>
                    <div class="min-w-0">
                        <div class="text-xs text-slate-400 font-bold hidden sm:block">بەخێربێیت 👋</div>
                        <div class="font-black text-lg truncate">{{ $header ?? 'داشبۆرد' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="hidden sm:flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 text-violet-700 grid place-items-center font-black">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-right leading-tight">
                            <div class="text-sm font-bold">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button title="چوونەدەرەوە" class="w-10 h-10 rounded-xl text-slate-500 hover:text-red-600 hover:bg-red-50 grid place-items-center transition">
                            <x-icon name="log-out" size="19"/>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="page-shell p-4 lg:p-8">
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-800 p-4 flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-emerald-100 grid place-items-center"><x-icon name="check" size="18"/></span>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 text-red-700 p-4">
                    <div class="font-black mb-1">تکایە ئەم خاڵانە چاک بکە:</div>
                    <ul class="list-disc mr-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 lg:hidden" style="display:none">
        <div @click="open=false" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
        <aside x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               class="absolute right-0 top-0 bottom-0 w-[min(88vw,340px)] app-sidebar text-white p-4 shadow-2xl overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="brand-mark w-11 h-11 rounded-2xl grid place-items-center"><x-icon name="sparkles" size="23"/></div>
                    <div class="font-black">فرۆشگای شیرینی</div>
                </div>
                <button @click="open=false" class="w-10 h-10 rounded-xl bg-white/10 grid place-items-center"><x-icon name="x" size="21"/></button>
            </div>
            <nav class="space-y-1">
                @foreach($items as [$icon,$routeName,$label])
                    <a @click="open=false" class="nav-link {{ request()->routeIs($routeName) ? 'active' : '' }}" href="{{ route($routeName) }}">
                        <x-icon :name="$icon" size="19"/><span>{{ $label }}</span>
                    </a>
                @endforeach
                @if(in_array(auth()->user()->role, ['Super Admin','Admin'], true))
                    <div class="text-[11px] font-bold text-slate-500 px-3 py-3 mt-2 border-t border-white/10">بەڕێوەبردن</div>
                    <a class="nav-link" href="{{ route('users.index') }}"><x-icon name="users" size="19"/><span>بەکارهێنەران</span></a>
                    <a class="nav-link" href="{{ route('backup.index') }}"><x-icon name="database" size="19"/><span>Backup</span></a>
                    <a class="nav-link" href="{{ route('settings.edit') }}"><x-icon name="settings" size="19"/><span>ڕێکخستنەکان</span></a>
                @endif
            </nav>
        </aside>
    </div>
</div>
</body>
</html>
