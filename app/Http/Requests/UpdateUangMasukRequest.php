<?php

namespace App\Http\Requests;

use App\Models\UangMasuk;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUangMasukRequest extends FormRequest
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
        /** @var UangMasuk|null $income */
        $income = $this->route('income');

        if ($income?->isSubmission()) {
            return [
                'status' => ['required', 'in:approved,rejected'],
                'approval_note' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
        ];
    }
}
