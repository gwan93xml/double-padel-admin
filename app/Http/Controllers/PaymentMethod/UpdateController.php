<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'group' => 'required|string',
            'code' => 'required|string|unique:payment_methods,code,' . $paymentMethod->id,
            'name' => 'required|string',
            'transaction_fee' => 'required|integer',
            'image' => 'nullable|string',
            'how_to_pay' => 'nullable|array',
        ]);

        $paymentMethod->update([
            'group' => $request->group,
            'code' => $request->code,
            'name' => $request->name,
            'transaction_fee' => $request->transaction_fee,
            'image' => $request->image,
            'how_to_pay' => $request->how_to_pay,
        ]);

        return new JsonResource($paymentMethod);
    }
}
