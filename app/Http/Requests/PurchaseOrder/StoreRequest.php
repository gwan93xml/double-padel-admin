<?php

namespace App\Http\Requests\PurchaseOrder;

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
            'vendor_id' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'division_id' => 'nullable',
            'discount'  => 'required',
            'remarks' => 'nullable|string',
            'tax' => 'required|numeric|min:0',
            'tax_is_auto' => 'required|boolean',
            'items' => 'required|array',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'nullable',
            'items.*.quantity' => 'required',
            'items.*.price' => 'required',
            'items.*.discount' => 'required',
            'items.*.unit' => 'nullable',
            'items.*.remarks' => 'nullable',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.notes' => 'nullable',
            'items.*.is_stock' => 'required|boolean',
        ];
    }
}
