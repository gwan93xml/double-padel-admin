<?php

namespace App\Http\Requests\StockMovement;

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
            'date' => 'required|date',
            'division_id' => 'required',
            'warehouse_out_id' => 'required',
            'warehouse_in_id' => 'required',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
            'items.*.unit' => 'required',
            'remarks' => 'nullable',
        ];
    }
}
