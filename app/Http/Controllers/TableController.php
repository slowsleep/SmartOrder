<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Table;
use App\Enums\TableStatus;

class TableController extends Controller
{
    public function init($qrToken)
    {
        $table = Table::where('qr_token', $qrToken)
            ->where('status', TableStatus::AVAILABLE->value)
            ->firstOrFail();

        // Создаем сессию стола на 2 часа
        session()->put('table_session', [
            'table_id' => $table->id,
            'table_number' => $table->number,
            'expires_at' => now()->addHours(2),
        ]);

        // Редирект на страницу меню
        return redirect()->route('menu');
    }
}
