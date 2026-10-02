@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <div class="inline-flex items-center gap-2 text-violet-700 bg-violet-50 border border-violet-100 rounded-full px-3 py-1 text-xs font-black mb-2">
            <x-icon name="sparkles" size="14"/> پوختەی ڕۆژ
        </div>
        <h1 class="text-3xl lg:text-4xl font-black tracking-tight">داشبۆرد</h1>
        <p class="text-slate-500 mt-1">کۆنتڕۆڵی فرۆشتن، کۆگا و قەرزەکان لە یەک شوێن.</p>
    </div>
    <a href="{{ route('pos') }}" class="btn-primary">
        <x-icon name="shopping-cart" size="18"/> فرۆشتنی نوێ
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['فرۆشتنی ئەمڕۆ',$stats['sales'],'IQD','shopping-cart','bg-violet-50 text-violet-700'],
            ['پارەی وەرگیراو',$stats['paid'],'IQD','wallet','bg-emerald-50 text-emerald-700'],
            ['قەرزی ئەمڕۆ',$stats['debt'],'IQD','alert','bg-rose-50 text-rose-700'],
            ['ژمارەی پسوڵە',$stats['count'],'','receipt','bg-amber-50 text-amber-700'],
        ];
    @endphp
    @foreach($cards as [$label,$value,$unit,$icon,$iconClass])
        <div class="card card-hover p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-slate-500 font-bold">{{ $label }}</div>
                    <div class="text-2xl lg:text-3xl font-black mt-2">{{ number_format($value) }} <span class="text-sm text-slate-400">{{ $unit }}</span></div>
                </div>
                <div class="stat-icon {{ $iconClass }}"><x-icon name="{{ $icon }}" size="21"/></div>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card p-5 flex items-center justify-between">
        <div><div class="text-slate-400 text-xs font-bold">کۆی بەرهەمەکان</div><div class="text-2xl font-black mt-1">{{ $stats['products'] }}</div></div>
        <div class="stat-icon bg-slate-100 text-slate-600"><x-icon name="package" size="20"/></div>
    </div>
    <div class="card p-5 flex items-center justify-between">
        <div><div class="text-slate-400 text-xs font-bold">کۆگای کەم</div><div class="text-2xl font-black mt-1 text-rose-600">{{ $stats['low'] }}</div></div>
        <div class="stat-icon bg-rose-50 text-rose-600"><x-icon name="alert" size="20"/></div>
    </div>
    <div class="card p-5 flex items-center justify-between">
        <div><div class="text-slate-400 text-xs font-bold">کڕیارەکان</div><div class="text-2xl font-black mt-1">{{ $stats['customers'] }}</div></div>
        <div class="stat-icon bg-sky-50 text-sky-600"><x-icon name="users" size="20"/></div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-black text-lg">دواین فرۆشتنەکان</h2>
            <p class="text-xs text-slate-400 mt-1">نوێترین مامەڵەکانی سیستەم</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn-soft text-sm">هەمووی ببینە <x-icon name="chevron-left" size="16"/></a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50/80">
                <tr class="border-b">
                    <th class="p-4 text-right">پسوڵە</th><th class="p-4 text-right">کڕیار</th><th class="p-4 text-right">کۆی گشتی</th><th class="p-4 text-right">دۆخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent as $s)
                    <tr class="border-b last:border-0">
                        <td class="p-4"><a class="font-black text-violet-700 hover:text-violet-900" href="{{ route('sales.show',$s) }}">{{ $s->invoice_number }}</a></td>
                        <td class="p-4 text-slate-600">{{ $s->customer?->name ?? 'کڕیاری نەناسراو' }}</td>
                        <td class="p-4 font-black">{{ number_format($s->total) }} <span class="text-xs text-slate-400">IQD</span></td>
                        <td class="p-4"><span class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">{{ $s->payment_status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
