<?php

namespace App\Http\Requests\Admin;

use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('admin.pegawai.store')) {
            return $this->user()->can('create', Pegawai::class);
        }

        return $this->user()->can('update', $this->route('pegawai'));
    }

    public function rules(): array
    {
        $pegawaiId = $this->route('pegawai')?->id;

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'jabatan_id' => ['nullable', 'exists:jabatan,id'],
            'struktural_id' => ['nullable', 'exists:struktural,id'],
            'fungsional_id' => ['nullable', 'exists:fungsional,id'],
            'nik' => ['required', 'string', 'max:255', "unique:pegawai,nik,{$pegawaiId}"],
            'nama_pegawai' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tanggal_masuk' => ['nullable', 'date'],
            'status_pegawai' => ['required', 'in:aktif,nonaktif,pensiun'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_sk' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'ktp' => ['nullable', 'string', 'max:255'],
            'nidn' => ['nullable', 'string', 'max:255'],
            'id_ptk' => ['nullable', 'string', 'max:255'],
            'nuptk' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'agama' => ['nullable', 'string', 'max:255'],
            'kewarganegaraan' => ['nullable', 'string', 'max:255'],
            'suku' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'tgl_lahir' => ['nullable', 'date'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'bidang_keahlian' => ['nullable', 'string', 'max:255'],
            'no_sk' => ['nullable', 'string', 'max:255'],
            'tgl_sk' => ['nullable', 'date'],
            'status_dosen' => ['nullable', 'in:dosen,bukan_dosen'],
            'ikatan_kerja' => ['nullable', 'string', 'max:255'],
            'status_kawin' => ['nullable', 'in:belum_kawin,kawin,cerai'],
            'nama_sm' => ['nullable', 'string', 'max:255'],
            'nip_sm' => ['nullable', 'string', 'max:255'],
            'nohp_sm' => ['nullable', 'string', 'max:255'],
            'pekerjaan_sm' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'masa_jabatan' => ['nullable', 'string', 'max:255'],
            'tgl_sk_jabatan' => ['nullable', 'date'],
            'create_user' => ['nullable', 'boolean'],
            'user_username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'user_password' => ['nullable', 'string', 'min:8'],
            'user_role' => ['nullable', 'in:pegawai,tendik,bpsdm'],
        ];
    }
}
