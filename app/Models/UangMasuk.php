<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UangMasuk extends Model
{
    use HasFactory;

    protected $table = 'uang_masuk';

    protected $fillable = [
        'created_by',
        'approved_by',
        'source',
        'jumlah',
        'deskripsi',
        'tanggal',
        'status',
        'approval_note',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isSubmission(): bool
    {
        return $this->source === 'pengajuan';
    }
}
