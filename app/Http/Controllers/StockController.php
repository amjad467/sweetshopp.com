<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return view('stock.index', [
            'products' => Product::with('category')->orderBy('name')->paginate(25),
        ]);
    }

    public function adjust(Request $r, StockService $service)
    {
        $d = $r->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity_grams' => 'required|integer',
            'type'           => 'required|in:purchase,damage,waste,adjustment',
            'notes'          => 'nullable',
        ]);

        $p = Product::findOrFail($d['product_id']);
        $grams = (int) $d['quantity_grams'];

        // بڕی damage و waste بکە بە منفی
        if (in_array($d['type'], ['damage', 'waste'], true)) {
            $grams = -abs($grams);
        }


        $service->adjust($p, $grams, $d['type'], auth()->id(), $d['notes'] ?? null);

        return back()->with('success', 'کۆگا نوێکرایەوە.');
    }
}
