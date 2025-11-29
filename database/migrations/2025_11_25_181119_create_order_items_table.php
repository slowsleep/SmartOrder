<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OrderItemStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2); // цена на момент заказа
            $table->enum('status', array_column(OrderItemStatus::cases(), 'value'))->default(OrderItemStatus::PENDING->value);
            $table->foreignId('cook_id')->constrained('users');
            $table->foreignId('served_by')->constrained('users'); // кто конкретно подавал
            $table->timestamp('served_at')->nullable(); // когда подано
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
