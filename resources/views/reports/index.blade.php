@extends('layouts.app')
@section('content')
<div class="mb-7">
    <div class="inline-flex items-center gap-2 text-violet-700 bg-violet-50 border border-violet-100 rounded-full px-3 py-1 text-xs font-black mb-2">
        <x-icon name="bar-chart" size="14"/> شیکردنەوە
    </div>
    <h1 class="text-3xl font-black">ڕاپۆرتەکان</h1>
    <p class="text-slate-500 mt-1">داتاکانی فرۆشتن و کۆگا بە شێوەیەکی ڕوون ببینە.</p>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <a class="card card-hover p-6 group" href="{{route('reports.sales')}}">
        <div class="stat-icon bg-violet-50 text-violet-700 mb-5"><x-icon name="receipt" size="21"/></div>
        <div class="font-black text-lg">ڕاپۆرتی فرۆشتن</div><p class="text-sm text-slate-400 mt-1">مامەڵە و کۆی فرۆشتن</p>
    </a>
    <a class="card card-hover p-6 group" href="{{route('reports.profit')}}">
        <div class="stat-icon bg-emerald-50 text-emerald-700 mb-5"><x-icon name="wallet" size="21"/></div>
        <div class="font-black text-lg">ڕاپۆرتی قازانج</div><p class="text-sm text-slate-400 mt-1">قازانج و نرخەکان</p>
    </a>
    <a class="card card-hover p-6 group" href="{{route('reports.stock')}}">
        <div class="stat-icon bg-amber-50 text-amber-700 mb-5"><x-icon name="package" size="21"/></div>
        <div class="font-black text-lg">ڕاپۆرتی کۆگا</div><p class="text-sm text-slate-400 mt-1">بڕ و کەمبوونەوەی کۆگا</p>
    </a>
    <a class="card card-hover p-6 group" href="{{route('reports.debts')}}">
        <div class="stat-icon bg-rose-50 text-rose-700 mb-5"><x-icon name="wallet" size="21"/></div>
        <div class="font-black text-lg">ڕاپۆرتی قەرز</div><p class="text-sm text-slate-400 mt-1">کڕیار و باڵانسی قەرز</p>
    </a>
</div>
@endsection
