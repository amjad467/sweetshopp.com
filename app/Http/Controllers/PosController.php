<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category, Customer};
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        return view('pos.index', [
            'products' => $this->productQuery()->limit(60)->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function search(Request $r)
    {
        $d = $r->validate([
            'q' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query = $this->productQuery();
        $term = trim((string) ($d['q'] ?? ''));

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('sku', $term)
                    ->orWhere('barcode', $term)
                    ->orWhere('sku', 'like', '%' . $term . '%')
                    ->orWhere('barcode', 'like', '%' . $term . '%');
            });

            // Exact barcode/SKU first: ideal for USB/Bluetooth barcode scanners.
            $query->orderByRaw(
                'CASE WHEN barcode = ? THEN 0 WHEN sku = ? THEN 1 ELSE 2 END',
                [$term, $term]
            );
        }

        if (! empty($d['category_id'])) {
            $query->where('category_id', $d['category_id']);
        }

        $products = $query
            ->orderBy('name')
            ->limit(60)
            ->get();

        return response()->json([
            'products' => $products->map(fn (Product $p) => $this->productPayload($p))->values(),
        ]);
    }

    public function store(Request $r, SaleService $service)
    {
        $d = $r->validate([
            'customer_id'            => 'nullable|exists:customers,id',
            'discount'              => 'nullable|numeric|min:0',
            'paid_amount'           => 'required|numeric|min:0',
            'payment_method'        => 'required|in:cash,card,bank',
            'notes'                 => 'nullable',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.001',
            'items.*.quantity_grams'=> 'required|integer|min:1',
        ]);

        $sale = $service->create($d, (int) auth()->id());

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'فرۆشتن بە سەرکەوتوویی تۆمارکرا.');
    }

    private function productQuery()
    {
        return Product::query()
            ->where('is_active', true)
            ->select([
                'id', 'category_id', 'name', 'sku', 'barcode',
                'selling_price_per_kg', 'current_stock_grams', 'unit_type',
            ]);
    }

    private function productPayload(Product $p): array
    {
        return [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'name' => $p->name,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'price' => (float) $p->selling_price_per_kg,
            'stock' => (int) $p->current_stock_grams,
            'unit' => $p->unit_type,
        ];
    }
}
