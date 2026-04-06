<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUangKeluarRequest extends FormRequest
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
            'bukti' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:10240'],
        ];
    }
}
