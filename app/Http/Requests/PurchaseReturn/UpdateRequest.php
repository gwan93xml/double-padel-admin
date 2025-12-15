<?php

namespace App\Http\Requests\PurchaseReturn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'origin_invoice' => 'nullable',
            'cut_on_invoice' => 'nullable',
            'vendor_id' => 'required',
            'warehouse_id' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.item_name' => 'required',
            'items.*.unit' => 'required',
            'items.*.quantity' => 'required|numeric',
            'items.*.subtotal' => 'required|numeric',
            'items.*.total' => 'required|numeric',
            'items.*.remarks' => 'nullable',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
        ];
    }
}
