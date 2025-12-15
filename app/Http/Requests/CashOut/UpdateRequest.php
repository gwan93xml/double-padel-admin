<?php

namespace App\Http\Requests\CashOut;

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
            'division_id' => 'required|exists:divisions,id',
            'paid_to' => 'required',
            'date' => 'required|date',
            'description' => 'required',
            'details' => 'required|array',
            'details.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.credit' => 'required|numeric|min:0',
            'details.*.description' => 'nullable|string|max:255',
            'pay_debt_header_id' => 'nullable|exists:pay_debt_headers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'purchase_deposit_id' => 'nullable|exists:purchase_deposits,id',
        ];
    }

    public function messages()
    {
        return [
            'paid_to.required' => 'Kolom dibayar kepada tidak boleh kosong.',
            'date.required' => 'Kolom tanggal tidak boleh kosong.',
            'date.date' => 'Kolom tanggal harus berupa tanggal yang valid.',
            'description.required' => 'Kolom deskripsi tidak boleh kosong.'
        ];
    }
}
