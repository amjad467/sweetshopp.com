<?php

namespace App\Services;

use App\Models\{Product, StockMovement};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function adjust(Product $p, int $grams, string $type, int $userId, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($p, $grams, $type, $userId, $notes) {
            $locked = Product::whereKey($p->id)->lockForUpdate()->firstOrFail();
            if ($locked->current_stock_grams + $grams < 0) {
                throw ValidationException::withMessages(['quantity_grams' => 'کۆگا ناتوانێت منفی بێت.']);
            }
            $locked->increment('current_stock_grams', $grams);
            return StockMovement::create([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity_grams' => $grams,
                'notes' => $notes,
                'user_id' => $userId,
            ]);
        });
    }
}
