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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->string('reference_number')->unique();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->decimal('total_amount', 10, 2);
        $table->string('status');
        $table->date('sale_date');
        $table->text('notes')->nullable();
        $table->timestamps();
    });

    Schema::create('sale_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('sales');
    }
};
