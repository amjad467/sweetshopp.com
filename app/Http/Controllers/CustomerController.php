<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DebtTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $r)
    {
        $customers = Customer::when($r->search, fn ($q) => $q
                ->where('name', 'like', '%' . $r->search . '%')
                ->orWhere('phone', 'like', '%' . $r->search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name'            => 'required|max:255',
            'phone'           => 'nullable|max:50',
            'address'         => 'nullable',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit'    => 'nullable|numeric|min:0',
            'notes'           => 'nullable',
            'is_active'       => 'boolean',
        ]);
        DB::transaction(function () use ($d) {
            $customer = Customer::create($d);
            if ((float) ($d['opening_balance'] ?? 0) > 0) {
                DebtTransaction::create([
                    'customer_id' => $customer->id, 'type' => 'debt', 'amount' => $d['opening_balance'],
                    'description' => 'باڵانسی سەرەتایی', 'user_id' => auth()->id(), 'transaction_date' => now(),
                ]);
            }
        });

        return redirect()->route('customers.index')->with('success', 'کڕیار زیادکرا.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $r, Customer $customer)
    {
        $customer->update($r->validate([
            'name'            => 'required|max:255',
            'phone'           => 'nullable|max:50',
            'address'         => 'nullable',
            'credit_limit'    => 'nullable|numeric|min:0',
            'notes'           => 'nullable',
            'is_active'       => 'boolean',
        ]));

        return redirect()->route('customers.index')->with('success', 'کڕیار نوێکرایەوە.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'کڕیار سڕایەوە.');
    }

    public function ledger(Customer $customer)
    {
        return view('customers.ledger', [
            'customer' => $customer,
            'sales'    => $customer->sales()->latest()->paginate(15),
        ]);
    }
}
