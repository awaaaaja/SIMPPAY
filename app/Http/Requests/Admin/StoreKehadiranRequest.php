<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKehadiranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id' => ['required', 'exists:pegawai,id'],
            'periode' => ['required', 'date_format:Y-m-d', 'date'],
            'hadir' => ['required', 'integer', 'min:0'],
            'sakit' => ['required', 'integer', 'min:0'],
            'alpha' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode.date_format' => 'Format periode harus YYYY-MM-01.',
            'periode.required' => 'Periode wajib diisi.',
            'pegawai_id.exists' => 'Pegawai tidak ditemukan.',
        ];
    }
}
