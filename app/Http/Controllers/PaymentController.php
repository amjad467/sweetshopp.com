<?php

namespace App\Http\Controllers;

use App\Models\{Payment, Customer, DebtTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function debts()
    {
        $customers = Customer::query()->get()
            ->map(function ($customer) {
                $customer->setAttribute('current_balance', $customer->balance);
                return $customer;
            })
            ->filter(fn ($customer) => $customer->current_balance > 0)
            ->sortByDesc('current_balance')->values();

        return view('debts.index', compact('customers'));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($d) {
            $customer = Customer::whereKey($d['customer_id'])->lockForUpdate()->firstOrFail();
            $balance = $customer->balance;
            if ((float) $d['amount'] > $balance) {
                throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'پارەدان ناتوانێت لە قەرزی ئێستا زیاتر بێت.']);
            }

            $p = Payment::create([
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'amount' => $d['amount'],
                'payment_method' => $d['payment_method'],
                'payment_date' => now(),
                'notes' => $d['notes'] ?? null,
            ]);
            DebtTransaction::create([
                'customer_id' => $customer->id,
                'payment_id' => $p->id,
                'type' => 'payment',
                'amount' => $d['amount'],
                'description' => $d['notes'] ?? 'پارەدانی قەرز',
                'user_id' => auth()->id(),
                'transaction_date' => now(),
            ]);
        });

        return back()->with('success', 'پارەدان تۆمارکرا.');
    }
}
