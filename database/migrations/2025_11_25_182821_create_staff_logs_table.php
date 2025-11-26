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
        Schema::create('staff_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', [
                'order_created',
                'order_status_changed',
                'order_updated',
                'order_item_created',
                'order_item_status_changed',
                'order_item_updated',
                'product_created',
                'product_updated',
                'product_status_changed',
                'user_created',
                'user_updated'
            ]);
            $table->string('entity_type'); // 'App\\Models\\Order', 'App\\Models\\Product'
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable(); // данные до изменения
            $table->json('new_values')->nullable(); // данные после изменения
            $table->text('description')->nullable(); // человеко-читаемое описание
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_logs');
    }
};
