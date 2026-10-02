<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index', [
            'sales' => Sale::with('customer', 'user')->latest()->paginate(20),
        ]);
    }

    public function show(Sale $sale)
    {
        return view('sales.show', [
            'sale' => $sale->load('items.product', 'customer', 'user', 'payments'),
        ]);
    }
}
