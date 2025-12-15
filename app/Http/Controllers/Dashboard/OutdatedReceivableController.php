<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutdatedReceivableController extends Controller
{
    public function __invoke(Request $request)
    {
        $receivables = Receivable::where('status', Receivable::STATUS_UNPAID)
            ->where('due_date', '<', now())
            ->whereNot('remaining_amount', 0)
            ->orderBy('date', 'asc')
            ->get();
        return new JsonResource($receivables);
    }
}
