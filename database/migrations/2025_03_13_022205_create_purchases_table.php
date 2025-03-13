<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->string('reference_number')->unique();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->decimal('total_amount', 10, 2);
        $table->string('status');
        $table->date('purchase_date');
        $table->text('notes')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->decimal('quantity', 10, 2);
        $table->decimal('unit_price', 10, 2);
        $table->decimal('total_price', 10, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
