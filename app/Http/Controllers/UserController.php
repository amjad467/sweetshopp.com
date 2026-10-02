<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::latest()->get()]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name'      => 'required|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:Super Admin,Admin,Cashier,Stock Manager',
            'is_active' => 'boolean',
        ]);

        $d['password'] = Hash::make($d['password']);
        User::create($d);

        return redirect()->route('users.index')->with('success', 'بەکارهێنەر زیادکرا.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $r, User $user)
    {
        $d = $r->validate([
            'name'      => 'required|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|in:Super Admin,Admin,Cashier,Stock Manager',
            'is_active' => 'boolean',
        ]);

        if (! $d['password']) {
            unset($d['password']);
        } else {
            $d['password'] = Hash::make($d['password']);
        }

        $user->update($d);

        return redirect()->route('users.index')->with('success', 'بەکارهێنەر نوێکرایەوە.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'ناتوانیت خۆت بسڕیتەوە.']);
        }

        $user->delete();

        return back()->with('success', 'بەکارهێنەر سڕایەوە.');
    }
}
