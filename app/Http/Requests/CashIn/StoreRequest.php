<?php

namespace App\Http\Requests\CashIn;

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
            'division_id' => 'required|exists:divisions,id',
            'received_from' => 'required',
            'date' => 'required|date',
            'description' => 'required',
            'details' => 'required|array',
            'details.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.credit' => 'required|numeric|min:0',
            'details.*.description' => 'nullable|string|max:255',
            'account_receivable_header_id' => 'nullable|exists:account_receivable_headers,id',
            'sale_id' => 'nullable|exists:sales,id',
            'sales_deposit_id' => 'nullable|exists:sales_deposits,id',
        ];
    }

    public function messages(){
        return [
            'received_from.required' => 'Kolom penerima tidak boleh kosong.',
            'date.required' => 'Kolom tanggal tidak boleh kosong.',
            'date.date' => 'Kolom tanggal harus berupa tanggal yang valid.',
            'description.required' => 'Kolom deskripsi tidak boleh kosong.'
        ];
    }
}
