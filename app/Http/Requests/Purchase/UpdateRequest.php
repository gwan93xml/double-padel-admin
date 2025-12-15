<?php

namespace App\Http\Requests\Purchase;

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
            'vendor_id' => 'required',
            'type_of_tax_id' => 'required',
            'warehouse_id' => 'required',
            'purchase_date' => 'required',
            'discount'  => 'required',
            'purchase_discount' => 'required|numeric|min:0',
            'purchase_discount_percent' => 'required|numeric|min:0|max:100',
            'invoice_number' => 'required|unique:purchases,invoice_number,' . $this->purchase->id,
            'purchase_order_number' => 'nullable|unique:purchases,purchase_order_number,' . $this->purchase->id,
            'no' => 'required',
            'division_id' => 'nullable',
            'payment_method' => 'required',
            'due_date' => $this->payment_method == 'Cash' ? 'nullable' : 'required',
            'note' => 'nullable|string',
            'tax' => 'required|numeric|min:0',
            'tax_is_auto' => 'required|boolean',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.discount' => 'required',
            'items.*.item_name' => 'nullable',
            'items.*.unit' => 'nullable',
            'items.*.quantity' => 'required',
            'items.*.is_stock' => 'required|boolean',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable|string',
            'shipping_cost' => 'required|numeric',
            'stamp_duty' => 'required|numeric',
            'is_pinned' => 'nullable|boolean',
            'tax_invoice_number' => 'required|string',
        ];
    }
}
