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
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreignId('from_store_id')
                ->nullable()
                ->after('from_branch_id')
                ->constrained('stores')
                ->nullOnDelete();

            $table->foreignId('to_store_id')
                ->nullable()
                ->after('to_branch_id')
                ->constrained('stores')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_store_id']);
            $table->dropForeign(['to_store_id']);

            $table->dropColumn([
                'from_store_id',
                'to_store_id',
            ]);
        });
    }
};
