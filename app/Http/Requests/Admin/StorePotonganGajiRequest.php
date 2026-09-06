<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePotonganGajiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_potongan' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:nominal,persentase'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'is_alpha_penalty' => ['boolean'],
            'aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_potongan.required' => 'Nama potongan wajib diisi.',
            'tipe.in' => 'Tipe harus nominal atau persentase.',
            'nilai.required' => 'Nilai wajib diisi.',
            'nilai.min' => 'Nilai tidak boleh negatif.',
        ];
    }
}
