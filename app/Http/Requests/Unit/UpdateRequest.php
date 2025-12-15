<?php

namespace App\Http\Requests\Unit;

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
            'name' => 'required|string|max:255|unique:units,name,' . $this->unit->id,
            'sub_units' => 'required|array',
            'sub_units.*.name' => 'required|string|max:255',
            'sub_units.*.conversion' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Kolom nama wajib diisi.',
            'name.string' => 'Kolom nama harus berupa string.',
            'name.max' => 'Kolom nama tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Nama unit sudah ada.',
            'sub_units.required' => 'Kolom sub satuan wajib diisi.',
            'sub_units.array' => 'Kolom sub satuan harus berupa array.',
            'sub_units.*.name.required' => 'Kolom nama sub satuan wajib diisi.',
            'sub_units.*.name.string' => 'Kolom nama sub satuan harus berupa string.',
            'sub_units.*.name.max' => 'Kolom nama sub satuan tidak boleh lebih dari 255 karakter.',
            'sub_units.*.conversion.required' => 'Kolom konversi sub satuan wajib diisi.',
            'sub_units.*.conversion.numeric' => 'Kolom konversi sub satuan harus berupa angka.',
            'sub_units.*.conversion.min' => 'Kolom konversi sub satuan tidak boleh kurang dari 0.',
        ];
    }
}
