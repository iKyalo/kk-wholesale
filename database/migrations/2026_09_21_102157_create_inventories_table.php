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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
        
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(10);
        
            $table->timestamps();
        
            // Prevent duplicate inventory records
            $table->unique(['store_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
