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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
        
            $table->text('description')->nullable();
        
            $table->string('category')->nullable();
            $table->string('unit')->default('piece');
        
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2);
        
            $table->boolean('is_active')->default(true);
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
