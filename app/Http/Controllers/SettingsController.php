<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit', [
            'settings' => [
                'shop_name'      => Setting::get('shop_name', 'فرۆشگای شیرینی'),
                'phone'          => Setting::get('phone', ''),
                'address'        => Setting::get('address', ''),
                'currency'       => Setting::get('currency', 'IQD'),
                'invoice_footer' => Setting::get('invoice_footer', 'سوپاس بۆ سەردانتان'),
            ],
        ]);
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'shop_name'      => 'required|max:255',
            'phone'          => 'nullable|max:50',
            'address'        => 'nullable|max:255',
            'currency'       => 'required|max:10',
            'invoice_footer' => 'nullable|max:500',
        ]);

        foreach ($data as $k => $v) {
            Setting::set($k, $v);
        }

        return back()->with('success', 'ڕێکخستنەکان نوێکرانەوە.');
    }
}
