<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Product, Customer};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $sales = Sale::whereDate('sale_date', $today);

        return view('dashboard.index', [
            'stats' => [
                'sales'     => (float) $sales->sum('total'),
                'paid'      => (float) $sales->sum('paid_amount'),
                'debt'      => (float) $sales->sum('debt_amount'),
                'count'     => $sales->count(),
                'products'  => Product::count(),
                'low'       => Product::whereColumn('current_stock_grams', '<=', 'minimum_stock_grams')->count(),
                'customers' => Customer::count(),
            ],
            'recent' => Sale::with('customer')->latest()->limit(8)->get(),
        ]);
    }
}
