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
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            // foreign keys are typically indexed, but explicitly checking combined usage might be needed.
            // But basic single column indexes are good starts.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
