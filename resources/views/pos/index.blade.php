@extends('layouts.app')

@section('content')
<div x-data="posApp()" x-init="init()" x-cloak>
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 mb-5">
        <div>
            <div class="inline-flex items-center gap-2 text-violet-700 bg-violet-50 border border-violet-100 rounded-full px-3 py-1 text-xs font-black mb-2">
                <x-icon name="shopping-cart" size="14"/> خاڵی فرۆشتن
            </div>
            <h1 class="text-3xl font-black">POS ـی فرۆشتن</h1>
            <p class="text-slate-500 mt-1">بەرهەمێک هەڵبژێرە یان Barcode ـەکە scan بکە، پاشان بڕ و پارەدان تەواو بکە.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-500">
            <span class="bg-white border border-slate-200 rounded-xl px-3 py-2">Barcode: Scan + Enter</span>
            <span class="bg-white border border-slate-200 rounded-xl px-3 py-2">نرخ: IQD/کگ</span>
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-5 items-start">
        <div class="xl:col-span-2 min-w-0">
            <div class="card p-4 mb-4 space-y-3">
                <div class="relative">
                    <x-icon name="search" size="19" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input
                        x-ref="search"
                        x-model="search"
                        @input.debounce.250ms="loadProducts()"
                        @keydown.enter.prevent="scanOrSearch()"
                        autocomplete="off"
                        autofocus
                        placeholder="گەڕان بە ناو، SKU یان Barcode..."
                        class="field pr-10 text-base"
                    >
                    <button x-show="search" type="button" @click="clearSearch()" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 grid place-items-center" aria-label="پاککردنەوە">
                        <x-icon name="x" size="16"/>
                    </button>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto soft-scroll pb-1">
                    <button type="button" @click="category=null; loadProducts()" :class="category===null ? 'bg-violet-700 text-white border-violet-700' : 'bg-white text-slate-600 border-slate-200'" class="shrink-0 px-4 py-2 rounded-xl border text-xs font-black transition">
                        هەموو
                    </button>
                    @foreach($categories as $c)
                        <button type="button" @click="category={{ $c->id }}; loadProducts()" :class="category==={{ $c->id }} ? 'bg-violet-700 text-white border-violet-700' : 'bg-white text-slate-600 border-slate-200'" class="shrink-0 px-4 py-2 rounded-xl border text-xs font-black transition">
                            {{ $c->name }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400" x-text="loading ? 'گەڕان...' : products.length + ' بەرهەم'" ></span>
                    <span x-show="search" class="text-violet-600 font-bold">Enter = هەوڵی زیادکردنی Barcode/SKU</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-3 max-h-[68vh] overflow-y-auto soft-scroll pl-1">
                <template x-for="p in products" :key="p.id">
                    <button type="button" @click="add(p)" class="card card-hover p-4 text-right group min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 grid place-items-center group-hover:bg-violet-100 transition shrink-0">
                                <x-icon name="package" size="19"/>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400" x-text="p.unit === 'piece' ? 'دانە' : 'کگ'" ></span>
                        </div>
                        <div class="font-black mt-3 leading-6 line-clamp-2" x-text="p.name"></div>
                        <div class="text-violet-700 font-black mt-2"><span x-text="money(p.price)"></span> <span class="text-xs">IQD/کگ</span></div>
                        <div class="text-[11px] text-slate-400 mt-1 truncate" x-text="p.barcode ? 'Barcode: ' + p.barcode : (p.sku ? 'SKU: ' + p.sku : 'Barcode/SKU نییە')"></div>
                        <div class="text-xs text-slate-400 mt-1" x-text="p.unit === 'piece' ? 'کۆگا: ' + Math.floor(p.stock) + ' دانە' : 'کۆگا: ' + (p.stock/1000).toFixed(2) + ' kg'"></div>
                    </button>
                </template>

                <div x-show="!loading && !products.length" class="col-span-full empty-state">
                    هیچ بەرهەمێک نەدۆزرایەوە. ناو، SKU، Barcode یان گروپێکی تر تاقی بکەرەوە.
                </div>
                <div x-show="loading" class="col-span-full empty-state">تکایە چاوەڕێ بکە...</div>
            </div>
        </div>

        <div class="card p-4 xl:sticky xl:top-24">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="font-black text-lg">سەبەت</h2>
                    <p class="text-xs text-slate-400" x-text="items.length + ' بەرهەم'" ></p>
                </div>
                <button x-show="items.length" type="button" @click="items=[]" class="text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg px-2 py-1">پاککردنەوە</button>
            </div>

            <div class="space-y-3 max-h-80 overflow-y-auto soft-scroll pl-1">
                <template x-if="!items.length">
                    <div class="py-10 text-center text-slate-400">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 grid place-items-center mb-3"><x-icon name="shopping-cart" size="24"/></div>
                        <div class="font-bold">سەبەت بەتاڵە</div>
                        <div class="text-xs mt-1">لە بەرهەمەکان هەڵبژێرە یان Barcode scan بکە.</div>
                    </div>
                </template>

                <template x-for="(i,idx) in items" :key="i.id">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                        <div class="flex justify-between gap-2">
                            <div class="min-w-0"><b class="truncate block" x-text="i.name"></b><span class="text-[10px] text-slate-400" x-text="i.barcode || i.sku || ''"></span></div>
                            <button type="button" @click="items.splice(idx,1)" class="text-slate-400 hover:text-red-600"><x-icon name="x" size="17"/></button>
                        </div>
                        <div class="flex gap-2 mt-2 items-center">
                            <input type="number" min="1" :max="i.unit==='piece' ? Math.floor(i.stock) : i.stock" x-model.number="i.grams" @change="i.grams=Math.max(1, Number(i.grams||1))" class="field py-2 w-24">
                            <span class="text-xs text-slate-500" x-text="i.unit==='piece' ? 'دانە' : 'گرام'"></span>
                            <span class="mr-auto font-black text-violet-700" x-text="money(lineTotal(i))"></span>
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

                    <button class="btn-primary w-full mt-4 py-3.5" :disabled="!items.length || total() <= 0">
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
        search: '',
        category: null,
        products: @js($products->map(fn($p) => [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'name' => $p->name,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'price' => (float) $p->selling_price_per_kg,
            'stock' => (int) $p->current_stock_grams,
            'unit' => $p->unit_type,
        ])->values()),
        items: [],
        discount: 0,
        loading: false,
        searchTimer: null,
        init(){ this.$nextTick(() => this.$refs.search?.focus()); },
        async loadProducts(){
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(async () => {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.search.trim()) params.set('q', this.search.trim());
                    if (this.category) params.set('category_id', this.category);
                    const res = await fetch(@js(route('pos.search')) + '?' + params.toString(), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                    if (!res.ok) throw new Error('Search failed');
                    const data = await res.json();
                    this.products = data.products || [];
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            }, 120);
        },
        async scanOrSearch(){
            const term = this.search.trim();
            if (!term) return;
            this.loading = true;
            try {
                const params = new URLSearchParams({q: term});
                if (this.category) params.set('category_id', this.category);
                const res = await fetch(@js(route('pos.search')) + '?' + params.toString(), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                if (!res.ok) throw new Error('Search failed');
                const data = await res.json();
                const list = data.products || [];
                const exact = list.find(p => p.barcode === term || p.sku === term);
                if (exact) {
                    this.add(exact);
                    this.search = '';
                    await this.loadProducts();
                } else {
                    this.products = list;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
                this.$nextTick(() => this.$refs.search?.focus());
            }
        },
        clearSearch(){ this.search=''; this.loadProducts(); this.$nextTick(() => this.$refs.search?.focus()); },
        add(p){
            const i=this.items.find(x=>x.id===p.id);
            if(i) i.grams += (p.unit==='piece' ? 1 : 100);
            else this.items.push({...p, grams:p.unit==='piece'?1:100});
        },
        lineTotal(i){ return i.unit==='piece' ? i.grams*i.price : i.grams*i.price/1000; },
        subtotal(){ return this.items.reduce((s,i)=>s+this.lineTotal(i),0); },
        total(){ return Math.max(0,this.subtotal()-Number(this.discount||0)); },
        money(v){ return new Intl.NumberFormat('en-US').format(Math.round(v))+' IQD'; },
        sync(e){
            let c=document.getElementById('hidden-items'); c.innerHTML='';
            this.items.forEach((i,n)=>{
                [['product_id',i.id],['quantity',i.unit==='piece'?i.grams:i.grams/1000],['quantity_grams',i.grams]].forEach(([k,v])=>{
                    let x=document.createElement('input'); x.type='hidden'; x.name=`items[${n}][${k}]`; x.value=v; c.appendChild(x);
                });
            });
        }
    }
}
</script>
@endsection
