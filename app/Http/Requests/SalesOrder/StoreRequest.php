<?php

namespace App\Http\Requests\SalesOrder;

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
            'customer_id' => 'required',
            'purchase_order_number' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'notes' => 'nullable|string',
            'tax' => 'required|numeric|min:0',
            'tax_is_auto' => 'required|boolean',
            'items' => 'required|array',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
            'items.*.discount' => 'required',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable',
            'discount' => 'nullable',
            'division_id' => 'nullable',
            
        ];
    }
}
