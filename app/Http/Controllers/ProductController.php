<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category, StockMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function rules($id = null)
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => ['nullable','string','max:100', $id ? 'unique:products,sku,'.$id : 'unique:products,sku'],
            'barcode' => ['nullable','string','max:100', $id ? 'unique:products,barcode,'.$id : 'unique:products,barcode'],
            'description' => 'nullable|string',
            'purchase_price_per_kg' => 'required|numeric|min:0',
            'selling_price_per_kg' => 'required|numeric|min:0',
            'minimum_stock_grams' => 'required|integer|min:0',
            'current_stock_grams' => 'required|integer|min:0',
            'unit_type' => 'required|in:kg,gram,piece',
            'is_active' => 'boolean',
        ];
    }

    public function index(Request $r)
    {
        $q = Product::with('category')
            ->when($r->search, function ($x) use ($r) {
                $x->where(function ($query) use ($r) {
                    $query->where('name', 'like', '%' . $r->search . '%')
                        ->orWhere('barcode', $r->search)
                        ->orWhere('sku', $r->search);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $q,
        ]);
    }

    public function create()
    {
        // بەرهەمێکی نوێ بۆ form ـی create
        $product = new Product();

        return view('products.create', [
            'product' => $product,
            'categories' => Category::where('is_active', 1)->get(),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate($this->rules());

        $p = DB::transaction(fn () => Product::create($d));

        if ($p->current_stock_grams > 0) {
            StockMovement::create([
                'product_id' => $p->id,
                'type' => 'opening',
                'quantity_grams' => $p->current_stock_grams,
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'بەرهەم بە سەرکەوتوویی زیادکرا.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::where('is_active', 1)->get(),
        ]);
    }

    public function update(Request $r, Product $product)
    {
        $d = $r->validate($this->rules($product->id));

        $old = $product->current_stock_grams;

        $product->update($d);

        $diff = $product->current_stock_grams - $old;

        if ($diff != 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity_grams' => $diff,
                'user_id' => auth()->id(),
                'notes' => 'گۆڕینی کۆگا لە بەشی بەرهەم',
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'بەرهەم نوێکرایەوە.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with(
            'success',
            'بەرهەم سڕایەوە.'
        );
    }
}