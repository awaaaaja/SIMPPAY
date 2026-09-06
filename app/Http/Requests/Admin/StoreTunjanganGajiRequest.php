<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTunjanganGajiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_tunjangan' => ['required', 'string', 'max:255'],
            'target_tipe' => ['required', 'in:semua,jabatan,pegawai'],
            'jabatan_id' => ['nullable', 'required_if:target_tipe,jabatan', 'exists:jabatan,id'],
            'pegawai_id' => ['nullable', 'required_if:target_tipe,pegawai', 'exists:pegawai,id'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_tunjangan.required' => 'Nama tunjangan wajib diisi.',
            'target_tipe.in' => 'Target tipe harus semua, jabatan, atau pegawai.',
            'jabatan_id.required_if' => 'Jabatan wajib dipilih jika target = jabatan.',
            'jabatan_id.exists' => 'Jabatan tidak ditemukan.',
            'pegawai_id.required_if' => 'Pegawai wajib dipilih jika target = pegawai.',
            'pegawai_id.exists' => 'Pegawai tidak ditemukan.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.min' => 'Nominal tidak boleh negatif.',
        ];
    }
}
