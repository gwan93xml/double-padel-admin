<?php

namespace App\Http\Requests\Production;

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
            'division_id' => 'required',
            'item_id' => 'required',
            'warehouse_id' => 'required',
            'date' => 'required',
            'quantity' => 'required',
            'notes' => 'nullable',
            'unit' => 'required',
            'status' => 'required',
            'items' => 'required|array',
            'chart_of_accounts' => 'nullable|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required',
            'items.*.remarks' => 'nullable|string|max:255',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.stock' => 'nullable',
            'items.*.notes' => 'nullable',

        ];
    }
}
