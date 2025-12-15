<?php

namespace App\Http\Requests\SalesReturn;

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
            'sale_id' => 'required',
            'deduct_on_sale_id' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'warehouse_id' => 'required',
            'notes' => 'nullable|string',
            'discount' => 'required',
            'tax' => 'required',
            'sales_total' => 'required',
            'sales_subtotal' => 'required',
            'sales_discount' => 'required',
            'subtotal' => 'required',
            'total' => 'required',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'required',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.is_stock' => 'required|boolean',
            'items.*.unit' => 'required',
            'items.*.quantity' => 'required',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable',
        ];
    }
}
