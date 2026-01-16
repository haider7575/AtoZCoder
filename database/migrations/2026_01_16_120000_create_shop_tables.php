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
        // Update Users Table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])->default('staff')->after('email');
            $table->softDeletes();
        });

        // Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->string('status')->default('active'); // active, inactive
            $table->softDeletes();
            $table->timestamps();
        });

        // Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, confirmed, shipped, cancelled
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        // Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Price at time of order
            $table->timestamps();
        });

        // Shipments Table
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('label_url')->nullable();
            $table->json('provider_response')->nullable();
            $table->string('status')->default('created'); // created, failed, delivered
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'deleted_at']);
        });
    }
};
