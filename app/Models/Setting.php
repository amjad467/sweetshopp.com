<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * بەدەستهێنانی بەهای ڕێکخستن.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $v = static::where('key', $key)->value('value');

        return $v === null ? $default : $v;
    }

    /**
     * دانان/نوێکردنەوەی بەهای ڕێکخستن.
     */
    public static function set(string $key, mixed $value): static
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
