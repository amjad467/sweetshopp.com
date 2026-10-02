@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-black mb-5">قەرزەکان</h1>

@if($customers->isEmpty())
    <div class="card p-8 text-center">
        <div class="text-5xl mb-3">✅</div>
        <div class="text-slate-500 text-lg">هیچ قەرزداری چالاک نییە</div>
        <p class="text-slate-400 text-sm mt-2">کاتێک فرۆشتنی قەرزدار ئەنجام بدەیت لێرە پیشان دەدرێن.</p>
    </div>
@else
    {{-- کۆی گشتی قەرز --}}
    <div class="card p-5 mb-5 flex items-center justify-between">
        <div>
            <div class="text-slate-500 text-sm">کۆی گشتی قەرزەکان</div>
            <div class="text-3xl font-black text-red-600">
                {{ number_format($customers->sum('balance')) }} IQD
            </div>
        </div>
        <div class="text-slate-400 text-sm">
            {{ $customers->count() }} کڕیاری قەرزدار
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b bg-slate-50">
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">کڕیار</th>
                    <th class="p-3 text-center">مۆبایل</th>
                    <th class="p-3 text-center">قەرز</th>
                    <th class="p-3 text-center">کردار</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $i => $c)
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="p-3 text-slate-400">{{ $i + 1 }}</td>
                    <td class="p-3 font-semibold">{{ $c->name }}</td>
                    <td class="p-3 text-center text-slate-600">{{ $c->phone ?? '—' }}</td>
                    <td class="p-3 text-center text-red-600 font-bold">
                        {{ number_format($c->balance) }} IQD
                    </td>
                    <td class="p-3 text-center">
                        <a class="inline-block bg-indigo-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition"
                           href="{{ route('customers.ledger', $c) }}">
                            پارەدان
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection