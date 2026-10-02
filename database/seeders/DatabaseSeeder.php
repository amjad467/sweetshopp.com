<?php

namespace Database\Seeders;

use App\Models\{Category, Product, Setting, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ١. دروستکردن یان نوێکردنەوەی ئەدمین
        User::updateOrCreate(
            ['email' => 'admin@sweetshop.local'],
            [
                'name'      => 'بەڕێوەبەر',
                'password'  => Hash::make('admin12345'),
                'role'      => 'Super Admin',
                'is_active' => true,
            ]
        );

        // ٢. دروستکردنی هاوپۆلەکان (Categories)
        $categories = ['کۆلێجە', 'بسکیت', 'کێک', 'شیرینی', 'شەکرەمەنی'];

        foreach ($categories as $n) {
            Category::firstOrCreate(
                ['name' => $n],
                [
                    // بەکارهێنانی Str::slug لەگەڵ شێوازی Unicode بۆ پشتیوانی پیتی کوردی
                    'slug'      => Str::slug($n, '-', null),
                    'is_active' => true,
                ]
            );
        }

        // ٣. ڕێکخستنەکان (Settings)
        Setting::set('shop_name', 'فرۆشگای شیرینی');
        Setting::set('currency', 'IQD');
        Setting::set('invoice_footer', 'سوپاس بۆ سەردانتان');
    }
}