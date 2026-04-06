<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUangKeluarRequest extends FormRequest
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
        $routeName = (string) $this->route()?->getName();

        if (str_starts_with($routeName, 'keuangan.approval-pengeluaran.')) {
            return [
                'status' => ['required', 'in:approved,rejected'],
                'approval_note' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'bukti' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:10240'],
        ];
    }
}
