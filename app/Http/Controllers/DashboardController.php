<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Product, Customer, Payment, DebtTransaction};

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $sales = Sale::query()->whereDate('sale_date', $today);

        // پارەی ڕاستەقینەی وەرگیراو: هەموو Payment ـەکانی ئەمڕۆ.
        $todayPaid = (float) Payment::query()
            ->whereDate('payment_date', $today)
            ->sum('amount');

        // قەرزی ڕاستەقینەی ئێستا: opening + مانەوەی قەرزی پسوڵەکان
        // - پارەدانی قەرزی دواتر + adjustment.
        $debtCustomers = Customer::query()
            ->where('is_active', true)
            ->select('customers.*')
            ->selectSub(
                Sale::query()
                    ->selectRaw('COALESCE(SUM(debt_amount), 0)')
                    ->whereColumn('sales.customer_id', 'customers.id'),
                'sales_debt_total'
            )
            ->selectSub(
                Payment::query()
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('payments.customer_id', 'customers.id')
                    ->whereNull('payments.sale_id'),
                'later_payment_total'
            )
            ->selectSub(
                DebtTransaction::query()
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('debt_transactions.customer_id', 'customers.id')
                    ->where('type', 'adjustment'),
                'adjustment_total'
            )
            ->get()
            ->map(function ($customer) {
                $customer->dashboard_balance = max(
                    0,
                    round(
                        (float) $customer->opening_balance
                        + (float) $customer->sales_debt_total
                        - (float) $customer->later_payment_total
                        + (float) $customer->adjustment_total,
                        2
                    )
                );
                return $customer;
            })
            ->filter(fn ($customer) => $customer->dashboard_balance > 0)
            ->sortByDesc('dashboard_balance')
            ->values();

        $currentDebt = (float) $debtCustomers->sum('dashboard_balance');

        $todaySales = (clone $sales)
            ->with('customer')
            ->latest('sale_date')
            ->limit(30)
            ->get();

        $todayPayments = Payment::query()
            ->with(['customer', 'sale'])
            ->whereDate('payment_date', $today)
            ->latest('payment_date')
            ->limit(30)
            ->get();

        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->whereColumn('current_stock_grams', '<=', 'minimum_stock_grams')
            ->orderBy('current_stock_grams')
            ->limit(20)
            ->get();

        return view('dashboard.index', [
            'stats' => [
                'sales' => (float) (clone $sales)->sum('total'),
                'paid' => round($todayPaid, 2),
                'debt' => round($currentDebt, 2),
                'count' => $sales->count(),
                'products' => Product::query()->where('is_active', true)->count(),
                'low' => Product::query()->where('is_active', true)->whereColumn('current_stock_grams', '<=', 'minimum_stock_grams')->count(),
                'customers' => Customer::query()->where('is_active', true)->count(),
            ],
            'todaySales' => $todaySales,
            'todayPayments' => $todayPayments,
            'debtCustomers' => $debtCustomers->take(30),
            'lowStockProducts' => $lowStockProducts,
            'latestProducts' => Product::query()->where('is_active', true)->latest()->limit(10)->get(),
            'latestCustomers' => Customer::query()->where('is_active', true)->latest()->limit(10)->get(),
            'recent' => Sale::with('customer')->latest()->limit(8)->get(),
        ]);
    }
}
