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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
        
            $table->string('transfer_number')->unique()->nullable();
        
            $table->foreignId('from_branch_id')
                ->nullable()
                ->constrained('branches')
                ->restrictOnDelete();
        
            $table->foreignId('to_branch_id')
                ->nullable()
                ->constrained('branches')
                ->restrictOnDelete();
        
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
        
            $table->string('status')->default('pending');
        
            $table->text('notes')->nullable();
        
            $table->timestamp('transferred_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
