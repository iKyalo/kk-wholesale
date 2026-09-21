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
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('stock_transfer_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();
        
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('received_quantity')->default(0);
        
            $table->timestamps();
        
            $table->unique(['stock_transfer_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
