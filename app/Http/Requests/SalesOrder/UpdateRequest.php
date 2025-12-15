<?php

namespace App\Http\Requests\SalesOrder;

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
            'purchase_order_number' => 'required',
            'type_of_tax_id' => 'required',
            'date' => 'required',
            'purchase_order_number' => 'nullable',
            'notes' => 'nullable|string',
            'tax' => 'required|numeric|min:0',
            'tax_is_auto' => 'required|boolean',
            'items' => 'required|array',
            'items.*.chart_of_account_id' => 'nullable',
            'items.*.item_id' => 'nullable',
            'items.*.item_name' => 'nullable',
            'items.*.quantity' => 'required',
            'items.*.discount' => 'required',
            'items.*.price' => 'required',
            'items.*.notes' => 'nullable',
            'discount' => 'nullable',
            'division_id' => 'nullable',
        ];
    }

    public function message()
    {
        return [
            'customer_id.required' => 'Kolom customer tidak boleh kosong.',
            'type_of_tax_id.required' => 'Kolom pajak tidak boleh kosong.',
            'date.required' => 'Kolom tanggal tidak boleh kosong.',
            'items.*.quantity.required' => 'Kolom kuantitas tidak boleh kosong.',
            'items.*.price.required' => 'Kolom harga tidak boleh kosong.',
        ];
    }
}
