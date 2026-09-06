<?php

namespace App\Http\Requests\Admin;

use App\Models\Fungsional;
use Illuminate\Foundation\Http\FormRequest;

class FungsionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('admin.fungsional.store')) {
            return $this->user()->can('create', Fungsional::class);
        }

        return $this->user()->can('update', $this->route('fungsional'));
    }

    public function rules(): array
    {
        return [
            'nama_fungsional' => ['required', 'string', 'max:255'],
            'angka_kredit' => ['required', 'numeric', 'min:0'],
            'pangkat' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
