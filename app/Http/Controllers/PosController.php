<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category, Customer};
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $r)
    {
        $products = Product::where('is_active', 1)
            ->when($r->search, fn ($q) => $q
                ->where('name', 'like', '%' . $r->search . '%')
                ->orWhere('barcode', $r->search))
            ->orderBy('name')
            ->get();

        return view('pos.index', [
            'products'   => $products,
            'categories' => Category::where('is_active', 1)->get(),
            'customers'  => Customer::where('is_active', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $r, SaleService $service)
    {
        $d = $r->validate([
            'customer_id'           => 'nullable|exists:customers,id',
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
}
