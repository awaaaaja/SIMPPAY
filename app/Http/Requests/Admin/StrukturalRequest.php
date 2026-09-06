<?php

namespace App\Http\Requests\Admin;

use App\Models\Struktural;
use Illuminate\Foundation\Http\FormRequest;

class StrukturalRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('admin.struktural.store')) {
            return $this->user()->can('create', Struktural::class);
        }

        return $this->user()->can('update', $this->route('struktural'));
    }

    public function rules(): array
    {
        return [
            'nama_struktural' => ['required', 'string', 'max:255'],
            'level_struktural' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ];
    }
}
