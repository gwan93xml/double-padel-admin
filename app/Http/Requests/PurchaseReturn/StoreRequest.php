<?php

namespace App\Http\Requests\PurchaseReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'purchase_id' => 'required',
            'deduct_on_purchase_id' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'warehouse_id' => 'required',
            'notes' => 'nullable|string',
            'discount' => 'required',
            'tax' => 'required',
            'purchases_total' => 'required',
            'purchases_subtotal' => 'required',
            'purchases_discount' => 'required',
            'subtotal' => 'required',
            'total' => 'required',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'required',
            'items.*.is_stock' => 'required|boolean',
            'items.*.unit' => 'required',
            'items.*.quantity' => 'required',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable',
        ];
    }
    
}
