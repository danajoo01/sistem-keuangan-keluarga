<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UangKeluar extends Model
{
    use HasFactory;

    protected $table = 'uang_keluar';

    protected $fillable = [
        'created_by',
        'approved_by',
        'jumlah',
        'deskripsi',
        'tanggal',
        'bukti_path',
        'bukti_original_name',
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
}
