<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TableStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique(); // № стола: 1, 2, 3...
            $table->string('qr_token')->unique(); // секретный токен в QR
            $table->string('qr_code')->nullable(); // путь к картинке QR
            $table->enum('status', array_column(TableStatus::cases(), 'value'))->default(TableStatus::AVAILABLE->value);
            $table->timestamps();

            $table->index('qr_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
