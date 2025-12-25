<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TableStatus;
use App\Traits\ApiResponse;

class TableController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $freeTables = \App\Models\Table::where('status', TableStatus::AVAILABLE)->get();

        return $this->success($freeTables);
    }
}
