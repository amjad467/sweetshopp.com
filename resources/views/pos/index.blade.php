@extends('layouts.app')

@section('content')
<div x-data="posApp()" x-cloak>
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 mb-5">
        <div>
            <div class="inline-flex items-center gap-2 text-violet-700 bg-violet-50 border border-violet-100 rounded-full px-3 py-1 text-xs font-black mb-2">
                <x-icon name="shopping-cart" size="14"/> خاڵی فرۆشتن
            </div>
            <h1 class="text-3xl font-black">POS ـی فرۆشتن</h1>
            <p class="text-slate-500 mt-1">بەرهەم زیاد بکە، بڕی گرام/دانە دیاری بکە و پسوڵە تەواو بکە.</p>
        </div>
        <div class="text-xs font-bold text-slate-400 bg-white border border-slate-200 rounded-xl px-3 py-2">
            نرخەکان بە کیلۆیە؛ حساب بە گرام دەکرێت.
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-5 items-start">
        <div class="xl:col-span-2">
            <div class="card p-4 mb-4">
                <div class="relative">
                    <x-icon name="search" size="19" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input x-model="search" placeholder="گەڕان بە ناوی بەرهەم یان Barcode..." class="field pr-10">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[68vh] overflow-y-auto soft-scroll pl-1">
                @foreach($products as $p)
                    <button type="button"
                        @click="add({id:{{$p->id}},name:'{{addslashes($p->name)}}',price:{{$p->selling_price_per_kg}},unit:'{{$p->unit_type}}',stock:{{$p->current_stock_grams}}})"
                        x-show="!search || @js($p->name).toLowerCase().includes(search.toLowerCase())"
                        class="card card-hover p-4 text-right group">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 grid place-items-center group-hover:bg-violet-100 transition">
                                <x-icon name="package" size="19"/>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">{{ $p->unit_type }}</span>
                        </div>
                        <div class="font-black mt-3 leading-6">{{ $p->name }}</div>
                        <div class="text-violet-700 font-black mt-2">{{ number_format($p->selling_price_per_kg) }} <span class="text-xs">IQD/کگ</span></div>
                        <div class="text-xs text-slate-400 mt-1">کۆگا: {{ number_format($p->current_stock_grams/1000,2) }} kg</div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="card p-4 xl:sticky xl:top-24">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="font-black text-lg">سەبەت</h2>
                    <p class="text-xs text-slate-400" x-text="items.length + ' بەرهەم'"></p>
                </div>
                <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-700 grid place-items-center"><x-icon name="shopping-cart" size="18"/></span>
            </div>

            <div class="space-y-3 max-h-80 overflow-y-auto soft-scroll pl-1">
                <template x-if="!items.length">
                    <div class="py-10 text-center text-slate-400">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 grid place-items-center mb-3"><x-icon name="shopping-cart" size="24"/></div>
                        <div class="font-bold">سەبەت بەتاڵە</div>
                        <div class="text-xs mt-1">لە لیستی بەرهەمەکان بەرهەمێک هەڵبژێرە.</div>
                    </div>
                </template>

                <template x-for="(i,idx) in items" :key="idx">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                        <div class="flex justify-between gap-2">
                            <b class="truncate" x-text="i.name"></b>
                            <button type="button" @click="items.splice(idx,1)" class="text-slate-400 hover:text-red-600"><x-icon name="x" size="17"/></button>
                        </div>
                        <div class="flex gap-2 mt-2 items-center">
                            <input type="number" min="1" x-model.number="i.grams" class="field py-2 w-24">
                            <span class="text-xs text-slate-500" x-text="i.unit==='piece' ? 'دانە' : 'گرام'"></span>
                            <span class="mr-auto font-black text-violet-700" x-text="money(i.total)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t mt-4 pt-4 space-y-3">
                <div class="flex justify-between text-sm"><span class="text-slate-500">کۆی بنەڕەتی</span><b x-text="money(subtotal())"></b></div>
                <label class="block">
                    <span class="text-sm font-bold">داشکاندن</span>
                    <input type="number" min="0" x-model.number="discount" class="field mt-1" placeholder="0">
                </label>
                <div class="rounded-2xl bg-slate-900 text-white p-4 flex justify-between items-end">
                    <span class="text-sm text-slate-300">کۆی گشتی</span>
                    <b class="text-2xl" x-text="money(total())"></b>
                </div>

                <form method="POST" action="{{ route('pos.store') }}" @submit="sync($event)">
                    @csrf
                    <div id="hidden-items"></div>
                    <input type="hidden" name="discount" x-model="discount">

                    <label class="block mt-2">
                        <span class="text-sm font-bold">کڕیار</span>
                        <select name="customer_id" class="field mt-1">
                            <option value="">نەناسراو</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </label>

                    <label class="block mt-2">
                        <span class="text-sm font-bold">پارەی دراو</span>
                        <input required type="number" min="0" step="0.01" name="paid_amount" class="field mt-1" placeholder="0">
                    </label>

                    <label class="block mt-2">
                        <span class="text-sm font-bold">جۆری پارەدان</span>
                        <select name="payment_method" class="field mt-1">
                            <option value="cash">نەقد</option>
                            <option value="card">کارت</option>
                            <option value="bank">بانک</option>
                        </select>
                    </label>

                    <button class="btn-primary w-full mt-4 py-3.5" :disabled="!items.length">
                        <x-icon name="check" size="18"/> تەواوکردنی فرۆشتن
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function posApp(){
    return {
        search:'', items:[], discount:0,
        add(p){
            let i=this.items.find(x=>x.id===p.id);
            if(i) i.grams += (p.unit==='piece' ? 1 : 100);
            else this.items.push({...p, grams:p.unit==='piece'?1:100});
        },
        subtotal(){ return this.items.reduce((s,i)=>s+(i.unit==='piece'?i.grams*i.price:i.grams*i.price/1000),0) },
        total(){ return Math.max(0,this.subtotal()-Number(this.discount||0)) },
        money(v){ return new Intl.NumberFormat('en-US').format(Math.round(v))+' IQD' },
        sync(e){
            let c=document.getElementById('hidden-items'); c.innerHTML='';
            this.items.forEach((i,n)=>{
                [['product_id',i.id],['quantity',i.unit==='piece'?i.grams:i.grams/1000],['quantity_grams',i.grams]].forEach(([k,v])=>{
                    let x=document.createElement('input'); x.type='hidden'; x.name=`items[${n}][${k}]`; x.value=v; c.appendChild(x)
                })
            })
        }
    }
}
</script>
@endsection
