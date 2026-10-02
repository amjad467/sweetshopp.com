<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- ئەمه زیاد کرا
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $d = $r->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = Str::lower($d['email']) . '|' . $r->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors(['email' => 'تکایە کەمێک چاوەڕێ بکە و دووبارە هەوڵ بدە.']);
        }

        $u = User::where('email', $d['email'])
            ->where('is_active', 1)
            ->first();

        if (! $u || ! Hash::check($d['password'], $u->password)) {
            RateLimiter::hit($key, 60);
            return back()
                ->withErrors(['email' => 'ئیمەیڵ یان وشەی نهێنی هەڵەیە.'])
                ->withInput();
        }

        RateLimiter::clear($key);

        // ١. دروستکردنەوەی ئاسایشی Session
        $r->session()->regenerate();

        // ٢. لۆگینکردنی فەرمیی بەکارهێنەر لە سیستەمی Authدا
        Auth::login($u);

        // ٣. ناردنی بەکارهێنەر بۆ دێشبۆرد
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $r)
    {
        // لۆگ ئاوت کردنی بەکارهێنەر لە Auth
        Auth::logout();

        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login');
    }
}