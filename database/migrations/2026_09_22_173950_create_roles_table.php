<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        
        DB::table('roles')->insert([
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Branch Manager',
                'slug' => 'branch-manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Store Manager',
                'slug' => 'store-manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
