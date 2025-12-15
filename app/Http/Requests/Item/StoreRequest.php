<?php

namespace App\Http\Requests\Item;

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
            'code' => 'required|string|max:255|unique:items',
            'name' => 'required|string|max:255',
            'item_category_id' => 'required',
            'unit' => 'required|string',
            'purchase_price' => 'required',
            'selling_price' => 'required',
            'description' => 'nullable',
            'picture' => 'nullable',
            'file' => 'nullable',
            'units' => 'nullable|array',
            'units.*.name' => 'required|string|max:255',
            'units.*.conversion' => 'required|numeric',
            'units.*.is_purchase_price' => 'nullable|boolean',
            'is_linked' => 'required|boolean',
            'linked_item_id' => 'required_if:is_linked,true|nullable|exists:items,id',
            'unit_report_1' => 'required',
            'unit_report_2' => 'required',
            'vendor_id' => 'nullable|exists:vendors,id',
        ];
    }
}
