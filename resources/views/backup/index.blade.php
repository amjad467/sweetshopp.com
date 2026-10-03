@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div>
        <div class="inline-flex items-center gap-2 text-violet-700 bg-violet-50 border border-violet-100 rounded-full px-3 py-1 text-xs font-black mb-2">
            <x-icon name="database" size="14"/> پاراستنی داتا
        </div>
        <h1 class="text-2xl font-black">Backup ـی Database</h1>
        <p class="text-sm text-slate-500 mt-1">Backup بە شێوەی SQL.GZ دروست دەکرێت و دوای دروستبوون بە شێوەی download بۆ browser دەنێردرێت.</p>
    </div>
    <form method="POST" action="{{ route('backup.create') }}">
        @csrf
        <button class="btn-primary px-5 py-3" onclick="this.disabled=true;this.innerText='لە کاردایە...'">
            <x-icon name="download" size="18"/> دروستکردن و Download
        </button>
    </form>
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-5">
    <div class="card p-4"><div class="text-xs text-slate-400 font-bold">جۆری فایل</div><div class="font-black mt-1">.sql.gz</div></div>
    <div class="card p-4"><div class="text-xs text-slate-400 font-bold">شوێنی پاراستن</div><div class="font-black mt-1">storage/app/backups</div></div>
    <div class="card p-4"><div class="text-xs text-slate-400 font-bold">پاراستنی خۆکار</div><div class="font-black mt-1">دوایین 15 Backup</div></div>
</div>

<div class="card overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <h2 class="font-black">Backup ـەکانی هەنووکە</h2>
        <p class="text-xs text-slate-400 mt-1">لەسەر سیستەمەکە هەڵگیراون؛ دەتوانیت هەر یەکێکیان دابگریت.</p>
    </div>
    @if($files->count())
        <div class="divide-y divide-slate-100">
            @foreach($files as $file)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-black truncate">{{ basename($file) }}</div>
                        <div class="text-xs text-slate-400 mt-1">SQL Database Backup</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a class="btn-soft text-sm" href="{{ route('backup.download', basename($file)) }}">
                            <x-icon name="download" size="16"/> Download
                        </a>
                        <form method="POST" action="{{ route('backup.destroy', basename($file)) }}" onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم Backup ـە؟')">
                            @csrf @method('DELETE')
                            <button class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 grid place-items-center" title="سڕینەوە">
                                <x-icon name="trash" size="16"/>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8"><div class="empty-state">هێشتا هیچ Backup ـێک دروست نەکراوە.</div></div>
    @endif
</div>
@endsection
