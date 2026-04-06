<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUangMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'jumlah' => 'jumlah',
            'deskripsi' => 'deskripsi',
            'tanggal' => 'tanggal',
        ];
    }
}
