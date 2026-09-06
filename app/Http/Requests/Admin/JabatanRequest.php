<?php

namespace App\Http\Requests\Admin;

use App\Models\Jabatan;
use Illuminate\Foundation\Http\FormRequest;

class JabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('admin.jabatan.store')) {
            return $this->user()->can('create', Jabatan::class);
        }

        return $this->user()->can('update', $this->route('jabatan'));
    }

    public function rules(): array
    {
        $jabatanId = $this->route('jabatan')?->id;

        return [
            'nama_jabatan' => ['required', 'string', 'max:255', "unique:jabatan,nama_jabatan,{$jabatanId}"],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'tj_transport' => ['required', 'numeric', 'min:0'],
            'uang_makan' => ['required', 'numeric', 'min:0'],
        ];
    }
}
