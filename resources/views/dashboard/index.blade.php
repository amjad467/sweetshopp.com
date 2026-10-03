@extends('layouts.app')

@section('content')
<div x-data="{ selected: null }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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

    @php
        $cards = [
            ['sales','فرۆشتنی ئەمڕۆ',$stats['sales'],'IQD','shopping-cart','bg-violet-50 text-violet-700','وردەکاری فرۆشتنەکانی ئەمڕۆ'],
            ['paid','پارەی وەرگیراو',$stats['paid'],'IQD','wallet','bg-emerald-50 text-emerald-700','پارە و وەسڵەکانی ئەمڕۆ'],
            ['debt','قەرزی ماوە',$stats['debt'],'IQD','alert','bg-rose-50 text-rose-700','قەرزەکان و دۆخی قەرز'],
            ['count','ژمارەی پسوڵە',$stats['count'],'','receipt','bg-amber-50 text-amber-700','لیستی پسوڵەکانی ئەمڕۆ'],
            ['products','کۆی بەرهەمەکان',$stats['products'],'','package','bg-slate-100 text-slate-700','نوێترین بەرهەمەکان'],
            ['low','کۆگای کەم',$stats['low'],'','alert','bg-rose-50 text-rose-600','بەرهەمە پێویستەکان بە ئاگاداری'],
            ['customers','کڕیارەکان',$stats['customers'],'','users','bg-sky-50 text-sky-600','نوێترین کڕیارەکان'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($cards as [$key,$label,$value,$unit,$icon,$iconClass,$hint])
            <button type="button"
                    @click="selected = selected === '{{ $key }}' ? null : '{{ $key }}'"
                    :class="selected === '{{ $key }}' ? 'ring-2 ring-violet-300 border-violet-200 -translate-y-0.5' : ''"
                    class="card card-hover p-5 text-right w-full group transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-violet-300">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm text-slate-500 font-bold">{{ $label }}</div>
                        <div class="text-2xl lg:text-3xl font-black mt-2">
                            {{ is_numeric($value) ? number_format($value) : $value }}
                            <span class="text-sm text-slate-400">{{ $unit }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-2 font-bold">{{ $hint }}</div>
                    </div>
                    <div class="stat-icon {{ $iconClass }} shrink-0 group-hover:scale-105 transition-transform">
                        <x-icon name="{{ $icon }}" size="21"/>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                    <span class="text-violet-600">کلیک بکە بۆ بینینی وردەکاری</span>
                    <x-icon name="chevron-left" size="15"/>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Clickable detail area --}}
    <div x-show="selected" x-transition class="card overflow-hidden" style="display:none">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
            <div>
                <h2 class="font-black text-lg" x-text="
                    selected === 'sales' ? 'وردەکاری فرۆشتنی ئەمڕۆ' :
                    selected === 'paid' ? 'پارەی وەرگیراوی ئەمڕۆ' :
                    selected === 'debt' ? 'قەرزی ماوەی هەموو کڕیارەکان' :
                    selected === 'count' ? 'پسوڵەکانی ئەمڕۆ' :
                    selected === 'products' ? 'نوێترین بەرهەمەکان' :
                    selected === 'low' ? 'کۆگای کەم' :
                    'نوێترین کڕیارەکان'
                "></h2>
                <p class="text-xs text-slate-400 mt-1">لەسەر کارتەکە کلیک بکە بۆ داخستن.</p>
            </div>
            <button type="button" @click="selected=null" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 grid place-items-center">
                <x-icon name="x" size="18"/>
            </button>
        </div>

        <div class="p-4 lg:p-5">
            {{-- Sales / receipt details --}}
            <div x-show="selected === 'sales' || selected === 'count'" x-transition>
                @if($todaySales->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50/80"><tr class="border-b">
                                <th class="p-3 text-right">پسوڵە</th>
                                <th class="p-3 text-right">کڕیار</th>
                                <th class="p-3 text-right">کۆی گشتی</th>
                                <th class="p-3 text-right">وەرگیراو</th>
                                <th class="p-3 text-right">قەرز</th>
                                <th class="p-3 text-right">دۆخ</th>
                            </tr></thead>
                            <tbody>
                            @foreach($todaySales as $s)
                                <tr class="border-b last:border-0">
                                    <td class="p-3"><a class="font-black text-violet-700 hover:text-violet-900" href="{{ route('sales.show',$s) }}">{{ $s->invoice_number }}</a></td>
                                    <td class="p-3 text-slate-600">{{ $s->customer?->name ?? 'کڕیاری نەناسراو' }}</td>
                                    <td class="p-3 font-black">{{ number_format($s->total) }} <span class="text-xs text-slate-400">IQD</span></td>
                                    <td class="p-3 font-bold text-emerald-600">{{ number_format($s->paid_amount) }}</td>
                                    <td class="p-3 font-bold {{ (float)$s->debt_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ number_format($s->debt_amount) }}</td>
                                    <td class="p-3"><span class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">{{ $s->payment_status }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">هیچ فرۆشتنێک بۆ ئەمڕۆ تۆمار نەکراوە.</div>
                @endif
            </div>

            {{-- Payments --}}
            <div x-show="selected === 'paid'" x-transition>
                @if($todayPayments->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($todayPayments as $payment)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 grid place-items-center shrink-0"><x-icon name="wallet" size="18"/></div>
                                    <div class="min-w-0">
                                        <div class="font-black truncate">{{ $payment->customer?->name ?? 'کڕیار' }}</div>
                                        <div class="text-xs text-slate-400">{{ $payment->sale?->invoice_number ? 'پسوڵە '.$payment->sale->invoice_number : 'پارەدانی قەرز' }}</div>
                                    </div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="font-black text-emerald-700">{{ number_format($payment->amount) }} IQD</div>
                                    <div class="text-[11px] text-slate-400">{{ optional($payment->payment_date)->format('H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">ئەمڕۆ هیچ پارەدانێک تۆمار نەکراوە.</div>
                @endif
            </div>

            {{-- Debts --}}
            <div x-show="selected === 'debt'" x-transition>
                @if($debtCustomers->count())
                    <div class="space-y-3">
                        @foreach($debtCustomers as $customer)
                            <a href="{{ route('customers.ledger', $customer) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 p-4 hover:bg-rose-50/50 hover:border-rose-100 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 grid place-items-center shrink-0"><x-icon name="users" size="18"/></div>
                                    <div class="min-w-0">
                                        <div class="font-black truncate">{{ $customer->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $customer->phone ?: 'ژمارەی تەلەفۆن نییە' }}</div>
                                    </div>
                                </div>
                                <div class="text-left shrink-0">
                                    <div class="font-black text-rose-600">{{ number_format($customer->dashboard_balance) }} IQD</div>
                                    <div class="text-[11px] text-slate-400">بینینی ledger</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">هیچ قەرزێکی ماوە نییە. 🎉</div>
                @endif
            </div>

            {{-- Products --}}
            <div x-show="selected === 'products'" x-transition>
                @if($latestProducts->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($latestProducts as $product)
                            <a href="{{ route('products.edit', $product) }}" class="rounded-2xl border border-slate-100 p-4 hover:bg-violet-50/40 hover:border-violet-100 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 grid place-items-center"><x-icon name="package" size="18"/></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-black truncate">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-400 truncate">{{ $product->sku ?: 'SKU نییە' }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4"><a href="{{ route('products.index') }}" class="btn-soft text-sm">هەموو بەرهەمەکان <x-icon name="chevron-left" size="16"/></a></div>
                @endif
            </div>

            {{-- Low stock --}}
            <div x-show="selected === 'low'" x-transition>
                @if($lowStockProducts->count())
                    <div class="space-y-3">
                        @foreach($lowStockProducts as $product)
                            <a href="{{ route('products.edit', $product) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-rose-100 bg-rose-50/40 p-4 hover:bg-rose-50 transition">
                                <div class="min-w-0"><div class="font-black truncate">{{ $product->name }}</div><div class="text-xs text-slate-400">کەمتر یان یەکسان بە سنووری کەمترین کۆگا</div></div>
                                <div class="text-left shrink-0"><div class="font-black text-rose-600">{{ number_format($product->current_stock_grams / 1000, 2) }} kg</div><div class="text-[11px] text-slate-400">کۆگای ئێستا</div></div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4"><a href="{{ route('stock.index') }}" class="btn-soft text-sm">بینینی کۆگا <x-icon name="chevron-left" size="16"/></a></div>
                @else
                    <div class="empty-state">هیچ بەرهەمێکی کەم‌کۆگا نییە. 🎉</div>
                @endif
            </div>

            {{-- Customers --}}
            <div x-show="selected === 'customers'" x-transition>
                @if($latestCustomers->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($latestCustomers as $customer)
                            <a href="{{ route('customers.ledger', $customer) }}" class="rounded-2xl border border-slate-100 p-4 hover:bg-sky-50/40 hover:border-sky-100 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 grid place-items-center"><x-icon name="users" size="18"/></div>
                                    <div class="min-w-0 flex-1"><div class="font-black truncate">{{ $customer->name }}</div><div class="text-xs text-slate-400 truncate">{{ $customer->phone ?: 'تەلەفۆن نییە' }}</div></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4"><a href="{{ route('customers.index') }}" class="btn-soft text-sm">هەموو کڕیارەکان <x-icon name="chevron-left" size="16"/></a></div>
                @endif
            </div>
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
</div>
@endsection
