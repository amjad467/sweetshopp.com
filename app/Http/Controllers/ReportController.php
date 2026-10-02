<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Product, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * ماوەی ڕاپۆرت لە query string بخوێنەوە.
     */
    private function range(Request $r): array
    {
        $from = $r->input('from', now()->startOfMonth()->toDateString());
        $to   = $r->input('to', now()->toDateString());

        return [$from, $to];
    }

    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $r)
    {
        [$from, $to] = $this->range($r);

        $sales = Sale::with('customer')
            ->whereBetween('sale_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('reports.sales', compact('sales', 'from', 'to'));
    }

    public function profit(Request $r)
    {
        [$from, $to] = $this->range($r);

        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.sale_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity_grams) grams'),
                DB::raw('SUM(sale_items.subtotal) sales'),
                DB::raw('SUM(sale_items.cost_price) cost'),
                DB::raw('SUM(sale_items.profit) profit')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('profit')
            ->get();

        return view('reports.profit', compact('rows', 'from', 'to'));
    }

    public function stock()
    {
        return view('reports.stock', [
            'products' => Product::with('category')->orderBy('name')->get(),
        ]);
    }

    public function debts()
    {
        $customers = Customer::with('debtTransactions')
            ->get()
            ->filter(fn ($c) => $c->balance > 0);

        return view('reports.debts', compact('customers'));
    }
}
