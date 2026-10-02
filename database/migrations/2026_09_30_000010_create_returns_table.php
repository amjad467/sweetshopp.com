<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('returns',function(Blueprint $t){$t->id();$t->foreignId('sale_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->decimal('total_amount',14,2)->default(0);$t->text('reason')->nullable();$t->dateTime('returned_at');$t->timestamps();}); } public function down(): void { Schema::dropIfExists('returns'); } };
