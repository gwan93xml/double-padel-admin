<?php

namespace App\Http\Requests\Sale;

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
            'customer_id' => 'required',
            'type_of_tax_id' => 'required',
            'warehouse_id' => 'required',
            'sale_date' => 'required',
            'division_id' => 'nullable',
            'sales_order_number' => 'nullable',
            'purchase_order_number' => 'nullable',
            'discount'  => 'required',
            'sales_discount' => 'required',
            'sales_discount_percent' => 'required',
            'sales_order_number' => 'nullable',
            'payment_method' => 'required',
            'tax' => 'required|numeric|min:0',
            'tax_is_auto' => 'required|boolean',
            'tax_invoice_number' => 'required|string',
            'due_date' => $this->payment_method == 'Cash' ? 'nullable' : 'required',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.item_name' => 'nullable',
            'items.*.quantity' => 'required',
            'items.*.discount' => 'required',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable|string',
        ];
    }
}
