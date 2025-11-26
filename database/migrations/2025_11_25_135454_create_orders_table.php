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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('table_number')->nullable();
            $table->enum('delivery_type', ['full', 'partial'])->default('partial');
            $table->enum('status', [
                'pending',      // ожидает подтверждения
                'confirmed',    // подтвержден
                'preparing',    // готовится (хотя бы один item)
                'partially_ready', // часть блюд готова
                'ready',        // все готово
                'completed',    // завершен (все подано)
                'cancelled'     // отменен
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('waiter_id')->nullable()->constrained('users'); // основной официант или null, если заказ partial
            $table->timestamps();

            $table->index(['status', 'delivery_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
